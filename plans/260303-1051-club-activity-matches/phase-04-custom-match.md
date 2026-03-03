# Phase 04 - Custom Match

## Context Links
- Plan overview: `plans/260303-1051-club-activity-matches/plan.md`
- Phase 01: `plans/260303-1051-club-activity-matches/phase-01-schema-models-tab-ui.md`
- Phase 03: `plans/260303-1051-club-activity-matches/phase-03-scoring-standings.md`
- Match model: `app/Models/ClubActivityMatch.php`
- Rounds model: `app/Models/ClubActivityMatchRound.php`
- Match controller: `app/Http/Controllers/ClubMatchController.php`
- Matches scripts: `resources/views/clubs/activities/partials/_matches-scripts.blade.php`

## Overview
- **Priority:** P3 (independent of Phase 3, but depends on Phase 1)
- **Status:** complete
- **Goal:** Allow management to manually create an ad-hoc match (pick players + court + type) and add it to an existing or new round

## Requirements
- Management only: create a match by hand-picking players
- Select match type: singles or doubles
- Select players from confirmed participants list (not free text)
- Optionally assign to an existing round or create a new round
- Optionally assign court number
- Created match appears immediately in rounds list
- Standings update after score is saved (reuses Phase 3 saveScore)

## Architecture

### New Controller Method (add to `ClubMatchController`)
```
POST matches/custom    → createCustom()   management only
```

### Route addition
```php
Route::post('custom', [ClubMatchController::class, 'createCustom'])->name('create-custom');
```

### ClubMatchService addition
```php
public function createCustomMatch(
    ClubActivity $activity,
    string $matchType,         // singles|doubles
    int $player1Id,
    ?int $player2Id,           // null for singles
    int $player3Id,
    ?int $player4Id,           // null for singles
    ?int $courtNumber,
    ?int $roundId              // null = create new round
): ClubActivityMatch
```
Logic:
- Validate all playerIds are confirmed participants of this activity
- Validate no player appears twice in same match
- If `$roundId` null: create new round with next `round_number`
- Else: load round, confirm it belongs to activity
- Create `ClubActivityMatch` with status `scheduled`
- Return created match with players loaded

### Request Validation
```php
'match_type'  => 'required|in:singles,doubles',
'player1_id'  => 'required|exists:users,id',
'player2_id'  => 'nullable|required_if:match_type,doubles|exists:users,id',
'player3_id'  => 'required|exists:users,id',
'player4_id'  => 'nullable|required_if:match_type,doubles|exists:users,id',
'court_number'=> 'nullable|integer|min:1|max:20',
'round_id'    => 'nullable|exists:club_activity_match_rounds,id',
```

## Related Code Files

### Modify
- `app/Http/Controllers/ClubMatchController.php` — add createCustom()
- `app/Services/ClubMatchService.php` — add createCustomMatch()
- `routes/web.php` — add custom route inside matches group
- `resources/views/clubs/activities/partials/_matches-panel.blade.php` — add custom match button
- `resources/views/clubs/activities/partials/_matches-scripts.blade.php` — add custom modal JS
- `resources/views/clubs/activities/partials/_matches-styles.blade.php` — player picker styles

### Create
- `resources/views/clubs/activities/partials/_matches-custom-modal.blade.php`

## Implementation Steps

1. **ClubMatchService — createCustomMatch()**
   - Validate player IDs are all in `$activity->confirmedParticipants()->pluck('user_id')`
   - Validate no duplicate player IDs within the match
   - If `$roundId` null: `$nextNum = $activity->matchRounds()->max('round_number') + 1`, create round status `in_progress`
   - Create `ClubActivityMatch` record
   - Return match with `load(['player1','player2','player3','player4','round'])`

2. **ClubMatchController — createCustom()**
   - `authorize('manageActivity', $club)`
   - Validate request (see schema above)
   - If `round_id` provided, verify it belongs to activity
   - Call `$this->service->createCustomMatch(...)`
   - Return JSON `{success: true, match: $match, round: $match->round}`

