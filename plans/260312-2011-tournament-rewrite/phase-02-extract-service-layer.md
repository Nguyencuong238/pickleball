# Phase 2: Service Layer Extraction

## Context Links
- [Plan Overview](./plan.md)
- Current controller: `app/Http/Controllers/Front/HomeYardTournamentController.php`
- Existing services: `app/Services/` (20+ files, follow same pattern)

## Overview
- **Priority:** P1 (unblocks phases 5, 6, 7)
- **Status:** Complete
- **Effort:** 5h

Extract tournament business logic from God controller into 3 focused services. These services will be used by both old and new controllers during transition.

## Key Insights
- Existing service pattern: constructor DI, DB::transaction for atomic ops, typed returns
- Draw logic (~400 lines): random, seeded (snake draft), manual, pair extraction
- Match generation (~300 lines): singles/doubles round-robin creation
- Standing calculation (~200 lines): group standings, differential, rankings

## Requirements

### Functional
- 3 services encapsulating all tournament business logic
- Services callable from both old and new controllers
- No behavior changes - identical results to current code

### Non-functional
- Each service < 200 lines
- Follow existing DI pattern (constructor injection)
- DB transactions for all multi-model operations

## Architecture

```
app/Services/Tournament/
├── TournamentDrawService.php      # Draw/seeding logic
├── TournamentMatchService.php     # Match generation + scoring
└── TournamentStandingService.php  # Rankings + standings calculation
```

## Related Code Files

### Create
- `app/Services/Tournament/TournamentDrawService.php`
- `app/Services/Tournament/TournamentMatchService.php`
- `app/Services/Tournament/TournamentStandingService.php`

### Reference (extract from)
- `app/Http/Controllers/Front/HomeYardTournamentController.php`
  - Draw methods: `drawAthletes`, `drawPairsByRandom`, `drawPairsBySeeding`, `drawAthletesByRandom`, `drawAthletesBySeeding`, `getDrawResults`, `resetDraw`, `getManualDraw`, `saveManualDraw`, `getGroupedAthletes`, `getPairsFromAthletes`
  - Match methods: `createMatchesForGroups`, `createSingleMatches`, `createDoubleMatches`, `storeMatch`, `updateMatchScore`, `handleEndSet`, `handleEndMatch`, `handleRegularUpdate`
  - Standing methods: `updateGroupStandingsWithSets`, `updateGroupStandings`, `updateTournamentAthleteStats`, `recalculateGroupRankings`

## Implementation Steps

### 1. Create Service Directory
- `app/Services/Tournament/`

### 2. TournamentDrawService
Extract these methods from controller:
```php
namespace App\Services\Tournament;

class TournamentDrawService
{
    // Main entry point
    public function executeDraw(Tournament $tournament, int $categoryId, string $method): array

    // Draw methods
    public function drawByRandom(Tournament $tournament, int $categoryId): array
    public function drawBySeeding(Tournament $tournament, int $categoryId): array

    // Pair handling (doubles)
    public function drawPairsByRandom(Collection $athletes, Collection $groups): array
    public function drawPairsBySeeding(Collection $athletes, Collection $groups): array

    // Singles handling
    public function drawAthletesByRandom(Collection $athletes, Collection $groups): array
    public function drawAthletesBySeeding(Collection $athletes, Collection $groups): array

    // Results & reset
    public function getDrawResults(Tournament $tournament, int $categoryId): array
    public function resetDraw(Tournament $tournament, int $categoryId): void

    // Manual draw
    public function getManualDrawData(Tournament $tournament, int $categoryId): array
    public function saveManualDraw(Tournament $tournament, int $categoryId, array $assignments): void

    // Helpers
    private function getPairsFromAthletes(Collection $athletes): Collection
    private function getGroupedAthletes(Collection $athletes): Collection
    private function isDoubleCategory(TournamentCategory $category): bool
}
```

### 3. TournamentMatchService
Extract match generation and scoring:
```php
namespace App\Services\Tournament;

class TournamentMatchService
{
    // Match generation
    public function createMatchesForGroups(Tournament $tournament, int $categoryId, int $roundId): array
    public function createSingleMatches(Group $group, Round $round, Collection $athletes): array
    public function createDoubleMatches(Group $group, Round $round, Collection $pairs): array

    // Single match CRUD
    public function storeMatch(array $data): MatchModel
    public function updateScore(MatchModel $match, array $scores): MatchModel

    // Score processing
    public function handleEndSet(MatchModel $match, array $setData): void
    public function handleEndMatch(MatchModel $match): void
    public function handleRegularUpdate(MatchModel $match, array $data): void

    // Query helpers
    public function getMatches(Tournament $tournament, array $filters = []): Collection
    public function getMatch(int $matchId): MatchModel
}
```

### 4. TournamentStandingService
Extract standings and ranking logic:
```php
namespace App\Services\Tournament;

class TournamentStandingService
{
    // Standing updates
    public function updateGroupStandings(Group $group): void
    public function updateGroupStandingsWithSets(Group $group, MatchModel $match): void
    public function updateTournamentAthleteStats(TournamentAthlete $athlete): void

    // Ranking calculation
    public function recalculateGroupRankings(Group $group): void
    public function getRankings(Tournament $tournament, int $categoryId): Collection
    public function getCategoryGroups(Tournament $tournament, int $categoryId): Collection

    // All tournaments rankings
    public function getAllTournamentsRankings(array $filters = []): Collection
}
```

### 5. Wire Services to New Controllers
- Inject services via constructor in each new controller
- TournamentDrawController → TournamentDrawService
- TournamentMatchController → TournamentMatchService
- TournamentRankingController → TournamentStandingService

### 6. Wire Services to Old Controller (backward compat)
- Update `HomeYardTournamentController` constructor to inject new services
- Replace inline logic with service calls in old controller methods
- Verify old routes produce same results

## Todo List
- [x] Create `app/Services/Tournament/` directory
- [x] Extract TournamentDrawService from controller
- [x] Extract TournamentMatchService from controller
- [x] Extract TournamentStandingService from controller
- [x] Inject services into new controllers
- [ ] Inject services into old controller for backward compatibility (deferred — old controller untouched per plan decision)
- [x] Verify old tournament routes still produce same results

## Success Criteria
- All 3 services created with extracted logic
- Old controller calls services instead of inline logic
- No behavior changes - identical results
- Each service < 200 lines

## Risk Assessment
- **Logic extraction errors:** High risk - carefully copy logic, test each method
- **Missing edge cases:** Medium - doubles vs singles handling has many branches
- **Transaction boundaries:** Ensure DB transactions preserved in service layer

## Security Considerations
- Services should validate inputs (don't trust controller-level validation alone)
- Maintain `lockForUpdate()` patterns in draw/match generation

## Next Steps
- Phase 3: Dashboard layout consumes these services via new controllers
