# Phase 4: Tournament Referee Assignment

**Date**: 2025-12-09
**Status**: Completed
**Completion Date**: 2025-12-09
**Priority**: High
**Parent Plan**: [plan.md](./plan.md)
**Depends On**: [Phase 3 - Referee Dashboard](./phase-03-referee-dashboard.md)

---

## Context

Enable HomeYard users to assign referees to tournaments during creation/editing. UI in tournament form shows multi-select of users with referee role. Backend creates TournamentReferee pivot records with audit trail (assigned_by, assigned_at). API endpoints handle AJAX add/remove referee operations.

---

## Overview

1. Update HomeYardTournamentController with referee assignment logic
2. Modify tournament create/edit forms to show referee selection
3. Add API endpoints for AJAX referee add/remove
4. Implement validation: only users with referee role can be assigned
5. Activity logging for referee assignments

---

## Key Insights from Research

**Assignment Workflow**:
- Select from users with referee role (Spatie query: User::role('referee'))
- Multi-select UI (Select2 or native multi-select)
- Store pivot records with assigned_by FK
- Activity log tracks who assigned whom and when

**From TournamentAthlete Pattern**:
- HomeYardTournamentController handles assignments
- Form uses select with existing users
- Store method creates pivot records in transaction
- Update method syncs assignments (add/remove)

**Validation Rules**:
- User must have referee role
- Prevent duplicate assignments (unique constraint)
- Track who made assignment (assigned_by FK)

---

## Requirements

### Functional

1. Tournament create/edit form shows referee multi-select
2. Only users with referee role appear in dropdown
3. Assigning referee creates TournamentReferee record with audit
4. Removing referee soft deletes TournamentReferee record
5. Activity log tracks all assignment changes

### Non-Functional

1. AJAX endpoints for add/remove without page reload
2. Transaction safety for bulk assignments
3. Performance: Cache referee list query
4. UI feedback on successful assignment

---

## Related Files

### Controllers to Modify

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Front/HomeYardTournamentController.php` | MODIFY | Add referee assignment in store/update |

### Views to Modify

| File | Action | Description |
|------|--------|-------------|
| `resources/views/home-yard/tournaments/create.blade.php` | MODIFY | Add referee multi-select |
| `resources/views/home-yard/tournaments/edit.blade.php` | MODIFY | Add referee multi-select |
| `resources/views/home-yard/tournaments/show.blade.php` | MODIFY | Display assigned referees |

### Routes to Add

| File | Action | Description |
|------|--------|-------------|
| `routes/web.php` | MODIFY | Add API routes for referee add/remove |

---

## Implementation Steps

### Step 1: Update HomeYardTournamentController - Store Method

**File**: `app/Http/Controllers/Front/HomeYardTournamentController.php`

Add to existing `store()` method after tournament creation:

```php
/**
 * Store a newly created tournament (existing method - add referee assignment)
 */
public function store(Request $request)
{
    // ... existing validation and tournament creation code ...

    try {
        DB::beginTransaction();

        // ... existing tournament creation code ...
        $tournament = Tournament::create($validated);

        // NEW: Assign referees
        if ($request->filled('referee_ids')) {
            $this->assignReferees($tournament, $request->referee_ids);
        }

        DB::commit();

        return redirect()->route('homeyard.tournaments.index')
            ->with('success', 'Tournament created successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Tournament creation failed', ['error' => $e->getMessage()]);
        return back()->with('error', 'Failed to create tournament')->withInput();
    }
}

/**
 * Assign referees to tournament with audit trail
 */
private function assignReferees(Tournament $tournament, array $refereeIds): void
{
    $assignedBy = auth()->id();
    $assignedAt = now();

    foreach ($refereeIds as $refereeId) {
        // Verify user has referee role
        $referee = User::findOrFail($refereeId);
        if (!$referee->hasRole('referee')) {
            throw new \Exception("User {$refereeId} is not a referee");
        }

        $tournament->tournamentReferees()->create([
            'user_id' => $refereeId,
            'assigned_by' => $assignedBy,
            'assigned_at' => $assignedAt,
            'status' => 'active',
        ]);

        // Activity log
        activity()
            ->performedOn($tournament)
            ->causedBy(auth()->user())
            ->withProperties([
                'referee_id' => $refereeId,
                'referee_name' => $referee->name,
            ])
            ->log('Assigned referee to tournament');
    }
}
```

### Step 2: Update HomeYardTournamentController - Update Method

**File**: `app/Http/Controllers/Front/HomeYardTournamentController.php`

Add to existing `update()` method:

```php
/**
 * Update tournament (existing method - add referee sync)
 */
