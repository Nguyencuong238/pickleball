# Code Review: Bracket Match Editor

## Scope
- Files: 8 (4 backend, 4 frontend)
- LOC: ~450 new/modified
- Focus: Bracket match editing feature (athlete reassignment, properties, cascade)

## Overall Assessment
Solid feature with good cascade detection and modal UX. Several security and correctness issues need attention, ranging from critical (authorization bypass on athlete IDs) to medium (race conditions, edge cases).

---

## Critical Issues

### 1. IDOR: athlete IDs not scoped to tournament (Security)
**File:** `TournamentBracketController::updateMatch()` line 127-128
```php
'athlete1_id' => 'nullable|integer|exists:tournament_athletes,id',
'athlete2_id' => 'nullable|integer|exists:tournament_athletes,id',
```
**Problem:** Validates that `tournament_athletes.id` exists globally, but does NOT verify the athlete belongs to the same tournament. An attacker can inject athlete IDs from other tournaments.

**Fix:** Scope the exists rule:
```php
'athlete1_id' => ['nullable', 'integer', Rule::exists('tournament_athletes', 'id')->where('tournament_id', $tournament->id)],
'athlete2_id' => ['nullable', 'integer', Rule::exists('tournament_athletes', 'id')->where('tournament_id', $tournament->id)],
```

### 2. Same athlete in both slots not prevented (Logic Bug)
**File:** `TournamentBracketController::updateMatch()` + `KnockoutBracketQuery::updateMatchAthletes()`

No validation prevents `athlete1_id === athlete2_id`. A user can assign the same athlete/pair to both sides of a match.

**Fix:** Add validation in controller:
```php
// After validation
if ($validated['athlete1_id'] && $validated['athlete2_id']
    && (int) $validated['athlete1_id'] === (int) $validated['athlete2_id']) {
    return response()->json(['success' => false, 'message' => 'Hai VDV phai khac nhau'], 422);
}
```

---

## High Priority

### 3. Cascade clear runs OUTSIDE the DB transaction (Race Condition)
**File:** `KnockoutBracketService::updateMatch()` lines 239-240
```php
if ($affectedCount > 0) {
    $this->bracketQuery->cascadeClearDownstream($match);  // <-- outside DB::transaction
}

return DB::transaction(function () use ($match, $data) {  // <-- transaction starts after
```
**Problem:** `cascadeClearDownstream` runs before the transaction wrapping `updateMatchAthletes` + `reEvaluateMatch`. If the transaction fails/rolls back, downstream matches are already cleared -- data loss.

**Fix:** Move cascade clear inside the transaction:
```php
return DB::transaction(function () use ($match, $data, $athleteChanged, $affectedCount) {
    if ($athleteChanged && $affectedCount > 0) {
        $this->bracketQuery->cascadeClearDownstream($match);
    }
    $this->bracketQuery->updateMatchAthletes($match, $data);
    $this->reEvaluateMatch($match->fresh());
    return ['success' => true, 'message' => 'Cap nhat thanh cong'];
});
```

### 4. Bronze round excluded from eligible athletes logic
**File:** `KnockoutBracketQuery::getEligibleAthletes()` line 100
```php
$bracketRoundTypes = ['knockout', 'quarterfinal', 'semifinal', 'final'];
```
Bronze round is excluded from `$bracketRoundTypes`. When editing the bronze match:
- `$firstBracketRound` will be a knockout round, not bronze
- `$currentRoundNumber` for bronze is 99
- It falls into the `else` branch, looking for winners from previous rounds with types in the array -- this actually works correctly for finding semifinal losers, BUT it finds *winners* not *losers*. Bronze match should get semifinal *losers*, not winners.

**Impact:** Eligible athletes for bronze match will be wrong -- it returns semifinal winners instead of losers.

**Fix:** Add special handling for bronze round:
```php
if ($round->round_type === 'bronze') {
    // Get semifinal losers
    $semiFinalRound = Round::where('tournament_id', $tournamentId)
        ->where('category_id', $categoryId)
        ->where('round_type', 'semifinal')
        ->first();
    if ($semiFinalRound) {
        $semiMatches = MatchModel::where('round_id', $semiFinalRound->id)->get();
        $loserIds = $semiMatches->map(function ($m) {
            if (!$m->winner_id) return null;
            return $m->winner_id === $m->athlete1_id ? $m->athlete2_id : $m->athlete1_id;
        })->filter();
        $eligible = $basePool->whereIn('id', $loserIds);
    }
}
```

