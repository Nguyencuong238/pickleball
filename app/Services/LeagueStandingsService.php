<?php

namespace App\Services;

use App\Models\League;
use App\Models\LeagueMatch;
use App\Models\LeagueMatchGame;
use App\Models\LeagueStanding;
use App\Models\LeagueTeam;
use Illuminate\Support\Facades\DB;

class LeagueStandingsService
{
    /**
     * Khoi tao standings cho tat ca doi active trong league
     */
    public function initializeStandings(League $league): void
    {
        $teams = $league->teams()->active()->get();

        foreach ($teams as $team) {
            LeagueStanding::updateOrCreate(
                ['league_id' => $league->id, 'league_team_id' => $team->id],
                ['played' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0, 'games_won' => 0, 'games_lost' => 0, 'points' => 0, 'rank' => 0]
            );
        }
    }

    /**
     * Luu diem game va tu dong xac dinh ket qua match khi tat ca games hoan thanh
     */
    public function saveGameScore(LeagueMatchGame $game, int $homeScore, int $awayScore): void
    {
        DB::transaction(function () use ($game, $homeScore, $awayScore) {
            $match = $game->match()->lockForUpdate()->first();

            // Cap nhat diem game
            $game->update([
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'status' => 'completed',
                'winner_team_id' => $homeScore > $awayScore
                    ? $match->home_team_id
                    : ($awayScore > $homeScore ? $match->away_team_id : null),
            ]);

            // Kiem tra tat ca games da hoan thanh chua
            $totalGames = $match->games()->count();
            $completedGames = $match->games()->where('status', 'completed')->count();

            if ($totalGames === $completedGames) {
                $this->determineMatchWinner($match);
            }
        });
    }

    /**
     * Xac dinh doi thang match dua tren ket qua cac games
     * MLP: total score across all games. Traditional: game count (best-of).
     */
    public function determineMatchWinner(LeagueMatch $match): ?LeagueTeam
    {
        $league = $match->round->league;

        if ($league->competition_format === 'mlp') {
            // MLP: winner = team with higher total score across all games
            $totalHome = $match->games()->sum('home_score');
            $totalAway = $match->games()->sum('away_score');

            $winnerId = null;
            if ($totalHome > $totalAway) {
                $winnerId = $match->home_team_id;
            } elseif ($totalAway > $totalHome) {
                $winnerId = $match->away_team_id;
            }

            $match->update([
                'home_score' => $totalHome,
                'away_score' => $totalAway,
                'winner_team_id' => $winnerId,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } else {
            // Traditional: winner = team with more games won
            $homeGamesWon = $match->games()->where('winner_team_id', $match->home_team_id)->count();
            $awayGamesWon = $match->games()->where('winner_team_id', $match->away_team_id)->count();

            $winnerId = null;
            if ($homeGamesWon > $awayGamesWon) {
                $winnerId = $match->home_team_id;
            } elseif ($awayGamesWon > $homeGamesWon) {
                $winnerId = $match->away_team_id;
            }

            $match->update([
                'home_score' => $homeGamesWon,
                'away_score' => $awayGamesWon,
                'winner_team_id' => $winnerId,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        $this->recalculateStandings($league);

        return $winnerId ? LeagueTeam::find($winnerId) : null;
    }

    /**
     * Nhap diem truc tiep o cap do match (khong qua game)
     */
    public function updateMatchResult(LeagueMatch $match, int $homeScore, int $awayScore): void
    {
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

            $league = $match->round->league;
            $this->recalculateStandings($league);
        });
    }

    /**
     * Tinh lai toan bo standings cho league
     * Reset ve 0 roi duyet qua tat ca completed matches
     */
    public function recalculateStandings(League $league): void
    {
        DB::transaction(function () use ($league) {
            // Reset tat ca standings
            $league->standings()->update([
                'played' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0,
                'games_won' => 0, 'games_lost' => 0, 'points' => 0, 'rank' => 0,
            ]);

            $pointsForWin = $league->getConfigValue('points_for_win', LeagueService::DEFAULT_CONFIG['points_for_win']);
            $pointsForLoss = $league->getConfigValue('points_for_loss', LeagueService::DEFAULT_CONFIG['points_for_loss']);

            // Lay tat ca matches da hoan thanh
            $completedMatches = LeagueMatch::whereHas('round', fn ($q) => $q->where('league_id', $league->id))
                ->where('status', 'completed')
                ->get();

            // Load standings vao map de tranh N+1 queries
            $standingsMap = $league->standings()->get()->keyBy('league_team_id');

            foreach ($completedMatches as $match) {
                $homeStanding = $standingsMap->get($match->home_team_id);
                $awayStanding = $standingsMap->get($match->away_team_id);

                if (!$homeStanding || !$awayStanding) {
                    continue;
                }

                // Tinh toan in-memory (da reset ve 0 truoc do)
                $homeStanding->played++;
                $awayStanding->played++;

                $homeStanding->games_won += $match->home_score;
                $homeStanding->games_lost += $match->away_score;
                $awayStanding->games_won += $match->away_score;
                $awayStanding->games_lost += $match->home_score;

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
                }
            }

            // Luu tat ca standings 1 lan
            foreach ($standingsMap as $standing) {
                $standing->save();
            }

            // Tinh rank: sap xep theo diem > hieu so game > game thang
            $standings = $league->standings()
                ->orderByDesc('points')
                ->orderByRaw('(CAST(games_won AS SIGNED) - CAST(games_lost AS SIGNED)) DESC')
                ->orderByDesc('games_won')
                ->get();

            foreach ($standings as $index => $standing) {
                $standing->update(['rank' => $index + 1]);
            }
        });
    }
}
