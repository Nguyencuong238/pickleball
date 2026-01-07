# Phase 5: Match Management & Score Entry

**Date**: 2025-12-09
**Status**: Completed
**Completion Date**: 2025-12-09
**Priority**: High
**Parent Plan**: [plan.md](./plan.md)
**Depends On**: [Phase 4 - Tournament Referee Assignment](./phase-04-tournament-referee-assignment.md)

---

## Context

Enable match referee assignment during match creation/editing and referee score entry. HomeYard users assign ONE referee per match from tournament referee pool. Referees update scores via dashboard using existing set_scores JSON pattern. Auto-update referee_name cache on assignment. Observer pattern syncs cached data.

---

## Overview

1. Update match create/edit forms with referee dropdown (tournament referees only)
2. Add validation: referee must be in tournament referee pool
3. Update RefereeController with score entry methods
4. Create score entry form mirroring HomeYard match result pattern
5. Add MatchObserver to sync referee_name cache on assignment
6. Activity logging for referee assignments and score updates

---

## Key Insights from Research

**Score Entry Pattern**:
- Mirror HomeYardTournamentController updateMatchResult() logic
- set_scores JSON array: [{"set": 1, "athlete1": 11, "athlete2": 7}, ...]
- final_score string: "2-1" or "11-7, 8-11, 11-9"
- winner_id determined from set_scores

**From MatchModel Schema**:
- referee_id nullable FK to users
- referee_name cached string (avoid join on leaderboard)
- Observer syncs referee_name when referee_id changes

**Validation Rules**:
- Referee must be in tournament.referees list
- Match not completed (status != 'completed')
- Only assigned referee can update scores

---

## Requirements

### Functional

1. Match create/edit shows referee dropdown (tournament referees only)
2. Assigning referee updates referee_id and referee_name
3. Referee can update match scores via dashboard
4. Score update validates set_scores format and calculates winner
5. Activity log tracks referee assignment and score updates

### Non-Functional

1. Observer pattern syncs referee_name cache automatically
2. Transaction safety for score updates
3. Validation prevents score entry after match completed
4. Performance: No N+1 queries on match list

---

## Related Files

### Controllers to Modify

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Front/HomeYardTournamentController.php` | MODIFY | Add referee assignment in match store/update |
| `app/Http/Controllers/Front/RefereeController.php` | MODIFY | Add updateScore method |

### Observers to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Observers/MatchObserver.php` | CREATE | Sync referee_name on referee_id change |

### Views to Modify

| File | Action | Description |
|------|--------|-------------|
| `resources/views/home-yard/tournaments/matches/create.blade.php` | MODIFY | Add referee dropdown |
| `resources/views/home-yard/tournaments/matches/edit.blade.php` | MODIFY | Add referee dropdown |
| `resources/views/referee/matches/show.blade.php` | MODIFY | Add score entry form |

### Routes to Add

| File | Action | Description |
|------|--------|-------------|
| `routes/web.php` | MODIFY | Add referee score update route |

---

## Implementation Steps

### Step 1: Create MatchObserver

**File**: `app/Observers/MatchObserver.php`

```php
<?php

namespace App\Observers;

use App\Models\MatchModel;
use App\Models\User;

class MatchObserver
{
    /**
     * Handle the MatchModel "saving" event.
     * Sync referee_name cache when referee_id changes
     */
    public function saving(MatchModel $match): void
    {
        if ($match->isDirty('referee_id')) {
            if ($match->referee_id) {
                $referee = User::find($match->referee_id);
                $match->referee_name = $referee?->name;
            } else {
                $match->referee_name = null;
            }
        }
    }
}
```

**Register Observer**: `app/Providers/EventServiceProvider.php`

```php
use App\Models\MatchModel;
use App\Observers\MatchObserver;

public function boot(): void
{
    MatchModel::observe(MatchObserver::class);
}
```

### Step 2: Update HomeYardTournamentController - Match Store

**File**: `app/Http/Controllers/Front/HomeYardTournamentController.php`

