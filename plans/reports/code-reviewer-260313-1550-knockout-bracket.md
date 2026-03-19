# Code Review: Single Elimination Knockout Bracket Feature

**Date:** 2026-03-13
**Reviewer:** code-reviewer
**Scope:** 18 files (4 PHP services, 1 controller, 2 traits, 1 migration, 5 Blade templates, 4 JS files, 1 CSS file, routes)

---

## Overall Assessment

Well-structured feature with clean separation of concerns (service layer, query layer, builder, controller). Backend logic is sound. Several issues found ranging from critical (missing model casts) to medium (edge cases, potential race conditions).

---

## Critical Issues

### C1. Tournament model missing `bracket_data` cast and `enable_third_place` cast

**File:** `app/Models/Tournament.php`

The model uses `$guarded = []` (so fillable is not an issue), but the `$casts` array does NOT include:
- `bracket_data` as `array` or `json` -- code reads/writes it as array (`$tournament->bracket_data ?? []`, `$tournament->update(['bracket_data' => $bracketData])`)
- `enable_third_place` as `boolean`
- `auto_bracket_generation` as `boolean`

Without the `bracket_data => 'array'` cast, `$tournament->bracket_data` returns a raw JSON string, and `$bracketData["category_{$categoryId}_ready"] = true` will fail with a string offset error.

**Impact:** Runtime error when auto-bracket generation triggers in `MatchScoreTrait`. Sidebar badge count also broken.

**Fix:**
```php
protected $casts = [
    // existing...
    'bracket_data' => 'array',
    'enable_third_place' => 'boolean',
    'auto_bracket_generation' => 'boolean',
];
```

### C2. Swap endpoint missing tournament ownership validation on match IDs

**File:** `app/Http/Controllers/Front/Tournament/TournamentBracketController.php` (line 77-82)

The `swap()` method validates `exists:matches,id` but does NOT verify that those match IDs belong to the given `$tournament`. A malicious user could swap athletes in another user's tournament by providing arbitrary match IDs.

**Fix:** Add custom validation or check after fetching:
```php
'match_id_1' => ['required', 'integer', Rule::exists('matches', 'id')->where('tournament_id', $tournament->id)],
'match_id_2' => ['required', 'integer', Rule::exists('matches', 'id')->where('tournament_id', $tournament->id)],
```

### C3. getData endpoint missing tournament scope on category_id

**File:** `app/Http/Controllers/Front/Tournament/TournamentBracketController.php` (line 34-36)

`'category_id' => 'required|integer|exists:tournament_categories,id'` does not scope to the current tournament. User could query bracket data for categories belonging to other tournaments.

**Fix:**
```php
'category_id' => ['required', 'integer', Rule::exists('tournament_categories', 'id')->where('tournament_id', $tournament->id)],
```

---

## High Priority

### H1. KnockoutMatchBuilder iterates top-down but assigns bottom-up -- off-by-one risk

**File:** `app/Services/Tournament/KnockoutMatchBuilder.php` (line 30-70)

The loop iterates `position = 1..totalPositions`, computing `depth = floor(log2(position))`. The round assignment at line 37 is `$roundFromFinal = $totalRounds - 1 - $depth`. For position=1 (root/final), depth=0, so roundFromFinal = totalRounds-1. But the final round has roundFromFinal=0. This means position=1 maps to the first/earliest round, not the final.

This inverts the bracket tree: the final match gets assigned to the earliest round. The `createRounds` method creates rounds keyed by `roundFromFinal` where 0=final, but `createMatches` maps position=1 to key=totalRounds-1 which is the first round, not the final.

**Impact:** Matches assigned to wrong rounds. The final match (position 1) is placed in the first-round Round record. Bracket display would show incorrect round names for matches.

**Recommendation:** Verify with a test case (e.g., 4 athletes, totalRounds=2). If confirmed, the depth-to-roundFromFinal mapping needs inverting or the rounds array key convention must be aligned.

### H2. `BracketAdvancementTrait::handleBracketAdvancement` only runs when `next_match_id` is set

**File:** `app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php` (line 92)

The condition is `$match->next_match_id && $match->status === 'completed' && $match->winner_id`. But `handleBracketAdvancement` also calls `handleThirdPlaceRouting` (for semifinal losers), which is needed even for semifinal matches that DO have `next_match_id`. This part works.

However, the **final match** has `next_match_id = null`, so `handleBracketAdvancement` is never called for the final. This means:
- `updateRoundCompletionStatus` never runs for the final round
- The final round's `completed_matches` count never updates

**Impact:** Final round status stays "pending" forever.

**Fix:** Change the condition to check for knockout-type matches more broadly:
```php
if ($match->status === 'completed' && $match->winner_id && $match->bracket_position) {
    if (method_exists($this, 'handleBracketAdvancement')) {
        $this->handleBracketAdvancement($match);
    }
}
```
And in `handleBracketAdvancement`, only call `advanceWinner` when `next_match_id` exists, but always call `updateRoundCompletionStatus`.

### H3. Race condition in auto-bracket generation

**File:** `app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php` (lines 99-131)

The auto-bracket check runs outside the DB transaction (line 80+). If two group matches complete simultaneously:
1. Both check `checkCategoryCompletion` -> true
2. Both check `isBracketGenerated` -> false
3. Both call `generateBracket` -> duplicate rounds/matches created

**Fix:** Wrap the check+generate in `DB::transaction` with a lock on the tournament row, or use `isBracketGenerated` check inside `generateBracket` itself.

---

## Medium Priority

