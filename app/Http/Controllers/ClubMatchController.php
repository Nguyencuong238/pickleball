<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivityMatch;
use App\Models\ClubActivityMatchRound;
use App\Services\ClubMatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ClubMatchController extends Controller
{
    public function __construct(private ClubMatchService $service)
    {
    }

    /**
     * GET matches/rounds — load all rounds with matches + player names.
     */
    public function index(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('view', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $rounds = $activity->matchRounds()
            ->with(['matches.player1', 'matches.player2', 'matches.player3', 'matches.player4'])
            ->orderBy('round_number')
            ->get()
            ->map(fn ($round) => $this->formatRound($round));

        // For open play: include matches without rounds as a virtual round
        if ($activity->isOpenPlay()) {
            $orphanMatches = $activity->matches()
                ->whereNull('round_id')
                ->with(['player1', 'player2', 'player3', 'player4'])
                ->orderBy('id')
                ->get();

            if ($orphanMatches->isNotEmpty()) {
                $rounds->push([
                    'id' => null,
                    'round_number' => $rounds->count() + 1,
                    'status' => 'completed',
                    'matches' => $orphanMatches->map(fn ($m) => $this->formatMatch($m)),
                ]);
            }
        }

        return response()->json(['rounds' => $rounds]);
    }

    /**
     * POST matches/generate — generate match rounds.
     */
    public function generate(Request $request, Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $validated = $request->validate([
            'format' => 'required|in:rotating_doubles,fixed_doubles,singles_rr',
            'court_count' => 'required|integer|min:1|max:10',
        ]);

        try {
            $this->service->generateMatches($activity, $validated['format'], $validated['court_count']);
            return response()->json(['success' => true, 'message' => 'Đã tạo trận đấu thành công.']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * PUT matches/rounds/{round}/complete — mark round as completed.
     */
    public function completeRound(Club $club, ClubActivity $activity, ClubActivityMatchRound $round): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        if ($round->club_activity_id !== $activity->id) {
            return response()->json(['message' => 'Không tìm thấy vòng đấu'], 404);
        }

        $round->update(['status' => 'completed']);

        return response()->json(['success' => true, 'message' => 'Đã hoàn thành vòng đấu.']);
    }

    /**
     * DELETE matches/rounds — delete all rounds (cascade deletes matches).
     */
    public function reset(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $activity->matchRounds()->delete();
        $activity->matchStandings()->delete();

        return response()->json(['success' => true, 'message' => 'Đã xoá tất cả trận đấu.']);
    }

    /**
     * PUT matches/{match}/score — save score for a match.
     */
    public function saveScore(
        Request $request,
        Club $club,
        ClubActivity $activity,
        ClubActivityMatch $match
    ): JsonResponse {
        $this->authorize('manageActivity', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        if ($match->round->club_activity_id !== $activity->id) {
            return response()->json(['message' => 'Không tìm thấy trận đấu'], 404);
        }

        $validated = $request->validate([
            'team1_score' => 'required|integer|min:0|max:99',
            'team2_score' => 'required|integer|min:0|max:99',
        ]);

        try {
            $this->service->saveScore($activity, $match, $validated['team1_score'], $validated['team2_score']);
            return response()->json(['success' => true, 'message' => 'Đã cập nhật điểm số.']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET matches/standings — individual standings.
     */
    public function standings(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('view', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        return response()->json(['standings' => $this->service->getStandings($activity)]);
    }

    /**
     * POST matches/custom — create a custom ad-hoc match.
     */
    public function createCustom(Request $request, Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $validated = $request->validate([
            'match_type' => 'required|in:singles,doubles',
            'player1_id' => 'required|exists:users,id',
            'player2_id' => 'nullable|required_if:match_type,doubles|exists:users,id',
            'player3_id' => 'required|exists:users,id',
            'player4_id' => 'nullable|required_if:match_type,doubles|exists:users,id',
            'court_number' => 'nullable|integer|min:1|max:20',
            'round_id' => 'nullable|exists:club_activity_match_rounds,id',
        ]);

        try {
            $match = $this->service->createCustomMatch(
                $activity,
                $validated['match_type'],
                $validated['player1_id'],
                $validated['player2_id'] ?? null,
                $validated['player3_id'],
                $validated['player4_id'] ?? null,
                $validated['court_number'] ?? null,
                $validated['round_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'match' => $match,
                'round' => $match->round,
                'message' => 'Đã thêm trận đấu tuỳ chỉnh.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function formatRound(ClubActivityMatchRound $round): array
    {
        return [
            'id' => $round->id,
            'round_number' => $round->round_number,
            'status' => $round->status,
            'matches' => $round->matches->map(fn ($m) => $this->formatMatch($m)),
        ];
    }

    private function formatMatch(ClubActivityMatch $m): array
    {
        return [
            'id' => $m->id,
            'court_number' => $m->court_number,
            'match_type' => $m->match_type,
            'status' => $m->status,
            'team1_score' => $m->team1_score,
            'team2_score' => $m->team2_score,
            'players' => [
                'player1' => $m->player1 ? ['id' => $m->player1->id, 'name' => $m->player1->name] : null,
                'player2' => $m->player2 ? ['id' => $m->player2->id, 'name' => $m->player2->name] : null,
                'player3' => $m->player3 ? ['id' => $m->player3->id, 'name' => $m->player3->name] : null,
                'player4' => $m->player4 ? ['id' => $m->player4->id, 'name' => $m->player4->name] : null,
            ],
        ];
    }

    private function validateActivityBelongsToClub(Club $club, ClubActivity $activity): void
    {
        if ($activity->club_id !== $club->id) {
            abort(404, 'Khong tim thay hoat dong');
        }
    }
}
