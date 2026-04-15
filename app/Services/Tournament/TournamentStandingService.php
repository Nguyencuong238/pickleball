<?php

namespace App\Services\Tournament;

use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\MatchModel;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TournamentStandingService
{
    public function __construct(
        private RankingQueryHelper $rankingQuery,
        private GroupRankingSorter $rankingSorter,
    ) {}

    /**
     * Idempotent: xây lại toàn bộ standings của một group từ bảng matches.
     * Fix root-cause bug: incremental update gây over-count khi update trùng.
     */
    public function recalculateGroupStandings(int $groupId): void
    {
        try {
            DB::transaction(function () use ($groupId) {
                // Reset mọi standing row của group về 0 (giữ lại row để partner dedup trong ranking view)
                GroupStanding::where('group_id', $groupId)->update([
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                    'win_rate' => 0,
                ]);

                $matches = MatchModel::where('group_id', $groupId)
                    ->where('status', 'completed')
                    ->whereNotNull('set_scores')
                    ->whereNotNull('athlete1_id')
                    ->whereNotNull('athlete2_id')
                    ->get();

                foreach ($matches as $match) {
                    [$setsWon1, $setsWon2] = $this->countSetsFromMatch($match);
                    [$gamesWon1, $gamesWon2] = $this->countGamesFromMatch($match);

                    // Bỏ qua match không có set valid (set_scores rỗng) → tránh cộng dummy row
                    if ($setsWon1 === 0 && $setsWon2 === 0) {
                        continue;
                    }

                    $standing1 = $this->getOrCreateStanding($groupId, $match->athlete1_id);
                    $standing2 = $this->getOrCreateStanding($groupId, $match->athlete2_id);

                    $this->applyMatchDeltas($standing1, $standing2, $setsWon1, $setsWon2, $gamesWon1, $gamesWon2);
                }

                $this->recalculateGroupRankings($groupId);
            });
        } catch (\Exception $e) {
            Log::error('Recalculate group standings error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Idempotent: xây lại stats của 1 TournamentAthlete từ mọi match completed liên quan.
     */
    public function recalculateTournamentAthleteStats(int $athleteId): void
    {
        try {
            $athlete = TournamentAthlete::find($athleteId);
            if (!$athlete) {
                return;
            }

            // Lấy mọi id "cùng cặp" với athlete (self + partner + ai link partner_id = self)
            // → để doubles partner stats cũng được rebuild chính xác.
            $pairIds = [$athleteId];
            if ($athlete->partner_id) {
                $pairIds[] = (int) $athlete->partner_id;
            }
            TournamentAthlete::where('partner_id', $athleteId)
                ->pluck('id')
                ->each(function ($id) use (&$pairIds) { $pairIds[] = (int) $id; });
            $pairIds = array_values(array_unique($pairIds));

            $matches = MatchModel::where('status', 'completed')
                ->whereNotNull('set_scores')
                ->where(function ($q) use ($pairIds) {
                    $q->whereIn('athlete1_id', $pairIds)
                      ->orWhereIn('athlete2_id', $pairIds);
                })
                ->get();

            $played = 0;
            $won = 0;
            $lost = 0;
            $setsWon = 0;
            $setsLost = 0;

            foreach ($matches as $match) {
                [$s1, $s2] = $this->countSetsFromMatch($match);
                if ($s1 === 0 && $s2 === 0) {
                    continue;
                }

                $isTeam1 = in_array((int) $match->athlete1_id, $pairIds, true);
                $selfSets = $isTeam1 ? $s1 : $s2;
                $oppSets = $isTeam1 ? $s2 : $s1;

                $played++;
                $setsWon += $selfSets;
                $setsLost += $oppSets;

                if ($selfSets > $oppSets) {
                    $won++;
                } elseif ($oppSets > $selfSets) {
                    $lost++;
                }
            }

            $athlete->matches_played = $played;
            $athlete->matches_won = $won;
            $athlete->matches_lost = $lost;
            $athlete->sets_won = $setsWon;
            $athlete->sets_lost = $setsLost;
            $athlete->save();
        } catch (\Exception $e) {
            Log::error('Recalculate tournament athlete stats error: ' . $e->getMessage());
            throw $e;
        }
    }

    // ---- Wrappers giữ API cũ (idempotent qua recompute) -------------------

    public function updateGroupStandingsWithSets(MatchModel $match, int $setsWon1, int $setsWon2): void
    {
        $this->recalculateGroupStandings($match->group_id);
    }

    public function updateGroupStandings(MatchModel $match): void
    {
        $this->recalculateGroupStandings($match->group_id);
    }

    public function updateTournamentAthleteStats(MatchModel $match, int $setsWon1, int $setsWon2): void
    {
        if (!$match->athlete1_id || !$match->athlete2_id) {
            return;
        }
        $this->recalculateTournamentAthleteStats((int) $match->athlete1_id);
        $this->recalculateTournamentAthleteStats((int) $match->athlete2_id);
    }

    // ---- Ranking + queries -----------------------------------------------

    public function recalculateGroupRankings(int $groupId): void
    {
        try {
            $standings = GroupStanding::where('group_id', $groupId)->get()->all();
            $sorted = $this->rankingSorter->sortStandings($standings, $groupId);

            foreach ($sorted as $index => $standing) {
                $standing->update([
                    'rank_position' => $index + 1,
                    'win_rate' => $standing->calculateWinRate(),
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

    public function getRankings(Tournament $tournament, ?int $categoryId, ?int $groupId, int $page = 1, int $perPage = 10): array
    {
        return $this->rankingQuery->getRankings($tournament, $categoryId, $groupId, $page, $perPage);
    }

    public function getAllTournamentsRankings(array $tournamentIds, ?string $categoryType, int $page, int $perPage): array
    {
        return $this->rankingQuery->getAllTournamentsRankings($tournamentIds, $categoryType, $page, $perPage);
    }

    // ---- Helpers ---------------------------------------------------------

    /**
     * Parse set_scores của 1 match → [setsWon1, setsWon2].
     * Hỗ trợ cả hai shape key: `athlete1_score`/`athlete2_score` và `athlete1`/`athlete2`.
     */
    private function countSetsFromMatch(MatchModel $match): array
    {
        $setScores = $match->set_scores ?? [];
        if (!is_array($setScores)) {
            return [0, 0];
        }

        $setsWon1 = 0;
        $setsWon2 = 0;
        foreach ($setScores as $set) {
            if (!is_array($set)) {
                continue;
            }
            $a1 = $set['athlete1_score'] ?? $set['athlete1'] ?? null;
            $a2 = $set['athlete2_score'] ?? $set['athlete2'] ?? null;
            if ($a1 === null || $a2 === null) {
                continue;
            }
            if ((int) $a1 > (int) $a2) {
                $setsWon1++;
            } elseif ((int) $a2 > (int) $a1) {
                $setsWon2++;
            }
        }
        return [$setsWon1, $setsWon2];
    }

    /**
     * Parse set_scores → [gamesWon1, gamesWon2] = tổng raw point scores mỗi bên qua mọi set.
     * Hỗ trợ cả hai shape key như countSetsFromMatch.
     */
    private function countGamesFromMatch(MatchModel $match): array
    {
        $setScores = $match->set_scores ?? [];
        if (!is_array($setScores)) {
            return [0, 0];
        }

        $games1 = 0;
        $games2 = 0;
        foreach ($setScores as $set) {
            if (!is_array($set)) {
                continue;
            }
            $a1 = $set['athlete1_score'] ?? $set['athlete1'] ?? null;
            $a2 = $set['athlete2_score'] ?? $set['athlete2'] ?? null;
            if ($a1 === null || $a2 === null) {
                continue;
            }
            $games1 += (int) $a1;
            $games2 += (int) $a2;
        }
        return [$games1, $games2];
    }

    /**
     * Apply 1 match kết quả vào 2 standing rows (đã reset từ trước khi replay).
     * Preserve hành vi cũ: 3 điểm/win, draw không điểm.
     */
    private function applyMatchDeltas(
        GroupStanding $s1,
        GroupStanding $s2,
        int $setsWon1,
        int $setsWon2,
        int $gamesWon1,
        int $gamesWon2
    ): void {
        $s1->matches_played += 1;
        $s2->matches_played += 1;
        $s1->sets_won += $setsWon1;
        $s1->sets_lost += $setsWon2;
        $s2->sets_won += $setsWon2;
        $s2->sets_lost += $setsWon1;

        if ($setsWon1 > $setsWon2) {
            $s1->matches_won += 1;
            $s1->points += 3;
            $s2->matches_lost += 1;
        } elseif ($setsWon2 > $setsWon1) {
            $s2->matches_won += 1;
            $s2->points += 3;
            $s1->matches_lost += 1;
        } else {
            $s1->matches_drawn += 1;
            $s2->matches_drawn += 1;
        }

        $s1->sets_differential = $s1->sets_won - $s1->sets_lost;
        $s2->sets_differential = $s2->sets_won - $s2->sets_lost;

        $s1->games_won += $gamesWon1;
        $s1->games_lost += $gamesWon2;
        $s1->games_differential = $s1->games_won - $s1->games_lost;
        $s2->games_won += $gamesWon2;
        $s2->games_lost += $gamesWon1;
        $s2->games_differential = $s2->games_won - $s2->games_lost;

        $s1->save();
        $s2->save();
    }

    private function getOrCreateStanding(int $groupId, int $athleteId): GroupStanding
    {
        return GroupStanding::firstOrCreate(
            ['group_id' => $groupId, 'athlete_id' => $athleteId],
            [
                'rank_position' => 0, 'matches_played' => 0, 'matches_won' => 0,
                'matches_lost' => 0, 'matches_drawn' => 0, 'points' => 0,
                'sets_won' => 0, 'sets_lost' => 0, 'sets_differential' => 0,
                'games_won' => 0, 'games_lost' => 0, 'games_differential' => 0,
            ]
        );
    }
}
