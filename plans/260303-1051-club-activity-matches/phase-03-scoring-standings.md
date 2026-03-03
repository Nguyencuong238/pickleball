# Phase 03 - Scoring + Standings

## Context Links
- Plan overview: `plans/260303-1051-club-activity-matches/plan.md`
- Phase 02: `plans/260303-1051-club-activity-matches/phase-02-generate-matches.md`
- Competition score pattern: `app/Http/Controllers/ClubCompetitionController.php` saveScore()
- Competition service score pattern: `app/Services/ClubCompetitionService.php`
- Standings model: `app/Models/ClubActivityMatchStanding.php` (created in Phase 1)
- Match model: `app/Models/ClubActivityMatch.php` (created in Phase 1)

## Overview
- **Priority:** P2
- **Status:** complete
- **Goal:** Score entry UI for management, auto-recalculate individual standings after each score save

## Requirements
- Inline score entry on match cards (management only)
- Score inputs: two integer fields (team1_score, team2_score)
- On save: update match status to `completed`, set `completed_at`, recalculate standings for all 2-4 involved players
- Standings: individual leaderboard sorted by wins desc, then point_differential desc
- Round completion: button to mark round as completed (locks scores for that round)
- All club members can view standings

## Architecture

### Standings Calculation Logic
On each score save, for the match's players:
- Determine winner: team1 wins if team1_score > team2_score (and vice versa)
- For singles: player1 vs player3 — increment wins/losses + points
- For doubles: player1+player2 vs player3+player4 — all 4 players updated
- Recalculate from scratch for affected players (re-aggregate all completed matches):
  ```
  SELECT SUM(wins), SUM(losses), SUM(points_scored), SUM(points_against)
  FROM all completed matches WHERE user_id IN (involved players)
  ```
  Then upsert into `club_activity_match_standings`

### New Controller Methods (add to `ClubMatchController`)
```
PUT  matches/{match}/score    → saveScore()   management only
GET  matches/standings        → standings()   view only
```

### Route additions (inside matches prefix group)
```php
Route::put('{match}/score', [ClubMatchController::class, 'saveScore'])->name('save-score');
Route::get('standings', [ClubMatchController::class, 'standings'])->name('standings');
```

### ClubMatchService additions
```php
public function saveScore(ClubActivityMatch $match, int $t1, int $t2): void
  // DB::transaction:
  // 1. update match scores + status=completed + completed_at
  // 2. collect player IDs from match
  // 3. recalculateStandings($activity, $playerIds)

private function recalculateStandings(ClubActivity $activity, array $userIds): void
  // for each userId: query all completed matches, aggregate, upsert standing

public function getStandings(ClubActivity $activity): Collection
  // return standings ordered by wins desc, point_differential desc
  // with user relationship loaded
```

### Standings JSON shape
```json
{
  "standings": [
    {
      "rank": 1,
      "user": {"id": 5, "name": "Nguyen Van A"},
      "matches_played": 6,
      "wins": 5,
      "losses": 1,
      "points_scored": 66,
      "points_against": 42,
      "point_differential": 24
    }
  ]
}
```

## Related Code Files

### Modify
- `app/Http/Controllers/ClubMatchController.php` — add saveScore(), standings()
- `app/Services/ClubMatchService.php` — add saveScore(), recalculateStandings(), getStandings()
- `routes/web.php` — add score + standings routes inside matches group
- `resources/views/clubs/activities/partials/_matches-scripts.blade.php` — add score save + standings render JS
- `resources/views/clubs/activities/partials/_matches-styles.blade.php` — score input + standings table styles
- `resources/views/clubs/activities/partials/_matches-panel.blade.php` — add standings section div

## Implementation Steps

1. **ClubMatchService — saveScore()**
   - Validate match belongs to activity (check `$match->round->club_activity_id`)
   - Wrap in `DB::transaction`
   - Update match: `team1_score`, `team2_score`, `status = completed`, `completed_at = now()`
   - Collect player IDs: `array_filter([$m->player1_id, $m->player2_id, $m->player3_id, $m->player4_id])`
   - Call `recalculateStandings($activity, $playerIds)`