3. **Route** — add inside matches prefix group:
   ```php
   Route::post('custom', [ClubMatchController::class, 'createCustom'])->name('create-custom');
   ```

4. **_matches-custom-modal.blade.php** (management only)
   - Hidden modal `id="custom-match-modal"`
   - match_type radio: singles / doubles
   - Player selects: 4 `<select>` dropdowns populated from `$confirmedParticipants`
     - Singles: show player1 (Team 1) + player3 (Team 2), hide player2/player4
     - Doubles: show all 4
   - court_number input (optional)
   - Round select: "Vòng mới" (value="") + existing rounds from `id="existing-rounds-data"` (populated by JS after loadRounds)
   - Submit → `submitCustomMatch()` JS function

5. **_matches-panel.blade.php** — add "Thêm trận tùy chỉnh" button if `$isManagement`:
   ```blade
   @if($isManagement)
   <button class="btn-matches-secondary" onclick="openCustomModal()">
       [PLUS] Thêm trận tùy chỉnh
   </button>
   @endif
   ```
   Include `_matches-custom-modal.blade.php` if `$isManagement`.
   Pass confirmed participants as JSON data attribute:
   ```blade
   <div id="confirmed-participants-data"
        data-participants='@json($activity->confirmedParticipants->map(fn($p) => ["id"=>$p->user_id,"name"=>$p->user->name]))'
        style="display:none"></div>
   ```
   Note: requires `confirmedParticipants.user` eager-loaded in controller (already done in `ClubActivityController::show()`).

6. **JS additions to _matches-scripts.blade.php**
   - `openCustomModal()` / `closeCustomModal()`
   - `onMatchTypeChange()`: toggle player2/player4 selects visibility
   - `populatePlayerSelects()`: read `#confirmed-participants-data`, fill all 4 selects
   - `populateRoundSelect()`: after loadRounds(), fill round dropdown with existing round IDs
   - `submitCustomMatch()`: POST to custom route, on success call `loadRounds()` (refreshes full view), close modal
   - Call `populatePlayerSelects()` on DOM ready

7. **Styles** — minimal additions: modal player-grid layout (2×2 for doubles, 2×1 for singles)

## Todo List
- [ ] `ClubMatchService::createCustomMatch()` with participant validation
- [ ] `ClubMatchController::createCustom()` endpoint
- [ ] Add route to `routes/web.php`
- [ ] `_matches-custom-modal.blade.php` with player selects + round select
- [ ] Update `_matches-panel.blade.php` with button + participant data div
- [ ] JS: openCustomModal, onMatchTypeChange, populatePlayerSelects, submitCustomMatch
- [ ] JS: populateRoundSelect() called after loadRounds()
- [ ] Update `ClubActivityController::show()` eager load if needed (already loads `confirmedParticipants.user`)
- [ ] Test: create singles custom match → appears in correct round
- [ ] Test: create doubles custom match into existing round
- [ ] Test: duplicate player validation returns 422

## Success Criteria
- Custom match created and visible in UI immediately after submit
- Player selects populated from confirmed participants only
- Singles mode hides partner selects (player2/player4)
- New round auto-created when no round selected
- Existing round option works correctly
- Duplicate player in same match rejected with clear error
- Score entry works on custom matches (reuses Phase 3 flow)

## Risk Assessment
- **Participant data in Blade:** passing JSON of participants as data attribute is safe for reasonable group sizes (<200); no N+1 since controller already eager loads
- **Round select sync:** must refresh round select after `loadRounds()` — `populateRoundSelect()` must run post-render
- **Player constraint skipped:** custom matches intentionally bypass no-repeat-partner constraint (it's ad-hoc by design)

## Security
- `createCustom` requires `manageActivity` policy
- Player IDs validated as confirmed participants of this specific activity (prevent adding outsiders)
- `round_id` validated as belonging to this activity (prevent cross-activity injection)
