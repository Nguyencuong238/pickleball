<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivityMatch;
use App\Services\ClubMatchmakingService;
use App\Services\ClubScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubOpenPlayController extends Controller
{
    public function queue(Club $club, ClubActivity $activity): View
    {
        $this->validateActivity($club, $activity);
        return view('front.clubs.queue', compact('club', 'activity'));
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
            return response()->json(['success' => false, 'message' => 'Không đủ người chơi hoặc sân trống.'], 422);
        }
        return response()->json(['success' => true, 'matches_created' => $matches->count(), 'message' => "Đã tạo {$matches->count()} trận đấu."]);
    }

    public function startMatch(Club $club, ClubActivity $activity, ClubActivityMatch $match): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);
        $match->update(['started_at' => now(), 'status' => 'in_progress']);
        return response()->json(['success' => true]);
    }

    public function endMatch(Club $club, ClubActivity $activity, ClubActivityMatch $match, Request $request): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);

        if ($request->boolean('skip_score')) {
            app(ClubMatchmakingService::class)->completeMatch($match);
            return response()->json(['success' => true, 'message' => 'Trận đấu đã kết thúc (không có điểm).']);
        }

        return response()->json([
            'success' => true,
            'redirect_to_score' => true,
            'score_url' => route('club.activity.score-form', [$club->slug, $activity->id, $match->id]),
        ]);
    }

    public function playerEndMatch(Club $club, ClubActivity $activity, ClubActivityMatch $match): JsonResponse
    {
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);

        $userId = auth()->id() ?? session('checkin_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }
        $isPlayer = in_array($userId, [$match->player1_id, $match->player2_id, $match->player3_id, $match->player4_id]);

        if (!$isPlayer) {
            return response()->json(['success' => false, 'message' => 'Bạn không ở trong trận này.'], 403);
        }
        if ($match->ended_at) {
            return response()->json(['success' => false, 'message' => 'Trận đấu đã kết thúc.'], 422);
        }

        return response()->json([
            'success' => true,
            'score_url' => route('club.activity.score-form', [$club->slug, $activity->id, $match->id]),
        ]);
    }

    public function myMatch(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->validateActivity($club, $activity);
        $userId = auth()->id() ?? session('checkin_user_id');
        if (!$userId) return response()->json(['match' => null]);

        $match = $activity->matches()
            ->whereNotNull('started_at')->whereNull('ended_at')
            ->where(fn($q) => $q->where('player1_id', $userId)->orWhere('player2_id', $userId)->orWhere('player3_id', $userId)->orWhere('player4_id', $userId))
            ->with(['player1:id,name', 'player2:id,name', 'player3:id,name', 'player4:id,name'])
            ->first();

        return response()->json(['match' => $match]);
    }

    public function scoreForm(Club $club, ClubActivity $activity, ClubActivityMatch $match): View
    {
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);
        $match->load(['player1:id,name', 'player2:id,name', 'player3:id,name', 'player4:id,name']);

        $userId = auth()->id() ?? session('checkin_user_id');
        $isAdmin = auth()->check() && $club->isManagement(auth()->user());
        $isPending = $match->score_status === 'pending_confirmation';
        $canConfirm = $isPending && (
            $isAdmin || in_array($userId, $match->getOpposingTeamPlayerIds($match->result_submitted_by ?? 0))
        );

        return view('front.clubs.score-submit', [
            'club' => $club, 'activity' => $activity, 'match' => $match,
            'isAdmin' => $isAdmin, 'mode' => $canConfirm ? 'confirm' : 'submit',
        ]);
    }

    public function submitScore(Club $club, ClubActivity $activity, ClubActivityMatch $match, Request $request): JsonResponse
    {
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);

        $validated = $request->validate([
            'set_scores' => 'required|array|min:1|max:' . ($activity->best_of ?? 1),
            'set_scores.*.team1' => 'required|integer|min:0|max:' . ($activity->points_per_set ?? 21),
            'set_scores.*.team2' => 'required|integer|min:0|max:' . ($activity->points_per_set ?? 21),
        ]);

        $userId = auth()->id() ?? session('checkin_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }
        $isPlayer = in_array($userId, [$match->player1_id, $match->player2_id, $match->player3_id, $match->player4_id]);
        $isAdmin = auth()->check() && $club->isManagement(auth()->user());

        if (!$isPlayer && !$isAdmin) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền cập nhật điểm trận này.'], 403);
        }
        if ($match->result_confirmed) {
            return response()->json(['success' => false, 'message' => 'Điểm đã được nhập trước đó.'], 422);
        }
        if ($match->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Trận đấu đã kết thúc bởi admin.'], 422);
        }
        if ($match->score_status === 'pending_confirmation') {
            return response()->json(['success' => false, 'message' => 'Điểm đã được nhập, đang chờ xác nhận.', 'redirect' => route('club.activity.score-form', [$club->slug, $activity->id, $match->id])], 422);
        }
        foreach ($validated['set_scores'] as $set) {
            if ($set['team1'] === $set['team2']) {
                return response()->json(['success' => false, 'message' => 'Mỗi set phải có đội thắng (không được hòa).'], 422);
            }
        }

        $scoreService = app(ClubScoreService::class);
        if ($isAdmin) {
            $scoreService->adminSubmitScore($match, $validated['set_scores'], $userId);
            return response()->json(['success' => true, 'message' => 'Điểm đã được cập nhật.']);
        }

        $scoreService->playerSubmitScore($match, $validated['set_scores'], $userId);
        return response()->json(['success' => true, 'message' => 'Điểm đã được gửi, đang chờ đội còn lại xác nhận.']);
    }

    public function confirmScore(Club $club, ClubActivity $activity, ClubActivityMatch $match, Request $request): JsonResponse
    {
        $this->validateActivity($club, $activity);
        $this->validateMatchBelongsToActivity($match, $activity);

        $userId = auth()->id() ?? session('checkin_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }
        $isAdmin = auth()->check() && $club->isManagement(auth()->user());
        $opposingIds = $match->getOpposingTeamPlayerIds($match->result_submitted_by ?? 0);

        if (!$isAdmin && !in_array($userId, $opposingIds)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xác nhận.'], 403);
        }
        if ($match->score_status !== 'pending_confirmation') {
            return response()->json(['success' => false, 'message' => 'Điểm đã được xử lý.'], 422);
        }

        $scoreService = app(ClubScoreService::class);
        if ($request->input('action') === 'reject') {
            $scoreService->rejectScore($match);
            return response()->json(['success' => true, 'message' => 'Điểm đã bị từ chối. Người nhập có thể nhập lại.']);
        }

        $scoreService->confirmScore($match, $userId, $isAdmin);
        return response()->json(['success' => true, 'message' => 'Điểm đã được xác nhận.']);
    }

    private function getCourtStatus(ClubActivity $activity): array
    {
        $courts = [];
        for ($i = 1; $i <= $activity->courts_count; $i++) {
            $match = $activity->matches()->where('scheduled_court', $i)->whereNotNull('started_at')->whereNull('ended_at')
                ->with(['player1:id,name', 'player2:id,name', 'player3:id,name', 'player4:id,name'])->first();
            $courts[] = ['court' => $i, 'status' => $match ? 'playing' : 'available', 'match' => $match];
        }
        return $courts;
    }

    private function getMyStatus(ClubActivity $activity, ?int $userId): ?array
    {
        if (!$userId) return null;
        $participant = $activity->participants()->where('user_id', $userId)->first();
        if (!$participant) return null;

        $currentMatchId = null;
        if ($participant->current_status === 'playing') {
            $currentMatch = $activity->matches()->whereNotNull('started_at')->whereNull('ended_at')
                ->where(fn($q) => $q->where('player1_id', $userId)->orWhere('player2_id', $userId)->orWhere('player3_id', $userId)->orWhere('player4_id', $userId))
                ->first();
            $currentMatchId = $currentMatch?->id;
        }

        $pendingMatch = $activity->matches()->where('score_status', 'pending_confirmation')
            ->where(fn($q) => $q->where('player1_id', $userId)->orWhere('player2_id', $userId)->orWhere('player3_id', $userId)->orWhere('player4_id', $userId))
            ->first();

        $rejectedMatch = $activity->matches()->where('score_status', 'rejected')->where('result_submitted_by', $userId)->first();

        return [
            'current_status' => $participant->current_status,
            'queue_position' => $participant->queue_position,
            'matches_played' => $participant->matches_played_count,
            'user_id' => $userId,
            'current_match_id' => $currentMatchId,
            'pending_score_match_id' => $pendingMatch?->id,
            'can_confirm_score' => $pendingMatch && in_array($userId, $pendingMatch->getOpposingTeamPlayerIds($pendingMatch->result_submitted_by ?? 0)),
            'rejected_match_id' => $rejectedMatch?->id,
        ];
    }

    private function validateActivity(Club $club, ClubActivity $activity): void
    {
        if ($activity->club_id !== $club->id) abort(404, 'Không tìm thấy hoạt động');
    }

    private function validateMatchBelongsToActivity(ClubActivityMatch $match, ClubActivity $activity): void
    {
        if ($match->club_activity_id !== $activity->id) abort(404, 'Không tìm thấy trận đấu');
    }
}
