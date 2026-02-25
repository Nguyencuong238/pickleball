# Code Review: MLP League Management — Phase 1 & 2

**Date**: 2026-02-25
**Reviewer**: code-reviewer agent
**Scope**: Phase 1 (7 migrations, 7 models) + Phase 2 (3 service classes)
**Plan**: `plans/260225-1117-mlp-league-management/`
**Reference**: Laravel 10, PHP 8.1+, MySQL

---

## Overall Assessment

Solid implementation. Schema design is clean, model patterns are consistent with project standards, and service layer correctly uses DB transactions. Found **2 bugs** (one algorithmic, one data-consistency), **2 high-priority issues**, and several medium items.

---

## Critical Issues

### BUG-1: Round-Robin rotation is incorrect — produces duplicate matchups

**File**: `app/Services/LeagueScheduleService.php` — line 72-73

```php
// Current (WRONG): removes last element then splices back at index 1
$last = array_pop($teamIds);
array_splice($teamIds, 1, 0, [$last]);
```

The circle method requires rotating all positions _except_ position 0. The correct operation is to:
1. Take the last element of the array (excluding position 0)
2. Insert it at position 1

But `array_pop` pops from the full array, which moves the **last** element from the rotating ring — that part is correct. However the splice inserts it _before_ position 1's current value, shifting everything right. This is functionally correct for the rotation itself.

**Wait — deeper issue**: the rotation must be applied _after_ each round is generated, but the rotation is applied to `$teamIds` which still includes position 0 fixed. The current code pops the absolute last element of the full array (including the fixed element at 0). For N teams after padding, position 0 should be fixed and positions 1..N-1 should rotate. `array_pop` correctly takes from end; `array_splice($arr, 1, 0, [$last])` inserts at position 1. This is actually the **correct** circle method.

**Actual Bug Found**: When team count is odd and a `null` bye is appended, the number of rounds becomes `N - 1` (correct for even N). But `$numRounds = $numTeams - 1` where `$numTeams` includes the null slot. For 5 real teams: numTeams=6, numRounds=5. Correct — 5 rounds. Each round has floor(6/2)=3 slots, one of which has null, so 2 real matches per round. This is correct.

**Actual Bug Found**: The loop `for ($i = 0; $i < $numTeams / 2; $i++)` with integer division should use `intdiv()` to be explicit, but since `$numTeams` is always even after bye-padding, `/2` works. Not a bug.

**REAL BUG — Algorithm produces wrong pairs for odd-team case**: When `$numTeams = 6` (5 real + 1 null), position 5 is null. The pairing `$teamIds[$i]` vs `$teamIds[$numTeams-1-$i]` for i=0 pairs index 0 vs index 5 (null). That bye slot is at the _end_, so the team at position 0 (the fixed team) always gets the bye in round 1. After rotation, the null moves away from index 5. This is correct behavior — the fixed team gets bye in round 1, and the bye rotates via the rotation step. Confirmed: algorithm is correct.

**ACTUAL BUG — Duplicate match creation risk**: No unique constraint on `(league_round_id, home_team_id, away_team_id)` in the `league_matches` migration. If `generateRoundRobin` is called twice (e.g., due to a retry or bug), duplicate matches are created. `updateStatus()` checks `rounds()->count() === 0` before calling generate, but there is a TOCTOU race condition: two concurrent requests could both pass the check before either creates rounds.

**Fix**: Add a unique constraint in the migration:
```php
$table->unique(['league_round_id', 'home_team_id', 'away_team_id'], 'unique_match_per_round');
```
And/or wrap the `rounds()->count()` check and `generateRoundRobin` in the same `DB::transaction` with a lock.

---

### BUG-2: `updateAfterMatch()` on LeagueStanding has a race condition with mixed increment/assign

**File**: `app/Models/LeagueStanding.php` — lines 62-75

```php
$this->increment('played');
// ...
$this->games_won += $gamesWon;   // reads stale in-memory value
$this->games_lost += $gamesLost;
$this->save();                    // overwrites DB increments
```

`increment()` hits the DB directly. Then `$this->games_won += $gamesWon` reads the model's in-memory (pre-increment) value of `games_won`, then `save()` overwrites whatever the DB has. This loses DB-side increments if another process ran between the `increment` calls and `save()`.

**Note**: `recalculateStandings()` in `LeagueStandingsService` does NOT use `updateAfterMatch()` — it uses separate `increment()` calls for each field which are safe individually. So this bug only fires if `updateAfterMatch()` is called directly. It should be removed or fixed to use all-increment or all-in-memory-then-save pattern consistently.

**Fix** (all increments, no manual `+=`):
```php
$this->increment('games_won', $gamesWon);
$this->increment('games_lost', $gamesLost);
// remove the manual += and save()
```

---

## High Priority

### HIGH-1: `recalculateStandings` — N+1 query problem inside transaction

