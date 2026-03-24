# Code Review: Club Activity Open Play Feature

**Date:** 2026-03-23
**Reviewer:** code-reviewer
**Score: 5.5 / 10**

---

## Scope

- **Files reviewed:** 15 (controllers, services, models, migrations, routes, policy)
- **LOC:** ~850 (new/modified)
- **Focus:** Security, race conditions, validation, logical bugs

## Overall Assessment

Feature is functionally structured but has several **critical security gaps**, a **fatal missing import**, **race conditions** in queue management, and **authorization holes** on public routes. The matchmaking and Elo services are reasonably designed but need hardening.

---

## CRITICAL Issues

### C1. Missing `use` import causes fatal error in EloService

**File:** `app/Services/EloService.php:205`

`processClubMatchElo(ClubActivityMatch $match, ...)` references `ClubActivityMatch` but the class is never imported. This will throw a `\Error` (class not found) at runtime whenever OPRS processing is triggered.

**Fix:** Add `use App\Models\ClubActivityMatch;` to imports.

---

### C2. Public routes lack authentication -- session spoofing via `checkin_user_id`

**File:** `routes/web.php:352-365`, `ClubOpenPlayController.php:34,101,145`

All check-in and open-play routes (trigger-match, start-match, end-match, submit-score) are **outside** any `auth` middleware group. The `submitScore` method uses `auth()->id() ?? session('checkin_user_id')` for identity.

**Impact:**
- `triggerMatch`, `startMatch`, `endMatch` do call `$this->authorize('manageActivity', $club)` which requires an authenticated user. But since the routes have no `auth` middleware, Laravel will throw a generic error instead of a clean 401 redirect. This is not a security hole per se (authorize still works) but produces poor UX.
- `submitScore` trusts `session('checkin_user_id')` set during check-in. An attacker who obtains the QR token can check in as ANY existing user by calling the `/confirm` endpoint with any valid `user_id`, then submit scores for any match that user is in.

**Fix:**
- Wrap admin routes (`trigger-match`, `start-match`, `end-match`) in `auth` middleware.
- For `submitScore`: validate that the session user matches the authenticated user or add a PIN/OTP confirmation step. At minimum, verify the session was set for THIS activity.

---

### C3. Arbitrary user impersonation via check-in confirm endpoint

**File:** `ClubCheckinController.php:55-73`

The `confirm` method accepts any `user_id` from the request and stores it in the session. Anyone with the QR token can impersonate any user by posting `user_id=<victim_id>`. The QR token is the only guard, and it is static (UUID stored on the activity).

**Impact:** Score manipulation, queue manipulation, identity spoofing.

**Fix:** After lookup by phone, require OTP verification or at minimum verify the phone number in the confirm step matches the looked-up user. Do not accept raw `user_id` from untrusted input.

---

### C4. XSS in `buildPostContent` (stored XSS)

**File:** `ClubActivity.php:214`

```php
return "<p>CLB vua dang {$typeLabel} moi: <strong>{$this->title}</strong></p>";
```

`$this->title` is user-supplied and interpolated directly into HTML without escaping. If this content is rendered with `{!! !!}` in Blade, it enables stored XSS.

**Fix:** Use `e($this->title)` or `htmlspecialchars()`.

---

## HIGH Priority Issues

### H1. Race condition in queue position assignment

**File:** `ClubActivityService.php:82`

```php
$nextPos = ($activity->participants()->max('queue_position') ?? 0) + 1;
```

This query is inside a transaction with `lockForUpdate` on the existing participant row, but the `max('queue_position')` query does NOT use `lockForUpdate`. Two concurrent check-ins can get the same `queue_position`.

**Fix:** Add `lockForUpdate()` to the max query:
```php
$nextPos = ($activity->participants()->lockForUpdate()->max('queue_position') ?? 0) + 1;
```

---

### H2. Race condition in match number generation

**File:** `ClubMatchmakingService.php:40`

```php
$matchNumber = ($activity->matches()->max('match_number') ?? 0) + 1;
```

Not locked. Concurrent `triggerMatch` calls could create duplicate match numbers.

**Fix:** Use `lockForUpdate()` or a DB sequence.

---

### H3. Double `completeMatch` call in `submitScore`

**File:** `ClubOpenPlayController.php:183`

`submitScore` calls `ClubMatchmakingService::completeMatch($match)` which sets `ended_at` and returns players to queue. But `completeMatch` is also called from `endMatch` (line 92). If admin calls `endMatch` then a player submits score, players get re-queued twice, `matches_played_count` incremented twice, and queue positions corrupted.

**Fix:** Check if match is already completed before re-queuing:
```php
if ($match->status !== 'completed') {
    app(ClubMatchmakingService::class)->completeMatch($match);
}
```

---

### H4. `determineWinner` does not handle ties

**File:** `ClubOpenPlayController.php:192-197`

If all sets are tied (e.g., `[{team1: 11, team2: 11}, {team1: 11, team2: 11}]`), `t1Wins` and `t2Wins` are both 0, so `team2` wins by default. This is incorrect.

**Fix:** Add tie detection and either reject the score or handle it explicitly.

---

### H5. No validation that match belongs to activity

**File:** `ClubOpenPlayController.php:77-84,87-95,121-132,134-189`

`startMatch`, `endMatch`, `scoreForm`, `submitScore` accept `{match}` via route model binding but never verify `$match->club_activity_id === $activity->id`. An admin could operate on a match from a different activity.

**Fix:** Add `if ($match->club_activity_id !== $activity->id) abort(404);` to each method.

---

### H6. EloService::processClubMatchElo does not handle null players

