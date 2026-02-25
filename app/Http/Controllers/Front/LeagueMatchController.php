<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\LeagueMatch;
use App\Models\LeagueMatchGame;
use App\Services\LeagueStandingsService;
use Illuminate\Http\Request;

class LeagueMatchController extends Controller
{
    public function __construct(private LeagueStandingsService $standingsService)
    {
        $this->middleware(['auth']);
    }

    public function index(League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        $league->load([
            'rounds' => fn ($q) => $q->orderBy('round_number'),
            'rounds.matches.homeTeam',
            'rounds.matches.awayTeam',
            'rounds.matches.games',
        ]);

        return view('home-yard.leagues.matches', compact('league'));
    }

    public function updateScore(Request $request, League $league, LeagueMatch $match)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($match->round->league_id !== $league->id, 404);

        $request->validate([
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
        ]);

        $this->standingsService->updateMatchResult(
            $match,
            $request->home_score,
            $request->away_score
        );

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật điểm trận đấu thành công.',
            'match' => $match->fresh(['homeTeam', 'awayTeam']),
        ]);
    }

    public function updateGameScore(Request $request, League $league, LeagueMatch $match, LeagueMatchGame $game)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($match->round->league_id !== $league->id, 404);
        abort_if($game->league_match_id !== $match->id, 404);

        $request->validate([
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
        ]);

        $this->standingsService->saveGameScore(
            $game,
            $request->home_score,
            $request->away_score
        );

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật điểm game thành công.',
            'game' => $game->fresh(),
        ]);
    }
}
