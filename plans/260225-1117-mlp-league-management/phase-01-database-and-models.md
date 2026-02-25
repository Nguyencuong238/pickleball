# Phase 1: Database & Models

## Context Links
- [Plan Overview](./plan.md)
- [Code Standards](../../docs/code-standards.md) - Migration/Model conventions
- [System Architecture](../../docs/system-architecture.md) - Existing schema patterns
- Reference: `app/Models/GroupStanding.php`, `app/Models/Tournament.php`, `app/Models/MatchModel.php`

## Overview
- **Priority**: P1
- **Status**: pending
- **Description**: Create 7 migration files and 7 Eloquent models for the league system

## Key Insights
- Tournament model uses `$guarded = []`; new league models should use explicit `$fillable` per code standards
- GroupStanding model provides pattern for standings with `updateAfterMatch()` method
- MatchModel uses custom `$table = 'matches'`; league models use default table naming
- Migration naming: `2026_02_25_description.php` (no timestamp suffix)

## Requirements

### Functional
- 7 tables: leagues, league_teams, league_team_players, league_rounds, league_matches, league_match_games, league_standings
- Foreign key constraints with cascadeOnDelete where parent owns children
- JSON config column on leagues for flexible match format configuration
- Enum columns for status fields
- Unique constraint on league_standings(league_id, league_team_id)

### Non-functional
- Indexes on foreign keys and status columns for query performance
- Default values for score/counter columns (0)

## Architecture

```
League (1) ---> LeagueTeam (N) ---> LeagueTeamPlayer (N)
League (1) ---> LeagueRound (N) ---> LeagueMatch (N) ---> LeagueMatchGame (N)
League (1) ---> LeagueStanding (N, one per team)
LeagueMatch ---> home_team, away_team, winner_team (all FK to LeagueTeam)
```

## Related Code Files

| File | Action |
|------|--------|
| `database/migrations/2026_02_25_create_leagues_table.php` | create |
| `database/migrations/2026_02_25_create_league_teams_table.php` | create |
| `database/migrations/2026_02_25_create_league_team_players_table.php` | create |
| `database/migrations/2026_02_25_create_league_rounds_table.php` | create |
| `database/migrations/2026_02_25_create_league_matches_table.php` | create |
| `database/migrations/2026_02_25_create_league_match_games_table.php` | create |
| `database/migrations/2026_02_25_create_league_standings_table.php` | create |
| `app/Models/League.php` | create |
| `app/Models/LeagueTeam.php` | create |
| `app/Models/LeagueTeamPlayer.php` | create |
| `app/Models/LeagueRound.php` | create |
| `app/Models/LeagueMatch.php` | create |
| `app/Models/LeagueMatchGame.php` | create |
| `app/Models/LeagueStanding.php` | create |

## Implementation Steps

### Migrations

1. Create `2026_02_25_create_leagues_table.php`:
   - `id`, `user_id` (FK users, cascadeOnDelete), `name` (string 255), `slug` (string unique), `description` (text nullable)
   - `season_name` (string nullable), `config` (json nullable), `status` (enum: draft/registration/active/completed/cancelled, default draft)
   - `start_date` (date nullable), `end_date` (date nullable), `registration_deadline` (datetime nullable)
   - `logo` (string nullable), `timestamps`
   - Index on `status`, `user_id`

2. Create `2026_02_25_create_league_teams_table.php`:
   - `id`, `league_id` (FK leagues, cascadeOnDelete), `name` (string), `logo` (string nullable)
   - `captain_user_id` (FK users, nullOnDelete), `status` (enum: active/inactive/disqualified, default active)
   - `timestamps`
   - Index on `league_id`

3. Create `2026_02_25_create_league_team_players_table.php`:
   - `id`, `league_team_id` (FK league_teams, cascadeOnDelete), `user_id` (FK users, cascadeOnDelete)
   - `position` (string nullable), `gender` (enum: male/female)
   - `status` (enum: active/inactive, default active), `timestamps`
   - Unique constraint on `league_team_id, user_id`

4. Create `2026_02_25_create_league_rounds_table.php`:
   - `id`, `league_id` (FK leagues, cascadeOnDelete), `round_number` (unsignedInteger)
   - `name` (string nullable), `scheduled_date` (date nullable)
   - `status` (enum: pending/in_progress/completed, default pending), `timestamps`
   - Index on `league_id`

5. Create `2026_02_25_create_league_matches_table.php`:
   - `id`, `league_round_id` (FK league_rounds, cascadeOnDelete)
   - `home_team_id` (FK league_teams, cascadeOnDelete), `away_team_id` (FK league_teams, cascadeOnDelete)
   - `status` (enum: scheduled/in_progress/completed/cancelled, default scheduled)
   - `home_score` (unsignedInteger default 0), `away_score` (unsignedInteger default 0)
   - `winner_team_id` (FK league_teams nullable, nullOnDelete)
   - `scheduled_at` (datetime nullable), `completed_at` (datetime nullable)
   - `notes` (text nullable), `timestamps`
   - Index on `league_round_id`, `home_team_id`, `away_team_id`