**File:** `EloService.php:209-213`

```php
$team1Elo = (int) round(
    ($match->player1->elo_rating + ($match->player2->elo_rating ?? 0)) / 2
);
```

If `player2` is null, dividing by 2 halves `player1`'s Elo, which is mathematically wrong for singles. The null-coalesce on `elo_rating` masks the real issue -- when player2 is null, you should use player1's Elo directly, not average with 0.

**Fix:**
```php
$p2Elo = $match->player2?->elo_rating;
$team1Elo = $p2Elo !== null
    ? (int) round(($match->player1->elo_rating + $p2Elo) / 2)
    : $match->player1->elo_rating;
```

---

## MEDIUM Priority Issues

### M1. Phone validation regex incomplete

**File:** `ClubCheckinController.php:34,81`

Pattern `^(0|\+84)[0-9]{9}$` only handles Vietnam mobile numbers starting with 0 or +84. Does not handle international format with spaces, dashes, or parentheses. Also does not validate that the digit after 0/+84 is valid (e.g., 01, 02 are landlines).

---

### M2. No rate limiting on lookup/register endpoints

**File:** `routes/web.php:354-356`

The `/lookup` endpoint can be used to enumerate phone numbers (returns `found: true/false`). No rate limiting applied.

**Fix:** Add `throttle` middleware.

---

### M3. User creation without email in `register`

**File:** `ClubCheckinController.php:90-95`

Creates a user with a random password and no email. This user cannot log in or reset password. Consider flagging these as "guest" accounts.

---

### M4. `ClubMemberStatsService::updateAfterMatch` not in a transaction

**File:** `ClubMemberStatsService.php:14-52`

Multiple `increment` and `update` calls without a wrapping transaction. If any call fails midway, stats become inconsistent.

---

### M5. N+1 query in `ClubMatchmakingService::createPods`

**File:** `ClubMatchmakingService.php:83`

```php
$sorted = $players->sortBy(fn($p) => $p->user->total_oprs ?? 0);
```

`$players` are loaded without eager-loading `user`, causing N+1 queries when sorting by OPRS.

**Fix:** Add `->with('user')` to the query in `generateMatches`.

---

### M6. `oprs_weight` applied as K-factor multiplier, not as declared "weight"

**File:** `EloService.php:228`

```php
$k = $this->getKFactor($p['user']) * $oprsWeight;
```

`oprs_weight` default is 0.50 (migration), so the effective K-factor is halved for club matches. This is a design decision but the naming is misleading. If `oprs_weight` is 0 it disables Elo entirely (guarded by check in `submitScore`). But values > 1.0 would amplify K beyond intended bounds.

**Fix:** Clamp `oprs_weight` in validation: `min:0|max:1`.

---

### M7. Leaderboard has no access control for club privacy

**File:** `ClubLeaderboardController.php`, `routes/web.php:378`

Leaderboard is fully public. Any visitor can see all club members' stats and OPRS. May be a privacy concern for private clubs.

---

## LOW Priority Issues

### L1. Duplicated court-status logic

`getCourtStatus()` in `ClubOpenPlayController` and `dashboardState()` in `ClubDashboardController` contain identical court-building loops. Extract to a shared service or trait.

### L2. Duplicated phone normalization

`normalizePhone()` in `ClubCheckinController` and inline logic in `ClubDashboardController::addMember` duplicate the same normalization. Extract to a helper.

### L3. `avg_match_duration` column added but never used

The migration adds `avg_match_duration` but no code reads or writes it.

### L4. `activities_participated` stat never incremented

`ClubMemberStat` has `activities_participated` field (default 0) but `updateAfterMatch` never increments it.

### L5. `gender_preference` and `gender_preference_enabled` unused

Fields exist in migration and model but no matchmaking logic uses them.

---

## Positive Observations

1. Good use of `DB::transaction` + `lockForUpdate` in matchmaking service for core queue operations
2. Clean separation of concerns: controllers delegate to focused services
3. Proper use of Laravel policies for admin authorization
4. Well-structured migration with clean rollback
5. `determineWinner` logic is simple and readable (aside from tie edge case)
6. QR-code based check-in flow is a good UX pattern for walk-in scenarios

---

## Recommended Actions (Priority Order)

1. **[CRITICAL]** Add `use App\Models\ClubActivityMatch` import to `EloService.php` -- blocks all Elo processing
2. **[CRITICAL]** Fix user impersonation in check-in confirm flow -- add OTP or phone re-verification
3. **[CRITICAL]** Escape `$this->title` in `buildPostContent()` to prevent stored XSS
4. **[HIGH]** Validate match belongs to activity in all `ClubOpenPlayController` match methods
5. **[HIGH]** Fix double `completeMatch` call in `submitScore`
6. **[HIGH]** Add `lockForUpdate` to `max('queue_position')` queries
7. **[HIGH]** Handle tie scores in `determineWinner`
8. **[HIGH]** Fix null player Elo averaging bug
9. **[MEDIUM]** Add rate limiting to lookup/register endpoints
10. **[MEDIUM]** Wrap stats updates in transaction
11. **[MEDIUM]** Add `->with('user')` to matchmaking query to prevent N+1

---

## Unresolved Questions

1. Is the QR token meant to be single-use or session-bound? Currently it is static and reusable indefinitely.
2. Should `submitScore` require confirmation from both teams (dual-confirm pattern) to prevent score manipulation by one player?
3. What happens when a club activity's `ended_at` is set -- should check-ins and matchmaking be blocked?
4. Is there an intent to support singles matches (2 players)? Current code always expects 4 players.
5. `member_status` check is missing during check-in -- should suspended members be blocked?
