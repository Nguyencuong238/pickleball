# Phase 2 — Refactor entry points, remove duplicates

**Priority:** High
**Status:** pending
**Effort:** M
**Depends on:** phase-01

## Goal

Eliminate inline duplicates of standings-update logic. All callers use `TournamentStandingService` (now idempotent after phase 1).

## Files

**Modify:**
- `app/Http/Controllers/Front/RefereeController.php`
- `app/Http/Controllers/Api/RefereeController.php`
- `app/Http/Controllers/Front/HomeYardTournamentController.php`

## Duplicate Locations (to remove)

- `Front/RefereeController.php:251-288` — `updateGroupStandingsAndAthleteStats` + `updateGroupStandingsWithSets` private
- `Api/RefereeController.php:307-341` — same pattern
- `HomeYardTournamentController.php:4298-4500+` — `updateGroupStandingsWithSets`, `updateGroupStandings`, `updateTournamentAthleteStats` private (lines 4298, 4385, 4474)

## Implementation

For each controller:

1. Inject `TournamentStandingService` via **method parameter** (Laravel auto-resolves action method params). Avoid constructor injection on `HomeYardTournamentController` — it's a God controller, constructor touch can ripple. For private helpers, accept service as arg from the action caller, or `app(TournamentStandingService::class)` inline.
2. Replace bodies of the private helpers with a single call to service methods — or remove helpers entirely and call service inline.
3. Keep the same side effects: service update → athlete stats update.
4. Don't change the outer HTTP behavior / response shape.

Pattern example (`Front/RefereeController`):
```php
// Before
private function updateGroupStandingsAndAthleteStats(MatchModel $match, array $setScores): void
{
    // 30+ lines of duplicated logic
}

// After
private function updateGroupStandingsAndAthleteStats(MatchModel $match, array $setScores): void
{
    [$setsWon1, $setsWon2] = $this->countSetsWon($setScores);
    $this->standingService->updateGroupStandingsWithSets($match, $setsWon1, $setsWon2);
    $this->standingService->updateTournamentAthleteStats($match, $setsWon1, $setsWon2);
}
```

Extract tiny `countSetsWon` helper locally (set-counting not standings logic).

## Todo

- [ ] Front/RefereeController: inject service, strip inline updater
- [ ] Api/RefereeController: inject service, strip inline updater
- [ ] HomeYardTournamentController: inject service, strip 3 inline updaters
- [ ] `grep -r "updateAfterMatch\|increment('matches_played')" app/` → 0 hits
- [ ] Run `php artisan route:list` + `php -l` sanity compile
- [ ] Quick manual smoke: update a match tỉ số via each endpoint if possible

## Success Criteria

- No call to `GroupStanding::updateAfterMatch` remains in app code.
- Only `TournamentStandingService` touches `group_standings` and `tournament_athletes.matches_played`.
- All existing update-score endpoints still function (manual smoke).

## Risks

- `HomeYardTournamentController` constructor may already have heavy deps — use method-level `app(TournamentStandingService::class)` if constructor injection breaks existing DI.
- Inline helpers may be called from multiple methods — trace all callers before deletion.
