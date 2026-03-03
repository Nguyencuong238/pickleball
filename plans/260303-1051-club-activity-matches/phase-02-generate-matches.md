# Phase 02 - Generate Matches

## Context Links
- Plan overview: `plans/260303-1051-club-activity-matches/plan.md`
- Phase 01: `plans/260303-1051-club-activity-matches/phase-01-schema-models-tab-ui.md`
- Competition controller (API pattern): `app/Http/Controllers/ClubCompetitionController.php`
- Competition service (service pattern): `app/Services/ClubCompetitionService.php`
- Routes: `routes/web.php` lines 314-323
- Matches panel stub: `resources/views/clubs/activities/partials/_matches-panel.blade.php`

## Overview
- **Priority:** P1 (blocks Phase 3)
- **Status:** complete
- **Goal:** AJAX endpoint + service to generate match rounds from confirmed participants, render rounds/matches in UI

## Requirements
- Input: confirmed participants, court_count, format (`rotating_doubles` / `fixed_doubles` / `singles_rr`)
- Generate rounds with court assignments
- Handle odd player counts with bye (null player)
- No repeat partner in rotating_doubles across rounds
- Display rounds grouped, show player names + court numbers
- Management-only generation; all members can view matches

## Architecture

### Generation Algorithms

**singles_rr (Round Robin)**
- Standard polygon/circle rotation for N players
- N-1 rounds if N even, N rounds if N odd (bye rotation)
- Each round: floor(N/2) matches

**rotating_doubles**
- Treat 4 players per court as a unit per round
- Use polygon rotation on player list; constraint: partner from previous round cannot repeat
- N players → floor(N/4) courts per round × multiple rounds
- Fallback: if constraint can't be met (small N), allow repeat after warning

**fixed_doubles**
- Shuffle players into permanent pairs
- Pairs play round-robin against each other (reuse singles_rr logic on pairs)

### Service: `ClubMatchService`
```
generateMatches(ClubActivity $activity, string $format, int $courtCount): void
  - fetch confirmed participants
  - validate min players per format (singles: 2+, doubles: 4+)
  - call private method per format
  - wrap in DB::transaction
  - create ClubActivityMatchRound + ClubActivityMatch records

private generateSinglesRR(array $playerIds, int $courts): array  → rounds[]
private generateRotatingDoubles(array $playerIds, int $courts): array → rounds[]
private generateFixedDoubles(array $playerIds, int $courts): array → rounds[]
private distributeMatchesToCourts(array $matches, int $courts): array
private deleteExistingMatches(ClubActivity $activity): void
```

### Controller: `ClubMatchController`
Routes under `{activity}/matches` prefix, name prefix `matches.`:
```
GET    matches/rounds          → index()      view rounds + matches (JSON)
POST   matches/generate        → generate()   generate matches (management)
PUT    matches/rounds/{round}/complete → completeRound() (management)
DELETE matches/rounds          → reset()      delete all rounds (management)
```

### Route Registration
Add to `routes/web.php` inside `clubs.activities.` group:
```php
Route::prefix('{activity}/matches')->name('matches.')->group(function () {
    Route::get('rounds', [ClubMatchController::class, 'index'])->name('index');
    Route::post('generate', [ClubMatchController::class, 'generate'])->name('generate');
    Route::put('rounds/{round}/complete', [ClubMatchController::class, 'completeRound'])->name('complete-round');
    Route::delete('rounds', [ClubMatchController::class, 'reset'])->name('reset');
});
```

### JSON Response Shape
`GET matches/rounds`:
```json
{
  "rounds": [
    {
      "id": 1, "round_number": 1, "status": "completed",
      "matches": [
        {
          "id": 1, "court_number": 1, "match_type": "doubles", "status": "completed",
          "team1_score": 11, "team2_score": 8,
          "players": {
            "player1": {"id": 5, "name": "Nguyen Van A"},
            "player2": {"id": 7, "name": "Tran Thi B"},
            "player3": {"id": 3, "name": "Le Van C"},
            "player4": {"id": 9, "name": "Pham Thi D"}
          }
        }
      ]
    }
  ]
}
```

## Related Code Files

### Create
- `app/Services/ClubMatchService.php`
- `app/Http/Controllers/ClubMatchController.php`
- `resources/views/clubs/activities/partials/_matches-generate-modal.blade.php`
- `resources/views/clubs/activities/partials/_matches-scripts.blade.php`

### Modify
- `routes/web.php` — add matches route group
- `resources/views/clubs/activities/partials/_matches-panel.blade.php` — add rounds container + modal trigger
- `resources/views/clubs/activities/partials/_matches-styles.blade.php` — add round/match card styles

## Implementation Steps