public function update(Request $request, Tournament $tournament)
{
    // ... existing validation and authorization code ...

    try {
        DB::beginTransaction();

        // ... existing tournament update code ...
        $tournament->update($validated);

        // NEW: Sync referees (add new, remove old)
        if ($request->has('referee_ids')) {
            $this->syncReferees($tournament, $request->referee_ids ?? []);
        }

        DB::commit();

        return redirect()->route('homeyard.tournaments.show', $tournament)
            ->with('success', 'Tournament updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Tournament update failed', ['error' => $e->getMessage()]);
        return back()->with('error', 'Failed to update tournament')->withInput();
    }
}

/**
 * Sync referees (add new, remove old)
 */
private function syncReferees(Tournament $tournament, array $newRefereeIds): void
{
    $currentRefereeIds = $tournament->referees()->pluck('user_id')->toArray();
    $assignedBy = auth()->id();

    // Add new referees
    $toAdd = array_diff($newRefereeIds, $currentRefereeIds);
    foreach ($toAdd as $refereeId) {
        $referee = User::findOrFail($refereeId);
        if (!$referee->hasRole('referee')) {
            throw new \Exception("User {$refereeId} is not a referee");
        }

        $tournament->tournamentReferees()->create([
            'user_id' => $refereeId,
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
            'status' => 'active',
        ]);

        activity()
            ->performedOn($tournament)
            ->causedBy(auth()->user())
            ->withProperties(['referee_id' => $refereeId, 'referee_name' => $referee->name])
            ->log('Assigned referee to tournament');
    }

    // Remove old referees
    $toRemove = array_diff($currentRefereeIds, $newRefereeIds);
    foreach ($toRemove as $refereeId) {
        $referee = User::find($refereeId);
        $tournament->tournamentReferees()->where('user_id', $refereeId)->delete();

        activity()
            ->performedOn($tournament)
            ->causedBy(auth()->user())
            ->withProperties(['referee_id' => $refereeId, 'referee_name' => $referee?->name])
            ->log('Removed referee from tournament');
    }
}
```

### Step 3: Add AJAX API Endpoints

**File**: `app/Http/Controllers/Front/HomeYardTournamentController.php`

Add new methods:

```php
/**
 * Add referee to tournament (AJAX)
 */
