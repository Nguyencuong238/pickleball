<?php

namespace App\Services;

use App\Models\League;
use App\Models\LeagueMatch;
use App\Models\LeagueRound;
use App\Models\LeagueTeamPlayer;
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

                    if ($league->competition_format === 'mlp') {
                        $this->generateMlpGames($match, $home, $away);
                    } else {
                        foreach ($matchFormat as $index => $gameType) {
                            $match->games()->create([
                                'game_number' => $index + 1,
                                'game_type' => $gameType,
                                'status' => 'pending',
                            ]);
                        }
                    }
                }

                // Xoay mang: co dinh vi tri 0, xoay phan con lai
                $last = array_pop($teamIds);
                array_splice($teamIds, 1, 0, [$last]);
            }
        });
    }

    /**
     * Validate MLP teams have min 4 active players each
     */
    public function validateMlpTeams(League $league): void
    {
        $teams = $league->teams()->active()->withCount([
            'players' => fn ($q) => $q->where('status', 'active'),
        ])->get();

        foreach ($teams as $team) {
            if ($team->players_count < 4) {
                throw new InvalidArgumentException("Doi '{$team->name}' can toi thieu 4 VDV cho format MLP.");
            }
        }
    }

    /**
     * Generate 6 MLP sub-games using C(4,2) pair combinations
     */
    private function generateMlpGames(LeagueMatch $match, int $homeTeamId, int $awayTeamId): void
    {
        $homePlayers = LeagueTeamPlayer::where('league_team_id', $homeTeamId)
            ->where('status', 'active')
            ->orderBy('order')->orderBy('id')
            ->take(4)->get();

        $awayPlayers = LeagueTeamPlayer::where('league_team_id', $awayTeamId)
            ->where('status', 'active')
            ->orderBy('order')->orderBy('id')
            ->take(4)->get();

        // C(4,2) = 6 pair combinations
        $pairs = [[0, 1], [0, 2], [0, 3], [1, 2], [1, 3], [2, 3]];

        foreach ($pairs as $index => $pair) {
            $match->games()->create([
                'game_number' => $index + 1,
                'game_type' => 'DOUBLES',
                'status' => 'pending',
                'home_player_1_id' => $homePlayers[$pair[0]]->id,
                'home_player_2_id' => $homePlayers[$pair[1]]->id,
                'away_player_1_id' => $awayPlayers[$pair[0]]->id,
                'away_player_2_id' => $awayPlayers[$pair[1]]->id,
            ]);
        }
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