Add to existing `storeMatch()` method:

```php
/**
 * Store match (existing method - add referee assignment)
 */
public function storeMatch(Request $request, Tournament $tournament)
{
    $validated = $request->validate([
        // ... existing validation rules ...
        'referee_id' => 'nullable|exists:users,id',
    ]);

    // NEW: Validate referee is in tournament referee pool
    if ($request->filled('referee_id')) {
        if (!$tournament->hasReferee(User::find($request->referee_id))) {
            return back()->withErrors(['referee_id' => 'Referee must be assigned to tournament'])
                ->withInput();
        }
    }

    try {
        DB::beginTransaction();

        $match = MatchModel::create($validated);

        // Observer automatically syncs referee_name

        DB::commit();

        activity()
            ->performedOn($match)
            ->causedBy(auth()->user())
            ->log('Created match');

        if ($match->referee_id) {
            activity()
                ->performedOn($match)
                ->causedBy(auth()->user())
                ->withProperties(['referee_id' => $match->referee_id, 'referee_name' => $match->referee_name])
                ->log('Assigned referee to match');
        }

        return redirect()->route('homeyard.tournaments.show', $tournament)
            ->with('success', 'Match created successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Match creation failed', ['error' => $e->getMessage()]);
        return back()->with('error', 'Failed to create match')->withInput();
    }
}
```

### Step 3: Update HomeYardTournamentController - Match Update

**File**: `app/Http/Controllers/Front/HomeYardTournamentController.php`

Add to existing `updateMatch()` method:

```php
/**
 * Update match (existing method - add referee assignment)
 */
public function updateMatch(Request $request, Tournament $tournament, MatchModel $match)
{
    $validated = $request->validate([
        // ... existing validation rules ...
        'referee_id' => 'nullable|exists:users,id',
    ]);

    // NEW: Validate referee is in tournament referee pool
    if ($request->filled('referee_id')) {
        if (!$tournament->hasReferee(User::find($request->referee_id))) {
            return back()->withErrors(['referee_id' => 'Referee must be assigned to tournament'])
                ->withInput();
        }
    }

    try {
        $oldRefereeId = $match->referee_id;

        $match->update($validated);

        // Observer automatically syncs referee_name

        if ($oldRefereeId != $match->referee_id) {
            activity()
                ->performedOn($match)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_referee_id' => $oldRefereeId,
                    'new_referee_id' => $match->referee_id,
                    'new_referee_name' => $match->referee_name,
                ])
                ->log('Changed match referee');
        }

        return redirect()->route('homeyard.tournaments.show', $tournament)
            ->with('success', 'Match updated successfully');
    } catch (\Exception $e) {
        Log::error('Match update failed', ['error' => $e->getMessage()]);
        return back()->with('error', 'Failed to update match')->withInput();
    }
}
```

### Step 4: Add RefereeController Score Update

**File**: `app/Http/Controllers/Front/RefereeController.php`

Add new method:

```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Update match scores
 */
public function updateScore(Request $request, MatchModel $match)
{
    $referee = auth()->user();

    // Authorization
    if (!$match->canEditScores($referee)) {
        return back()->with('error', 'You cannot edit this match');
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

        // Calculate winner from set scores
        $winnerId = $this->calculateWinner($validated['set_scores'], $match);
        $finalScore = $this->formatFinalScore($validated['set_scores']);

        $match->update([
            'set_scores' => $validated['set_scores'],
            'final_score' => $finalScore,
            'winner_id' => $winnerId,
            'status' => $validated['status'],
            'actual_end_time' => $validated['status'] === 'completed' ? now() : null,
        ]);

        DB::commit();

        activity()
            ->performedOn($match)
            ->causedBy($referee)
            ->withProperties([
                'set_scores' => $validated['set_scores'],
                'final_score' => $finalScore,
                'winner_id' => $winnerId,
                'status' => $validated['status'],
            ])
            ->log('Updated match scores');

        return redirect()->route('referee.matches.show', $match)
            ->with('success', 'Scores updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Score update failed', ['error' => $e->getMessage()]);
        return back()->with('error', 'Failed to update scores')->withInput();
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
        } else {
            $athlete2Sets++;
        }
    }

    if ($athlete1Sets > $athlete2Sets) {
        return $match->athlete1_id;
    } elseif ($athlete2Sets > $athlete1Sets) {
        return $match->athlete2_id;
    }

    return null; // Draw (unlikely in pickleball)
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
```

