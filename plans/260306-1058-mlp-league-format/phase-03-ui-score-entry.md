# Phase 03: UI - MLP Sub-Game Score Entry

## Context Links
- [plan.md](plan.md) | [phase-01](phase-01-database.md) | [phase-02](phase-02-service-logic.md)
- `resources/views/home-yard/leagues/_tab-matches.blade.php`
- `app/Http/Controllers/Front/LeagueMatchController.php`
- Existing route: `PUT leagues/{league}/matches/{match}/games/{game}/score`

## Overview
- **Priority**: Medium
- **Status**: Pending
- Replace simple match-score modal with sub-game score entry for MLP matches. Show player pairs per game; admin enters score for each sub-game.
<!-- Updated: Validation Session 1 - Total score display, collapsible results -->

## Key Insights
- Current UI: single modal with 2 number inputs (match-level score), calls `updateScore` endpoint
- Existing `updateGameScore` endpoint + `saveGameScore()` service already handle game-level scoring
- **Match winner by total score** (not game count) - UI must show running total
- Need to show player names in each sub-game row for MLP clarity
- Traditional format should keep current simple modal unchanged
- Admin (league creator) manages scores, not system admin

## Requirements
### Functional
- MLP matches: clicking "Diem" opens modal showing 6 sub-games with player pair names
- Each sub-game row: home pair names | score inputs | away pair names
- **Show running total** at bottom of modal (sum of all home scores vs sum of all away scores)
- Submit saves each game score individually via existing `updateGameScore` endpoint
- Match winner = team with higher total score (auto-calculated after all 6 games scored)
- Traditional matches: keep current simple score modal

### Non-functional
- Responsive modal (works on mobile)
- Consistent styling with existing modals

## Architecture
```
_tab-matches.blade.php
  |
  +--> Traditional match: openScoreModal() (existing, unchanged)
  +--> MLP match: openMlpScoreModal(matchId, games[])
         |
         +--> Renders 6 rows with player names + score inputs
         +--> Submit: loops games, PUTs each game score
         +--> On all success: reload page
```

## Related Code Files
- **Modify**: `resources/views/home-yard/leagues/_tab-matches.blade.php` -- add MLP modal + conditional button
- **Modify**: `app/Http/Controllers/Front/HomeYardLeagueController.php` -- eager load game player relationships in `show()`

## Implementation Steps

1. **HomeYardLeagueController::show()** -- Extend eager loading:
   ```php
   'rounds.matches.games.homePlayer1.user',
   'rounds.matches.games.homePlayer2.user',
   'rounds.matches.games.awayPlayer1.user',
   'rounds.matches.games.awayPlayer2.user',
   ```

2. **_tab-matches.blade.php** -- Conditional score button:
   - Check `$league->competition_format === 'mlp'`
   - MLP: call `openMlpScoreModal()` with match data + games JSON
   - Traditional: keep existing `openScoreModal()` call

3. **_tab-matches.blade.php** -- Add MLP score modal HTML:
   - Modal title: "Nhap Diem Cac Game - [HomeTeam] vs [AwayTeam]"
   - 6 rows, each showing:
     ```
     [Player1 + Player2]  [score input] - [score input]  [Player3 + Player4]
     ```
   - "Luu Tat Ca" button to submit all scores

4. **_tab-matches.blade.php** -- Add MLP score submit JS:
   ```javascript
   function submitMlpScores() {
       var games = document.querySelectorAll('.mlp-game-row');
       var promises = [];
       games.forEach(function(row) {
           var gameId = row.dataset.gameId;
           var matchId = row.dataset.matchId;
           var home = row.querySelector('.mlp-home-score').value;
           var away = row.querySelector('.mlp-away-score').value;
           promises.push(
               fetch(leagueUrl + '/matches/' + matchId + '/games/' + gameId + '/score', {
                   method: 'PUT',
                   headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                   body: JSON.stringify({ home_score: parseInt(home), away_score: parseInt(away) })
               }).then(r => r.json())
           );
       });
       Promise.all(promises).then(function(results) {
           var allSuccess = results.every(r => r.success);
           if (allSuccess) { toastr.success('Cap nhat diem thanh cong.'); window.location.reload(); }
           else { toastr.error('Co loi xay ra.'); }
       });
   }
   ```

5. **_tab-matches.blade.php** -- Show MLP game results in match display:
   - Under each MLP match row, show collapsible sub-game results when completed
   - Format: `Game 1: PlayerA + PlayerB  2-1  PlayerC + PlayerD`

## Todo List
- [ ] Extend eager loading in HomeYardLeagueController::show()
- [ ] Add competition_format conditional for score button
- [ ] Create MLP score modal HTML with running total row
- [ ] Add openMlpScoreModal() JS function
- [ ] Add submitMlpScores() JS function
- [ ] Add collapsible sub-game results display for completed MLP matches
- [ ] Add drag-drop player order UI in team detail view (for MLP leagues) -- reuse SortableJS (already in project, see config.blade.php:1235)
- [ ] Add endpoint to save player order (update `order` column on league_team_players)
- [ ] Test on mobile viewport

## Success Criteria
- MLP match shows 6 sub-games with player pair names in modal
- Each sub-game score saves independently via existing endpoint
- Match auto-completes after all 6 games scored
- Traditional format UI unchanged
- Responsive on mobile

## Risk Assessment
- **Parallel fetch requests**: all 6 PUT requests fired together; if one fails, partial scores saved. Acceptable because admin can re-submit (idempotent updates).
- **Player name loading**: extra eager loading adds 4 relationships per game. For typical league (<100 games), negligible performance impact.

## Security
- All routes behind `auth` middleware + ownership check (existing)
- Game-match-league chain validated in `updateGameScore` controller (existing)
