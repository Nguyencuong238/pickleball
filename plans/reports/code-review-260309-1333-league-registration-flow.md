# Code Review: League Registration Flow

**Date:** 2026-03-09
**Reviewer:** code-reviewer
**Scope:** 16 files (service, controller, models, migrations, views, routes)
**Focus:** Security, correctness, edge cases

---

## Overall Assessment

Solid implementation with good structure: service layer separation, proper authorization checks, CSRF protection on AJAX, XSS escaping via `escapeHtml()`, and rate limiting on public routes. Several issues found ranging from critical (race condition, user creation without consent) to medium (phone normalization edge cases).

---

## Critical Issues

### C1. Race Condition on User Creation by Phone (TOCTOU)

**File:** `app/Services/LeagueRegistrationService.php:51-58`

`User::where('phone', $phone)->first()` followed by `User::create()` is not atomic. Two concurrent registrations with the same phone (different leagues) can create duplicate users.

The `users.phone` column has **no unique constraint** (migration `2025_11_18_015026`), and existing users may have `phone = NULL`, so adding a unique index requires cleanup.

**Fix:** Use `firstOrCreate` with a lock or `updateOrCreate`:
```php
$user = User::firstOrCreate(
    ['phone' => $phone],
    ['name' => $playerData['name'], 'password' => bcrypt(Str::random(16))]
);
```
Even better: add a unique index on `phone` (where not null) to enforce at DB level.

### C2. Uncontrolled User Account Creation from Public Endpoint

**File:** `app/Services/LeagueRegistrationService.php:53-57`

Any anonymous visitor can create user accounts by submitting the registration form with arbitrary phone numbers. This is a **user enumeration + account creation vector**: attackers can bulk-create accounts, and existing users get silently linked to registrations they never consented to.

**Impact:** User accounts created with random passwords; legitimate phone owners cannot register later (their phone is taken). If a password reset flow exists via phone, an attacker could take over these pre-created accounts.

**Recommendations:**
1. At minimum, log/audit when new users are auto-created
2. Consider requiring phone verification (OTP) before creating accounts
3. If auto-creation is intentional, document it as a known design choice and ensure password reset does not rely solely on phone

### C3. Phone Uniqueness Check is Not Race-Safe

**File:** `app/Http/Controllers/Front/LeagueRegistrationController.php:56-69`

The duplicate phone check (`$league->registrations()->whereIn('status',...)->whereHas(...)`) is outside the DB transaction in the service. Between the check and the `register()` call, another request could register the same phone.

**Fix:** Move the uniqueness check inside `LeagueRegistrationService::register()` within the transaction, or use a database unique constraint on `(league_registration_id, phone)` at the registration_players level combined with a check.

---

## High Priority

### H1. Phone Normalization Edge Cases

**File:** `app/Services/LeagueRegistrationService.php:20-33`

| Input | Output | Expected? |
|-------|--------|-----------|
| `84` | `00` | Wrong -- 2-digit input treated as country code |
| `841234` | `01234` | Wrong -- 6-digit number starting with 84 gets prefix stripped |
| `84912345678` | `0912345678` | Correct |
| `+84912345678` | `0912345678` | Correct (non-digits stripped first) |
| `0` | `0` | Meaningless but passes |
| `(empty after strip)` | `0` | Invalid but passes |

The condition `strlen($phone) > 9` is fragile. A Vietnamese mobile number after stripping `84` should be 9 digits (total 11 with `0` prefix).

**Fix:** Add length validation (10-11 digits for VN) after normalization:
```php
if (strlen($phone) < 10 || strlen($phone) > 11) {
    throw new \InvalidArgumentException("Invalid phone number: {$phone}");
}
```

### H2. Duplicate Phone Within Same Registration Batch Not Checked

**File:** `app/Http/Controllers/Front/LeagueRegistrationController.php:56-69`

If `required_players_per_registration = 4`, a user could submit the same phone for all 4 player slots. The check only validates against *existing* registrations, not within the current submission.

**Fix:** Add a duplicate check within the submitted players array:
```php
$phones = array_map(fn($p) => LeagueRegistrationService::normalizePhone($p['phone']), $request->players);
if (count($phones) !== count(array_unique($phones))) {
    return redirect()->back()->withInput()->with('error', 'Moi VDV can so dien thoai khac nhau.');
}
```

### H3. File Upload - No Extension/MIME Whitelist Enforcement Beyond `image` Rule

**File:** `app/Http/Controllers/Front/LeagueRegistrationController.php:50-52`

Laravel's `image` validation rule allows `jpeg, png, bmp, gif, svg, webp`. SVG files can contain embedded JavaScript (XSS via SVG).

**Fix:** Explicitly exclude SVG:
```php
'players.*.photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
```

### H4. `addGroupToTeam` Does Not Enforce `max_players_per_team`

**File:** `app/Services/LeagueRegistrationService.php:122-158`

Adding a whole group to a team bypasses the max players per team limit enforced in `LeagueService::addPlayer()`. A group of 4 added to a team that already has 8/10 players would push it to 12.

**Fix:** Add a capacity check before iterating:
```php
$currentCount = $team->players()->count();
$newPlayersCount = $registration->players->whereNotIn('user_id', $assignedUserIds)->count();
$maxPlayers = $league->getConfigValue('max_players_per_team', 10);

if ($currentCount + $newPlayersCount > $maxPlayers) {
    throw new \InvalidArgumentException("Team would exceed max players limit ({$maxPlayers}).");
}
```

