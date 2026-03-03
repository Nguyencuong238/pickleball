# Code Review: Club Activity Matches Feature

**Date:** 2026-03-03
**Scope:** 15 files — service, controller, 3 models, 3 migrations, routes, 6 blade partials

---

## Overall Assessment

Solid, well-structured feature that follows existing patterns (`ClubCompetitionController` / `ClubCompetitionService`). Authorization, CSRF, and input validation are all present. The primary concerns are a standings bug on re-generate, a missing cascade delete for standings in `deleteExistingMatches`, one N+1 query risk, and a minor XSS gap in JS.

---

## Critical Issues

### 1. Standings not cleared on re-generate

**File:** `app/Services/ClubMatchService.php` L244-247

`deleteExistingMatches()` only deletes rounds (which cascade-deletes matches). It does NOT delete standings. If a manager re-generates matches, old standings remain, then `recalculateStandings` only recalculates for players in the newly scored matches — players from the old schedule retain stale win/loss records until they play again.

**Fix:**
```php
private function deleteExistingMatches(ClubActivity $activity): void
{
    $activity->matchRounds()->delete();
    $activity->matchStandings()->delete(); // add this
}
```

---

## High Priority

### 2. N+1 query: `recalculateStandings` fetches `round_id` list per player

**File:** `app/Services/ClubMatchService.php` L280

`$activity->matchRounds()->pluck('id')` is called once per invocation — that's fine. But it is inside `saveScore()` which is called from a transaction. The real issue: this query is not eager-loaded and repeats on every call. Consider caching the `$roundIds` in the service call or accepting it as a parameter. Minor at current scale but worth noting.

### 3. `saveScore` loads `$match->round` relationship lazily

**File:** `app/Services/ClubMatchService.php` L269

```php
$activityId = $match->round->club_activity_id;
$activity = ClubActivity::findOrFail($activityId);
```

This issues two extra queries (lazy-load `round`, then `findOrFail` activity) when the activity is already available in the controller. Better to pass `$activity` into `saveScore()`:

```php
// Controller
$this->service->saveScore($match, $activity, $validated['team1_score'], $validated['team2_score']);

// Service signature
public function saveScore(ClubActivityMatch $match, ClubActivity $activity, int $team1Score, int $team2Score): void
```

Also avoids the `findOrFail` pattern inside a transaction.

### 4. `enableScoreEdit` querySelector is fragile

**File:** `_matches-scripts.blade.php` L152

```js
var card = document.querySelector('.match-card .btn-edit-score[onclick*="' + matchId + '"]');
```

Uses `onclick*=` attribute substring match, which is brittle if `matchId` collides as a substring (e.g., id=1 matches id=10, id=11). Could silently edit the wrong match's score display.

**Fix:** Use a `data-match-id` attribute and querySelector by that:
```js
var editBtn = document.querySelector('[data-match-id="' + matchId + '"] .btn-edit-score');
```
Requires adding `data-match-id="{{ m.id }}"` to the `.match-card` div in `renderMatch()`.

---

## Medium Priority

### 5. `unsignedTinyInteger` for `round_number` — overflow risk

**File:** Migration `000001` L14

`TINYINT UNSIGNED` holds max 255. For activity types with many players (e.g., 30-player singles RR = 29 rounds × future repeats), this is fine. But for rotating doubles where `totalRounds = max(3, ceil(n/4)+1)` and a manager re-generates multiple times (cumulative round numbers increment), `round_number` could in theory never reach 255 in practice, but `SMALLINT` costs nothing more and removes the mental footnote.

### 6. `unsignedTinyInteger` for scores (0-99) — validation matches, good

Score columns are `TINYINT UNSIGNED` (0-255) while validation enforces max 99. Consistent and intentional. No issue.

### 7. Draw for rotating doubles is non-deterministic and may loop

**File:** `app/Services/ClubMatchService.php` L109-139

`generateRotatingDoubles` shuffles on every round, then calls `pickDoublesGroup` which takes the first 4 from the shuffled list and checks 3 arrangements. If all 3 arrangements have repeated partners, it falls back to arrangement 0 (documented). The outer loop calls `break` when `$group === null` (when `count($available) < 4`). This is fine. However: if the count is always `>= 4` but `pickDoublesGroup` keeps returning non-null fallbacks, the loop still terminates (it runs `$matchesPerRound` times max). Logic is safe, just could produce repeated partners more than expected with small player pools. Document the fallback behavior.

### 8. `deleteExistingMatches` does not cascade standings in `reset` endpoint

**File:** `app/Http/Controllers/ClubMatchController.php` L105-106

The controller `reset()` manually deletes both — correct. The `deleteExistingMatches()` service method (called only from `generateMatches`) is the one missing the standings delete. Already flagged as Critical above.

### 9. `confirmedParticipants` relationship used as both query and collection

**File:** `_matches-panel.blade.php` L41

```php
$activity->confirmedParticipants->map(...)
```

This uses the cached collection (property access). The service uses `$activity->confirmedParticipants()->pluck(...)` (query builder). In the same request the view may double-query if not already eager-loaded. Low impact but inconsistent.

### 10. `round_id` validation does not cross-check club

**File:** `app/Http/Controllers/ClubMatchController.php` L166

