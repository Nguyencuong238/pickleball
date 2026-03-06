# Phase 02: Service - MLP Game Generation Logic

## Context Links
- [plan.md](plan.md) | [phase-01](phase-01-database.md)
- `app/Services/LeagueScheduleService.php`
- `app/Services/LeagueService.php`
- `app/Services/LeagueStandingsService.php`

## Overview
- **Priority**: High (blocks Phase 3)
- **Status**: Pending
- When `competition_format === 'mlp'`, generate 6 sub-games per match using C(4,2) player pair combinations
<!-- Updated: Validation Session 1 - Scoring by total points, not game count -->

## Key Insights
- Current flow: `generateRoundRobin()` iterates `match_format` array to create games with `game_type`
- For MLP: ignore `match_format`, generate 6 games with all pair combos from team's 4 players
- C(4,2) = 6 pairs: (1,2), (1,3), (1,4), (2,3), (2,4), (3,4)
- Each game pairs home_pair[i] vs away_pair[i] (same index pairing)
- **Match winner = team with higher TOTAL SCORE across all 6 games** (not game count)
- Admin nhap diem tung game, khong can max_score config
- Admin sap xep thu tu VDV (A1,A2,A3,A4) truoc khi generate schedule

## Requirements
### Functional
- MLP format: each match gets exactly 6 games
- Each game stores 4 player IDs (home pair + away pair)
- Validate min 4 active players per team before generating schedule
- `game_type` = `'DOUBLES'` for all MLP games
- **determineMatchWinner()**: MLP = compare total score (sum home_score vs sum away_score across 6 games). Traditional = unchanged (game count)
- Player ordering: admin sets `order` field on league_team_players. Schedule uses this order for pairing.

### Non-functional
- Keep traditional format path unchanged
- No changes to standings calculation logic (standings still use match wins)

## Architecture
```
LeagueScheduleService::generateRoundRobin()
  |
  +--> if traditional: existing loop over match_format (unchanged)
  +--> if mlp: call generateMlpGames($match, $homeTeam, $awayTeam)
         |
         +--> get first 4 active players per team
         +--> generate 6 pair combos per team
         +--> create 6 games with player FK assignments
```

## Related Code Files
- **Modify**: `app/Services/LeagueScheduleService.php` -- add MLP branch in game creation + helper method
- **Modify**: `app/Services/LeagueService.php` -- add MLP player count validation before schedule generation

## Implementation Steps

1. **LeagueService** -- Add validation helper:
   ```php
   public function validateMlpTeams(League $league): void
   {
       $teams = $league->teams()->active()->withCount(['players' => fn($q) => $q->where('status', 'active')])->get();
       foreach ($teams as $team) {
           if ($team->players_count < 4) {
               throw new InvalidArgumentException("Doi '{$team->name}' can toi thieu 4 VDV cho format MLP.");
           }
       }
   }
   ```

2. **LeagueScheduleService::generateRoundRobin()** -- Branch game creation:
   - After creating match, check `$league->competition_format`
   - If `'mlp'`: call `$this->generateMlpGames($match, $home, $away)`
   - If `'traditional'`: existing `foreach ($matchFormat ...)` loop (unchanged)

3. **LeagueScheduleService** -- Add `generateMlpGames()` method:
   ```php
   private function generateMlpGames(LeagueMatch $match, int $homeTeamId, int $awayTeamId): void
   {
       // Order by admin-set `order` field for deterministic pairing
       $homePlayers = LeagueTeamPlayer::where('league_team_id', $homeTeamId)
           ->where('status', 'active')->orderBy('order')->take(4)->get();
       $awayPlayers = LeagueTeamPlayer::where('league_team_id', $awayTeamId)
           ->where('status', 'active')->orderBy('order')->take(4)->get();

       // C(4,2) combinations - indices into the 4-player array
       $pairs = [[0,1],[0,2],[0,3],[1,2],[1,3],[2,3]];

       foreach ($pairs as $index => $pair) {
           $match->games()->create([
               'game_number' => $index + 1,
               'game_type' => 'DOUBLES',
               'status' => 'pending',
               'home_player_1_id' => $homePlayers[$pair[0]]->id,
               'home_player_2_id' => $homePlayers[$pair[1]]->id,
               'away_player_1_id' => $awayPlayers[$pair[0]]->id,
               'away_player_2_id' => $awayPlayers[$pair[1]]->id,
           ]);
       }
   }
   ```

4. **LeagueScheduleService or LeagueService** -- Update `determineMatchWinner()` for MLP:
   ```php
   // MLP: winner = team with higher total score across all games
   if ($match->league->competition_format === 'mlp') {
       $totalHome = $match->games->sum('home_score');
       $totalAway = $match->games->sum('away_score');
       $winnerId = $totalHome > $totalAway ? $match->home_team_id
                 : ($totalAway > $totalHome ? $match->away_team_id : null);
   }
   // Traditional: winner = team with more games won (unchanged)
   ```

5. **LeagueService::updateStatus()** -- Add MLP validation before generating schedule

6. **HomeYardLeagueController::generateSchedule()** -- Same MLP validation

7. **Migration** -- Add `order` column to `league_team_players` (nullable int, default null, for admin sorting)

## Todo List
- [ ] Add `order` column migration to `league_team_players`
- [ ] Add `validateMlpTeams()` to LeagueService
- [ ] Add MLP validation call in `LeagueService::updateStatus()`
- [ ] Add MLP validation call in `HomeYardLeagueController::generateSchedule()`
- [ ] Add `generateMlpGames()` private method to LeagueScheduleService
- [ ] Branch game creation in `generateRoundRobin()` by competition_format
- [ ] Update `determineMatchWinner()` for MLP: total score comparison
- [ ] Verify traditional format still works unchanged

## Success Criteria
- MLP league with 4+ players/team generates 6 games per match
- Each game has correct player pair assignments (ordered by admin-set order)
- Match winner = team with higher total score across 6 games
- Traditional league schedule generation unchanged
- Error thrown if MLP team has < 4 active players

## Risk Assessment
- **Player ordering**: using `orderBy('id')` ensures deterministic pair assignment
- **Edge case**: team with exactly 4 players works; >4 players uses first 4 by ID (KISS)
- **Existing data**: no impact on existing leagues (all are traditional or have no schedule yet)

## Security
- All operations behind `auth` middleware + ownership check (existing)
- Player FK constraints prevent invalid references
