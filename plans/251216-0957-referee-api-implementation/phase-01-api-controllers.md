# Phase 01: API Controllers

**Parent Plan**: [plan.md](./plan.md)
**Date**: 2025-12-16
**Priority**: High
**Status**: Completed
**Review Status**: Complete

## Context Links

- Frontend controller: `app/Http/Controllers/Front/RefereeController.php`
- Frontend controller: `app/Http/Controllers/Front/RefereeProfileController.php`
- API pattern reference: `app/Http/Controllers/Api/OprsController.php`

## Overview

Create two API controllers mirroring frontend referee functionality.

## Key Insights

- Frontend `RefereeController` handles: dashboard stats, match list, match detail, start match, update score
- Frontend `RefereeProfileController` handles: public referee list, referee profile detail
- Both use existing models: `User`, `MatchModel`, `GroupStanding`, `TournamentAthlete`
- Score update includes complex logic for group standings and athlete stats

## Requirements

### Functional
- RF1: Dashboard stats API (total/completed/upcoming matches, tournaments)
- RF2: Match list with filters (tournament, status, date range)
- RF3: Match detail with authorization check
- RF4: Start match action
- RF5: Update score with winner calculation
- RF6: Public referee list with search/filter
- RF7: Public referee profile with stats

### Non-Functional
- NF1: JSON response format consistent with existing APIs
- NF2: Proper HTTP status codes
- NF3: Authorization via Sanctum + role check

## Architecture

### API Response Format
```json
{
  "success": true|false,
  "data": { ... },
  "message": "...",
  "errors": { ... }  // only on validation errors
}
```

### Controller Methods

**Api/RefereeController**
- `dashboard()` - GET stats + upcoming matches
- `matches()` - GET paginated match list with filters
- `showMatch(MatchModel $match)` - GET match detail
- `startMatch(MatchModel $match)` - POST start match
- `updateScore(Request $request, MatchModel $match)` - PUT update score

**Api/RefereeProfileController**
- `index(Request $request)` - GET referee list
- `show(User $referee)` - GET referee profile

## Related Code Files

### Files to Create
| Path | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Api/RefereeController.php` | Create | Referee API controller |
| `app/Http/Controllers/Api/RefereeProfileController.php` | Create | Public referee API controller |

### Files to Modify
| Path | Action | Description |
|------|--------|-------------|
| None | - | Controllers are standalone |

## Implementation Steps

### Step 1: Create Api/RefereeController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchModel;
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

        if ($match->status !== 'scheduled') {
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

    // Private helper methods (same as frontend controller)
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

    private function formatFinalScore(array $setScores): string
    {
        $scores = [];
        foreach ($setScores as $set) {
            $scores[] = $set['athlete1'] . '-' . $set['athlete2'];
        }
        return implode(', ', $scores);
    }

    private function updateGroupStandingsAndAthleteStats(MatchModel $match, array $setScores): void
    {
        // Copy logic from frontend RefereeController
        // ... (same implementation)
    }
}
```

### Step 2: Create Api/RefereeProfileController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefereeProfileController extends Controller
{
    /**
     * List active referees
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::role('referee')
            ->where('referee_status', 'active');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('status')) {
            $query->where('referee_status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 12), 50);

        $referees = $query->withCount([
            'refereeMatches as matches_completed' => function ($q) {
                $q->where('status', 'completed');
            }
        ])
        ->orderBy('name')
        ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $referees,
        ]);
    }

    /**
     * Show referee profile
     */
    public function show(User $referee): JsonResponse
    {
        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Referee not found.',
            ], 404);
        }

        $referee->load([
            'refereeMatches' => function ($query) {
                $query->where('status', 'completed')
                    ->with(['tournament', 'category'])
                    ->latest('match_date')
                    ->limit(20);
            },
            'refereeTournaments'
        ]);

        $stats = [
            'total_matches' => $referee->refereeMatches()->count(),
            'completed_matches' => $referee->refereeMatches()->where('status', 'completed')->count(),
            'upcoming_matches' => $referee->refereeMatches()
                ->where('status', 'scheduled')
                ->where('match_date', '>=', now())
                ->count(),
            'tournaments' => $referee->refereeTournaments()->count(),
            'completion_rate' => $this->calculateCompletionRate($referee),
            'avg_rating' => $referee->referee_rating ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'referee' => [
                    'id' => $referee->id,
                    'name' => $referee->name,
                    'referee_bio' => $referee->referee_bio,
                    'referee_status' => $referee->referee_status,
                    'referee_rating' => $referee->referee_rating,
                    'matches_officiated' => $referee->matches_officiated,
                ],
                'stats' => $stats,
                'recent_matches' => $referee->refereeMatches,
                'tournaments' => $referee->refereeTournaments,
            ],
        ]);
    }

    private function calculateCompletionRate(User $referee): float
    {
        $total = $referee->refereeMatches()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $referee->refereeMatches()->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 1);
    }
}
```

## Todo List

- [x] Create `app/Http/Controllers/Api/RefereeController.php`
- [x] Create `app/Http/Controllers/Api/RefereeProfileController.php`
- [x] Copy helper methods for group standings/athlete stats
- [x] Test each endpoint

## Success Criteria

- [x] Both controllers created with proper namespace
- [x] All methods return consistent JSON format
- [x] Authorization checks in place for referee endpoints
- [x] Public endpoints accessible without auth

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Score update logic duplication | Medium | Extract to service class (future refactor) |
| Missing role check | High | Use middleware or explicit check in each method |

## Security Considerations

- Referee role verification on all protected endpoints
- Match assignment verification before operations
- Validation on score input

## Next Steps

After completion, proceed to [Phase 02: API Routes](./phase-02-api-routes.md)
