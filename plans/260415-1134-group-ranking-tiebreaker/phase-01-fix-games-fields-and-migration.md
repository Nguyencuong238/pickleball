# Phase 01 — Fix games_won/games_lost Population + Migration

## Context Links
- Service: `app/Services/Tournament/TournamentStandingService.php` (lines 241-267 `applyMatchDeltas`, line 210-235 `countSetsFromMatch`)
- Model: `app/Models/GroupStanding.php`
- Migration: `database/migrations/2025_11_19_000007_create_group_standings_table.php`
- Tests: `tests/Feature/Tournament/RankingsIdempotencyTest.php`

## Overview
- **Priority:** P1 (blocker — all tiers 2-5 depend on correct data)
- **Status:** pending
- **Description:** `games_won`/`games_lost` are reset to 0 but never re-populated in `applyMatchDeltas`. Add `countGamesFromMatch()` helper, wire it into replay loop, add `manual_rank_override` migration column.

## Key Insights
- `countSetsFromMatch()` already handles both key shapes (`athlete1_score`/`athlete1`). New `countGamesFromMatch()` must mirror same dual-key logic but sum raw int scores instead of counting set wins.
- `sets_differential` is correctly computed (lines 263-264) — model for how `games_differential` should work.
- `TournamentRankingController::buildCategoryRankingsResponse()` currently recomputes game scores from match JSON on-the-fly (lines 100-116) because DB fields are 0. After this fix, controller can rely on DB fields directly — but this is a separate refactor (YAGNI: leave controller as-is for now, both paths will be consistent).
- `manual_rank_override`: nullable int, no unique constraint needed (same value allowed across different groups).

## Out of Scope (explicit)
- **`recalculateTournamentAthleteStats()` (lines 76-140) stays UNCHANGED.** `TournamentAthlete` model only has `matches_*` and `sets_*` fields, NO `games_*` fields. Do NOT add games accumulation to this method. Tiebreaker spec applies to `GroupStanding` (group-level), not `TournamentAthlete` (athlete-level).
- Refactoring `TournamentRankingController::buildCategoryRankingsResponse()` on-the-fly game score computation — leave as-is for now, Phase 04 will touch this file anyway.

## Requirements
- `countGamesFromMatch(MatchModel $match): array` — returns `[int $gamesWon1, int $gamesWon2]` (total raw points per side across all sets)
- `applyMatchDeltas()` must call `countGamesFromMatch()` and accumulate into `games_won`, `games_lost`, `games_differential`
- Reset block (lines 37-40) already resets these fields to 0 — no change needed there
- New migration: add `manual_rank_override` nullable int to `group_standings`
- `GroupStanding` model: add `manual_rank_override` to `$fillable` and `$casts`

## Architecture
```
recalculateGroupStandings()
  └── foreach completed match
        ├── countSetsFromMatch()   → [setsWon1, setsWon2]
        ├── countGamesFromMatch()  → [gamesWon1, gamesWon2]  ← NEW
        └── applyMatchDeltas($s1, $s2, sets, games)          ← UPDATED signature
```

## Related Code Files

**Modify:**
- `app/Services/Tournament/TournamentStandingService.php`
- `app/Models/GroupStanding.php`

**Create:**
- `database/migrations/2026_04_15_000001_add_manual_rank_override_to_group_standings.php`

## Implementation Steps

1. **Add `countGamesFromMatch()` private method** in `TournamentStandingService` after `countSetsFromMatch()`:
   ```php
   private function countGamesFromMatch(MatchModel $match): array
   {
       $setScores = $match->set_scores ?? [];
       if (!is_array($setScores)) {
           return [0, 0];
       }
       $games1 = 0;
       $games2 = 0;
       foreach ($setScores as $set) {
           if (!is_array($set)) continue;
           $a1 = $set['athlete1_score'] ?? $set['athlete1'] ?? null;
           $a2 = $set['athlete2_score'] ?? $set['athlete2'] ?? null;
           if ($a1 === null || $a2 === null) continue;
           $games1 += (int) $a1;
           $games2 += (int) $a2;
       }
       return [$games1, $games2];
   }
   ```

2. **Update `recalculateGroupStandings()` loop** to fetch both counts and pass to `applyMatchDeltas`:
   ```php
   [$setsWon1, $setsWon2] = $this->countSetsFromMatch($match);
   [$gamesWon1, $gamesWon2] = $this->countGamesFromMatch($match);
   if ($setsWon1 === 0 && $setsWon2 === 0) { continue; }
   // ...
   $this->applyMatchDeltas($standing1, $standing2, $setsWon1, $setsWon2, $gamesWon1, $gamesWon2);
   ```

3. **Update `applyMatchDeltas()` signature and body** — add `int $gamesWon1, int $gamesWon2` params, accumulate:
   ```php
   private function applyMatchDeltas(
       GroupStanding $s1, GroupStanding $s2,
       int $setsWon1, int $setsWon2,
       int $gamesWon1, int $gamesWon2
   ): void {
       // ... existing sets/points/matches logic unchanged ...
       $s1->games_won += $gamesWon1;
       $s1->games_lost += $gamesWon2;
       $s1->games_differential = $s1->games_won - $s1->games_lost;
       $s2->games_won += $gamesWon2;
       $s2->games_lost += $gamesWon1;
       $s2->games_differential = $s2->games_won - $s2->games_lost;
       $s1->save();
       $s2->save();
   }
   ```

4. **Create migration** `2026_04_15_000001_add_manual_rank_override_to_group_standings.php`:
   ```php
   Schema::table('group_standings', function (Blueprint $table) {
       $table->integer('manual_rank_override')->nullable()->after('is_advanced');
   });
   ```
   Down: `$table->dropColumn('manual_rank_override');`

5. **Update `GroupStanding` model** — add to `$fillable`: `'manual_rank_override'`; add to `$casts`: `'manual_rank_override' => 'integer'`.

6. **Run migration** and verify: `php artisan migrate`

7. **Remove stale comment** on line 37: `// games_won/games_lost giữ nguyên 0 (xem plan phase-01 note)`

## Todo List
- [ ] Add `countGamesFromMatch()` private method
- [ ] Update `recalculateGroupStandings()` to call it
- [ ] Update `applyMatchDeltas()` signature + games accumulation
- [ ] Remove stale comment line 37
- [ ] Create migration for `manual_rank_override`
- [ ] Update `GroupStanding` fillable + casts
- [ ] Run `php artisan migrate`

## Success Criteria
- After `recalculateGroupStandings()`, `games_won`/`games_lost`/`games_differential` reflect actual raw point scores
- Match with set_scores `[{athlete1:11, athlete2:5}, {athlete1:8, athlete2:11}]` → athlete1: games_won=19, games_lost=16
- `manual_rank_override` column exists, nullable, no constraint violation on existing data
- Existing idempotency tests still pass

## Risk Assessment
- **`applyMatchDeltas` signature change**: only called internally in 1 place — low risk
- **Existing matches with `athlete1_score` key shape**: `countGamesFromMatch` handles dual-key same as `countSetsFromMatch` — no data loss

## Security Considerations
- Migration is additive (nullable column) — safe rollback with `dropColumn`
- No auth/input concerns in this phase
