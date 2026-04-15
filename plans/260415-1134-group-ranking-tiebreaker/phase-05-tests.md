# Phase 05 — Comprehensive Tiebreaker Tests

## Context Links
- Existing tests: `tests/Feature/Tournament/RankingsIdempotencyTest.php`
- Service: `app/Services/Tournament/TournamentStandingService.php`
- Blocked by: Phases 01 + 02 (all logic must be in place before tests are written)

## Overview
- **Priority:** P2
- **Status:** pending
- **Description:** Add a dedicated test class covering all tiebreaker tiers, H2H 2-team, 3-team cycle skip, manual override. Extend existing `makeCompletedMatch()` helper pattern.

## Key Insights
- Existing `makeCompletedMatch()` in `RankingsIdempotencyTest` uses `athlete1`/`athlete2` key shape (not `athlete1_score`). New tests should cover BOTH key shapes to validate `countGamesFromMatch()` dual-key parsing.
- To test `games_differential` as tiebreaker: all teams must have equal `points`. Construct matches so points are equal but raw game scores differ.
- H2H 2-team test: A and B tied on points + games_differential. A beat B in group match (set scores give A more set wins). Expect A rank 1.
- H2H skip for 3-team: A, B, C all tied on points + games_differential. C has fewest `games_lost`. Expect C rank 1. H2H must NOT have been called (verify via mock or by confirming no H2H-specific ordering mismatch).
- Manual override test: 2 teams still tied after all 4 tiers. Set `manual_rank_override` on both. Expect lower number ranks first.
- Cycle scenario (A beats B, B beats C, C beats A — all 3 pts equal): H2H skipped (3-team bucket), sort falls to tier 4.

## Requirements
- New test class `tests/Feature/Tournament/TiebreakerRankingTest.php`
- Use `RefreshDatabase` trait
- Reuse or extend `makeCompletedMatch()` and `makeAthlete()` helpers (DRY — extract to trait or base class if both test files need them)
- All tests must pass with `php artisan test --filter TiebreakerRanking`

## Test Cases

### Group A — games_differential tiebreaker (tier 2)
**Scenario:** 3 teams A, B, C. A and B both beat C but with different margins. A vs B NOT played. Both A and B on 3 pts → tier 1 tied → tier 2 (games_diff) decides.
- Match A vs C: A wins 2-0, scores `[{a1:11,a2:5},{a1:11,a2:5}]`
  - A: games_won=22, games_lost=10 → diff=+12
  - C (from this match): games_won=10, games_lost=22
- Match B vs C: B wins 2-0, scores `[{a1:11,a2:9},{a1:11,a2:9}]`
  - B: games_won=22, games_lost=18 → diff=+4
  - C (from this match): games_won=18, games_lost=22
- Totals: A=3pts/diff=+12, B=3pts/diff=+4, C=0pts/diff=-16
- **Assert:** A rank=1, B rank=2, C rank=3 (A beats B on games_diff despite same points)

### Group B — H2H 2-team tiebreaker (tier 3)
**Scenario:** A and B play 2 matches each (3rd team C). A and B end up tied on pts AND games_differential. A beat B directly.
- Construct standings so A and B: same points, same games_differential
- Direct match A vs B: A wins (more sets)
- **Assert:** A rank=1, B rank=2

### Group C — H2H skip for 3+ tied bucket (tier 4 fallback)
**Scenario:** 4 teams A, B, C, D. Top 3 (A, B, C) beat D, cycle among themselves → all on 6 pts. Constructed with asymmetric scores so tier 2 (diff) ties but tier 4 (games_lost) differs. H2H must be SKIPPED for 3-team bucket → fallback to tier 4 decides.

**Math constraint:** in a 3-team symmetric cycle, games_lost is forced equal (see phase review note). Must use 4-team group with asymmetric D-matches to compensate.

**Matches (all 2-set wins):**
- A vs B: A wins 11-5, 11-5 → A: +22/-10
- B vs C: B wins 11-3, 11-2 → B: +22/-5
- C vs A: C wins 11-8, 11-7 → C: +22/-15
- A vs D: A wins 11-8, 11-7 → A: +22/-15
- B vs D: B wins 11-8, 11-7 → B: +22/-15
- C vs D: C wins 11-0, 11-0 → C: +22/-0  *(unusual score, legal for test isolation)*

**Totals:**
| Team | points | games_won | games_lost | diff |
|------|--------|-----------|------------|------|
| A | 6 (2W) | 22+15+22=59 | 10+22+15=47 | +12 |
| B | 6 (2W) | 10+22+22=54 | 22+5+15=42 | +12 |
| C | 6 (2W) | 5+22+22=49 | 22+15+0=37 | +12 |
| D | 0 (0W) | 15+15+0=30 | 22+22+22=66 | -36 |