### 5. `countCascadeAffected` follows single chain only
**File:** `KnockoutBracketQuery::countCascadeAffected()` line 185
```php
while ($currentMatch->next_match_id) { ... }
```
This follows the `next_match_id` chain. If changing an athlete in match A affects match B downstream, which then also feeds into a bronze match or other path, those won't be counted. Same issue in `cascadeClearDownstream`.

**Impact:** Low for standard single-elimination brackets (one chain), but could miss clearing the bronze match if the match's athlete was also placed there.

### 6. No `lockForUpdate` on match during update
**File:** `KnockoutBracketService::updateMatch()`

The match is fetched without pessimistic locking. Two concurrent requests could both read, both check cascade, then both write -- causing inconsistent state.

**Fix:** Use `lockForUpdate()` inside the transaction:
```php
$match = MatchModel::lockForUpdate()->findOrFail($match->id);
```

---

## Medium Priority

### 7. `findMatch` defined in two mixins (DRY violation)
**Files:** `bracket-score-entry.js` line 21, `bracket-swap-editor.js` line 73

Both mixins define `findMatch()` identically. When spread into `bracketManager`, the last one wins (swap editor). Works by accident but fragile.

**Fix:** Move `findMatch` to `bracketManager` directly or to a shared utility mixin.

### 8. Match status check too lenient in controller
**File:** `TournamentBracketController::updateMatch()` lines 138-141
```php
$isCompleted = $match->status === 'completed'
    && $match->athlete1_id && $match->athlete2_id
    && $match->set_scores;
```
A match with status `in_progress` can be edited. Should likely also block `in_progress` matches.

### 9. `notes` field not sanitized for HTML/XSS
**File:** `bracket-match-editor.js` -- notes sent as raw string, stored in DB. If `notes` is ever rendered with `{!! !!}` in Blade (or via innerHTML in JS), XSS is possible. Currently safe since Alpine uses `x-text`, but worth noting.

The `max:500` validation is good. Consider `strip_tags` or storing sanitized.

### 10. URL construction brittle
**File:** `bracket-match-editor.js` line 39
```js
var url = this.dataUrl.replace('/data', '/eligible-athletes');
```
Relies on `dataUrl` containing `/data` at the right position. If route naming changes, this silently breaks.

**Fix:** Pass URLs from Blade directly (like `dataUrl` and `generateUrl` are passed).

### 11. `match_time` can be cleared but no explicit null handling
**File:** `bracket-match-editor.js` lines 86-88
```js
if (this.editForm.match_time) {
    body.match_time = this.editForm.match_time;
}
```
If user clears `match_time`, the field is not sent in the request, so the old value persists. User cannot clear a previously set match time.

**Fix:** Always send `match_time` (as null if empty):
```js
body.match_time = this.editForm.match_time || null;
```

---

## Low Priority

### 12. N+1 queries in `cascadeClearDownstream`
Each iteration does `MatchModel::find()` then `->update()` -- one SELECT + one UPDATE per downstream match. For deep brackets this is fine (max ~6 rounds), but for consistency could collect IDs first then bulk update.

### 13. Two separate `findMatch` definitions duplicate code
See #7 -- this is also a maintenance burden.

### 14. Alpine.js loaded from CDN with `@3.x.x` wildcard
**File:** `bracket.blade.php` line 17
```html
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
```
Pre-existing issue, not introduced by this PR. Consider pinning to specific version.

---

## Positive Observations
- Clean separation of concerns: Query layer, Service layer, Controller layer
- Cascade detection + confirmation UX is well-designed
- Eligible athletes filtering correctly excludes athletes used in other matches of the same round
- CSRF token properly included in all fetch calls
- Tournament ownership check (`authorizeOwner`) on all endpoints
- `match_id` validated with `Rule::exists` scoped to tournament

---

## Recommended Actions (Priority Order)
1. **[Critical]** Scope `athlete1_id`/`athlete2_id` exists validation to tournament
2. **[Critical]** Validate `athlete1_id !== athlete2_id`
3. **[High]** Move `cascadeClearDownstream` inside the DB transaction
4. **[High]** Add bronze-round-specific eligible athlete logic (semifinal losers)
5. **[High]** Add `lockForUpdate` in the transaction
6. **[Medium]** Block editing of `in_progress` matches
7. **[Medium]** Fix `match_time` clearing behavior in JS
8. **[Medium]** Consolidate duplicate `findMatch` methods
9. **[Low]** Pass URLs from Blade instead of string replacement

---

## Unresolved Questions
1. Should editing be allowed on the bronze match at all, given its special nature (losers bracket)?
2. Is there a scenario where `next_match_id` can form a cycle? (If yes, `cascadeClearDownstream` would infinite loop)
3. Should category_id also be validated on athlete IDs (athlete belongs to same category)?