6. Create `2026_02_25_create_league_match_games_table.php`:
   - `id`, `league_match_id` (FK league_matches, cascadeOnDelete), `game_number` (unsignedInteger)
   - `game_type` (string, e.g. "WD","MD","MXD"), `home_score` (unsignedInteger default 0), `away_score` (unsignedInteger default 0)
   - `winner_team_id` (FK league_teams nullable, nullOnDelete)
   - `status` (enum: pending/completed, default pending), `timestamps`
   - Index on `league_match_id`

7. Create `2026_02_25_create_league_standings_table.php`:
   - `id`, `league_id` (FK leagues, cascadeOnDelete), `league_team_id` (FK league_teams, cascadeOnDelete)
   - `played` (unsignedInteger default 0), `wins` (unsignedInteger default 0), `losses` (unsignedInteger default 0), `draws` (unsignedInteger default 0)
   - `games_won` (unsignedInteger default 0), `games_lost` (unsignedInteger default 0)
   - `points` (integer default 0), `rank` (unsignedInteger default 0)
   - `timestamps`
   - Unique constraint on `league_id, league_team_id`

### Models

8. Create `app/Models/League.php`:
   - `$fillable`: all columns except id/timestamps
   - `$casts`: config => array, start_date => date, end_date => date, registration_deadline => datetime
   - Route key: `slug` (override `getRouteKeyName()`)
   - Relationships: `user()` belongsTo User, `teams()` hasMany LeagueTeam, `rounds()` hasMany LeagueRound, `standings()` hasMany LeagueStanding
   - Scopes: `scopeByUser($query, $userId)`, `scopeActive($query)`, `scopeByStatus($query, $status)`
   - Boot method: auto-generate slug from name using `Str::slug()`

9. Create `app/Models/LeagueTeam.php`:
   - `$fillable`, relationships: `league()`, `captain()` belongsTo User, `players()` hasMany LeagueTeamPlayer, `homeMatches()`, `awayMatches()`, `standing()`
   - Scope: `scopeActive($query)`

10. Create `app/Models/LeagueTeamPlayer.php`:
    - `$fillable`, relationships: `team()` belongsTo LeagueTeam, `user()` belongsTo User

11. Create `app/Models/LeagueRound.php`:
    - `$fillable`, `$casts`: scheduled_date => date
    - Relationships: `league()`, `matches()` hasMany LeagueMatch

12. Create `app/Models/LeagueMatch.php`:
    - `$fillable`, `$casts`: scheduled_at => datetime, completed_at => datetime
    - Relationships: `round()` belongsTo LeagueRound, `homeTeam()`, `awayTeam()`, `winnerTeam()` belongsTo LeagueTeam, `games()` hasMany LeagueMatchGame
    - Accessor: `getLeagueAttribute()` via round->league

13. Create `app/Models/LeagueMatchGame.php`:
    - `$fillable`, relationships: `match()` belongsTo LeagueMatch, `winnerTeam()` belongsTo LeagueTeam

14. Create `app/Models/LeagueStanding.php`:
    - `$fillable`, `$casts`: all int columns => integer
    - Relationships: `league()`, `team()` belongsTo LeagueTeam
    - Method: `updateAfterMatch(bool $won, int $gamesWon, int $gamesLost, int $pointsForWin, int $pointsForLoss): void`
    - Scope: `scopeRanked($query)` orders by points desc, games_won-games_lost desc

15. Run `php artisan migrate` to verify all migrations

## Todo List
- [ ] Create leagues migration
- [ ] Create league_teams migration
- [ ] Create league_team_players migration
- [ ] Create league_rounds migration
- [ ] Create league_matches migration
- [ ] Create league_match_games migration
- [ ] Create league_standings migration
- [ ] Create League model
- [ ] Create LeagueTeam model
- [ ] Create LeagueTeamPlayer model
- [ ] Create LeagueRound model
- [ ] Create LeagueMatch model
- [ ] Create LeagueMatchGame model
- [ ] Create LeagueStanding model
- [ ] Run migrations successfully

## Success Criteria
- All 7 migrations run without errors
- All 7 models instantiable with correct relationships
- Foreign keys and unique constraints enforced
- `php artisan migrate:rollback` works cleanly

## Risk Assessment
- **Migration order**: Tables must be created in dependency order (leagues first, then teams, then matches). Mitigate by numbering migration files or using single migration.
- **Enum changes**: Adding new status values later requires migration. Acceptable for MVP.

## Security Considerations
- `user_id` FK on leagues ensures ownership check possible
- `captain_user_id` on teams for authorization
- No sensitive data stored; standard Laravel protections apply