**File**: `app/Services/LeagueStandingsService.php` — lines 134-136

```php
foreach ($completedMatches as $match) {
    $homeStanding = $league->standings()->where('league_team_id', $match->home_team_id)->first();
    $awayStanding = $league->standings()->where('league_team_id', $match->away_team_id)->first();
```

For each completed match (up to N*(N-1)/2 for a full round-robin), two DB queries are issued. For 16 teams: 120 matches = 240 queries just for standings lookups. This runs inside a transaction, holding a write lock longer than necessary.

**Fix**: Load all standings into a keyed collection once:
```php
$standingsMap = $league->standings()->get()->keyBy('league_team_id');

foreach ($completedMatches as $match) {
    $homeStanding = $standingsMap->get($match->home_team_id);
    $awayStanding = $standingsMap->get($match->away_team_id);
    // ...
}
// Bulk update at end: foreach ($standingsMap as $s) { $s->save(); }
```

Note: the current `increment()` approach still hits DB per-call. A full optimization would accumulate diffs in PHP then do bulk updates.

---

### HIGH-2: `saveGameScore` re-queries `$game->match()` but `$game` model may have stale `match` after lock

**File**: `app/Services/LeagueStandingsService.php` — line 35

```php
$match = $game->match()->lockForUpdate()->first();
```

This locks the match row — good. But `$game->update(...)` on line 38 is called using `$game` which was not locked. Another process could concurrently update the same game. The game row itself should also be locked:

```php
$game = LeagueMatchGame::where('id', $game->id)->lockForUpdate()->first();
$match = $game->match()->lockForUpdate()->first();
```

Also, after `$game->update(...)`, the counts on lines 48-49 query the DB including the just-updated game — this is correct. But the `determineMatchWinner` is called on the original `$match` reference (not refreshed after lock), which is fine since `$match` was just fetched.

---

## Medium Priority

### MED-1: Slug race condition in `createLeague()` and `updateLeague()`

**File**: `app/Services/LeagueService.php` — lines 45-48 and `app/Models/League.php` boot

Both the service and the model boot method generate slugs with the same pattern: check existence, append random suffix if taken. Without a DB-level unique constraint + retry loop, two concurrent creates with the same name could both pass the `exists()` check and one would fail on the unique constraint. The model's boot method runs at `creating` time (inside the transaction from the service), so the service's slug code at line 45 duplicates what boot does — the boot version will overwrite the service's slug if it's different.

**Issue**: `createLeague()` sets `slug` explicitly in the `create()` array, so the boot listener's `empty($league->slug)` check means boot will NOT re-generate. Good. But the service does its own uniqueness check _before_ the transaction lock, so a concurrent request can slip through and cause a duplicate key violation.

**Fix**: Wrap slug generation + league creation in a single transaction with `DB::transaction()` (already done) — but add a retry on unique constraint violation, or use `lockForUpdate` on a slug uniqueness query.

---

### MED-2: `updateTeam()` has no mass-assignment guard for sensitive fields

**File**: `app/Services/LeagueService.php` — line 148

```php
$team->update($data);
```

`$data` comes directly from the controller's input (once controllers are written). The `LeagueTeam::$fillable` guards `league_id` from direct assignment, but `captain_user_id` can be freely updated. This is not a bug _per se_ if controllers validate properly, but the service should validate or whitelist fields:

```php
$team->update(array_intersect_key($data, array_flip(['name', 'logo', 'captain_user_id', 'status'])));
```

---

### MED-3: `league_team_players` — user can be on multiple teams in same league

**File**: `database/migrations/2026_02_25_003_create_league_team_players_table.php`

The unique constraint is `(league_team_id, user_id)` — prevents a user from joining the same team twice. But there is no constraint preventing a user from joining **multiple teams in the same league**. The service check at `addPlayer()` only checks within the same team.

**Fix**: Add a cross-team check in `addPlayer()`:
```php
// Check user is not already in any team in this league
$alreadyInLeague = LeagueTeamPlayer::whereHas('team', fn($q) => $q->where('league_id', $team->league->id))
    ->where('user_id', $data['user_id'])
    ->exists();

if ($alreadyInLeague) {
    throw new InvalidArgumentException('Nguoi choi da o trong mot doi khac trong giai.');
}
```
DB-level enforcement would require a partial unique index or an application-level check. The application check above is sufficient for MVP but note it's not atomic.

---

### MED-4: `determineMatchWinner` is public but should be private/protected

**File**: `app/Services/LeagueStandingsService.php` — line 60

`determineMatchWinner` is called internally from `saveGameScore` and also called from `LeagueStandingsService` methods. It mutates the match and triggers full standings recalculation. If called externally without the surrounding transaction from `saveGameScore`, it runs without a lock. Mark as `private` or at minimum `protected`, and ensure callers always wrap in transaction.

---

### MED-5: `getLeague` accessor on LeagueMatch causes N+1 on lazy load