### Step 5: Update Match Create/Edit Forms

**File**: `resources/views/home-yard/tournaments/matches/create.blade.php`

Add after existing match fields:

```blade
{{-- Referee Assignment --}}
<div class="mb-3">
    <label for="referee_id" class="form-label">Assign Referee</label>
    <select name="referee_id" id="referee_id" class="form-select">
        <option value="">-- No Referee --</option>
        @foreach($tournament->referees as $referee)
            <option value="{{ $referee->id }}">{{ $referee->name }}</option>
        @endforeach
    </select>
    <small class="form-text text-muted">
        Only referees assigned to this tournament are shown
    </small>
</div>
```

**File**: `resources/views/home-yard/tournaments/matches/edit.blade.php`

Add after existing match fields:

```blade
{{-- Referee Assignment --}}
<div class="mb-3">
    <label for="referee_id" class="form-label">Assign Referee</label>
    <select name="referee_id" id="referee_id" class="form-select">
        <option value="">-- No Referee --</option>
        @foreach($tournament->referees as $referee)
            <option value="{{ $referee->id }}"
                {{ $match->referee_id == $referee->id ? 'selected' : '' }}>
                {{ $referee->name }}
            </option>
        @endforeach
    </select>
    <small class="form-text text-muted">
        Only referees assigned to this tournament are shown
    </small>
</div>
```

### Step 6: Update Referee Match Detail View with Score Form

**File**: `resources/views/referee/matches/show.blade.php`

Replace placeholder score entry section:

```blade
{{-- Score Entry Form --}}
@if(!$match->isCompleted())
    <div class="card">
        <div class="card-header">
            <h5>Score Entry</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('referee.matches.update-score', $match) }}">
                @csrf
                @method('PUT')

                <div id="sets-container">
                    @php
                        $existingSets = $match->set_scores ?? [];
                    @endphp

                    @if(empty($existingSets))
                        {{-- Default first set --}}
                        <div class="set-entry mb-3 border p-3 rounded">
                            <h6>Set 1</h6>
                            <input type="hidden" name="set_scores[0][set]" value="1">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">{{ $match->athlete1_name }}</label>
                                    <input type="number" name="set_scores[0][athlete1]" class="form-control" min="0" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ $match->athlete2_name }}</label>
                                    <input type="number" name="set_scores[0][athlete2]" class="form-control" min="0" required>
                                </div>
                            </div>
                        </div>
                    @else
                        @foreach($existingSets as $index => $set)
                            <div class="set-entry mb-3 border p-3 rounded">
                                <h6>Set {{ $set['set'] }}</h6>
                                <input type="hidden" name="set_scores[{{ $index }}][set]" value="{{ $set['set'] }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ $match->athlete1_name }}</label>
                                        <input type="number" name="set_scores[{{ $index }}][athlete1]"
                                               class="form-control" min="0" value="{{ $set['athlete1'] }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ $match->athlete2_name }}</label>
                                        <input type="number" name="set_scores[{{ $index }}][athlete2]"
                                               class="form-control" min="0" value="{{ $set['athlete2'] }}" required>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <button type="button" class="btn btn-secondary mb-3" onclick="addSet()">Add Set</button>

                <div class="mb-3">
                    <label for="status" class="form-label">Match Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="in_progress" {{ $match->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $match->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save Scores</button>
            </form>
        </div>
    </div>

    <script>
        let setCount = {{ count($existingSets) ?: 1 }};

        function addSet() {
            setCount++;
            const container = document.getElementById('sets-container');
            const setHtml = `
                <div class="set-entry mb-3 border p-3 rounded">
                    <h6>Set ${setCount}</h6>
                    <input type="hidden" name="set_scores[${setCount - 1}][set]" value="${setCount}">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">{{ $match->athlete1_name }}</label>
                            <input type="number" name="set_scores[${setCount - 1}][athlete1]" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ $match->athlete2_name }}</label>
                            <input type="number" name="set_scores[${setCount - 1}][athlete2]" class="form-control" min="0" required>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', setHtml);
        }
    </script>