---

## Medium Priority

### M1. `listRegistrations` Returns Unbounded Result Set

**File:** `app/Http/Controllers/Front/LeagueRegistrationController.php:103-109`

`$query->get()` returns all registrations without pagination. For popular leagues, this could return hundreds of records with nested players.

**Fix:** Add pagination or a reasonable limit:
```php
return response()->json(['data' => $query->paginate(50)]);
```

### M2. `status` Filter Not Validated in `listRegistrations`

**File:** `app/Http/Controllers/Front/LeagueRegistrationController.php:105-107`

The `$request->status` value is used directly in a `where()` clause without validation. While Eloquent parameterizes it (no SQL injection), invalid status values like `status=xyz` silently return empty results.

**Fix:** Validate against allowed values:
```php
$request->validate(['status' => 'nullable|in:all,pending,approved,rejected']);
```

### M3. `admin_note` on Approve Not Validated

**File:** `app/Http/Controllers/Front/LeagueRegistrationController.php:112-119`

The `approve` action accepts `admin_note` via `$request->input('admin_note')` without any validation (length limit, etc). The `reject` action properly validates with `max:500`.

**Fix:** Add validation:
```php
$request->validate(['admin_note' => 'nullable|string|max:500']);
```

### M4. `registration_deadline` Cast Inconsistency

**File:** `app/Models/League.php:34` / `app/Http/Controllers/Front/HomeYardLeagueController.php:61`

`registration_deadline` is cast to `datetime` in the model but validated as `date` (not `datetime`) in store/update. The form uses `datetime-local` input. The `date` validation rule accepts date-only strings, losing the time component.

**Fix:** Change validation to `date_format:Y-m-d\TH:i` or just `date` (which accepts datetime strings in Laravel). Verify the stored value includes time.

### M5. No Confirmation/Idempotency Guard on Approve/Reject

**File:** `app/Services/LeagueRegistrationService.php:78-92`

An already-approved registration can be approved again, and an already-rejected one can be rejected again. More critically, an approved registration could be rejected (or vice versa) without any guard.

**Fix:** Add status transition check:
```php
public function approve(LeagueRegistration $registration, ?string $note = null): void
{
    if ($registration->status !== 'pending') {
        throw new \InvalidArgumentException('Only pending registrations can be approved.');
    }
    // ...
}
```

---

## Low Priority

### L1. Payment Proof Image Rendered from User-Controlled Path (XSS risk minimal)

**File:** `resources/views/home-yard/leagues/_tab-registrations.blade.php:128`

`reg.payment_proof` is inserted into an `<img src>` and `<a href>` via string concatenation. While the path is server-generated, if the DB value were tampered with, it could inject HTML. The `escapeHtml` function is not applied to URLs.

**Suggestion:** Apply `encodeURI()` to the path or use `escapeHtml()` on it.

### L2. `document.execCommand('copy')` is Deprecated

**File:** `resources/views/home-yard/leagues/_tab-registrations.blade.php:237`

**Suggestion:** Use `navigator.clipboard.writeText()` with fallback.

### L3. SortableJS Loaded from CDN without SRI Hash

**File:** `resources/views/home-yard/leagues/_tab-teams.blade.php:358`

No `integrity` attribute on the external script tag. Supply chain risk.

---

## Positive Observations

1. **Authorization consistently applied** -- all admin endpoints check `$league->user_id !== auth()->id()` and verify entity belongs to league
2. **CSRF on AJAX** -- `X-CSRF-TOKEN` header sent on all fetch calls
3. **XSS prevention** -- `escapeHtml()` function used in JS-rendered content; Blade `{{ }}` escaping in server-rendered views
4. **Rate limiting** -- public routes throttled (30/min GET, 5/min POST)
5. **DB transaction** -- registration creation and group-to-team addition wrapped in transactions
6. **Service layer separation** -- business logic in services, not controllers
7. **Lazy loading** -- registrations tab data fetched on first view only
8. **Proper cascade deletes** -- migrations use `cascadeOnDelete` on foreign keys

---

## Recommended Actions (Priority Order)

1. **[C1]** Fix race condition: use `firstOrCreate` for user lookup/creation + add unique index on `users.phone`
2. **[C2]** Add audit logging for auto-created users; consider OTP verification
3. **[C3]** Move phone uniqueness check inside transaction
4. **[H1]** Add phone length validation after normalization
5. **[H2]** Check for duplicate phones within the same submission
6. **[H3]** Restrict file uploads to `mimes:jpeg,png,jpg,gif,webp` (exclude SVG)
7. **[H4]** Add `max_players_per_team` enforcement in `addGroupToTeam`
8. **[M5]** Add status transition guards on approve/reject
9. **[M1]** Paginate `listRegistrations` response
10. **[M4]** Fix `registration_deadline` validation to accept datetime

---

## Metrics

- Type Coverage: N/A (PHP, no static analysis configured)
- Test Coverage: No tests found for registration flow
- Linting Issues: Not checked (no linter run requested)

---

## Unresolved Questions

1. Is auto-creating user accounts from a public form an intentional design decision? If yes, how is account ownership verified later?
2. Should rejected registrations be re-submittable, or is the phone permanently blocked for that league?
3. Is there a need for an "undo approve" flow (approved -> pending)?
4. What happens to auto-created users if a registration is rejected -- should they be cleaned up?