```php
'round_id' => 'nullable|exists:club_activity_match_rounds,id',
```

The rule verifies the round exists globally. However, `ClubMatchService::createCustomMatch` does:
```php
$round = ClubActivityMatchRound::where('id', $roundId)
    ->where('club_activity_id', $activity->id)
    ->firstOrFail();
```

So it IS validated at the service layer. Acceptable, but could be tightened at the validation layer too with a custom rule or `Rule::exists()->where('club_activity_id', $activity->id)`.

---

## Low Priority

### 11. Error strings are unlocalized Vietnamese ASCII

Error messages in `ClubMatchService` use unaccented Vietnamese (e.g., `"Can it nhat {$minPlayers} nguoi choi"`). These are returned as JSON and surfaced via `alert()` in JS. Fine for now, but when i18n is needed, these will need extraction. Not a bug.

### 12. `getStandings` computes `point_differential` via Eloquent accessor — not SQL-ordered

**File:** `app/Services/ClubMatchService.php` L334

`orderByRaw('(points_scored - points_against) DESC')` is correct SQL. The accessor on the model (`getPointDifferentialAttribute`) is a convenience for the return array. Consistent and correct.

### 13. `unsignedTinyInteger` for `matches_played`, `wins`, `losses`

**File:** Migration `000003`

Max 255 matches per player per activity. Practically safe.

### 14. File size — `_matches-scripts.blade.php` is 363 lines

Exceeds the 200-line guideline. Could be split into `_matches-rounds-script.blade.php` and `_matches-forms-script.blade.php`, though the IIFE wrapper makes it coherent as one unit. Low priority given it's one logical closure.

---

## Edge Cases Found by Scouting

1. **Re-generate with existing completed matches** — standings from prior round remain (see Critical #1). Players who left their confirmed status after generation but before re-generation keep stale IDs in old standings.

2. **Tie-score (team1_score === team2_score)** — `recalculateStandings` counts ties as a `loss` for both players (neither `> oppScore`). Intentional? No draw handling exists. Should be documented or a `draws` column added.

3. **Singles match `player2_id` is null** — `recalculateStandings` uses `array_filter` which removes nulls, so player2/player4 null for singles are correctly excluded from standings update. Safe.

4. **Player deleted after match created** — `player1_id` and `player3_id` have `cascadeOnDelete` on users. If a user is deleted, the match row is also deleted, which may corrupt completed standings. Consider `nullOnDelete` for all 4 player FKs and handle nulls in standings logic.

5. **`roundsLoaded` flag not reset on `resetMatches`** — after a reset, `loadMatchRounds` correctly fetches 0 rounds and shows empty state, but `roundsLoaded` stays `true`. Next tab activation won't re-fetch. This is actually fine because `resetMatches` calls `loadMatchRounds()` directly. Flag could become stale only if the page is not refreshed after a network error mid-reset.

---

## Positive Observations

- Auth model is correct: `view` policy for reads, `manageActivity` for writes — consistent with `ClubCompetitionController`.
- `validateActivityBelongsToClub` guard is present on every endpoint.
- CSRF token properly attached to all fetch calls via `X-CSRF-TOKEN` header.
- `esc()` helper correctly uses `createTextNode` for DOM-safe escaping on all user-generated content.
- Duplicate-player guard in `createCustomMatch` (L373-375) is a good defensive check.
- DB transactions wrap both generate and score-save operations.
- `withLock(btn, fn)` prevents double-submit on all buttons.
- `tinyInteger` for score is appropriate for pickleball (max score ~21).
- Polygon rotation algorithm is textbook-correct for singles RR.
- Migration FKs: nullable player2/4 with `nullOnDelete` is correct for singles matches.

---

## Recommended Actions

1. **[Critical]** `deleteExistingMatches` — add `$activity->matchStandings()->delete()` before re-generating rounds.
2. **[High]** Pass `ClubActivity $activity` into `saveScore()` service method to remove lazy load and redundant `findOrFail`.
3. **[High]** Fix `enableScoreEdit` querySelector to use `data-match-id` attribute to prevent id substring collision.
4. **[Medium]** Decide on tie/draw handling in standings. Add `draws` column or document that ties count as losses.
5. **[Medium]** Change `player1_id` and `player3_id` FK behavior to `nullOnDelete` (not `cascadeOnDelete`) to prevent silent match deletion when a user is removed.
6. **[Low]** Tighten `round_id` validation rule to scope by `club_activity_id` at the request layer.
7. **[Low]** Bump `round_number` to `unsignedSmallInteger` for future-proofing.

---

## Metrics

- Type coverage: N/A (PHP/Blade)
- Linting: No syntax errors observed; logic is clean
- Validation: All write endpoints validated; read endpoints unauthenticated but rely on policy
- File size violations: 1 (`_matches-scripts.blade.php` ~363 lines)
- Migration correctness: FK constraints structurally correct; cascade behaviors reviewed

---

## Unresolved Questions

1. Should tied matches (equal scores) count as a loss for both sides, or should a `draws` column be added?
2. Should the matches tab appear only if the activity `status` is not `cancelled`? Currently renders for cancelled activities.
3. Is `unsignedTinyInteger` for `round_number` (max 255) intentional for activities with high repeat counts?