@else
    {{-- Show completed match scores --}}
    <div class="card">
        <div class="card-header">
            <h5>Match Results</h5>
        </div>
        <div class="card-body">
            <p><strong>Final Score:</strong> {{ $match->final_score }}</p>
            <p><strong>Winner:</strong> {{ $match->winner->athlete_name ?? 'N/A' }}</p>

            @if($match->set_scores)
                <h6>Set Breakdown:</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Set</th>
                            <th>{{ $match->athlete1_name }}</th>
                            <th>{{ $match->athlete2_name }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($match->set_scores as $set)
                            <tr>
                                <td>Set {{ $set['set'] }}</td>
                                <td>{{ $set['athlete1'] }}</td>
                                <td>{{ $set['athlete2'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endif
```

### Step 7: Add Route for Score Update

**File**: `routes/web.php`

Add to referee route group:

```php
Route::middleware(['auth', 'role:referee'])
    ->prefix('referee')
    ->name('referee.')
    ->group(function () {
        // ... existing routes ...

        // NEW: Score update
        Route::put('matches/{match}/update-score', [Front\RefereeController::class, 'updateScore'])
            ->name('matches.update-score');
    });
```

---

## Todo List

- [x] Create MatchObserver with saving() method - COMPLETED
- [x] Register MatchObserver in EventServiceProvider - COMPLETED
- [x] Update HomeYardTournamentController storeMatch with referee validation - COMPLETED
- [x] Update HomeYardTournamentController updateMatch with referee validation - COMPLETED
- [x] Add RefereeController updateScore method - COMPLETED
- [x] Add calculateWinner() helper method - COMPLETED
- [x] Add formatFinalScore() helper method - COMPLETED
- [x] Update match create form with referee dropdown - COMPLETED
- [x] Update match edit form with referee dropdown - COMPLETED
- [x] Update referee match detail view with score entry form - COMPLETED
- [x] Add route for score update - COMPLETED
- [x] Test referee_name syncs automatically on assignment - COMPLETED
- [x] Test score entry calculates winner correctly - COMPLETED
- [x] Test validation: referee must be in tournament pool - COMPLETED
- [x] Verify activity log tracks referee assignments and score updates - COMPLETED

**Completion Summary**: All 15 implementation tasks completed successfully on 2025-12-09

---

## Success Criteria

- Match create/edit forms show referee dropdown (tournament referees only)
- Assigning referee updates referee_id and syncs referee_name automatically
- Referee can enter scores via dashboard
- Score calculation determines winner from set_scores
- Completed matches cannot be edited
- Activity log tracks all referee assignments and score updates
- Observer prevents referee_name cache drift

---

## Risk Assessment

**Risk**: Observer not firing on mass update
**Mitigation**: Use update() method (fires events), not query builder

**Risk**: Score calculation logic differs from HomeYard
**Mitigation**: Extract calculateWinner() to shared service class

**Risk**: Cache drift if referee name changes
**Mitigation**: Observer watches referee_id, re-syncs name on change

---

## Security Considerations

- Authorization: canEditScores() checks assignment and status
- Validation: set_scores format validation prevents malformed data
- CSRF protection on score update form
- Activity logging for audit trail
- Prevent score updates after match completed

---

## Next Steps

After match management complete:
1. Phase 6: Public referee profiles in Academy section
2. Extract calculateWinner() to MatchService for code reuse
3. Add email notifications to athletes when referee updates scores
