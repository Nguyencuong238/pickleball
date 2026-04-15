# Phase 02 — Rewrite 5-tier Sort + H2H Helper (extract `GroupRankingSorter`)

## Context Links
- Service: `app/Services/Tournament/TournamentStandingService.php` (lines 165-192 `recalculateGroupRankings`) — currently 281 lines, ALREADY over 200-line rule
- Model: `app/Models/MatchModel.php`, `app/Models/GroupStanding.php`
- Blocked by: Phase 01 (games_differential must be populated correctly first)

## Overview
- **Priority:** P1
- **Status:** pending
- **Description:** Extract ranking/H2H logic into new `GroupRankingSorter` class. Replace 3-field `usort` closure with bucket-based 5-tier sort. Tier 3 (H2H) only fires for 2-team tied buckets.

## Architecture Decision: Extract new class (committed)
`TournamentStandingService` is currently 281 lines (already over rule). Adding H2H helper + bucket sort (~60 lines) makes it worse. **Commit upfront:** create new file `app/Services/Tournament/GroupRankingSorter.php`. Service keeps `recalculateGroupRankings()` as thin delegator to sorter.

## Key Insights
- Current sort (lines 170-174): `points → matches_won → (games_won - games_lost)` — wrong on all 3 counts after tier 1.
- Bucket approach: group standings by `(points, games_differential)` into tie-buckets, then per-bucket apply H2H or fallback — avoids O(n²) DB queries on non-tied items.
- H2H: query `matches` table for `status=completed AND group_id=$gid AND ((athlete1_id=A AND athlete2_id=B) OR (athlete1_id=B AND athlete2_id=A))`. Sum set wins per side across all H2H matches (handles rare re-match scenario).
- 3+ tied bucket: skip H2H entirely, apply tier 4 (`games_lost ASC`) then tier 5 (`manual_rank_override ASC NULLS LAST`).
- `NULLS LAST` behavior for `manual_rank_override`: PHP `usort` — null should sort after int. Use: `($a->manual_rank_override ?? PHP_INT_MAX) <=> ($b->manual_rank_override ?? PHP_INT_MAX)`.
- `recalculateGroupRankings()` is already called inside the DB transaction of `recalculateGroupStandings()` — H2H query runs within same transaction, consistent read.

## Requirements
- `recalculateGroupRankings(int $groupId): void` — rewritten with bucket sort
- `resolveHeadToHeadBetweenTwo(int $groupId, int $athleteA, int $athleteB): int` — returns `-1` (A wins), `1` (B wins), `0` (draw/no data)
- Sort must be deterministic (no random ordering on equal tier 5)
- No external sort library — plain PHP `usort` + collection manipulation

## Architecture

```
recalculateGroupRankings($groupId)
  1. Load all standings for group
  2. Sort by tier 1+2 (points DESC, games_differential DESC) → initial order
  3. Identify tie-buckets (same points AND same games_differential)
  4. For each bucket:
     a. size == 1  → no tiebreak needed
     b. size == 2  → resolveHeadToHeadBetweenTwo() → sub-sort
     c. size >= 3  → sort by games_lost ASC, manual_rank_override ASC NULLS LAST
  5. Flatten buckets → assign rank_position sequentially
  6. Update rank_position + win_rate + is_advanced (existing logic)
```

```
resolveHeadToHeadBetweenTwo($groupId, $athleteA, $athleteB): int
  - Query matches (status=completed, group_id, pair A vs B)
  - Sum setsWonA, setsWonB across all H2H matches
  - Return: -1 if setsWonA > setsWonB, 1 if setsWonB > setsWonA, 0 if equal
```

## Related Code Files

**Create:**
- `app/Services/Tournament/GroupRankingSorter.php` — new class holding `resolveHeadToHeadBetweenTwo()`, `sortTieBucket()`, `sortStandings()` public entry

**Modify:**
- `app/Services/Tournament/TournamentStandingService.php` — inject `GroupRankingSorter` via constructor; `recalculateGroupRankings()` delegates sort to it; REMOVE inline closure

## Implementation Steps

1. **Create `app/Services/Tournament/GroupRankingSorter.php`** with public API:
   ```php
   namespace App\Services\Tournament;

   use App\Models\GroupStanding;
   use App\Models\MatchModel;

   class GroupRankingSorter
   {
       /**
        * Sort standings of a group by 5-tier spec and return ordered array.
        * @param GroupStanding[] $standings
        * @return GroupStanding[]
        */
       public function sortStandings(array $standings, int $groupId): array { /* bucket logic */ }

       private function sortTieBucket(array $bucket, int $groupId): array { /* ... */ }

       private function resolveHeadToHeadBetweenTwo(int $groupId, int $athleteA, int $athleteB): int { /* ... */ }

       private function countSetsFromMatch(MatchModel $match): array { /* copy from service — small, DRY acceptable here since single caller */ }
   }
   ```
   Keep file < 150 lines. Note: `countSetsFromMatch` is duplicated from service — acceptable since extraction would require yet another class. If both end up needing it long-term, future refactor can extract to a `MatchScoreParser`.

2. **Add `resolveHeadToHeadBetweenTwo()` body** inside the class:
   ```php
   private function resolveHeadToHeadBetweenTwo(int $groupId, int $athleteA, int $athleteB): int
   {
       $matches = MatchModel::where('group_id', $groupId)
           ->where('status', 'completed')
           ->whereNotNull('set_scores')
           ->where(function ($q) use ($athleteA, $athleteB) {
               $q->where(fn($q2) => $q2->where('athlete1_id', $athleteA)->where('athlete2_id', $athleteB))
                 ->orWhere(fn($q2) => $q2->where('athlete1_id', $athleteB)->where('athlete2_id', $athleteA));
           })
           ->get();

       $setsA = 0;
       $setsB = 0;
       foreach ($matches as $match) {
           [$s1, $s2] = $this->countSetsFromMatch($match);
           if ((int) $match->athlete1_id === $athleteA) {
               $setsA += $s1; $setsB += $s2;
           } else {
               $setsA += $s2; $setsB += $s1;
           }
       }
       if ($setsA > $setsB) return -1;
       if ($setsB > $setsA) return 1;
       return 0;
   }
   ```