public function addReferee(Request $request, Tournament $tournament)
{
    $this->authorize('update', $tournament);

    $request->validate([
        'referee_id' => 'required|exists:users,id',
    ]);

    $referee = User::findOrFail($request->referee_id);

    if (!$referee->hasRole('referee')) {
        return response()->json(['success' => false, 'message' => 'User is not a referee'], 400);
    }

    if ($tournament->hasReferee($referee)) {
        return response()->json(['success' => false, 'message' => 'Referee already assigned'], 400);
    }

    try {
        $tournament->assignReferee($referee, auth()->user());

        activity()
            ->performedOn($tournament)
            ->causedBy(auth()->user())
            ->withProperties(['referee_id' => $referee->id, 'referee_name' => $referee->name])
            ->log('Assigned referee to tournament');

        return response()->json([
            'success' => true,
            'message' => 'Referee assigned successfully',
            'referee' => [
                'id' => $referee->id,
                'name' => $referee->name,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

/**
 * Remove referee from tournament (AJAX)
 */
public function removeReferee(Request $request, Tournament $tournament)
{
    $this->authorize('update', $tournament);

    $request->validate([
        'referee_id' => 'required|exists:users,id',
    ]);

    $referee = User::findOrFail($request->referee_id);

    try {
        $tournament->removeReferee($referee);

        activity()
            ->performedOn($tournament)
            ->causedBy(auth()->user())
            ->withProperties(['referee_id' => $referee->id, 'referee_name' => $referee->name])
            ->log('Removed referee from tournament');

        return response()->json([
            'success' => true,
            'message' => 'Referee removed successfully',
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
```

### Step 4: Add Routes

**File**: `routes/web.php`

Add to HomeYard route group:

```php
Route::middleware(['auth', 'role:home_yard'])
    ->prefix('homeyard')
    ->name('homeyard.')
    ->group(function () {
        // ... existing routes ...

        // NEW: Referee management
        Route::post('tournaments/{tournament}/referees/add',
            [Front\HomeYardTournamentController::class, 'addReferee'])
            ->name('tournaments.referees.add');
        Route::delete('tournaments/{tournament}/referees/{referee}',
            [Front\HomeYardTournamentController::class, 'removeReferee'])
            ->name('tournaments.referees.remove');
    });
```

### Step 5: Update Tournament Create Form

**File**: `resources/views/home-yard/tournaments/create.blade.php`

Add after existing form fields:

```blade
{{-- Referee Assignment --}}
<div class="mb-3">
    <label for="referee_ids" class="form-label">Assign Referees</label>
    <select name="referee_ids[]" id="referee_ids" class="form-select" multiple size="5">
        @foreach(\App\Models\User::role('referee')->orderBy('name')->get() as $referee)
            <option value="{{ $referee->id }}">{{ $referee->name }}</option>
        @endforeach
    </select>
    <small class="form-text text-muted">
        Hold Ctrl (Cmd on Mac) to select multiple referees
    </small>
</div>
```

### Step 6: Update Tournament Edit Form

**File**: `resources/views/home-yard/tournaments/edit.blade.php`

Add after existing form fields:

```blade
{{-- Referee Assignment --}}
<div class="mb-3">
    <label for="referee_ids" class="form-label">Assign Referees</label>
    <select name="referee_ids[]" id="referee_ids" class="form-select" multiple size="5">
        @php
            $assignedRefereeIds = $tournament->referees->pluck('id')->toArray();
        @endphp
        @foreach(\App\Models\User::role('referee')->orderBy('name')->get() as $referee)
            <option value="{{ $referee->id }}"
                {{ in_array($referee->id, $assignedRefereeIds) ? 'selected' : '' }}>
                {{ $referee->name }}
            </option>
        @endforeach
    </select>
    <small class="form-text text-muted">
        Hold Ctrl (Cmd on Mac) to select multiple referees
    </small>
</div>
```

### Step 7: Update Tournament Show View

**File**: `resources/views/home-yard/tournaments/show.blade.php`

Add after existing tournament details:

```blade
{{-- Assigned Referees --}}
<div class="card mb-4">
    <div class="card-header">
        <h5>Assigned Referees</h5>
    </div>
    <div class="card-body">
        @if($tournament->referees->isEmpty())
            <p class="text-muted">No referees assigned yet</p>
        @else
            <ul class="list-group">
                @foreach($tournament->referees as $referee)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $referee->name }}
                        <span class="text-muted small">
                            Assigned {{ $referee->pivot->assigned_at->diffForHumans() }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
```

---

## Todo List

- [ ] Update HomeYardTournamentController store method with referee assignment
- [ ] Update HomeYardTournamentController update method with referee sync
- [ ] Add assignReferees() private method to controller
- [ ] Add syncReferees() private method to controller
- [ ] Add addReferee() AJAX endpoint
- [ ] Add removeReferee() AJAX endpoint
- [ ] Add routes for referee add/remove
- [ ] Update tournament create form with referee multi-select
- [ ] Update tournament edit form with referee multi-select
- [ ] Update tournament show view to display assigned referees
- [ ] Test referee assignment creates TournamentReferee record
- [ ] Test referee removal soft deletes record
- [ ] Verify activity log tracks assignments
- [ ] Test validation: non-referee user cannot be assigned

---

## Success Criteria

- Tournament create form shows referee multi-select
- Referee assignment creates TournamentReferee with assigned_by, assigned_at
- Tournament update syncs referees (add new, remove old)
- Activity log tracks all assignment changes
- Only users with referee role appear in dropdown
- Validation prevents assigning non-referee users
- Tournament show view displays assigned referees with timestamps

---

## Risk Assessment

**Risk**: Performance degradation with large referee list
**Mitigation**: Cache User::role('referee') query, paginate if >100 referees

**Risk**: Transaction failure on bulk assignment
**Mitigation**: Wrap in DB::transaction, rollback on error

**Risk**: Duplicate assignment if form submitted twice
**Mitigation**: Unique constraint on tournament_id + user_id prevents duplicates

---

## Security Considerations

- Authorization: TournamentPolicy update check
- Validation: referee must have referee role
- CSRF protection on AJAX endpoints
- Activity logging for audit trail
- Prevent assignment after tournament starts (optional business rule)

---

## Next Steps

After assignment complete:
1. Phase 5: Match referee assignment and score entry
2. Test multi-tournament scenarios (referee assigned to multiple tournaments)
3. Verify referee dashboard shows tournaments after assignment
