# Code Review: Phase 3 - League Controllers & Routes

## Scope
- **Files**: 5 controllers + 2 route files (league sections)
- **LOC**: ~330 (controllers) + ~30 (routes)
- **Focus**: New league management feature controllers & routing

## Overall Assessment

Good quality. Controllers follow existing patterns (constructor DI, auth middleware, ownership checks). Service delegation is clean. A few security edge cases and DRY violations need attention.

---

## Critical Issues

### C1. Missing parent-child relationship validation on nested routes

**Files**: `LeagueTeamController.php`, `LeagueMatchController.php`

Route params `{team}`, `{match}`, `{game}`, `{player}` are resolved by Laravel's implicit model binding independently from `{league}`. An attacker who owns League A could manipulate a team/match/player belonging to League B by crafting URLs like `/homeyard/leagues/{own-league-slug}/teams/{other-league-team-id}`.

The ownership check `$league->user_id !== auth()->id()` passes because the user owns the league in the URL, but the `$team`/`$match`/`$game`/`$player` may belong to a different league entirely.

**Impact**: Unauthorized mutation of other users' teams, matches, and player rosters.

**Fix** - Add relationship assertions in every nested action:

```php
// LeagueTeamController - in every method that receives $team
abort_if($team->league_id !== $league->id, 404);

// LeagueMatchController - in every method that receives $match
abort_if($match->round->league_id !== $league->id, 404);

// For $game:
abort_if($game->league_match_id !== $match->id, 404);

// For $player:
abort_if($player->league_team_id !== $team->id, 404);
```

Alternatively, use Laravel scoped bindings in routes:

```php
// In RouteServiceProvider or route definition:
Route::scopeBindings()->group(function () {
    // league team/match routes here
});
```

### C2. LeagueMatch model uses `id` route key but League uses `slug`

**File**: `app/Models/League.php` line 33, `app/Models/LeagueMatch.php`

League resolves by `slug` via `getRouteKeyName()`. But route `{match}` resolves LeagueMatch by default (`id`). This works but creates inconsistent URL patterns: `/leagues/{slug}/matches/{id}`. More importantly, since `{league}` is resolved by slug and `{match}` by id, they are completely independent - reinforcing C1 above.

**Impact**: Functional but inconsistent. Not blocking, but note for consistency.

---

## High Priority

### H1. Duplicate validation rules (DRY violation)

**File**: `HomeYardLeagueController.php` lines 47-60 and 100-113

`store()` and `update()` have identical validation rules copy-pasted. Extract to a private method or FormRequest.

```php
// Option A: Private method
private function leagueRules(): array
{
    return [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        // ...
    ];
}

// Option B: FormRequest (preferred for Laravel convention)
// app/Http/Requests/LeagueRequest.php
```

### H2. N+1 query risk in `index()` stats

**File**: `HomeYardLeagueController.php` lines 31-35

Three separate count queries for stats. Consolidate:

```php
$stats = League::where('user_id', $userId)
    ->selectRaw("
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    ")
    ->first();
```

### H3. `updateScore` and `updateGameScore` lack match status guard

**File**: `LeagueMatchController.php` lines 33-53, 55-75

No check if match is already `completed`. Users can overwrite completed match scores, corrupting standings.

```php
abort_if($match->status === 'completed', 422, 'Match already completed.');
```

### H4. Public API endpoints lack rate limiting

**File**: `routes/api.php` lines 344-346

Public endpoints (`show`, `standings`, `schedule`) have no rate limiting middleware. Could be abused.

```php
Route::middleware('throttle:60,1')->group(function () {
    Route::get('leagues/{league}', [LeagueApiController::class, 'show']);
    // ...
});
```

---

## Medium Priority

### M1. `config` array passed directly through validation

**File**: `HomeYardLeagueController.php` lines 54-60

Only specific config keys are validated, but `config` itself is `nullable|array` - meaning arbitrary keys could be injected into the config JSON. The service merges with defaults (`array_merge`), so unknown keys persist.

