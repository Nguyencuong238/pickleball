<?php

namespace App\Services;

use App\Models\League;
use App\Models\LeagueRound;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeagueScheduleService
{
    /**
     * Tao lich thi dau round-robin cho league
     * Circle method: co dinh team[0], xoay cac team con lai
     */
    public function generateRoundRobin(League $league): void
    {
        $teams = $league->teams()->active()->get();

        if ($teams->count() < 2) {
            throw new InvalidArgumentException('Can toi thieu 2 doi de tao lich thi dau.');
        }

        DB::transaction(function () use ($league, $teams) {
            $teamIds = $teams->pluck('id')->toArray();
            $hasBye = count($teamIds) % 2 !== 0;

            // Neu so doi le, them null lam bye
            if ($hasBye) {
                $teamIds[] = null;
            }

            $numTeams = count($teamIds);
            $numRounds = $numTeams - 1;
            $matchFormat = $league->getConfigValue('match_format', LeagueService::DEFAULT_CONFIG['match_format']);

            for ($round = 0; $round < $numRounds; $round++) {
                $leagueRound = LeagueRound::create([
                    'league_id' => $league->id,
                    'round_number' => $round + 1,
                    'name' => 'Vong ' . ($round + 1),
                    'status' => 'pending',
                ]);

                // Ghep cap: team[i] vs team[numTeams - 1 - i]
                for ($i = 0; $i < $numTeams / 2; $i++) {
                    $home = $teamIds[$i];
                    $away = $teamIds[$numTeams - 1 - $i];

                    // Bo qua neu mot trong hai la bye (null)
                    if ($home === null || $away === null) {
                        continue;
                    }

                    $match = $leagueRound->matches()->create([
                        'home_team_id' => $home,
                        'away_team_id' => $away,
                        'status' => 'scheduled',
                    ]);

                    // Tao game entries theo match_format
                    foreach ($matchFormat as $index => $gameType) {
                        $match->games()->create([
                            'game_number' => $index + 1,
                            'game_type' => $gameType,
                            'status' => 'pending',
                        ]);
                    }
                }

                // Xoay mang: co dinh vi tri 0, xoay phan con lai
                $last = array_pop($teamIds);
                array_splice($teamIds, 1, 0, [$last]);
            }
        });
    }

    /**
     * Xoa toan bo lich thi dau (chi khi draft/registration)
     */
    public function clearSchedule(League $league): void
    {
        if (!in_array($league->status, ['draft', 'registration'])) {
            throw new InvalidArgumentException('Chi co the xoa lich khi league o trang thai draft hoac registration.');
        }

        // FK cascade se xoa matches va games
        $league->rounds()->delete();
    }

    /**
     * Lay lich thi dau dang matrix voi eager loading
     */
    public function getScheduleMatrix(League $league): array
    {
        $rounds = $league->rounds()
            ->with(['matches.homeTeam', 'matches.awayTeam', 'matches.winnerTeam', 'matches.games'])
            ->orderBy('round_number')
            ->get();

        return $rounds->toArray();
    }
}
