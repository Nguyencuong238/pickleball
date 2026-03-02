# Code Review: Phase 5-6 (Scheduled Command + Testing)

## Scope
- **Files**: 15 files (1 command, 1 kernel mod, 4 factories, 5 test files, 2 migration fixes, plus reviewed underlying services/controllers/models)
- **LOC**: ~700 new lines
- **Focus**: Scheduled command, test suite, edge case scouting across services

## Overall Assessment

Good quality implementation. Tests are well-structured with real DB operations (no mocks), proper RefreshDatabase usage, and meaningful coverage of RSVP, waitlist, competition scheduling, and recurring meets. Several medium-priority edge cases found via scouting.

---

## Critical Issues

None found. No security vulnerabilities, no data loss risks, no secret exposure.

---

## High Priority

### H1. `saveScore` controller missing try-catch for `InvalidArgumentException`

**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/ClubCompetitionController.php` (line 113)

`saveMatchScore()` throws `InvalidArgumentException` when match is already completed, but the controller does not catch it -- resulting in a 500 error instead of a clean 422 JSON response.

```php
// Current (line 108-118):
$this->service->saveMatchScore($match, $validated['home_score'], $validated['away_score']);

// Fix:
try {
    $this->service->saveMatchScore($match, $validated['home_score'], $validated['away_score']);
} catch (InvalidArgumentException $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
}
```

### H2. Draw scenario awards zero points (no `points_for_draw` config)

**File**: `/Users/thaopv/Desktop/php/pickleball/app/Services/ClubCompetitionService.php` (lines 309-312)

When `homeScore === awayScore`, `winner_team_id` is null and both teams get `draws++` but **zero points**. The `DEFAULT_CONFIG` has no `points_for_draw` key. Most competition formats award 1 point for a draw. This is either a bug or intentional -- should be documented.

**Recommendation**: Add `'points_for_draw' => 1` to `DEFAULT_CONFIG` and apply it in the else branch:
```php
} else {
    $homeStanding->draws++;
    $awayStanding->draws++;
    $pointsForDraw = $this->getConfigValue($activity, 'points_for_draw');
    $homeStanding->points += $pointsForDraw;
    $awayStanding->points += $pointsForDraw;
}
```

### H3. No test for draw scenario in `ClubCompetitionServiceTest`

The test `test_save_match_score_updates_standings` only tests win/loss (3-1). No test verifies draw behavior (e.g., 2-2), which is important given H2 above.

---

## Medium Priority

### M1. `GenerateRecurringMeets` does not validate `--days` option

**File**: `/Users/thaopv/Desktop/php/pickleball/app/Console/Commands/GenerateRecurringMeets.php` (line 22)

`(int) $this->option('days')` silently converts non-numeric or negative values to 0, which just skips the loop. Technically safe but could confuse operators. Consider clamping:
```php
$daysAhead = max(1, min(90, (int) $this->option('days')));
```

### M2. `userCanJoin` treats `opr_level = 0` or `null` as passing skill checks

**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/ClubActivity.php` (lines 136-145)

If a user has `opr_level = null` (new user, no rating), the conditions `$user->opr_level < $this->min_skill_level` evaluate as `null < 3.0` which is true in PHP, so they'd be blocked. But `opr_level = 0` would pass `0 < 3.0` falsely blocking them too. Consider whether unrated users should be allowed regardless:
```php
if ($this->min_skill_level && $user->opr_level !== null && $user->opr_level < $this->min_skill_level) {
```

### M3. `cancelRsvp` controller catches generic `\Exception`

