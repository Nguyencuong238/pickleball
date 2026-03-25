# Code Review: Match End + Score Flow

## Scope
- Files: 17 modified, 2 new (ClubScoreService, migration)
- Focus: Security, correctness, edge cases, race conditions

## Overall Assessment
Solid implementation with clean separation (ClubScoreService). Good validation, CSRF protection, proper DB transactions. A few critical and high-priority issues found.

---

## Critical Issues

### C1. No Auth Middleware on Player Routes - Authorization Bypass
**File:** `routes/web.php:352`

The route group for `player-end-match` and `confirm-score` has **no auth middleware**. These routes are in the public checkin/queue group. The controller relies on `auth()->id() ?? session('checkin_user_id')` for identity.

**Risk:** `session('checkin_user_id')` is set during checkin flow. If a user's session expires or is manipulated, `$userId` could be `null`. The `in_array(null, [...])` check would pass if any player slot is `null` (e.g., singles match with `player3_id = null`, `player4_id = null`).

**Impact:** An unauthenticated user could potentially confirm/reject scores or submit scores for matches with empty player slots.

**Fix:** Add null check before authorization logic:
```php
// In playerEndMatch, submitScore, confirmScore:
$userId = auth()->id() ?? session('checkin_user_id');
if (!$userId) {
    return response()->json(['success' => false, 'message' => 'Vui long dang nhap.'], 401);
}
```

### C2. Missing `use` Statements in ClubScoreService
**File:** `app/Services/ClubScoreService.php:94-100`

The service references `EloService`, `OprsService`, `ClubMatchmakingService`, `ClubMemberStatsService` via `app()` but has no `use` statements for them. While `app()` resolves by class name string, the code uses `EloService::class` without importing - this will cause a **fatal error** at runtime.

**Fix:** Add imports:
```php
use App\Services\EloService;
use App\Services\OprsService;
use App\Services\ClubMatchmakingService;
use App\Services\ClubMemberStatsService;
```

### C3. Unused Import in ClubScoreService
**File:** `app/Services/ClubScoreService.php:5`

`use App\Models\Club;` is imported but never used. Minor but indicates copy-paste.

---

## High Priority

### H1. Race Condition: No Lock on Score Submission
**File:** `app/Services/ClubScoreService.php:32-48`, `app/Http/Controllers/ClubOpenPlayController.php:141-182`

The `submitScore` controller checks `$match->result_confirmed` and `$match->score_status` without acquiring a lock. Two players from the same team could submit scores simultaneously, both passing the status checks.

**Fix:** Use `lockForUpdate()` in the transaction:
```php
DB::transaction(function () use ($match, ...) {
    $match = ClubActivityMatch::lockForUpdate()->find($match->id);
    if ($match->result_confirmed || $match->score_status === 'pending_confirmation') {
        throw new \Exception('Already submitted');
    }
    $match->update([...]);
});
```

### H2. Race Condition: Double Confirm/Reject
**File:** `app/Http/Controllers/ClubOpenPlayController.php:184-208`

Same issue in `confirmScore` - the `score_status !== 'pending_confirmation'` check is outside a lock. Two opposing team members could both confirm simultaneously, triggering `processEloAndComplete` twice.

**Fix:** Same pattern - use `lockForUpdate()` inside the transaction in `ClubScoreService::confirmScore()`.

### H3. `determineWinner` Returns 'team2' on Draw
**File:** `app/Services/ClubScoreService.php:76-81`

If all sets are tied (e.g., best-of-3 where each set is a draw), `$t1Wins` and `$t2Wins` are both 0, and the method returns `'team2'` as default. While the controller validates no ties per set, this is a defensive coding gap.

**Fix:** Return `'draw'` or throw when `$t1Wins === $t2Wins`:
```php
if ($t1Wins === $t2Wins) {
    throw new \LogicException('Cannot determine winner: sets are tied.');
}
```

### H4. `rejectScore` Not Wrapped in Transaction and Missing State Reset
**File:** `app/Services/ClubScoreService.php:65-74`

