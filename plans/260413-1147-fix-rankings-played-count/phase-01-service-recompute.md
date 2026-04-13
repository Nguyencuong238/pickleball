# Phase 1 — Add recompute methods to service

**Priority:** Critical
**Status:** pending
**Effort:** S

## Goal

Add idempotent recompute methods as single source of truth for standings + athlete stats. Replace incremental update semantics.

## Files

**Modify:**
- `app/Services/Tournament/TournamentStandingService.php`

## Implementation

### 1. Add `recalculateGroupStandings(int $groupId): void`

Logic:
1. Load all `MatchModel` where `group_id = $groupId AND status = 'completed' AND set_scores IS NOT NULL`.
2. Zero out aggregates for **ALL** standings rows in group (matches_played/won/lost/drawn, sets_won/lost, sets_differential, points). **Critical for doubles:** partner standing rows (athlete not referenced in any match) must also be reset — otherwise stale inflation survives. Do NOT delete rows (dedup logic in `TournamentRankingController:123-138` relies on both partner rows existing).
3. For each match, parse `set_scores`:
   - Count `setsWon1`, `setsWon2`.
   - Sum `gamesScored1`, `gamesScored2` from each set (support both `athlete1_score` and `athlete1` key variants — mirror logic in `TournamentRankingController.php:105-106`).
4. For each of 2 athlete ids in match, `getOrCreateStanding` then apply deltas:
   - `matches_played += 1`
   - Winner → `matches_won += 1`, `points += 3`
   - Loser → `matches_lost += 1`
   - Draw → `matches_drawn += 1`, **no points** (preserve existing behavior — current code gives 0 pts on draw)
   - `sets_won += setsWon{self}`, `sets_lost += setsWon{opp}`
   - **DO NOT populate `games_won`/`games_lost`** — leave at 0. Current DB always has these = 0 (verified on group 34). Populating from set_scores would silently change the tie-break in `recalculateGroupRankings:141` (`games_won - games_lost`) and could reorder currently-stable tied rows. Out of scope for this fix.
5. Recompute `sets_differential`, `win_rate`. Leave `games_differential` = 0.
6. Save all standings.
7. Call existing `recalculateGroupRankings($groupId)` to re-rank + re-apply `is_advanced`.

Wrap in `DB::transaction`. Log exceptions.

### 2. Rewrite public methods as wrappers

```php
public function updateGroupStandingsWithSets(MatchModel $match, int $setsWon1, int $setsWon2): void
{
    $this->recalculateGroupStandings($match->group_id);
}

public function updateGroupStandings(MatchModel $match): void
{
    $this->recalculateGroupStandings($match->group_id);
}
```

Signature kept → call sites untouched this phase.

### 3. Add `recalculateTournamentAthleteStats(int $athleteId): void`

Similar logic but scoped to `TournamentAthlete` table. Query all completed matches where `athlete1_id = X OR athlete2_id = X`. Reset aggregates, replay. Save once.

Rewrite `updateTournamentAthleteStats` as wrapper calling recompute on both athletes.

## Todo

- [ ] Implement `recalculateGroupStandings`
- [ ] Implement `recalculateTournamentAthleteStats`
- [ ] Wrap `updateGroupStandingsWithSets` / `updateGroupStandings` / `updateTournamentAthleteStats`
- [ ] Verify `GroupStanding::updateAfterMatch` no longer referenced from service (still OK if referenced from duplicate code — phase 2 removes those)
- [ ] `php artisan tinker` sanity check on group 34

## Success Criteria

- Calling `recalculateGroupStandings(34)` 5× → identical final state.
- After recompute on group 34, all athletes show `matches_played = 4`.

## Notes

Current code supports draws: `matches_drawn` incremented, no points added. Preserve exactly. Check lines 31-44 & 69-82 in current service.
