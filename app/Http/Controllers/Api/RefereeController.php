<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\MatchEvent;
use App\Models\ActivityLog;
use App\Models\GroupStanding;
use App\Models\TournamentAthlete;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefereeController extends Controller
{
    /**
     * Get dashboard stats and upcoming matches
     */
    public function dashboard(Request $request): JsonResponse
    {
        $referee = $request->user();

        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Referee role required.',
            ], 403);
        }

        $stats = [
            'total_matches' => $referee->refereeMatches()->count(),
            'completed_matches' => $referee->refereeMatches()->where('status', 'completed')->count(),
            'upcoming_matches' => $referee->refereeMatches()
                ->where('status', 'scheduled')
                ->where('match_date', '>=', now()->toDateString())
                ->count(),
            'tournaments' => $referee->refereeTournaments()->count(),
        ];

        $upcomingMatches = $referee->refereeMatches()
            ->with(['tournament', 'athlete1', 'athlete2', 'category', 'court'])
            ->where('status', 'scheduled')
            ->where('match_date', '>=', now()->toDateString())
            ->orderBy('match_date')
            ->orderBy('match_time')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'upcoming_matches' => $upcomingMatches,
            ],
        ]);
    }

    /**
     * List assigned matches with filters
     */
    public function matches(Request $request): JsonResponse
    {
        $referee = $request->user();

        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Referee role required.',
            ], 403);
        }

        $query = $referee->refereeMatches()
            ->with(['tournament', 'athlete1', 'athlete2', 'category', 'court']);

        if ($request->filled('tournament_id')) {
            $query->where('tournament_id', $request->tournament_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('match_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('match_date', '<=', $request->date_to);
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $matches = $query->orderBy('match_date', 'desc')
            ->orderBy('match_time', 'desc')
            ->paginate($perPage);

        $tournaments = $referee->refereeTournaments;

        return response()->json([
            'success' => true,
            'data' => [
                'matches' => $matches,
                'tournaments' => $tournaments,
            ],
        ]);
    }

    /**
     * Show match detail
     */
    public function showMatch(MatchModel $match, Request $request): JsonResponse
    {
        $referee = $request->user();

        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Referee role required.',
            ], 403);
        }

        if (!$match->isAssignedToReferee($referee)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this match.',
            ], 403);
        }

        $match->load([
            'tournament',
            'category',
            'round',
            'court',
            'athlete1',
            'athlete2',
            'winner',
        ]);

        return response()->json([
            'success' => true,
            'data' => $match,
        ]);
    }

    /**
     * Start a match
     */
    public function startMatch(MatchModel $match, Request $request): JsonResponse
    {
        $referee = $request->user();

        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Referee role required.',
            ], 403);
        }

        if (!$match->isAssignedToReferee($referee)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this match.',
            ], 403);
        }

        if (!in_array($match->status, ['scheduled', 'ready'])) {
            return response()->json([
                'success' => false,
                'message' => 'Match cannot be started. Current status: ' . $match->status,
            ], 400);
        }

        $match->update([
            'status' => 'in_progress',
            'actual_start_time' => now(),
        ]);

        ActivityLog::log("Match #{$match->id} started by referee via API", 'Match', $match->id);

        return response()->json([
            'success' => true,
            'message' => 'Match started successfully.',
            'data' => $match->fresh(),
        ]);
    }

    /**
     * Update match scores
     */
    public function updateScore(Request $request, MatchModel $match): JsonResponse
    {
        $referee = $request->user();

        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Referee role required.',
            ], 403);
        }

        if (!$match->isAssignedToReferee($referee)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this match.',
            ], 403);
        }

        if ($match->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit completed match.',
            ], 400);
        }

        $validated = $request->validate([
            'set_scores' => 'required|array|min:1',
            'set_scores.*.set' => 'required|integer|min:1',
            'set_scores.*.athlete1' => 'required|integer|min:0',
            'set_scores.*.athlete2' => 'required|integer|min:0',
            'status' => 'required|in:in_progress,completed',
        ]);

        try {
            DB::beginTransaction();

            $winnerId = $this->calculateWinner($validated['set_scores'], $match);
            $finalScore = $this->formatFinalScore($validated['set_scores']);

            $match->update([
                'set_scores' => $validated['set_scores'],
                'final_score' => $finalScore,
                'winner_id' => $validated['status'] === 'completed' ? $winnerId : null,
                'status' => $validated['status'],
                'actual_end_time' => $validated['status'] === 'completed' ? now() : null,
            ]);

            if ($validated['status'] === 'completed' && $match->athlete1_id && $match->athlete2_id) {
                $this->updateGroupStandingsAndAthleteStats($match, $validated['set_scores']);
            }

            DB::commit();

            ActivityLog::log("Match #{$match->id} score updated via API: {$finalScore}", 'Match', $match->id);

            return response()->json([
                'success' => true,
                'message' => 'Score updated successfully.',
                'data' => $match->fresh()->load(['tournament', 'athlete1', 'athlete2', 'winner']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Score update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update score.',
            ], 500);
        }
    }

    /**
     * Calculate winner from set scores
     */
    private function calculateWinner(array $setScores, MatchModel $match): ?int
    {
        $athlete1Sets = 0;
        $athlete2Sets = 0;

        foreach ($setScores as $set) {
            if ($set['athlete1'] > $set['athlete2']) {
                $athlete1Sets++;
            } elseif ($set['athlete2'] > $set['athlete1']) {
                $athlete2Sets++;
            }
        }

        if ($athlete1Sets > $athlete2Sets) {
            return $match->athlete1_id;
        } elseif ($athlete2Sets > $athlete1Sets) {
            return $match->athlete2_id;
        }

        return null;
    }

    /**
     * Format final score string
     */
    private function formatFinalScore(array $setScores): string
    {
        $scores = [];
        foreach ($setScores as $set) {
            $scores[] = $set['athlete1'] . '-' . $set['athlete2'];
        }
        return implode(', ', $scores);
    }

    /**
     * Update group standings and tournament athlete statistics
     */
    private function updateGroupStandingsAndAthleteStats(MatchModel $match, array $setScores): void
    {
        try {
            $setsWonAthlete1 = 0;
            $setsWonAthlete2 = 0;

            foreach ($setScores as $set) {
                if ($set['athlete1'] > $set['athlete2']) {
                    $setsWonAthlete1++;
                } elseif ($set['athlete2'] > $set['athlete1']) {
                    $setsWonAthlete2++;
                }
            }

            if ($match->group_id) {
                $this->updateGroupStandingsWithSets($match, $setsWonAthlete1, $setsWonAthlete2);
            }

            $this->updateTournamentAthleteStats($match, $setsWonAthlete1, $setsWonAthlete2);

            Log::info('Group standings and athlete stats updated by referee via API', [
                'match_id' => $match->id,
                'sets_won_athlete1' => $setsWonAthlete1,
                'sets_won_athlete2' => $setsWonAthlete2,
            ]);
        } catch (\Exception $e) {
            Log::error('Update group standings error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update group standings with set information
     */
    private function updateGroupStandingsWithSets(MatchModel $match, int $setsWonAthlete1, int $setsWonAthlete2): void
    {
        try {
            $athlete1Id = $match->athlete1_id;
            $athlete2Id = $match->athlete2_id;
            $groupId = $match->group_id;

            $standing1 = GroupStanding::firstOrCreate(
                ['group_id' => $groupId, 'athlete_id' => $athlete1Id],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                ]
            );

            $standing2 = GroupStanding::firstOrCreate(
                ['group_id' => $groupId, 'athlete_id' => $athlete2Id],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                ]
            );

            if ($setsWonAthlete1 > $setsWonAthlete2) {
                $standing1->updateAfterMatch(true, $setsWonAthlete1, $setsWonAthlete2, 0, 0);
                $standing2->updateAfterMatch(false, $setsWonAthlete2, $setsWonAthlete1, 0, 0);
            } elseif ($setsWonAthlete2 > $setsWonAthlete1) {
                $standing1->updateAfterMatch(false, $setsWonAthlete1, $setsWonAthlete2, 0, 0);
                $standing2->updateAfterMatch(true, $setsWonAthlete2, $setsWonAthlete1, 0, 0);
            } else {
                $standing1->update([
                    'matches_played' => $standing1->matches_played + 1,
                    'matches_drawn' => $standing1->matches_drawn + 1,
                    'sets_won' => $standing1->sets_won + $setsWonAthlete1,
                    'sets_lost' => $standing1->sets_lost + $setsWonAthlete2,
                ]);
                $standing2->update([
                    'matches_played' => $standing2->matches_played + 1,
                    'matches_drawn' => $standing2->matches_drawn + 1,
                    'sets_won' => $standing2->sets_won + $setsWonAthlete2,
                    'sets_lost' => $standing2->sets_lost + $setsWonAthlete1,
                ]);
            }

            $this->recalculateGroupRankings($groupId);

            Log::info('Group standings updated with sets via API', [
                'group_id' => $groupId,
                'match_id' => $match->id,
                'sets_won_athlete1' => $setsWonAthlete1,
                'sets_won_athlete2' => $setsWonAthlete2,
            ]);
        } catch (\Exception $e) {
            Log::error('Update group standings error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update tournament athlete statistics (supports singles and doubles)
     */
    private function updateTournamentAthleteStats(MatchModel $match, int $setsWonAthlete1, int $setsWonAthlete2): void
    {
        try {
            $athlete1Id = $match->athlete1_id;
            $athlete2Id = $match->athlete2_id;

            if (!$athlete1Id || !$athlete2Id) {
                return;
            }

            // Get both athletes with partner relationship
            $athlete1 = TournamentAthlete::with('partner')->find($athlete1Id);
            $athlete2 = TournamentAthlete::with('partner')->find($athlete2Id);

            if (!$athlete1 || !$athlete2) {
                return;
            }

            // Determine winner
            $athlete1Wins = $setsWonAthlete1 > $setsWonAthlete2;
            $athlete2Wins = $setsWonAthlete2 > $setsWonAthlete1;

            // Update athlete1 statistics
            $athlete1->matches_played = ($athlete1->matches_played ?? 0) + 1;
            $athlete1->sets_won = ($athlete1->sets_won ?? 0) + $setsWonAthlete1;
            $athlete1->sets_lost = ($athlete1->sets_lost ?? 0) + $setsWonAthlete2;

            if ($athlete1Wins) {
                $athlete1->matches_won = ($athlete1->matches_won ?? 0) + 1;
            } elseif (!$athlete2Wins) {
                // Draw - khong tinh vao matches_won hay matches_lost
            } else {
                $athlete1->matches_lost = ($athlete1->matches_lost ?? 0) + 1;
            }

            $athlete1->save();

            // Update athlete2 statistics
            $athlete2->matches_played = ($athlete2->matches_played ?? 0) + 1;
            $athlete2->sets_won = ($athlete2->sets_won ?? 0) + $setsWonAthlete2;
            $athlete2->sets_lost = ($athlete2->sets_lost ?? 0) + $setsWonAthlete1;

            if ($athlete2Wins) {
                $athlete2->matches_won = ($athlete2->matches_won ?? 0) + 1;
            } elseif (!$athlete1Wins) {
                // Draw - khong tinh vao matches_won hay matches_lost
            } else {
                $athlete2->matches_lost = ($athlete2->matches_lost ?? 0) + 1;
            }

            $athlete2->save();

            // For doubles matches, also update partner statistics
            if ($match->isDoubles()) {
                $this->updatePartnerStats($athlete1, $setsWonAthlete1, $setsWonAthlete2, $athlete1Wins);
                $this->updatePartnerStats($athlete2, $setsWonAthlete2, $setsWonAthlete1, $athlete2Wins);
            }

            Log::info('Tournament athlete stats updated via API', [
                'match_id' => $match->id,
                'is_doubles' => $match->isDoubles(),
                'athlete1_id' => $athlete1Id,
                'athlete1_matches_played' => $athlete1->matches_played,
                'athlete1_matches_won' => $athlete1->matches_won,
                'athlete1_sets_won' => $athlete1->sets_won,
                'athlete2_id' => $athlete2Id,
                'athlete2_matches_played' => $athlete2->matches_played,
                'athlete2_matches_won' => $athlete2->matches_won,
                'athlete2_sets_won' => $athlete2->sets_won,
            ]);
        } catch (\Exception $e) {
            Log::error('Update tournament athlete stats error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper: Update partner stats for doubles matches
     */
    private function updatePartnerStats(TournamentAthlete $athlete, int $setsWon, int $setsLost, bool $won): void
    {
        $partner = $athlete->partner;
        if (!$partner) {
            return;
        }

        $partner->matches_played = ($partner->matches_played ?? 0) + 1;
        $partner->sets_won = ($partner->sets_won ?? 0) + $setsWon;
        $partner->sets_lost = ($partner->sets_lost ?? 0) + $setsLost;

        if ($won) {
            $partner->matches_won = ($partner->matches_won ?? 0) + 1;
        } else {
            $partner->matches_lost = ($partner->matches_lost ?? 0) + 1;
        }

        $partner->save();

        Log::info('Partner stats updated via API', [
            'partner_id' => $partner->id,
            'matches_played' => $partner->matches_played,
        ]);
    }

    /**
     * Recalculate rankings for a group
     */
    private function recalculateGroupRankings(int $groupId): void
    {
        try {
            $standings = GroupStanding::where('group_id', $groupId)
                ->get()
                ->sortByDesc(function ($standing) {
                    return [
                        $standing->points,
                        $standing->sets_differential,
                    ];
                })
                ->values();

            foreach ($standings as $index => $standing) {
                $standing->update(['rank_position' => $index + 1]);
            }

            Log::info('Group rankings recalculated via API', ['group_id' => $groupId]);
        } catch (\Exception $e) {
            Log::error('Recalculate group rankings error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync match events from mobile/app
     */
    public function syncEvents(Request $request, MatchModel $match): JsonResponse
    {
        $referee = $request->user();

        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Referee role required.',
            ], 403);
        }

        if (!$match->isAssignedToReferee($referee)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this match.',
            ], 403);
        }

        $validated = $request->validate([
            'events' => 'required|array',
            'events.*.type' => 'required|string',
            'events.*.team' => 'nullable|string|in:left,right',
            'events.*.data' => 'nullable|array',
            'events.*.timer_seconds' => 'nullable|integer',
            'events.*.created_at' => 'nullable|string',
            'match_state' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $eventCount = MatchEvent::recordBatch($match->id, $validated['events']);

            if (!empty($validated['match_state'])) {
                $match->syncState($validated['match_state']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Events synced successfully.',
                'events_synced' => $eventCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Sync events failed', ['error' => $e->getMessage(), 'match_id' => $match->id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync events.',
            ], 500);
        }
    }

    /**
     * End match and finalize results
     */
    public function endMatch(Request $request, MatchModel $match): JsonResponse
    {
        $referee = $request->user();

        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Referee role required.',
            ], 403);
        }

        if (!$match->isAssignedToReferee($referee)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this match.',
            ], 403);
        }

        if ($match->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Match already completed.',
            ], 400);
        }

        $validated = $request->validate([
            'winner' => 'required|string|in:left,right',
            'winnerId' => 'required|integer',
            'gameScores' => 'required|array',
            'gameScores.*.game' => 'required|integer',
            'gameScores.*.athlete1' => 'required|integer',
            'gameScores.*.athlete2' => 'required|integer',
            'finalScore' => 'required|string',
            'totalTimer' => 'required|integer',
            'teams' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $setScores = array_map(function ($game) {
                return [
                    'set' => $game['game'],
                    'athlete1' => $game['athlete1'],
                    'athlete2' => $game['athlete2'],
                ];
            }, $validated['gameScores']);

            $gamesWonAthlete1 = 0;
            $gamesWonAthlete2 = 0;
            foreach ($validated['gameScores'] as $game) {
                if ($game['athlete1'] > $game['athlete2']) {
                    $gamesWonAthlete1++;
                } else {
                    $gamesWonAthlete2++;
                }
            }

            $match->update([
                'status' => 'completed',
                'winner_id' => $validated['winnerId'],
                'set_scores' => $setScores,
                'final_score' => $validated['finalScore'],
                'actual_end_time' => now(),
                'game_scores' => $validated['gameScores'],
                'games_won_athlete1' => $gamesWonAthlete1,
                'games_won_athlete2' => $gamesWonAthlete2,
                'timer_seconds' => $validated['totalTimer'],
                'match_state' => null,
            ]);

            $match->recordEvent(MatchEvent::TYPE_MATCH_END, $validated['winner'], [
                'final_score' => $validated['finalScore'],
                'winner_id' => $validated['winnerId'],
            ], $validated['totalTimer']);

            if ($match->athlete1_id && $match->athlete2_id) {
                $this->updateGroupStandingsAndAthleteStats($match, $setScores);
            }

            DB::commit();

            ActivityLog::log("Match #{$match->id} completed via API: {$validated['finalScore']}", 'Match', $match->id);

            return response()->json([
                'success' => true,
                'message' => 'Match completed successfully.',
                'data' => [
                    'winner_id' => $validated['winnerId'],
                    'final_score' => $validated['finalScore'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API End match failed', ['error' => $e->getMessage(), 'match_id' => $match->id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to end match.',
            ], 500);
        }
    }

    /**
     * Get current match state for recovery
     */
    public function getMatchState(MatchModel $match, Request $request): JsonResponse
    {
        $referee = $request->user();

        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Referee role required.',
            ], 403);
        }

        if (!$match->isAssignedToReferee($referee)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this match.',
            ], 403);
        }

        $match->load(['tournament', 'category', 'round', 'court', 'athlete1.partner', 'athlete2.partner']);

        return response()->json([
            'success' => true,
            'data' => $match->toVueState(),
        ]);
    }
}
