<?php

namespace App\Services\Tournament;

use App\Models\GroupStanding;
use App\Models\MatchModel;

/**
 * 5-tier tiebreaker sorter cho group standings.
 * Tier 1 points, tier 2 games_differential, tier 3 H2H (chỉ 2 đội),
 * tier 4 games_lost ASC, tier 5 manual_rank_override ASC NULLS LAST.
 */
class GroupRankingSorter
{
    /**
     * Sắp xếp standings của 1 group theo spec 5 tier.
     *
     * @param GroupStanding[] $standings
     * @return GroupStanding[]
     */
    public function sortStandings(array $standings, int $groupId): array
    {
        // Tier 1 + 2: initial sort
        usort($standings, function ($a, $b) {
            return ($b->points <=> $a->points)
                ?: ($b->games_differential <=> $a->games_differential);
        });

        // Bucket by (points, games_differential) để xác định nhóm tied
        $buckets = [];
        $bucketOrder = [];
        foreach ($standings as $s) {
            $key = $s->points . '|' . $s->games_differential;
            if (!isset($buckets[$key])) {
                $buckets[$key] = [];
                $bucketOrder[] = $key;
            }
            $buckets[$key][] = $s;
        }

        $final = [];
        foreach ($bucketOrder as $key) {
            foreach ($this->sortTieBucket($buckets[$key], $groupId) as $s) {
                $final[] = $s;
            }
        }
        return $final;
    }

    /**
     * @param GroupStanding[] $bucket
     * @return GroupStanding[]
     */
    private function sortTieBucket(array $bucket, int $groupId): array
    {
        if (count($bucket) === 1) {
            return $bucket;
        }

        // 2-team bucket → tier 3 H2H
        if (count($bucket) === 2) {
            $idA = (int) $bucket[0]->athlete_id;
            $idB = (int) $bucket[1]->athlete_id;
            $h2h = $this->resolveHeadToHeadBetweenTwo($groupId, $idA, $idB);
            if ($h2h !== 0) {
                return $h2h === -1 ? [$bucket[0], $bucket[1]] : [$bucket[1], $bucket[0]];
            }
            // H2H draw → fall through tier 4+5
        }

        // 3+ team bucket (hoặc H2H draw): tier 4 games_lost ASC, tier 5 override ASC NULLS LAST
        usort($bucket, function ($a, $b) {
            return ($a->games_lost <=> $b->games_lost)
                ?: (($a->manual_rank_override ?? PHP_INT_MAX) <=> ($b->manual_rank_override ?? PHP_INT_MAX));
        });
        return $bucket;
    }

    /**
     * Trả về danh sách athlete_id mà sorter KHÔNG resolve được sau cả 5 tier.
     * Dùng cho UI: chỉ những row này mới hiển thị input override + flag is_tied.
     *
     * @param GroupStanding[] $standings
     * @return int[]
     */
    public function getUnresolvedTiedAthleteIds(array $standings, int $groupId): array
    {
        $tied = [];

        $buckets = [];
        foreach ($standings as $s) {
            $key = $s->points . '|' . $s->games_differential;
            $buckets[$key][] = $s;
        }

        foreach ($buckets as $bucket) {
            if (count($bucket) === 1) {
                continue;
            }

            // Tier 3: 2-team H2H. Nếu non-draw → resolved.
            if (count($bucket) === 2) {
                $h2h = $this->resolveHeadToHeadBetweenTwo(
                    $groupId,
                    (int) $bucket[0]->athlete_id,
                    (int) $bucket[1]->athlete_id
                );
                if ($h2h !== 0) {
                    continue;
                }
            }

            // Tier 4 + 5: sub-bucket theo (games_lost, manual_rank_override).
            // Hai row cùng sub-bucket → vẫn tied sau cả 5 tier.
            $subBuckets = [];
            foreach ($bucket as $s) {
                $subKey = $s->games_lost . '|' . ($s->manual_rank_override ?? 'null');
                $subBuckets[$subKey][] = $s;
            }
            foreach ($subBuckets as $subBucket) {
                if (count($subBucket) > 1) {
                    foreach ($subBucket as $s) {
                        $tied[] = (int) $s->athlete_id;
                    }
                }
            }
        }

        return $tied;
    }

    /**
     * H2H giữa 2 athletes trong cùng group.
     * @return int -1 nếu A thắng, 1 nếu B thắng, 0 nếu hòa hoặc không có dữ liệu
     */
    private function resolveHeadToHeadBetweenTwo(int $groupId, int $athleteA, int $athleteB): int
    {
        $matches = MatchModel::where('group_id', $groupId)
            ->where('status', 'completed')
            ->whereNotNull('set_scores')
            ->where(function ($q) use ($athleteA, $athleteB) {
                $q->where(function ($q2) use ($athleteA, $athleteB) {
                    $q2->where('athlete1_id', $athleteA)->where('athlete2_id', $athleteB);
                })->orWhere(function ($q2) use ($athleteA, $athleteB) {
                    $q2->where('athlete1_id', $athleteB)->where('athlete2_id', $athleteA);
                });
            })
            ->get();

        $setsA = 0;
        $setsB = 0;
        foreach ($matches as $match) {
            [$s1, $s2] = $this->countSetsFromMatch($match);
            if ((int) $match->athlete1_id === $athleteA) {
                $setsA += $s1;
                $setsB += $s2;
            } else {
                $setsA += $s2;
                $setsB += $s1;
            }
        }

        if ($setsA > $setsB) return -1;
        if ($setsB > $setsA) return 1;
        return 0;
    }

    /**
     * Duplicate nhỏ của TournamentStandingService::countSetsFromMatch().
     * DRY chấp nhận tại đây vì caller duy nhất; nếu cần thêm caller, tách MatchScoreParser sau.
     */
    private function countSetsFromMatch(MatchModel $match): array
    {
        $setScores = $match->set_scores ?? [];
        if (!is_array($setScores)) {
            return [0, 0];
        }
        $w1 = 0;
        $w2 = 0;
        foreach ($setScores as $set) {
            if (!is_array($set)) {
                continue;
            }
            $a1 = $set['athlete1_score'] ?? $set['athlete1'] ?? null;
            $a2 = $set['athlete2_score'] ?? $set['athlete2'] ?? null;
            if ($a1 === null || $a2 === null) {
                continue;
            }
            if ((int) $a1 > (int) $a2) $w1++;
            elseif ((int) $a2 > (int) $a1) $w2++;
        }
        return [$w1, $w2];
    }
}
