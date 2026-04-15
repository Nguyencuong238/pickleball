# Phase 03 — Align RankingQueryHelper Cross-tournament Sort

## Context Links
- File: `app/Services/Tournament/RankingQueryHelper.php` (lines 29-34 `getRankings` sort, line 122 `getAllTournamentsRankings` sort)
- Blocked by: Phase 01 (games_differential must be populated)

## Overview
- **Priority:** P2
- **Status:** pending
- **Description:** Both sort closures in `RankingQueryHelper` use the same wrong order as the main service. Align to spec — no H2H for cross-tournament (multi-group aggregation makes H2H undefined).

## Key Insights
- `getRankings()` (line 29-34): in-group sort used for display/pagination. After Phase 02, `rank_position` is stored correctly — this sort is a secondary display sort, should match same tier order (minus H2H).
- `getAllTournamentsRankings()` (line 122): aggregates stats across multiple tournaments per athlete. Cross-tournament: no H2H possible. Sort: `points DESC → games_differential DESC → games_lost ASC`. `matches_won` used as secondary currently — remove in favor of spec.
- Both fixes are 1-liner replacements — small, low risk.

## Requirements
- `getRankings()` sort: `points DESC → games_differential DESC → games_lost ASC`
- `getAllTournamentsRankings()` sort: same 3-tier (no H2H, no manual_override — cross-tournament override has no meaning)
- No structural changes — replace sort closures only

## Related Code Files

**Modify:**
- `app/Services/Tournament/RankingQueryHelper.php`

## Implementation Steps

1. **Replace `getRankings()` sort closure** (lines 29-33):
   ```php
   // BEFORE
   ->sort(function ($a, $b) {
       return ($b->points <=> $a->points)
           ?: ($b->matches_won <=> $a->matches_won)
           ?: (($b->games_won - $b->games_lost) <=> ($a->games_won - $a->games_lost));
   })

   // AFTER
   ->sort(function ($a, $b) {
       return ($b->points <=> $a->points)
           ?: ($b->games_differential <=> $a->games_differential)
           ?: ($a->games_lost <=> $b->games_lost);
   })
   ```
   Note: use stored `games_differential` field (not computed on-the-fly) for consistency with Phase 02 sort.

2. **Replace `getAllTournamentsRankings()` sort** (line 122):
   ```php
   // BEFORE
   $sorted = $athleteStats->sortByDesc('points')->sortByDesc('matches_won')->values();

   // AFTER — chain-sort: points DESC, then games_differential DESC, then games_lost ASC
   $sorted = $athleteStats->sortBy([
       ['points', 'desc'],
       ['games_differential', 'desc'],
       ['games_lost', 'asc'],
   ])->values();
   ```
   Note: `games_differential` and `games_lost` must be included in the aggregated `$athleteStats` map (lines 97-120). Currently only `points`, `matches_played`, `matches_won`, `matches_lost` are summed — add the missing fields.

3. **Add missing aggregated fields** to `getAllTournamentsRankings()` map (lines 97-120):
   ```php
   'games_differential' => $rows->sum('games_differential'),
   'games_lost'         => $rows->sum('games_lost'),
   ```

## Todo List
- [ ] Replace sort closure in `getRankings()`
- [ ] Add `games_differential` + `games_lost` to aggregated map in `getAllTournamentsRankings()`
- [ ] Replace sort chain in `getAllTournamentsRankings()`
- [ ] Verify no other sort closures reference `matches_won` as tiebreaker in this file

## Success Criteria
- `getRankings()` returns standings in correct tier order
- Cross-tournament ranking sorts by points → games_differential → games_lost
- No regression on existing pagination logic

## Risk Assessment
- **Low risk** — purely sort logic replacement, no schema or data changes
- `sortBy` with array of pairs is Laravel collection feature (available since L8) — safe

## Security Considerations
- None — read-only query helper, no user input in sort
