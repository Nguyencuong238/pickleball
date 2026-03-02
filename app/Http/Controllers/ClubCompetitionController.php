<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubCompetitionMatch;
use App\Models\ClubCompetitionTeam;
use App\Services\ClubCompetitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ClubCompetitionController extends Controller
{
    public function __construct(private ClubCompetitionService $service)
    {
    }

    /**
     * GET /clubs/{club}/activities/{activity}/competition/teams
     */
    public function teams(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('view', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $teams = $activity->competitionTeams()->orderBy('id')->get();

        return response()->json(['teams' => $teams]);
    }

    /**
     * POST /clubs/{club}/activities/{activity}/competition/teams
     */
    public function addTeam(Request $request, Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'captain_user_id' => 'nullable|exists:users,id',
        ]);

        $exists = $activity->competitionTeams()
            ->where('name', $validated['name'])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ten doi da ton tai.',
            ], 422);
        }

        $team = $activity->competitionTeams()->create(
            array_merge($validated, ['status' => 'active'])
        );

        return response()->json([
            'success' => true,
            'team' => $team,
            'message' => 'Đã thêm đội thành công.',
        ]);
    }

    /**
     * DELETE /clubs/{club}/activities/{activity}/competition/teams/{team}
     */
    public function removeTeam(Club $club, ClubActivity $activity, ClubCompetitionTeam $team): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        if ($team->club_activity_id !== $activity->id) {
            return response()->json(['message' => 'Không tìm thấy đội'], 404);
        }

        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa đội.',
        ]);
    }

    /**
     * POST /clubs/{club}/activities/{activity}/competition/generate-schedule
     */
    public function generateSchedule(Request $request, Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $validated = $request->validate([
            'format' => 'required|in:round_robin,pool_play,single_elimination',
        ]);

        try {
            $this->service->generateSchedule($activity, $validated['format']);
            $this->service->initializeStandings($activity);

            return response()->json([
                'success' => true,
                'message' => 'Lịch thi đấu đã được tạo.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * PUT /clubs/{club}/activities/{activity}/competition/matches/{match}/score
     */
    public function saveScore(
        Request $request,
        Club $club,
        ClubActivity $activity,
        ClubCompetitionMatch $match
    ): JsonResponse {
        $this->authorize('manageActivity', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        if ($match->club_activity_id !== $activity->id) {
            return response()->json(['message' => 'Không tìm thấy trận đấu'], 404);
        }

        $validated = $request->validate([
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
        ]);

        try {
            $this->service->saveMatchScore($match, $validated['home_score'], $validated['away_score']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật điểm số.',
        ]);
    }

    /**
     * GET /clubs/{club}/activities/{activity}/competition/standings
     */
    public function standings(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('view', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $standings = $activity->competitionStandings()
            ->with('team')
            ->orderByDesc('points')
            ->orderByDesc('wins')
            ->get();

        return response()->json(['standings' => $standings]);
    }

    /**
     * GET /clubs/{club}/activities/{activity}/competition/matches
     */
    public function matches(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('view', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $matches = $activity->competitionMatches()
            ->with(['homeTeam', 'awayTeam', 'winnerTeam'])
            ->orderBy('round_number')
            ->get();

        return response()->json(['matches' => $matches]);
    }

    private function validateActivityBelongsToClub(Club $club, ClubActivity $activity): void
    {
        if ($activity->club_id !== $club->id) {
            abort(404, 'Không tìm thấy hoạt động');
        }
    }
}