**H2H verification:** if H2H were applied (incorrect behavior), A would beat B in direct match (A won A vs B) → A ranked above B. Correct behavior: H2H SKIPPED for 3-team bucket → use games_lost ASC → C < B < A.

- **Assert:** C rank=1, B rank=2, A rank=3, D rank=4
- **Regression guard:** if assertion flips to A=1, B=2, C=3, it means code incorrectly applied H2H for 3-team bucket.

### Group D — manual_rank_override (tier 5)
**Scenario:** 2 teams tied after tiers 1-4. Manually set overrides.
- After `recalculateGroupStandings()`, directly set `manual_rank_override` on both standings
- Call `recalculateGroupRankings($groupId)` again
- **Assert:** team with override=1 gets rank=1, team with override=2 gets rank=2

### Group E — manual_rank_override partial (NULLS LAST)
**Scenario:** 3 teams tied after tiers 1-4. Only 1 team has `manual_rank_override=1`, others null.
- **Assert:** team with override=1 gets rank=1; other 2 can be in any order but after rank=1

### Group F — dual key shape for countGamesFromMatch
**Scenario:** matches using `athlete1_score`/`athlete2_score` keys (legacy shape).
- Match scores: `[{athlete1_score:11, athlete2_score:5}]`
- **Assert:** `games_won`/`games_lost` populated correctly (same result as `athlete1`/`athlete2` shape)

### Group G — games_differential used NOT sets_differential (regression guard)
**Scenario:** A and B both win 1 match (3 pts each), but A wins in 2 sets with tight margins while B wins in 3 sets with lopsided scores. A has better sets_diff, B has better games_diff. Sort must use games_diff, NOT sets_diff.

- Match A vs C: A wins 2-0, scores `[{a1:11,a2:9},{a1:11,a2:9}]`
  - A: sets_won=2 sets_lost=0 sets_diff=+2; games_won=22 games_lost=18 games_diff=+4
- Match B vs C: B wins 2-1, scores `[{a1:11,a2:2},{a1:2,a2:11},{a1:11,a2:2}]`
  - B: sets_won=2 sets_lost=1 sets_diff=+1; games_won=24 games_lost=15 games_diff=+9

**Assert:** B rank=1, A rank=2 (B wins on games_diff +9 > +4, despite A having higher sets_diff +2 > +1)

**Regression guard:** if assertion flips to A=1, B=2, code is using sets_differential instead of games_differential.

## Related Code Files

**Create:**
- `tests/Feature/Tournament/TiebreakerRankingTest.php`

**Possibly modify:**
- `tests/Feature/Tournament/RankingsIdempotencyTest.php` — extract `makeAthlete()` + `makeCompletedMatch()` to a shared trait if both classes need them

**Possibly create:**
- `tests/Feature/Tournament/Concerns/BuildsGroupFixtures.php` — trait with shared test helpers

## Implementation Steps

1. **Check if helper extraction is needed**: if `TiebreakerRankingTest` duplicates `makeAthlete`/`makeCompletedMatch`, extract to `tests/Feature/Tournament/Concerns/BuildsGroupFixtures.php` trait. Both test classes `use BuildsGroupFixtures`.

2. **Create `TiebreakerRankingTest`** with `setUp()` matching `RankingsIdempotencyTest` pattern (user + tournament + group + 3 athletes).

3. **Implement test cases A through G** as described above. Each test is a standalone method, uses `RefreshDatabase` isolation.

4. **For H2H skip test (Group C)**: assert rank by checking `GroupStanding::where(...)->value('rank_position')`. Do not mock the service — test end-to-end through `recalculateGroupStandings()`.

5. **Run full test suite** to confirm no regressions in `RankingsIdempotencyTest`:
   ```bash
   php artisan test --filter Tournament
   ```

## Todo List
- [ ] Decide: extract shared helpers to trait or duplicate (check line counts first)
- [ ] Create `TiebreakerRankingTest.php` with setUp
- [ ] Test A: games_differential as tiebreaker
- [ ] Test B: H2H 2-team
- [ ] Test C: H2H skip 3-team cycle → tier 4 fallback
- [ ] Test D: manual_rank_override both set
- [ ] Test E: manual_rank_override partial (NULLS LAST)
- [ ] Test F: dual key shape `athlete1_score`
- [ ] Test G: games_differential beats sets_differential (regression guard)
- [ ] Run `php artisan test --filter Tournament` — all pass

## Success Criteria
- All 7 new test methods pass
- All 4 existing `RankingsIdempotencyTest` methods still pass
- Total test count for `Tournament` filter: 11 passing, 0 failing

## Risk Assessment
- **Test data construction for tied games_differential**: requires careful arithmetic — document exact set scores inline in each test for readability
- **H2H test isolation**: each test uses `RefreshDatabase` — no cross-contamination

## Security Considerations
- Tests use factory/create patterns, no real user data
- `RefreshDatabase` ensures clean state — no data leakage between test cases