3. **Add private `sortTieBucket()` method** to handle a single bucket of tied standings:
   ```php
   private function sortTieBucket(array $bucket, int $groupId): array
   {
       if (count($bucket) === 1) return $bucket;

       // 2-team bucket: apply H2H (tier 3)
       if (count($bucket) === 2) {
           $idA = $bucket[0]->athlete_id;
           $idB = $bucket[1]->athlete_id;
           $h2h = $this->resolveHeadToHeadBetweenTwo($groupId, $idA, $idB);
           if ($h2h !== 0) {
               return $h2h === -1 ? [$bucket[0], $bucket[1]] : [$bucket[1], $bucket[0]];
           }
           // H2H draw → fall through to tier 4+5
       }

       // 3+ team bucket (or H2H draw): tier 4 games_lost ASC, tier 5 manual_rank_override ASC NULLS LAST
       usort($bucket, function ($a, $b) {
           return ($a->games_lost <=> $b->games_lost)
               ?: (($a->manual_rank_override ?? PHP_INT_MAX) <=> ($b->manual_rank_override ?? PHP_INT_MAX));
       });
       return $bucket;
   }
   ```

4. **Implement `sortStandings()` entry point** in `GroupRankingSorter`:
   ```php
   public function sortStandings(array $standings, int $groupId): array
   {
       // Tier 1 + 2 initial sort
       usort($standings, function ($a, $b) {
           return ($b->points <=> $a->points)
               ?: ($b->games_differential <=> $a->games_differential);
       });

       // Bucket by (points, games_differential)
       $buckets = [];
       foreach ($standings as $s) {
           $key = $s->points . '|' . $s->games_differential;
           $buckets[$key][] = $s;
       }

       // Sort each bucket + flatten
       $final = [];
       foreach ($buckets as $bucket) {
           foreach ($this->sortTieBucket($bucket, $groupId) as $s) {
               $final[] = $s;
           }
       }
       return $final;
   }
   ```

5. **Update `TournamentStandingService` constructor** — add second dependency:
   ```php
   public function __construct(
       private RankingQueryHelper $rankingQuery,
       private GroupRankingSorter $rankingSorter,
   ) {}
   ```

6. **Rewrite `recalculateGroupRankings()`** — delegate to sorter, keep rank_position/is_advanced update loop:
   ```php
   public function recalculateGroupRankings(int $groupId): void
   {
       try {
           $standings = GroupStanding::where('group_id', $groupId)->get()->all();
           $sorted = $this->rankingSorter->sortStandings($standings, $groupId);

           foreach ($sorted as $index => $standing) {
               $standing->update([
                   'rank_position' => $index + 1,
                   'win_rate'      => $standing->calculateWinRate(),
               ]);
           }
           $advancingCount = Group::find($groupId)?->advancing_count ?? 1;
           foreach ($sorted as $index => $standing) {
               $standing->update(['is_advanced' => ($index + 1) <= $advancingCount]);
           }
       } catch (\Exception $e) {
           Log::error('Recalculate group rankings error: ' . $e->getMessage());
           throw $e;
       }
   }
   ```
   Net line change on service: `recalculateGroupRankings` goes from ~28 lines to ~22 lines. Service file ends near 275 lines (still over 200 but same as before — deferred cleanup).

7. **Verify `GroupRankingSorter` < 150 lines.** If exceeds, split `countSetsFromMatch` into a separate `MatchScoreParser` utility.

8. **Laravel auto-resolves `GroupRankingSorter`** via constructor injection — no service provider binding needed.

## Todo List
- [ ] Create `GroupRankingSorter.php` class skeleton
- [ ] Implement `resolveHeadToHeadBetweenTwo()` private method
- [ ] Implement `sortTieBucket()` private method
- [ ] Implement `sortStandings()` public entry point
- [ ] Inject `GroupRankingSorter` into `TournamentStandingService` constructor
- [ ] Rewrite `recalculateGroupRankings()` as thin delegator
- [ ] Verify `GroupRankingSorter` < 150 lines
- [ ] Smoke test: manually recalculate a group with tied points

## Success Criteria
- Group with 3 teams all on 3 pts: ranked by games_differential, NOT by games_won (which was wrong before)
- 2 teams tied on pts + games_diff: H2H result determines rank
- 3 teams tied on pts + games_diff: H2H skipped, games_lost used
- Teams with `manual_rank_override = null` sort after those with values
- No DB N+1: H2H query runs once per 2-team bucket (not per comparison)

## Risk Assessment
- **Bucket key collision**: key `points|games_differential` — safe since both are int, separator `|` unambiguous
- **H2H with 0 completed matches** (match not yet played): `resolveHeadToHeadBetweenTwo` returns `0` → falls to tier 4 safely
- **File size**: extraction to `GroupRankingSorter` committed upfront — no size risk
- **Transaction scope**: `recalculateGroupRankings()` no longer called only from `recalculateGroupStandings()` transaction — Phase 04 also calls it from `updateRankOverrides`. Sorter is read-only for sort logic; rank_position updates remain in service method. Wrap Phase 04 call in its own transaction (see Phase 04).

## Security Considerations
- H2H query scoped by `group_id` — no cross-group data leak
- No user input in sort logic
