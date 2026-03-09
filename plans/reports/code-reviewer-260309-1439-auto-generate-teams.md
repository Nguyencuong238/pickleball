# Code Review: Auto-Generate Teams Feature

## Scope
- `app/Services/LeagueService.php` (autoGenerateTeams, snakeDraftPairing)
- `app/Http/Controllers/Front/LeagueTeamController.php` (autoGenerate)
- `routes/web.php` (POST route)
- `resources/views/home-yard/leagues/_tab-teams.blade.php` (modal + JS)

## High Priority

### 1. skill_level is VARCHAR(50) but cast to float -- silent misranking
**File:** `LeagueService.php:267`
`skill_level` column is `string(50)`. Non-numeric values (e.g., "beginner", "A") cast to `0.0`, making skill_ranked mode behave like random. Core feature silently broken for text-based skill levels.

**Fix:** Validate skill_level as numeric at registration, or add a mapping function.

### 2. Race condition: no lock on player pool query
**File:** `LeagueService.php:247-249`
Transaction present but no `lockForUpdate()` on league row. Two concurrent auto-generate requests read same pool, assign same players to multiple teams.

**Fix:** Add `$league = League::lockForUpdate()->find($league->id);` at start of transaction (same pattern as `updateStatus()` line 126).

### 3. Duplicate user_id possible -- bypasses addPlayer() checks
**File:** `LeagueService.php:298`
`autoGenerateTeams()` calls `$team->players()->create()` directly, skipping the duplicate-user check in `addPlayer()`. If a user appears in multiple approved registrations, they get assigned to a team multiple times.

**Fix:** Add `$players = $players->unique('user_id')->values();` before chunking, or add DB unique constraint.

## Medium Priority

### 4. Max teams limit hit with no user feedback
When max_teams reached, loop breaks silently. Response only reports teams created count, not *why* remaining players weren't assigned.

## Passed Checks
- Authorization: `abort_if` on all endpoints
- CSRF: properly handled
- SQL injection: parameterized queries throughout
- XSS: Blade escaping + `textContent` in JS
- Input validation: `in:skill_ranked,random`, `integer|min:2|max:10`
- Transaction wraps all writes
- JS: button disabled during request, re-enabled on error
- Edge case: incomplete groups correctly skipped

## Unresolved Questions
- What actual `skill_level` values exist in production? Determines severity of item 1.
- Is there a unique DB constraint on `league_team_players(user_id)` scoped to league? If not, item 3 is exploitable.
