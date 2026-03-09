# Plan Report: MLP League Format

## Summary
Created 3-phase implementation plan for MLP competition format. Core idea: reuse existing `league_match_games` table with 4 new nullable FK columns for player pair tracking. No new tables. Existing score saving + match winner determination logic works unchanged.

## Data Model Decision
- Add `home_player_1_id`, `home_player_2_id`, `away_player_1_id`, `away_player_2_id` to `league_match_games`
- FK to `league_team_players.id`, nullable (traditional format ignores them)
- No new tables -- KISS

## Key Flow
1. Admin creates MLP league (existing `competition_format = 'mlp'`)
2. Adds teams with min 4 players each
3. Generates schedule -> `generateRoundRobin()` branches:
   - Traditional: existing match_format loop
   - MLP: `generateMlpGames()` creates 6 games per match using C(4,2) pair combos
4. Admin opens match -> MLP modal shows 6 sub-games with player names
5. Scores each sub-game via existing `updateGameScore` endpoint
6. After 6th game scored, `saveGameScore()` auto-calls `determineMatchWinner()` -> standings updated

## Files to Change (7 total)
| File | Action |
|------|--------|
| `database/migrations/2026_03_06_add_player_pairs_to_league_match_games.php` | Create |
| `app/Models/LeagueMatchGame.php` | Modify (fillable + 4 relationships) |
| `app/Services/LeagueScheduleService.php` | Modify (MLP branch + generateMlpGames) |
| `app/Services/LeagueService.php` | Modify (validateMlpTeams) |
| `app/Http/Controllers/Front/HomeYardLeagueController.php` | Modify (eager load + validation) |
| `resources/views/home-yard/leagues/_tab-matches.blade.php` | Modify (MLP modal + JS) |

## Effort: ~6h across 3 phases

## Plan Location
`/Users/thaopv/Desktop/php/pickleball/plans/260306-1058-mlp-league-format/`