**Fix**: Either validate exhaustively or whitelist in service:

```php
$config = array_intersect_key(
    array_merge(self::DEFAULT_CONFIG, $data['config'] ?? []),
    self::DEFAULT_CONFIG
);
```

### M2. `description` field lacks max length validation

**File**: `HomeYardLeagueController.php` line 49

`'description' => 'nullable|string'` has no max length. Could store very large text.

```php
'description' => 'nullable|string|max:5000',
```

### M3. Admin LeagueController has no admin middleware on controller level

**File**: `app/Http/Controllers/Admin/LeagueController.php`

Controller itself has no middleware. Relies entirely on route group. This is fine since the route group at line 578 of `web.php` applies `['auth', 'role:admin']`. But for defense-in-depth:

```php
public function __construct()
{
    $this->middleware(['auth', 'role:admin']);
}
```

### M4. Logo upload has no filename sanitization

**File**: `LeagueTeamController.php` line 33

`->store('leagues/teams', 'public')` uses Laravel's auto-generated filename which is safe. No issue here, but the old logo deletion at line 70 should verify the path starts with expected prefix to prevent path traversal if `$team->logo` is ever tampered.

### M5. Missing try-catch in `LeagueMatchController::updateScore` and `updateGameScore`

**File**: `LeagueMatchController.php`

These methods call service methods that use DB transactions but don't wrap in try-catch. If the service throws, it returns a 500. Add error handling consistent with other controllers.

---

## Low Priority

### L1. Vietnamese comments are consistent but consider bilingual docs

All comments are in Vietnamese. Consistent with codebase pattern - no change needed.

### L2. `LeagueApiController::show` manually maps fields

**File**: `LeagueApiController.php` lines 32-43

Consider using an API Resource class for consistent serialization and reusability.

### L3. Route naming uses `homeyard.leagues.*` prefix

Routes are properly namespaced under `homeyard.` prefix. Consistent with existing patterns. Good.

---

## Positive Observations

1. **Consistent ownership checks** - `abort_if($league->user_id !== auth()->id(), 403)` on all mutating front-end actions
2. **Service delegation** - Controllers are thin, business logic in services
3. **Constructor DI** - PHP 8.1 promoted properties, matches existing pattern
4. **Dual response format** - `LeagueTeamController` handles both JSON and redirect responses cleanly
5. **Proper eager loading** - `show()` loads all needed relations in one call
6. **Route organization** - Clean grouping under homeyard prefix, proper naming conventions
7. **Admin routes read-only** - Only `index` and `show` exposed, appropriate for admin oversight

---

## Recommended Actions (Priority Order)

1. **[CRITICAL]** Add parent-child relationship validation in all nested resource controllers (C1)
2. **[HIGH]** Add match status guard before score updates (H3)
3. **[HIGH]** Extract duplicate validation rules to FormRequest (H1)
4. **[HIGH]** Add rate limiting to public API endpoints (H4)
5. **[MEDIUM]** Whitelist config keys in service layer (M1)
6. **[MEDIUM]** Add max length to description field (M2)
7. **[MEDIUM]** Add try-catch to match score update methods (M5)
8. **[LOW]** Consider API Resource for LeagueApiController (L2)

---

## Metrics

- **Type Coverage**: N/A (PHP, not TypeScript)
- **Test Coverage**: Not yet assessed (Phase 3 scope)
- **Linting Issues**: 0 syntax errors detected
- **Security Issues**: 1 critical (C1), 1 high (H4)

## Unresolved Questions

1. Should `LeagueMatch` and `LeagueMatchGame` use slug-based routing for URL consistency with League?
2. Is there a plan to add Laravel Policy classes for authorization instead of inline `abort_if` checks?
3. Should the `generateSchedule` endpoint be idempotent? Currently it can be called multiple times - the service may or may not handle duplicate generation gracefully.
