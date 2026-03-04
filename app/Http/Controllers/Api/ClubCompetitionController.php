<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubCompetitionMatch;
use App\Models\ClubCompetitionTeam;
use App\Services\ClubCompetitionService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ClubCompetitionController extends Controller
{
    public function __construct(private ClubCompetitionService $service)
    {
    }

    /**
     * Get competition teams.
     */
    public function teams(Club $club, ClubActivity $activity)
    {
        $this->authorize('view', $club);
        $this->validateActivityBelongsToClub($club, $activity);

        $teams = $activity->competitionTeams()->orderBy('id')->get();

        return response()->json(['teams' => $teams]);
    }

    /**
     * Add team.
     */
    public function addTeam(Request $request, Club $club, ClubActivity $activity)
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
                'message' => 'Tên đội đã tồn tại.',
            ], 422);
        }

        $team = $activity->competitionTeams()->create(
            array_merge($validated, ['status' => 'active'])
        );

        return response()->json([
            'success' => true,
            'team' => $team,
            'message' => 'Đã thêm đội thành công.',
        ], 201);
    }

    /**
     * Remove team.
     */
    public function removeTeam(Club $club, ClubActivity $activity, ClubCompetitionTeam $team)
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
     * Generate schedule.
     */
    public function generateSchedule(Request $request, Club $club, ClubActivity $activity)
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
     * Save match score.
     */
    public function saveScore(
        Request $request,
        Club $club,
        ClubActivity $activity,
        ClubCompetitionMatch $match
    ) {
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
     * Get standings.
     */
    public function standings(Club $club, ClubActivity $activity)
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
     * Get matches.
     */
    public function matches(Club $club, ClubActivity $activity)
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
