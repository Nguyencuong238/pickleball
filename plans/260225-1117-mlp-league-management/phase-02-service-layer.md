# Phase 2: Service Layer

## Context Links
- [Plan Overview](./plan.md)
- [Phase 1: Database & Models](./phase-01-database-and-models.md)
- [Code Standards - Services](../../docs/code-standards.md#service-classes)
- Reference: `app/Services/OprsService.php`, `app/Services/SkillQuizService.php`

## Overview
- **Priority**: P1
- **Status**: pending
- **Description**: Create 3 service classes encapsulating league business logic: CRUD, schedule generation, standings calculation

## Key Insights
- Existing services use constructor injection pattern (see OprsService, ChallengeService)
- Round-robin algorithm: each team plays every other team once. For N teams, N-1 rounds with N/2 matches each. If odd N, one team gets a bye per round.
- Standings recalc is triggered after each match score save -- simple approach, no caching needed for MVP
- Config JSON stores `match_format` array (e.g. `["WD","MD","MXD","MXD"]`), `points_for_win`, `points_for_loss`, `max_teams`, `max_players_per_team`, `scoring_type`

## Requirements

### Functional
- League CRUD with slug generation, status transitions (draft -> registration -> active -> completed)
- Round-robin schedule generation from active teams
- Automatic standings recalculation when match results submitted
- Match score entry with individual game scores and auto-determine winner

### Non-functional
- DB transactions for multi-model operations
- Type-hinted parameters and return types
- Constants for configurable values (default points)

## Architecture

```
LeagueService
├── createLeague(User, array): League
├── updateLeague(League, array): League
├── deleteLeague(League): bool
├── updateStatus(League, string): League
├── addTeam(League, array): LeagueTeam
├── updateTeam(LeagueTeam, array): LeagueTeam
├── removeTeam(LeagueTeam): bool
├── addPlayer(LeagueTeam, array): LeagueTeamPlayer
├── removePlayer(LeagueTeamPlayer): bool
└── validateLeagueOwnership(League, User): bool

LeagueScheduleService
├── generateRoundRobin(League): void
├── clearSchedule(League): void
└── getScheduleMatrix(League): array

LeagueStandingsService
├── recalculateStandings(League): void
├── updateMatchResult(LeagueMatch, int, int): void
├── saveGameScore(LeagueMatchGame, int, int): void
├── determineMatchWinner(LeagueMatch): ?LeagueTeam
└── initializeStandings(League): void
```

## Related Code Files

| File | Action |
|------|--------|
| `app/Services/LeagueService.php` | create |
| `app/Services/LeagueScheduleService.php` | create |
| `app/Services/LeagueStandingsService.php` | create |

## Implementation Steps

### LeagueService (~150 lines)

1. Create `app/Services/LeagueService.php` with constructor injection of `LeagueScheduleService` and `LeagueStandingsService`

2. Implement `createLeague(User $user, array $data): League`:
   - Validate required fields (name, config)
   - Generate slug from name using `Str::slug()`, append random suffix if duplicate
   - Set default config values if not provided: `points_for_win => 3, points_for_loss => 0, max_teams => 16, max_players_per_team => 10`
   - Wrap in DB::transaction
   - Return created League

3. Implement `updateLeague(League $league, array $data): League`:
   - Update fillable fields
   - Regenerate slug if name changed
   - Return updated League

4. Implement `deleteLeague(League $league): bool`:
   - Only allow deletion if status is `draft`
   - Cascade handled by FK constraints
   - Return success boolean

5. Implement `updateStatus(League $league, string $newStatus): League`:
   - Validate transition: draft->registration, registration->active, active->completed, any->cancelled
   - When transitioning to `active`: trigger schedule generation if no rounds exist, initialize standings
   - Return updated League

6. Implement team management methods:
   - `addTeam(League $league, array $data): LeagueTeam` -- validate max_teams not exceeded
   - `updateTeam(LeagueTeam $team, array $data): LeagueTeam`
   - `removeTeam(LeagueTeam $team): bool` -- only if league in draft/registration
   - `addPlayer(LeagueTeam $team, array $data): LeagueTeamPlayer` -- validate max_players, unique user per team
   - `removePlayer(LeagueTeamPlayer $player): bool`

7. Implement `validateLeagueOwnership(League $league, User $user): bool`:
   - Return `$league->user_id === $user->id`

### LeagueScheduleService (~100 lines)

8. Create `app/Services/LeagueScheduleService.php`

9. Implement `generateRoundRobin(League $league): void`:
   - Get active teams, require minimum 2
   - If odd number of teams, add a "bye" placeholder (null team_id)
   - Use circle method algorithm:
     - Fix team[0], rotate remaining teams
     - For N teams (or N+1 if odd), generate N-1 rounds
     - Each round: pair team[i] with team[N-1-i]
   - Wrap in DB::transaction
   - Create LeagueRound for each round with sequential round_number
   - Create LeagueMatch for each pairing (skip if either team is bye)
   - Create LeagueMatchGame entries based on league config `match_format` array

10. Implement `clearSchedule(League $league): void`:
    - Delete all rounds (cascades to matches and games via FK)
    - Only allow if league status is draft or registration

11. Implement `getScheduleMatrix(League $league): array`:
    - Return rounds with matches eagerly loaded with teams
    - Used by controller for display

### LeagueStandingsService (~120 lines)

12. Create `app/Services/LeagueStandingsService.php`

13. Implement `initializeStandings(League $league): void`:
    - Create LeagueStanding record for each active team (upsert to avoid duplicates)
    - Set all counters to 0

14. Implement `saveGameScore(LeagueMatchGame $game, int $homeScore, int $awayScore): void`:
    - Update game scores
    - Determine game winner (higher score wins)
    - Set game status to completed, set winner_team_id
    - After saving, check if all games in parent match are completed
    - If all completed, call `determineMatchWinner()`

15. Implement `determineMatchWinner(LeagueMatch $match): ?LeagueTeam`:
    - Count games won by each team
    - Set match `home_score` = home games won, `away_score` = away games won
    - Team with more games won is winner
    - If tied, mark as draw (winner_team_id = null)
    - Set match status to completed, completed_at = now()
    - Trigger `recalculateStandings()`

16. Implement `updateMatchResult(LeagueMatch $match, int $homeScore, int $awayScore): void`:
    - Direct match-level score entry (alternative to game-by-game)
    - Determine winner from scores
    - Set match completed
    - Trigger standings recalculation

17. Implement `recalculateStandings(League $league): void`:
    - Reset all standings for this league to 0
    - Query all completed matches for this league (through rounds)
    - For each completed match: increment played, wins/losses/draws, games_won/lost, points
    - Use league config for points_for_win, points_for_loss (default 3, 0)
    - Calculate rank by ordering: points desc, (games_won - games_lost) desc, games_won desc
    - Update rank field on each standing
    - Wrap in DB::transaction

## Todo List
- [ ] Create LeagueService with CRUD methods
- [ ] Implement league status transitions
- [ ] Implement team management in LeagueService
- [ ] Implement player management in LeagueService
- [ ] Create LeagueScheduleService
- [ ] Implement round-robin generation algorithm
- [ ] Create LeagueStandingsService
- [ ] Implement game score saving
- [ ] Implement match winner determination
- [ ] Implement standings recalculation
- [ ] Verify all methods have type hints and return types

## Success Criteria
- Round-robin correctly generates N-1 rounds for N teams
- Each team plays every other team exactly once
- Standings accurately reflect match results
- Points calculated per league config
- Rankings sorted correctly (points > game diff > games won)
- DB transactions wrap all multi-model operations

## Risk Assessment
- **Round-robin with odd teams**: Bye handling must skip match creation, not create null-team matches. Test with 3, 4, 5 team counts.
- **Concurrent score entry**: Two organizers updating same match simultaneously. Mitigate with `lockForUpdate()` on match during score save.
- **Standings consistency**: Full recalc approach is O(matches) per update. Acceptable for MVP (<100 matches per league).

## Security Considerations
- All service methods called from controllers that verify league ownership
- No direct user input passed to queries without validation
- DB transactions ensure atomic updates