1. **ClubMatchService** (`app/Services/ClubMatchService.php`, ~180 lines)
   - Constructor: no deps needed, pure PHP logic
   - `generateMatches()`: validate, call format method, DB::transaction wrap
   - `generateSinglesRR()`: polygon rotation algorithm
     ```php
     // N players: fix player[0], rotate player[1..N-1]
     // If N odd, add null as bye
     ```
   - `generateRotatingDoubles()`: pair rotation with partner-history tracking
   - `generateFixedDoubles()`: shuffle into pairs, then RR on pairs
   - `distributeMatchesToCourts()`: assign court_number 1..N cycling
   - `deleteExistingMatches()`: delete rounds (cascade deletes matches)
   - Keep file under 200 lines — split algorithms to private helpers

2. **ClubMatchController** (`app/Http/Controllers/ClubMatchController.php`, ~120 lines)
   - `index()`: load rounds with matches + player names (eager load)
   - `generate()`: validate `format` in, `court_count` 1-10, call service, return rounds JSON
   - `completeRound()`: set round status = completed
   - `reset()`: delete all rounds for activity
   - Use same `validateActivityBelongsToClub()` pattern as `ClubCompetitionController`
   - `authorize('manageActivity', $club)` for generate/complete/reset
   - `authorize('view', $club)` for index

3. **Routes** — add matches group to `routes/web.php` after competition group (line ~323)

4. **Generate Modal** (`_matches-generate-modal.blade.php`)
   - Hidden modal div, only rendered if `$isManagement`
   - Form fields:
     - `format` select: `rotating_doubles` (Đánh đôi luân phiên), `fixed_doubles` (Đánh đôi cố định), `singles_rr` (Đánh đơn vòng tròn)
     - `court_count` number input 1-10, default 2
   - Submit button calls `generateMatches()` JS function
   - Show confirmed participant count as hint: "{{ $activity->confirmed_participants_count }} người xác nhận"

5. **_matches-scripts.blade.php** (vanilla JS, ~150 lines)
   - `window.MATCH_ROUTES` object with route URLs (use `@json(route(...))`)
   - `loadRounds()`: fetch GET rounds, render or show empty state
   - `openGenerateModal()` / `closeGenerateModal()`
   - `generateMatches()`: POST generate, on success call `loadRounds()`
   - `renderRounds(rounds)`: build HTML string, inject into `#matches-rounds-container`, hide empty state
   - `renderMatch(match)`: returns match card HTML
   - Auto-call `loadRounds()` on tab activation (add to tab switch logic or DOMContentLoaded check for hash)

6. **Update _matches-panel.blade.php** — add:
   - `<div id="matches-rounds-container"></div>` (hidden initially)
   - Pass `$club->id` and `$activity->id` as data attributes on container for JS
   - Include `_matches-generate-modal.blade.php` if `$isManagement`
   - Include `_matches-scripts.blade.php`

7. **Tab load trigger** — in `_matches-scripts.blade.php`, listen for tab switch:
   ```js
   document.querySelectorAll('[data-tab="matches"]').forEach(btn =>
     btn.addEventListener('click', loadRounds)
   );
   // Also load if arriving via #matches hash
   if (window.location.hash === '#matches') loadRounds();
   ```

## Todo List
- [ ] `ClubMatchService` — singles_rr algorithm
- [ ] `ClubMatchService` — rotating_doubles algorithm
- [ ] `ClubMatchService` — fixed_doubles algorithm
- [ ] `ClubMatchService` — court distribution + DB persistence
- [ ] `ClubMatchController` — index, generate, completeRound, reset
- [ ] Register routes in `routes/web.php`
- [ ] `_matches-generate-modal.blade.php`
- [ ] `_matches-scripts.blade.php` — loadRounds, renderRounds, generateMatches
- [ ] Update `_matches-panel.blade.php` with container + modal include
- [ ] Manual test: generate singles_rr with 6 players, 2 courts
- [ ] Manual test: generate rotating_doubles with 8 players, 2 courts
- [ ] Verify odd-player bye handling

## Success Criteria
- POST generate creates rounds + matches in DB
- GET rounds returns correct JSON structure
- UI renders rounds grouped by round number
- Court assignments shown on match cards
- Player names resolved (not just IDs)
- Bye matches shown clearly ("Nghỉ" label)
- Reset clears all rounds (cascade)
- Generating when rounds exist replaces them (delete + regenerate)

## Risk Assessment
- **Small player counts:** rotating_doubles needs min 4; add clear validation message
- **Partner repeat constraint:** with very few players (4 exactly), constraint impossible — skip constraint silently
- **File size:** `ClubMatchService` will be dense — split if >200 lines (e.g. extract `MatchAlgorithms` trait)
- **N² complexity:** round-robin for large groups (30+) fine in PHP; no optimization needed

## Security
- `generate`, `completeRound`, `reset` require `manageActivity` policy
- Validate `court_count` range server-side (1-10)
- Validate `format` is in allowed enum server-side
- `index` only requires `view` policy (members can see matches)