2. **ClubMatchService — recalculateStandings()**
   - For each `$userId` in `$playerIds`:
     - Query all completed matches for this activity where user appears in any player slot
     - Aggregate: `matches_played`, `wins`, `losses`, `points_scored`, `points_against`
     - `upsert()` into `club_activity_match_standings` keyed on `(club_activity_id, user_id)`
   - Keep query count O(players_in_match) = max 4 queries per save — acceptable

3. **ClubMatchController — saveScore()**
   - Route model bind `ClubActivityMatch $match`
   - `authorize('manageActivity', $club)`
   - Validate: `team1_score` and `team2_score` required integer min:0 max:99
   - Confirm match's round belongs to this activity: `$match->round->club_activity_id !== $activity->id` → 404
   - Call `$this->service->saveScore($match, $t1, $t2)`
   - Return JSON `{success: true, message: 'Đã cập nhật điểm số.'}`

4. **ClubMatchController — standings()**
   - `authorize('view', $club)`
   - Return `$this->service->getStandings($activity)` as JSON with rank added

5. **Routes** — add inside existing matches prefix group:
   ```php
   Route::put('{match}/score', [ClubMatchController::class, 'saveScore'])->name('save-score');
   Route::get('standings', [ClubMatchController::class, 'standings'])->name('standings');
   ```

6. **_matches-panel.blade.php** — add standings section below rounds container:
   ```blade
   <div class="matches-section" id="standings-section" style="display:none">
       <h4 class="matches-heading">Bảng xếp hạng cá nhân</h4>
       <div id="standings-table"></div>
   </div>
   ```

7. **JS — saveScore(matchId)**
   - Collect team1_score and team2_score from `input[data-match-id="${matchId}"][data-team]`
   - PUT to score route, on success: update match card display inline, call `loadStandings()`

8. **JS — loadStandings()**
   - GET standings route
   - If standings.length > 0: show `#standings-section`, render table
   - Standings table columns: Rank, Tên, Trận, Thắng, Thua, Điểm +/-, Hiệu số

9. **JS — renderMatch() update** (from Phase 2)
   - If `$isManagement` and match status !== completed: show inline score inputs + save button
   - If match completed: show scores as read-only, "Sửa" link to re-open inputs

10. **Styles** — add to `_matches-styles.blade.php`:
    - `.score-inputs` inline flex row
    - `.standings-table` responsive table with rank highlight for top 3
    - `.match-score-display` bold score vs score

## Todo List
- [ ] `ClubMatchService::saveScore()` with transaction
- [ ] `ClubMatchService::recalculateStandings()` aggregate query
- [ ] `ClubMatchService::getStandings()` with rank computation
- [ ] `ClubMatchController::saveScore()` endpoint
- [ ] `ClubMatchController::standings()` endpoint
- [ ] Add routes to `routes/web.php`
- [ ] Update `_matches-panel.blade.php` with standings section
- [ ] JS: saveScore(), loadStandings(), renderStandingsTable()
- [ ] JS: update renderMatch() for score inputs
- [ ] Styles: score inputs, standings table
- [ ] Test: save score updates match + standing correctly
- [ ] Test: doubles — all 4 players updated in standings

## Success Criteria
- PUT score saves to DB, match status becomes `completed`
- Standings recalculate immediately after score save
- Standings table renders with correct rank, wins, point differential
- Management can re-edit scores on completed matches
- Non-management sees read-only scores and standings
- Round completion hides score inputs for that round

## Risk Assessment
- **Recalculate from scratch:** avoids cumulative drift bugs; O(4) queries per save is fine
- **Match re-edit:** if score is overwritten, recalculate runs again from completed matches — correct by design
- **Tie-breaking:** wins then point_differential covers most cases; no need for head-to-head tiebreak (YAGNI)

## Security
- `saveScore` requires `manageActivity` policy — non-members/guests cannot submit
- Validate match belongs to activity (prevent cross-activity score injection)
- Score range capped at 99 server-side
