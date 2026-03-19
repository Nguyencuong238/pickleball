<?php

namespace App\Services\Tournament;

use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\MatchModel;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use Illuminate\Support\Facades\Log;

class TournamentStandingService
{
    public function __construct(
        private RankingQueryHelper $rankingQuery
    ) {}

    public function updateGroupStandingsWithSets(MatchModel $match, int $setsWon1, int $setsWon2): void
    {
        try {
            $groupId = $match->group_id;
            $standing1 = $this->getOrCreateStanding($groupId, $match->athlete1_id);
            $standing2 = $this->getOrCreateStanding($groupId, $match->athlete2_id);

            if ($setsWon1 > $setsWon2) {
                $standing1->updateAfterMatch(true, $setsWon1, $setsWon2, 0, 0);
                $standing2->updateAfterMatch(false, $setsWon2, $setsWon1, 0, 0);
            } elseif ($setsWon2 > $setsWon1) {
                $standing1->updateAfterMatch(false, $setsWon1, $setsWon2, 0, 0);
                $standing2->updateAfterMatch(true, $setsWon2, $setsWon1, 0, 0);
            } else {
                $standing1->update([
                    'matches_played' => $standing1->matches_played + 1,
                    'matches_drawn' => $standing1->matches_drawn + 1,
                    'sets_won' => $standing1->sets_won + $setsWon1,
                    'sets_lost' => $standing1->sets_lost + $setsWon2,
                ]);
                $standing2->update([
                    'matches_played' => $standing2->matches_played + 1,
                    'matches_drawn' => $standing2->matches_drawn + 1,
                    'sets_won' => $standing2->sets_won + $setsWon2,
                    'sets_lost' => $standing2->sets_lost + $setsWon1,
                ]);
            }

            $this->recalculateGroupRankings($groupId);
        } catch (\Exception $e) {
            Log::error('Update group standings (sets) error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateGroupStandings(MatchModel $match): void
    {
        try {
            $groupId = $match->group_id;
            $score1 = $match->athlete1_score;
            $score2 = $match->athlete2_score;

            $standing1 = $this->getOrCreateStanding($groupId, $match->athlete1_id);
            $standing2 = $this->getOrCreateStanding($groupId, $match->athlete2_id);

            if ($score1 > $score2) {
                $standing1->updateAfterMatch(true, 0, 0, $score1, $score2);
                $standing2->updateAfterMatch(false, 0, 0, $score2, $score1);
            } elseif ($score2 > $score1) {
                $standing1->updateAfterMatch(false, 0, 0, $score1, $score2);
                $standing2->updateAfterMatch(true, 0, 0, $score2, $score1);
            } else {
                $standing1->update([
                    'matches_played' => $standing1->matches_played + 1,
                    'matches_drawn' => $standing1->matches_drawn + 1,
                    'games_won' => $standing1->games_won + $score1,
                    'games_lost' => $standing1->games_lost + $score2,
                ]);
                $standing2->update([
                    'matches_played' => $standing2->matches_played + 1,
                    'matches_drawn' => $standing2->matches_drawn + 1,
                    'games_won' => $standing2->games_won + $score2,
                    'games_lost' => $standing2->games_lost + $score1,
                ]);
            }

            $this->recalculateGroupRankings($groupId);
        } catch (\Exception $e) {
            Log::error('Update group standings error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateTournamentAthleteStats(MatchModel $match, int $setsWon1, int $setsWon2): void
    {
        try {
            if (!$match->athlete1_id || !$match->athlete2_id) {
                return;
            }

            $athlete1 = TournamentAthlete::find($match->athlete1_id);
            $athlete2 = TournamentAthlete::find($match->athlete2_id);

            if (!$athlete1 || !$athlete2) {
                return;
            }

            $athlete1Wins = $setsWon1 > $setsWon2;
            $athlete2Wins = $setsWon2 > $setsWon1;

            $athlete1->matches_played = ($athlete1->matches_played ?? 0) + 1;
            $athlete1->sets_won = ($athlete1->sets_won ?? 0) + $setsWon1;
            $athlete1->sets_lost = ($athlete1->sets_lost ?? 0) + $setsWon2;
            if ($athlete1Wins) {
                $athlete1->matches_won = ($athlete1->matches_won ?? 0) + 1;
            } elseif ($athlete2Wins) {
                $athlete1->matches_lost = ($athlete1->matches_lost ?? 0) + 1;
            }
            $athlete1->save();

            $athlete2->matches_played = ($athlete2->matches_played ?? 0) + 1;
            $athlete2->sets_won = ($athlete2->sets_won ?? 0) + $setsWon2;
            $athlete2->sets_lost = ($athlete2->sets_lost ?? 0) + $setsWon1;
            if ($athlete2Wins) {
                $athlete2->matches_won = ($athlete2->matches_won ?? 0) + 1;
            } elseif ($athlete1Wins) {
                $athlete2->matches_lost = ($athlete2->matches_lost ?? 0) + 1;
            }
            $athlete2->save();
        } catch (\Exception $e) {
            Log::error('Update tournament athlete stats error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function recalculateGroupRankings(int $groupId): void
    {
        try {
            $standings = GroupStanding::where('group_id', $groupId)
                ->get()
                ->sort(function ($a, $b) {
                    return ($b->points <=> $a->points)
                        ?: ($b->matches_won <=> $a->matches_won)
                        ?: (($b->games_won - $b->games_lost) <=> ($a->games_won - $a->games_lost));
                })
                ->values();

            foreach ($standings as $index => $standing) {
                $standing->update([
                    'rank_position' => $index + 1,
                    'win_rate' => $standing->calculateWinRate(),
                ]);
            }

            $advancingCount = Group::find($groupId)?->advancing_count ?? 1;
            foreach ($standings as $index => $standing) {
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