**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/ClubActivityParticipantController.php` (line 65)

The broad `\Exception` catch swallows all errors including ModelNotFoundException from `firstOrFail()`. A user who has never RSVP'd gets a vague 422 instead of a meaningful error. Consider catching `ModelNotFoundException` separately to return 404.

### M4. Migration `populate_video_and_user_slugs` -- `down()` uses static `update()` incorrectly

**File**: `/Users/thaopv/Desktop/php/pickleball/database/migrations/2025_12_26_populate_video_and_user_slugs.php` (lines 57-58)

```php
Video::update(['slug' => null]);  // This won't work -- Eloquent models don't have static update()
User::update(['slug' => null]);
```
Should be:
```php
Video::query()->update(['slug' => null]);
User::query()->update(['slug' => null]);
```

### M5. No test coverage for `cancelRsvp` when user not found (non-participant)

Feature test `ClubActivityRsvpTest` tests cancel for existing participant but not for a user who never RSVP'd. The `firstOrFail()` in service would throw -- verifying the controller handles it properly is important.

### M6. Missing test coverage for pool_play and single_elimination formats

`ClubCompetitionServiceTest` only tests `round_robin`. The `generatePoolPlay` and `generateSingleElimination` methods are untested. These have non-trivial logic (pool splitting, bracket byes).

---

## Low Priority

### L1. Factory `ClubActivityFactory::recurringChild` doesn't set `recurrence_day`

**File**: `/Users/thaopv/Desktop/php/pickleball/database/factories/ClubActivityFactory.php` (line 39)

The `recurringChild` state doesn't inherit `recurrence_day` from parent. Tests work because `createRecurringInstance` service sets it, but direct factory use could produce inconsistent child records.

### L2. `referrer_name` migration adds non-nullable column without default

**File**: `/Users/thaopv/Desktop/php/pickleball/database/migrations/2025_12_23_zz_add_referrer_name_to_referrals_table.php`

If there are referrals without matching users (orphaned records), the join-update won't populate them, leaving empty strings. Consider `$table->string('referrer_name')->nullable()->after(...)`.

### L3. Vietnamese text in command output

Not a bug, but the command output (`GenerateRecurringMeets`) and error messages in services use Vietnamese. Consistent with the codebase convention. No action needed unless i18n is planned.

---

## Edge Cases Found by Scouting

1. **Race condition on waitlist promotion**: `promoteFromWaitlist()` uses `lockForUpdate()` properly -- good.
2. **Re-RSVP after cancel**: Service correctly handles `$existing` with `cancelled` status, allowing re-join. Tested implicitly but no explicit test for this path.
3. **`max_participants = null`** (unlimited): `isFull()` returns false, `spotsLeft()` returns `PHP_INT_MAX`. The RSVP logic handles this via `$activity->max_participants &&` guard. Safe.
4. **Recurring template with no `activity_date` time**: `createRecurringInstance` calls `$template->activity_date->format('H:i:s')` -- if `activity_date` were null, this would throw. But the factory always sets it, and the `recurringTemplates()` scope filters by type/parent. Low risk.
5. **Double schedule generation**: `generateRoundRobin` deletes existing matches first. Good idempotency. But standings are not cleared -- `initializeStandings` uses `updateOrCreate` which resets values. OK.

---

## Positive Observations

- Real database testing with `RefreshDatabase` -- no mocks for DB operations
- `lockForUpdate()` in RSVP and waitlist prevents race conditions
- Idempotent recurring meet generation (checks for existing instances)
- Clean service layer separation from controllers
- Proper authorization checks (`$this->authorize`) in all controller methods
- Activity-club ownership validation prevents cross-club access
- Factory states (`full()`, `cancelled()`, `recurring()`, `competition()`) are well-designed and composable
- Command returns `self::SUCCESS` consistently

---

## Recommended Actions (Prioritized)

1. **[HIGH]** Wrap `saveScore` controller in try-catch for `InvalidArgumentException`
2. **[HIGH]** Add `points_for_draw` to competition config defaults
3. **[HIGH]** Add test for draw match scenario
4. **[MED]** Fix `Video::update()` / `User::update()` in migration rollback
5. **[MED]** Add test for pool_play and single_elimination formats
6. **[MED]** Add test for cancel-RSVP when user hasn't RSVP'd
7. **[LOW]** Consider null-safe `opr_level` check in `userCanJoin`

---

## Metrics

- **Test Count**: 25 tests, 70 assertions (all passing)
- **Format Coverage**: round_robin tested; pool_play and single_elimination untested
- **RSVP Paths Tested**: confirm, waitlist, cancel+promote, skill-reject, duplicate-reject
- **Command Tested**: correct day creation, idempotency, cancelled template skip
- **Linting Issues**: 0 syntax errors

---

## Unresolved Questions

1. Is the draw-awards-zero-points behavior intentional? If so, should be documented in `DEFAULT_CONFIG`.
2. Should unrated users (`opr_level = null`) be blocked or allowed to join skill-restricted activities?
3. Is there a plan to add pool_play/single_elimination tests, or are those formats considered experimental?