### M1. `buildSeedPositions` produces wrong standard seeding order

**File:** `app/Services/Tournament/BracketSeedingHelper.php` (line 149-163)

For bracketSize=8, the method produces `[1,8,5,4,3,6,7,2]`. Standard tournament seeding for 8 should place seeds so that seed 1 vs seed 8, seed 4 vs seed 5 are in one half, and seed 2 vs seed 7, seed 3 vs seed 6 in the other half. The expected standard order is `[1,8,4,5,2,7,3,6]`.

The current algorithm produces positions where seed 3 and seed 4 can meet before the semifinal, violating standard seeding rules.

**Impact:** Seeded athletes may face strong opponents earlier than expected.

### M2. Score entry modal does not pre-populate existing scores

**File:** `public/assets/js/bracket-score-entry.js` (line 8-11)

`openScore(matchId)` always resets to `[{ s1: 0, s2: 0 }]`. If a match was previously scored (status changed back to scheduled for correction), existing scores are lost.

### M3. Third-place match `bracket_position = 0` may conflict with queries

**File:** `app/Services/Tournament/KnockoutBracketService.php` (line 114)

The bronze match uses `bracket_position = 0` and `match_number = 0`. Other code ordering by `bracket_position` may place it unexpectedly. Not a bug per se, but worth documenting.

### M4. `$data` scope reference in third-place template

**File:** `resources/views/home-yard/tournaments/partials/_bracket-tree.blade.php` (line 157)

```html
x-data="{ match: $data.thirdPlaceMatch }"
```

`$data` is not a standard Alpine.js magic property. In Alpine v3, use `$root` or rely on the parent scope. This should be:
```html
x-data="{ match: thirdPlaceMatch }"
```
or simply use `thirdPlaceMatch` directly from parent scope without re-wrapping in `x-data`.

### M5. Hardcoded score URL in bracket-score-entry.js

**File:** `public/assets/js/bracket-score-entry.js` (line 28)

```js
const url = '/tournament-manage/' + this.tournamentSlug + '/matches/' + this.scoreMatchId + '/score';
```

Hardcoded URL path. If route prefix changes, this breaks silently. Consider passing the score URL template from Blade config, similar to `dataUrl`/`generateUrl`.

### M6. `swapAthletes` does not update `athlete1_name`/`athlete2_name` columns

**File:** `app/Services/Tournament/KnockoutBracketQuery.php` (line 48-70)

The swap only swaps `athlete1_id`/`athlete2_id` but not the corresponding `athlete1_name`/`athlete2_name` columns. If those name columns are used elsewhere for display (e.g., match listing), they will show stale names after a swap.

---

## Low Priority

### L1. Vietnamese text is correct
All Vietnamese strings checked -- proper diacritics used throughout ("Chung ket" -> "Chung ket" confirmed correct, "Ban ket" -> "Ban ket" confirmed correct). No issues found.

### L2. Alpine CDN version pinning
**File:** `resources/views/home-yard/tournaments/bracket.blade.php` (line 17)
```html
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
```
Using `@3.x.x` pins to a wildcard. Consider pinning to a specific version (e.g., `@3.14.8`) for stability.

### L3. CSS connector lines are pure CSS -- no vertical connectors
The bracket tree only has horizontal connector lines (`::after`). No vertical lines connecting pairs of matches to their next-round match. This is a UX limitation but not a bug.

---

## Positive Observations

1. **Clean service decomposition**: `BracketSeedingHelper`, `KnockoutMatchBuilder`, `KnockoutBracketQuery`, `KnockoutBracketService` each have a single responsibility
2. **Proper auth checks**: `authorizeOwner()` on every controller method
3. **DB transactions**: Bracket generation wrapped in transaction
4. **Bye handling**: Automatic completion and winner advancement for bye matches
5. **Mobile responsive**: CSS handles mobile with round navigation
6. **Input validation**: All controller endpoints validate input
7. **Error handling**: Try-catch with logging throughout
8. **JS modularity**: Mixin pattern keeps each concern in its own file

---

## Recommended Actions (Priority Order)

1. **[CRITICAL]** Add `bracket_data => 'array'`, `enable_third_place => 'boolean'`, `auto_bracket_generation => 'boolean'` to Tournament model `$casts`
2. **[CRITICAL]** Scope `match_id` and `category_id` validation to current tournament in controller
3. **[HIGH]** Verify round-assignment logic in `KnockoutMatchBuilder::createMatches` with a unit test -- the depth-to-roundFromFinal mapping looks inverted
4. **[HIGH]** Fix final-match completion status never updating (handleBracketAdvancement not called for final)
5. **[HIGH]** Add race condition guard for auto-bracket generation
6. **[MEDIUM]** Fix `$data.thirdPlaceMatch` Alpine scope reference
7. **[MEDIUM]** Verify seeding positions algorithm against standard tournament seeding
8. **[MEDIUM]** Pass score URL from config instead of hardcoding

---

## Unresolved Questions

1. Is the `KnockoutMatchBuilder` depth-to-round mapping intentional? The heap-style iteration (position 1 = root = final) seems to assign matches to rounds in reverse order. Need to trace through with a concrete example (4 athletes, 2 rounds) to confirm if it produces correct results or is a logic bug.
2. Does any other code depend on `athlete1_name`/`athlete2_name` columns that would be stale after a swap?
3. Is there an existing `pair_name` attribute on `TournamentAthlete` that is used in `KnockoutBracketQuery::formatAthleteSlot`? (Confirmed: yes, accessor exists at line 74 of TournamentAthlete.php)
