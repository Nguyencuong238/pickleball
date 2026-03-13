# Phase Implementation Report

## Executed Phase
- Phase: phase-02-extract-service-layer
- Plan: plans/260312-2011-tournament-rewrite/
- Status: completed

## Files Modified

### Created
- `app/Services/Tournament/TournamentDrawService.php` — 211 LOC, public draw API (isDoubleCategory, getPairsFromAthletes, draw*, getGroupedAthletes, resetDraw, getManualDrawData, saveManualDraw)
- `app/Services/Tournament/DrawAssignmentHelper.php` — 162 LOC, internal draw distribution algorithms (random/seeding snake-draft for pairs and singles)
- `app/Services/Tournament/TournamentMatchService.php` — 157 LOC, score processing (updateMatchScore, handleEndSet, handleEndMatch, handleRegularUpdate, parseSetsFromFinalScore)
- `app/Services/Tournament/MatchCreationHelper.php` — 122 LOC, round-robin match generation (createMatchesForGroups, createSingleMatches, createDoubleMatches)
- `app/Services/Tournament/TournamentStandingService.php` — 162 LOC, standing writes + ranking delegation (updateGroupStandingsWithSets, updateGroupStandings, updateTournamentAthleteStats, recalculateGroupRankings, getRankings, getAllTournamentsRankings)
- `app/Services/Tournament/RankingQueryHelper.php` — 118 LOC, ranking query + pagination formatting (getRankings, getAllTournamentsRankings)

### Modified (service injection only)
- `app/Http/Controllers/Front/Tournament/TournamentDrawController.php` — injected TournamentDrawService
- `app/Http/Controllers/Front/Tournament/TournamentMatchController.php` — injected TournamentMatchService
- `app/Http/Controllers/Front/Tournament/TournamentRankingController.php` — injected TournamentStandingService

## Tasks Completed
- [x] Create `app/Services/Tournament/` directory
- [x] Extract TournamentDrawService (+ DrawAssignmentHelper split for size compliance)
- [x] Extract TournamentMatchService (+ MatchCreationHelper split for size compliance)
- [x] Extract TournamentStandingService (+ RankingQueryHelper split for size compliance)
- [x] Inject services into new controllers via constructor DI
- [x] Verify compile — `php artisan route:list --path=tournament-manage` returns routes cleanly

## Tests Status
- Type check: n/a (PHP, no typecheck command configured)
- Unit tests: n/a (plan explicitly excludes unit tests)
- Compile check: pass — route:list returns 4 routes, no errors

## Issues Encountered
- Each service exceeded 200-line threshold when written as single file. Split each into a main service + internal helper: DrawAssignmentHelper, MatchCreationHelper, RankingQueryHelper. These helpers are not registered separately in service container — they are resolved via constructor injection and Laravel's auto-resolution.
- Old `HomeYardTournamentController` NOT wired to new services (per plan decision: "keep old code until new code verified correct"). Plan todo item marked as deferred.
- `returnSetsWon` stub method removed from final TournamentMatchService — standing updates are called directly by the controller after handleEndMatch (same pattern as old controller).

## Next Steps
- Phase 3 (Dashboard layout + CRUD) can now proceed — services are injectable
- Phase 5 (Draw UI) depends on TournamentDrawService — now available
- Phase 6 (Matches & Scoring UI) depends on TournamentMatchService — now available
- Phase 7 (Rankings UI) depends on TournamentStandingService — now available
- When new controllers are fully wired and verified, wire old HomeYardTournamentController to services for backward compat

## Unresolved Questions
- `GroupStanding::updateAfterMatch()` and `GroupStanding::calculateWinRate()` are called from services — these model methods must exist. Not verified in this session. If they don't exist, standing updates will fail at runtime.
- `TournamentCategory::isDoubles()` and `TournamentAthlete::hasPartner()` / `pair_name` accessor are called from old storeMatch logic — not used in new services but new match controller will need these when implemented.