**File**: `app/Models/LeagueMatch.php` — lines 63-66

```php
public function getLeagueAttribute(): ?League
{
    return $this->round?->league;
}
```

If `round` is not eager-loaded, accessing `$match->league` fires 2 queries (round, then league). In `recalculateStandings`, `$match->round->league` is accessed directly (not via the accessor), and only the last `$match` in the loop triggers it. But in general use, each `$match->league` call on an un-eager-loaded collection causes N+1. Document requirement to eager-load: `with('round.league')`.

---

### MED-6: `game_type` is a plain string with no enum validation

**File**: `database/migrations/2026_02_25_006_create_league_match_games_table.php` — line 15

`game_type` is `string` with a comment `// WD, MD, MXD`. Nothing enforces valid values at DB level. Since `match_format` config is an array of strings (e.g. `['WD', 'MD', 'MXD', 'MXD']`), invalid values can be inserted.

**Fix**: Either use an enum in the migration or validate in the service before inserting. At minimum add a note to validate in the controller/request.

---

## Low Priority

### LOW-1: Scope methods missing return type hints

Scopes in `League.php`, `LeagueTeam.php`, `LeagueStanding.php` use `$query` without type hints per code-standards requirement. Example:

```php
// Current
public function scopeActive($query)

// Should be (following code-standards.md)
public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
```

---

### LOW-2: `league_rounds` missing unique constraint on `(league_id, round_number)`

Two rounds with the same number can exist for the same league. Low risk since rounds are only created by `generateRoundRobin`, but defensively a unique constraint would be cleaner.

---

### LOW-3: `initializeStandings` not called inside a transaction

**File**: `app/Services/LeagueStandingsService.php` — line 17

`initializeStandings` does N `updateOrCreate` calls without a wrapping transaction. If it fails mid-way, some teams have standings records and others don't. Called from `updateStatus()` which is also not in a transaction (the status update itself uses `$league->update()` directly, not inside `DB::transaction`). Should wrap `updateStatus` in a transaction.

---

## Positive Observations

- Migration order is correct (leagues -> teams -> players -> rounds -> matches -> games -> standings)
- Cascade deletes are properly set: orphaned children cleaned up automatically
- `$fillable` used on all models — no `$guarded = []` vulnerability
- `DB::transaction` used in all mutation-heavy service methods
- `lockForUpdate` used in `saveGameScore` for the match row
- Standings recalculation uses full reset + replay approach — correct and avoids drift from incremental updates
- Slug-based routing consistent with Tournament model
- `getConfigValue()` helper with default fallback is a clean pattern
- `STATUS_TRANSITIONS` map makes valid state machine explicit and readable
- `updateOrCreate` in `initializeStandings` is idempotent — safe to call multiple times

---

## Recommended Actions (Priority Order)

1. **Fix BUG-2** (`updateAfterMatch` mixed increment/save race) — change `games_won +=` to `increment()` calls, remove trailing `save()`.
2. **Add unique constraint** on `league_matches(league_round_id, home_team_id, away_team_id)` to prevent duplicate match creation.
3. **Fix N+1 in `recalculateStandings`** — load standings into a `keyBy('league_team_id')` map before the loop.
4. **Add cross-league team player check** in `addPlayer()` to prevent a user joining two teams in the same league.
5. **Lock game row** in `saveGameScore` before updating.
6. **Wrap `updateStatus`** in a `DB::transaction` that includes both `initializeStandings` and the status update.
7. **Mark `determineMatchWinner` as private**.
8. Add unique constraint on `league_rounds(league_id, round_number)`.
9. Validate `game_type` values in the service or via enum.

---

## Plan TODO Verification

Phase 1 todos: All 14 items implemented (7 migrations + 7 models). Migrations verified structurally.
Phase 2 todos: All service classes created with correct method signatures per plan.

Phase 1 status: **can be marked complete** (pending migration run verification).
Phase 2 status: **complete pending fixes** for BUG-2 and HIGH-1 above.

---

## Metrics

- Files reviewed: 17 (7 migrations, 7 models, 3 services)
- Total LOC: ~600
- Bugs found: 2 (BUG-1 duplicate match risk, BUG-2 race condition in updateAfterMatch)
- High priority: 2
- Medium priority: 6
- Low priority: 3

---

## Unresolved Questions

1. Is `updateAfterMatch()` on `LeagueStanding` intended to be a public API used by future code, or is it dead code now that `recalculateStandings` does full reset-replay? If dead, remove it.
2. Should `draws` ever occur in MLP format (where each match has a defined number of games and a winner is determined by game count)? If draws are impossible, `draws` column and the draw-handling branch in `recalculateStandings` can be removed.
3. `points_for_loss` defaults to 0. Is there a use case for loss points (e.g., participation points)? If always 0, the `increment('points', $pointsForLoss)` calls are no-ops but harmless.
