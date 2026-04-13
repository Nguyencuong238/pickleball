<?php

namespace App\Http\Controllers\Api;

use App\Events\MatchScored;
use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\MatchEvent;
use App\Models\ActivityLog;
use App\Services\Tournament\TournamentStandingService;
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
     * Also handles starting subsequent sets (when match is already in_progress)
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

        // Allow: scheduled, ready (first start), or in_progress (subsequent sets)
        if (!in_array($match->status, ['scheduled', 'ready', 'in_progress'])) {
            return response()->json([
                'success' => false,
                'message' => 'Match cannot be started. Current status: ' . $match->status,
            ], 400);
        }

        // Only update start time if match hasn't started yet
        $updateData = ['status' => 'in_progress'];
        if (in_array($match->status, ['scheduled', 'ready'])) {
            $updateData['actual_start_time'] = now();
            ActivityLog::log("Trận đấu #{$match->id} bắt đầu bởi trọng tài qua API", 'Match', $match->id);
        }

        $match->update($updateData);

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
                $this->updateGroupStandingsAndAthleteStats($match);
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
     * Delegate standings + athlete stats sang service idempotent (recompute from matches).
     */
    private function updateGroupStandingsAndAthleteStats(MatchModel $match): void
    {
        try {
            $service = app(TournamentStandingService::class);

            if ($match->group_id) {
                $service->recalculateGroupStandings((int) $match->group_id);
            }
            if ($match->athlete1_id) {
                $service->recalculateTournamentAthleteStats((int) $match->athlete1_id);
            }
            if ($match->athlete2_id) {
                $service->recalculateTournamentAthleteStats((int) $match->athlete2_id);
            }

            Log::info('Group standings + athlete stats recomputed via API', [
                'match_id' => $match->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Update group standings error: ' . $e->getMessage());
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
                $this->updateGroupStandingsAndAthleteStats($match);
            }

            DB::commit();

            ActivityLog::log("Match #{$match->id} completed via API: {$validated['finalScore']}", 'Match', $match->id);

            // Dispatch event for point earning
            event(new MatchScored($match, $referee));

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