`rejectScore` clears scores but does NOT reset `result_submitted_by` or `status` (which was set to `pending_score`). This means after rejection, the match stays in `pending_score` status with a stale submitter ID.

**Fix:**
```php
public function rejectScore(ClubActivityMatch $match): void
{
    $match->update([
        'score_status' => 'rejected',
        'result_confirmed' => false,
        'set_scores' => null,
        'team1_score' => null,
        'team2_score' => null,
        'result_submitted_by' => null,
        'status' => 'in_progress',
    ]);
}
```

### H5. Admin "Reject & End" Non-Atomic
**File:** `public/assets/js/club-activity-dashboard.js:97-112`

`adminRejectAndEnd` fires two sequential requests (reject then end-match/skip). If the first succeeds but the second fails, the match is in `rejected` + `pending_score` status with no scores - a stuck state.

**Fix:** Either create a single backend endpoint for admin-reject-and-end, or handle the second request failure in JS with retry/rollback logic.

---

## Medium Priority

### M1. XSS via `activityTitle` in Queue Template
**File:** `resources/views/front/clubs/queue.blade.php:19`

```blade
activityTitle: '{{ $activity->title }}',
```

If `$activity->title` contains a single quote or `</script>`, this breaks the JS context. Should use `@json()`:
```blade
activityTitle: @json($activity->title),
```

### M2. `playerEndMatch` Does Not Set `ended_at`
**File:** `app/Http/Controllers/ClubOpenPlayController.php:86-105`

The player end-match endpoint validates the player is in the match but doesn't actually mark the match as ended. It only returns a redirect URL to the score form. The `ended_at` is set later in `playerSubmitScore`. But if the player navigates away without submitting, the match remains in `in_progress` with no mechanism to recover.

### M3. `getOpposingTeamPlayerIds` With Null `submitterId`
**File:** `app/Models/ClubActivityMatch.php:89-97`

When `result_submitted_by` is null (used as fallback 0 in controller), `in_array(0, $team1)` returns false, so it always returns `$team1` as "opposing". This could allow the submitter's own team to confirm their own score.

**Fix:** Guard against null/0 submitter:
```php
if (!$submitterId || !in_array($submitterId, array_merge($team1, $team2))) {
    return [];
}
```

### M4. No Rate Limiting on Score Endpoints
**File:** `routes/web.php:365-366`

The `player-end-match`, `submit-score`, and `confirm-score` POST routes have no `throttle` middleware, unlike the checkin routes. A malicious user could spam these endpoints.

---

## Low Priority

- Vietnamese diacritics: All UI text properly uses diacritics. Good.
- AlpineJS uses `x-text` (not `x-html`) for dynamic content - safe from XSS in templates.
- CSRF tokens properly included in all JS fetch headers.
- Migration has proper `down()` method with foreign key cleanup.

---

## Recommended Actions (Priority Order)

1. **[C2]** Add missing `use` imports in ClubScoreService - blocks runtime
2. **[C1]** Add null-check for `$userId` in all player-facing endpoints
3. **[H1/H2]** Add `lockForUpdate()` in score submission and confirmation transactions
4. **[H4]** Fix `rejectScore` to reset `status` and `result_submitted_by`
5. **[H3]** Handle draw case in `determineWinner`
6. **[M1]** Use `@json()` for JS-embedded Blade variables
7. **[M4]** Add throttle middleware to score routes
8. **[H5]** Consider atomic admin-reject-and-end endpoint

## Unresolved Questions

1. Is there a cleanup/timeout mechanism for matches stuck in `pending_score` status if no one confirms?
2. In singles matches (2 players), are `player3_id`/`player4_id` always null? If so, `getOpposingTeamPlayerIds` with `array_filter` returns an empty array for singles - opposing team confirmation would be impossible.
3. The `best_of` and `points_per_set` fields on the activity - are these guaranteed to exist for all open play activities, or could they be null? The `?? 1` and `?? 21` fallbacks in validation suggest uncertainty.
