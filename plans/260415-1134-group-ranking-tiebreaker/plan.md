---
title: "Fix Group Ranking Tiebreaker (5-tier)"
description: "Fix silent-broken games fields, rewrite 5-tier sort logic, add H2H helper, manual override UI + tests"
status: completed
priority: P1
effort: 8h
branch: main
tags: [tournament, ranking, tiebreaker, standings]
created: 2026-04-15
---

# Group Ranking Tiebreaker Fix

## Problem Summary

3 silent bugs in `TournamentStandingService`:
1. `games_won`/`games_lost` never populated → `games_differential` always 0
2. Sort uses broken `games_differential` (always 0) instead of `sets_differential`
3. No H2H, no manual override, no meaningful tiebreaker beyond points

## Final Tiebreaker Spec (5-tier)

| Tier | Field | Direction | Notes |
|------|-------|-----------|-------|
| 1 | points | DESC | win=+3, loss=0 |
| 2 | games_differential | DESC | raw point scores across all sets |
| 3 | head-to-head | — | only 2-team tied buckets; skip for 3+ |
| 4 | games_lost | ASC | defensive tiebreaker |
| 5 | manual_rank_override | ASC | BTC input, nullable |

## Phases

| # | Phase | Status | Est |
|---|-------|--------|-----|
| 01 | [Fix games fields + migration](./phase-01-fix-games-fields-and-migration.md) | completed | 1.5h |
| 02 | [Rewrite 5-tier sort + H2H helper](./phase-02-sort-logic-and-h2h.md) | completed | 2h |
| 03 | [Align RankingQueryHelper cross-tournament](./phase-03-ranking-query-helper.md) | completed | 0.5h |
| 04 | [Admin UI manual override](./phase-04-admin-ui-override.md) | completed | 2.5h |
| 05 | [Comprehensive tiebreaker tests](./phase-05-tests.md) | completed | 1.5h |

## Key Files

**Modify:**
- `app/Services/Tournament/TournamentStandingService.php` — populate games_*, delegate sort to sorter
- `app/Services/Tournament/RankingQueryHelper.php` — cross-tournament sort align
- `app/Http/Controllers/Front/Tournament/TournamentRankingController.php` — add is_tied + override fields to response
- `app/Models/GroupStanding.php` — add `manual_rank_override` to fillable/casts
- `resources/views/home-yard/tournaments/tournament-rankings.blade.php` — tied row markup
- `public/assets/js/tournament-rankings.js` — wire new mixin (minimal change)
- `routes/web.php` — POST rank-overrides route

**Create:**
- `database/migrations/2026_04_15_000001_add_manual_rank_override_to_group_standings.php`
- `app/Services/Tournament/GroupRankingSorter.php` — H2H + 5-tier bucket sort
- `app/Http/Controllers/Front/Tournament/TournamentRankOverrideController.php` — override endpoint
- `public/assets/js/tournament-rankings-override-mixin.js` — Alpine mixin
- `tests/Feature/Tournament/TiebreakerRankingTest.php` — 7 test cases

## Constraints

- No mini-league for 3+ tied teams (skip H2H, go tier 4)
- No point system change (keep 3pts/win)
- No per-tournament config (YAGNI)
- All new files < 200 lines (sorter < 150, override controller ~80, mixin ~60)
- `TournamentStandingService` stays ~275 lines (deferred — pre-existing debt)
- Scope: group-level standings only; `recalculateTournamentAthleteStats` untouched
