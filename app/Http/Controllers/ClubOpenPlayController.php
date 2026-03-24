<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivityMatch;
use App\Services\ClubMatchmakingService;
use App\Services\ClubMatchService;
use App\Services\ClubMemberStatsService;
use App\Services\EloService;
use App\Services\OprsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClubOpenPlayController extends Controller
{
    public function queue(Club $club, ClubActivity $activity): View
    {
        $this->validateActivity($club, $activity);

        return view('front.clubs.queue', [
            'club' => $club,
            'activity' => $activity,
        ]);
    }

    public function queueStatus(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->validateActivity($club, $activity);

        $userId = auth()->id() ?? session('checkin_user_id');

        return response()->json([
            'queue' => $activity->participants()
                ->with('user:id,name,avatar,total_oprs,opr_level')
                ->whereIn('current_status', ['queued', 'playing'])
                ->orderBy('queue_position')
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'user_id' => $p->user_id,
                    'name' => $p->user->name ?? '',
                    'avatar' => $p->user->avatar ?? null,
                    'oprs' => $p->user->total_oprs ?? 0,
                    'status' => $p->current_status,
                    'queue_position' => $p->queue_position,
                ]),
            'courts' => $this->getCourtStatus($activity),
            'my_status' => $this->getMyStatus($activity, $userId),
        ]);
    }

    public function triggerMatch(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivity($club, $activity);

        $matches = app(ClubMatchmakingService::class)->generateMatches($activity);

        if ($matches->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không đủ người chơi hoặc sân trống.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'matches_created' => $matches->count(),
            'message' => "Đã tạo {$matches->count()} trận đấu.",
        ]);
    }

    public function startMatch(Club $club, ClubActivity $activity, ClubActivityMatch $match): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);

        $match->update(['started_at' => now(), 'status' => 'in_progress']);

        return response()->json(['success' => true]);
    }

    public function endMatch(Club $club, ClubActivity $activity, ClubActivityMatch $match): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);

        app(ClubMatchmakingService::class)->completeMatch($match);

        return response()->json(['success' => true, 'message' => 'Trận đấu đã kết thúc.']);
    }

    public function myMatch(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->validateActivity($club, $activity);

        $userId = auth()->id() ?? session('checkin_user_id');
        if (!$userId) {
            return response()->json(['match' => null]);
        }

        $match = $activity->matches()
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->where(function ($q) use ($userId) {
                $q->where('player1_id', $userId)
                  ->orWhere('player2_id', $userId)
                  ->orWhere('player3_id', $userId)
                  ->orWhere('player4_id', $userId);
            })
            ->with(['player1:id,name', 'player2:id,name', 'player3:id,name', 'player4:id,name'])
            ->first();

        return response()->json(['match' => $match]);
    }

    public function scoreForm(Club $club, ClubActivity $activity, ClubActivityMatch $match): View
    {
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);

        $match->load(['player1:id,name', 'player2:id,name', 'player3:id,name', 'player4:id,name']);

        return view('front.clubs.score-submit', [
            'club' => $club,
            'activity' => $activity,
            'match' => $match,
        ]);
    }

    public function submitScore(Club $club, ClubActivity $activity, ClubActivityMatch $match, Request $request): JsonResponse
    {
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);

        $validated = $request->validate([
            'set_scores' => 'required|array|min:2|max:3',
            'set_scores.*.team1' => 'required|integer|min:0|max:21',
            'set_scores.*.team2' => 'required|integer|min:0|max:21',
        ]);

        // Validate submitter is a player
        $userId = auth()->id() ?? session('checkin_user_id');
        $playerIds = [$match->player1_id, $match->player2_id, $match->player3_id, $match->player4_id];
        if (!in_array($userId, $playerIds)) {
            return response()->json(['success' => false, 'message' => 'Bạn không phải người chơi trong trận này.'], 403);
        }

        if ($match->result_confirmed) {
            return response()->json(['success' => false, 'message' => 'Điểm đã được nhập trước đó.'], 422);
        }

        // Validate each set has a winner (no ties)
        foreach ($validated['set_scores'] as $set) {
            if ($set['team1'] === $set['team2']) {
                return response()->json(['success' => false, 'message' => 'Mỗi set phải có đội thắng (không được hòa).'], 422);
            }
        }

        DB::transaction(function () use ($match, $validated, $userId) {
            $setScores = $validated['set_scores'];
            $winner = $this->determineWinner($setScores);

            $match->update([
                'set_scores' => $setScores,
                'result_submitted_by' => $userId,
                'result_confirmed' => true,
                'ended_at' => $match->ended_at ?? now(),
                'status' => 'completed',
            ]);

            // Calculate total scores for compatibility
            $totalT1 = collect($setScores)->sum('team1');
            $totalT2 = collect($setScores)->sum('team2');
            $match->update([
                'team1_score' => $totalT1,
                'team2_score' => $totalT2,
            ]);

            // Process Elo if enabled
            if ($match->activity->oprs_weight > 0 && !$match->oprs_processed) {
                app(EloService::class)->processClubMatchElo($match, $winner);
                app(OprsService::class)->recalculateAfterClubMatch($match);
                $match->update(['oprs_processed' => true]);
            }

            // Return players to queue (skip if already completed by admin)
            if ($match->fresh()->status !== 'completed') {
                app(ClubMatchmakingService::class)->completeMatch($match);
            }

            // Update club member stats
            app(ClubMemberStatsService::class)->updateAfterMatch($match, $winner);
        });

        return response()->json(['success' => true, 'message' => 'Điểm đã được cập nhật.']);
    }

    private function determineWinner(array $setScores): string
    {
        $t1Wins = collect($setScores)->filter(fn($s) => $s['team1'] > $s['team2'])->count();
        $t2Wins = collect($setScores)->filter(fn($s) => $s['team2'] > $s['team1'])->count();
        return $t1Wins > $t2Wins ? 'team1' : 'team2';
    }

    private function getCourtStatus(ClubActivity $activity): array
    {
        $courts = [];
        for ($i = 1; $i <= $activity->courts_count; $i++) {
            $match = $activity->matches()
                ->where('scheduled_court', $i)
                ->whereNotNull('started_at')
                ->whereNull('ended_at')
                ->with(['player1:id,name', 'player2:id,name', 'player3:id,name', 'player4:id,name'])
                ->first();

            $courts[] = [
                'court' => $i,
                'status' => $match ? 'playing' : 'available',
                'match' => $match,
            ];
        }
        return $courts;
    }

    private function getMyStatus(ClubActivity $activity, ?int $userId): ?array
    {
        if (!$userId) return null;

        $participant = $activity->participants()
            ->where('user_id', $userId)
            ->first();

        if (!$participant) return null;

        $currentMatchId = null;
        if ($participant->current_status === 'playing') {
            $currentMatch = $activity->matches()
                ->whereNotNull('started_at')
                ->whereNull('ended_at')
                ->where(function ($q) use ($userId) {
                    $q->where('player1_id', $userId)
                      ->orWhere('player2_id', $userId)
                      ->orWhere('player3_id', $userId)
                      ->orWhere('player4_id', $userId);
                })
                ->first();
            $currentMatchId = $currentMatch?->id;
        }

        return [
            'current_status' => $participant->current_status,
            'queue_position' => $participant->queue_position,
            'matches_played' => $participant->matches_played_count,
            'user_id' => $userId,
            'current_match_id' => $currentMatchId,
        ];
    }

    private function validateActivity(Club $club, ClubActivity $activity): void
    {
        if ($activity->club_id !== $club->id) {
            abort(404, 'Không tìm thấy hoạt động');
        }
    }

    private function validateMatchBelongsToActivity(ClubActivityMatch $match, ClubActivity $activity): void
    {
        if ($match->club_activity_id !== $activity->id) {
            abort(404, 'Không tìm thấy trận đấu');
        }
    }
}
