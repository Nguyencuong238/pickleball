# Phase 01: Database - Player Pair Columns on league_match_games

## Context Links
- [plan.md](plan.md)
- Migration ref: `database/migrations/2026_02_25_006_create_league_match_games_table.php`
- Model: `app/Models/LeagueMatchGame.php`

## Overview
- **Priority**: High (blocks Phase 2 & 3)
- **Status**: Pending
- Add 4 nullable FK columns to `league_match_games` to track which players from each team are paired in each sub-game

## Key Insights
- Current `league_match_games` has `game_type` (string) and `game_number` -- sufficient for MLP
- `league_team_players` has `id`, `league_team_id`, `user_id`, `gender` -- player IDs ready
- No new table needed; 4 columns on existing table keeps it simple

## Requirements
- Store home_player_1_id, home_player_2_id, away_player_1_id, away_player_2_id per game
- These are nullable (traditional format won't use them)
- FK references `league_team_players.id`
- Add `order` column to `league_team_players` for admin-controlled player ordering (used in MLP pairing)

## Architecture
```
league_match_games (existing)
  + home_player_1_id  -> league_team_players.id (nullable)
  + home_player_2_id  -> league_team_players.id (nullable)
  + away_player_1_id  -> league_team_players.id (nullable)
  + away_player_2_id  -> league_team_players.id (nullable)

league_team_players (existing)
  + order  -> unsignedTinyInteger (nullable, for MLP player ordering)
```

## Related Code Files
- **Create**: `database/migrations/2026_03_06_add_player_pairs_to_league_match_games.php`
- **Modify**: `app/Models/LeagueMatchGame.php` (add fillable + relationships)

## Implementation Steps

1. Create migration `2026_03_06_add_player_pairs_to_league_match_games.php`:
   ```php
   $table->foreignId('home_player_1_id')->nullable()->constrained('league_team_players')->nullOnDelete();
   $table->foreignId('home_player_2_id')->nullable()->constrained('league_team_players')->nullOnDelete();
   $table->foreignId('away_player_1_id')->nullable()->constrained('league_team_players')->nullOnDelete();
   $table->foreignId('away_player_2_id')->nullable()->constrained('league_team_players')->nullOnDelete();
   ```

2. Update `LeagueMatchGame` model:
   - Add 4 columns to `$fillable`
   - Add 4 `BelongsTo` relationships: `homePlayer1()`, `homePlayer2()`, `awayPlayer1()`, `awayPlayer2()`

## Todo List
- [ ] Create migration file (4 FK columns on league_match_games + order column on league_team_players)
- [ ] Update LeagueMatchGame $fillable
- [ ] Add 4 BelongsTo relationships to LeagueMatchGame
- [ ] Update LeagueTeamPlayer $fillable with `order`
- [ ] Run `php artisan migrate` to verify

## Success Criteria
- Migration runs without errors
- Traditional format games still work (columns remain null)
- Model relationships resolve correctly

## Risk Assessment
- **Low risk**: purely additive, nullable columns, no existing data affected
- `nullOnDelete` ensures player removal doesn't break game records
