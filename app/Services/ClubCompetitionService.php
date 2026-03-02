<?php

namespace App\Services;

use App\Models\ClubActivity;
use App\Models\ClubCompetitionMatch;
use App\Models\ClubCompetitionStanding;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ClubCompetitionService
{
    public const DEFAULT_CONFIG = [
        'points_for_win' => 3,
        'points_for_draw' => 1,
        'points_for_loss' => 0,
        'max_teams' => 16,
    ];

    /**
     * Dispatch to format-specific schedule generation
     */
    public function generateSchedule(ClubActivity $activity, string $format): void
    {
        match ($format) {
            'round_robin' => $this->generateRoundRobin($activity),
            'pool_play' => $this->generatePoolPlay($activity),
            'single_elimination' => $this->generateSingleElimination($activity),
            default => throw new InvalidArgumentException("Định dạng không hợp lệ: {$format}"),
        };
    }

    /**
     * Circle method round-robin (adapted from LeagueScheduleService)
     */
    public function generateRoundRobin(ClubActivity $activity): void
    {
        $teams = $activity->competitionTeams()->active()->get();

        if ($teams->count() < 2) {
            throw new InvalidArgumentException('Cần tối thiểu 2 đội để tạo lịch thi đấu.');
        }

        DB::transaction(function () use ($activity, $teams) {
            // Clear existing matches
            $activity->competitionMatches()->delete();

            $teamIds = $teams->pluck('id')->toArray();
            $hasBye = count($teamIds) % 2 !== 0;
            if ($hasBye) {
                $teamIds[] = null;
            }

            $numTeams = count($teamIds);
            $numRounds = $numTeams - 1;

            for ($round = 0; $round < $numRounds; $round++) {
                for ($i = 0; $i < $numTeams / 2; $i++) {
                    $home = $teamIds[$i];
                    $away = $teamIds[$numTeams - 1 - $i];

                    if ($home === null || $away === null) {
                        continue;
                    }

                    $activity->competitionMatches()->create([
                        'round_number' => $round + 1,
                        'home_team_id' => $home,
                        'away_team_id' => $away,
                        'status' => 'scheduled',
                    ]);
                }

                // Rotate: fix position 0, rotate rest
                $last = array_pop($teamIds);
                array_splice($teamIds, 1, 0, [$last]);
            }
        });
    }

    /**
     * Pool play: split teams into pools, round-robin per pool
     */
    public function generatePoolPlay(ClubActivity $activity): void
    {
        $teams = $activity->competitionTeams()->active()->get();

        if ($teams->count() < 4) {
            throw new InvalidArgumentException('Cần tối thiểu 4 đội cho pool play.');
        }

        DB::transaction(function () use ($activity, $teams) {
            $activity->competitionMatches()->delete();

            $teamIds = $teams->pluck('id')->toArray();
            shuffle($teamIds);

            // Split into pools of 3-4 teams
            $poolSize = $teams->count() <= 8 ? (int) ceil($teams->count() / 2) : 4;
            $pools = array_chunk($teamIds, $poolSize);
            $poolLabels = range('A', chr(64 + count($pools)));

            foreach ($pools as $poolIndex => $poolTeamIds) {
                $label = $poolLabels[$poolIndex];
                $this->generatePoolRoundRobin($activity, $poolTeamIds, $label);
            }
        });
    }

    /**
     * Round-robin within a single pool
     */
    private function generatePoolRoundRobin(
        ClubActivity $activity,
        array $teamIds,
        string $poolLabel
    ): void {
        if (count($teamIds) < 2) {
            return;
        }

        $hasBye = count($teamIds) % 2 !== 0;
        if ($hasBye) {
            $teamIds[] = null;
        }

        $numTeams = count($teamIds);
        $numRounds = $numTeams - 1;

        for ($round = 0; $round < $numRounds; $round++) {
            for ($i = 0; $i < $numTeams / 2; $i++) {
                $home = $teamIds[$i];
                $away = $teamIds[$numTeams - 1 - $i];

                if ($home === null || $away === null) {
                    continue;
                }

                $activity->competitionMatches()->create([
                    'round_number' => $round + 1,
                    'home_team_id' => $home,
                    'away_team_id' => $away,
                    'status' => 'scheduled',
                    'pool_label' => $poolLabel,
                ]);
            }

            $last = array_pop($teamIds);
            array_splice($teamIds, 1, 0, [$last]);
        }
    }

    /**
     * Single elimination bracket
     */
    public function generateSingleElimination(ClubActivity $activity): void
    {
        $teams = $activity->competitionTeams()->active()->get();

        if ($teams->count() < 2) {
            throw new InvalidArgumentException('Cần tối thiểu 2 đội để tạo bracket.');
        }

        DB::transaction(function () use ($activity, $teams) {
            $activity->competitionMatches()->delete();

            $teamIds = $teams->pluck('id')->toArray();
            shuffle($teamIds);

            // Pad to next power of 2 for byes
            $bracketSize = 1;
            while ($bracketSize < count($teamIds)) {
                $bracketSize *= 2;
            }

            // Fill byes with null
            while (count($teamIds) < $bracketSize) {
                $teamIds[] = null;
            }

            $round = 1;
            $matchIndex = 0;

            // First round: create matches, auto-complete byes
            for ($i = 0; $i < count($teamIds); $i += 2) {
                $home = $teamIds[$i];
                $away = $teamIds[$i + 1];
                $matchIndex++;

                if ($home === null && $away === null) {
                    continue;
                }

                // Bye: team auto-advances
                if ($home === null || $away === null) {
                    $winnerId = $home ?? $away;
                    $activity->competitionMatches()->create([
                        'round_number' => $round,
                        'home_team_id' => $winnerId,
                        'away_team_id' => null,
                        'status' => 'completed',
                        'winner_team_id' => $winnerId,
                        'bracket_position' => "R{$round}M{$matchIndex}",
                        'completed_at' => now(),
                    ]);
                    continue;
                }

                $activity->competitionMatches()->create([
                    'round_number' => $round,
                    'home_team_id' => $home,
                    'away_team_id' => $away,
                    'status' => 'scheduled',
                    'bracket_position' => "R{$round}M{$matchIndex}",
                ]);
            }

            // Subsequent rounds created lazily when matches complete
            // via advanceBracketWinner() called from saveMatchScore()
        });
    }

    /**
     * Initialize standings for all active teams
     */
    public function initializeStandings(ClubActivity $activity): void
    {
        $teams = $activity->competitionTeams()->active()->get();

        foreach ($teams as $team) {
            ClubCompetitionStanding::updateOrCreate(
                ['club_activity_id' => $activity->id, 'club_competition_team_id' => $team->id],
                ['played' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0, 'points' => 0, 'rank' => 0]
            );
        }
    }

    /**
     * Save match score and recalculate standings
     */
    public function saveMatchScore(ClubCompetitionMatch $match, int $homeScore, int $awayScore): void
    {
        if ($match->status === 'completed') {
            throw new InvalidArgumentException('Trận đấu đã kết thúc, không thể cập nhật điểm.');
        }

        DB::transaction(function () use ($match, $homeScore, $awayScore) {
            $winnerId = null;
            if ($homeScore > $awayScore) {
                $winnerId = $match->home_team_id;
            } elseif ($awayScore > $homeScore) {
                $winnerId = $match->away_team_id;
            }

            $match->update([
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'winner_team_id' => $winnerId,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $this->recalculateStandings($match->activity);
        });
    }

    /**
     * Recalculate all standings (reset -> iterate matches -> rank)
     */
    public function recalculateStandings(ClubActivity $activity): void
    {
        DB::transaction(function () use ($activity) {
            // Reset
            $activity->competitionStandings()->update([
                'played' => 0, 'wins' => 0, 'losses' => 0,
                'draws' => 0, 'points' => 0, 'rank' => 0,
            ]);

            $pointsForWin = $this->getConfigValue($activity, 'points_for_win');
            $pointsForLoss = $this->getConfigValue($activity, 'points_for_loss');

            $completedMatches = $activity->competitionMatches()
                ->where('status', 'completed')
                ->whereNotNull('away_team_id')
                ->get();

            $standingsMap = $activity->competitionStandings()->get()->keyBy('club_competition_team_id');

            foreach ($completedMatches as $match) {
                $homeStanding = $standingsMap->get($match->home_team_id);
                $awayStanding = $standingsMap->get($match->away_team_id);

                if (!$homeStanding || !$awayStanding) {
                    continue;
                }

                $homeStanding->played++;
                $awayStanding->played++;

                if ($match->winner_team_id === $match->home_team_id) {
                    $homeStanding->wins++;
                    $homeStanding->points += $pointsForWin;
                    $awayStanding->losses++;
                    $awayStanding->points += $pointsForLoss;
                } elseif ($match->winner_team_id === $match->away_team_id) {
                    $awayStanding->wins++;
                    $awayStanding->points += $pointsForWin;
                    $homeStanding->losses++;
                    $homeStanding->points += $pointsForLoss;
                } else {
                    $homeStanding->draws++;
                    $awayStanding->draws++;
                    $pointsForDraw = $this->getConfigValue($activity, 'points_for_draw');
                    $homeStanding->points += $pointsForDraw;
                    $awayStanding->points += $pointsForDraw;
                }
            }

            // Batch save
            foreach ($standingsMap as $standing) {
                $standing->save();
            }

            // Rank by points desc, wins desc
            $standings = $activity->competitionStandings()
                ->orderByDesc('points')
                ->orderByDesc('wins')
                ->get();

            foreach ($standings as $index => $standing) {
                $standing->update(['rank' => $index + 1]);
            }
        });
    }

    /**
     * Get config value with fallback to defaults
     */
    public function getConfigValue(ClubActivity $activity, string $key): mixed
    {
        return data_get($activity->competition_config, $key, self::DEFAULT_CONFIG[$key] ?? null);
    }
}
