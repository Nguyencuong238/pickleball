<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\League;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeagueApiController extends Controller
{
    /**
     * Danh sách league của user đã đăng nhập (authenticated)
     */
    public function index(Request $request): JsonResponse
    {
        $leagues = League::where('user_id', $request->user()->id)
            ->withCount('teams')
            ->latest()
            ->paginate(20);

        return response()->json($leagues);
    }

    /**
     * Chi tiết league (public)
     */
    public function show(League $league): JsonResponse
    {
        $league->loadCount('teams');

        return response()->json([
            'id' => $league->id,
            'name' => $league->name,
            'slug' => $league->slug,
            'description' => $league->description,
            'season_name' => $league->season_name,
            'status' => $league->status,
            'config' => $league->config,
            'teams_count' => $league->teams_count,
            'start_date' => $league->start_date?->toDateString(),
            'end_date' => $league->end_date?->toDateString(),
        ]);
    }

    /**
     * Bảng xếp hạng (public)
     */
    public function standings(League $league): JsonResponse
    {
        $standings = $league->standings()
            ->with('team:id,name,logo')
            ->orderBy('rank')
            ->get()
            ->map(fn ($s) => [
                'rank' => $s->rank,
                'team' => $s->team?->name,
                'team_logo' => $s->team?->logo,
                'played' => $s->played,
                'wins' => $s->wins,
                'losses' => $s->losses,
                'draws' => $s->draws,
                'games_won' => $s->games_won,
                'games_lost' => $s->games_lost,
                'points' => $s->points,
            ]);

        return response()->json($standings);
    }

    /**
     * Lịch thi đấu (public)
     */
    public function schedule(League $league): JsonResponse
    {
        $rounds = $league->rounds()
            ->with([
                'matches.homeTeam:id,name,logo',
                'matches.awayTeam:id,name,logo',
            ])
            ->orderBy('round_number')
            ->get()
            ->map(fn ($round) => [
                'round_number' => $round->round_number,
                'name' => $round->name,
                'status' => $round->status,
                'matches' => $round->matches->map(fn ($m) => [
                    'id' => $m->id,
                    'home_team' => $m->homeTeam?->name,
                    'away_team' => $m->awayTeam?->name,
                    'home_score' => $m->home_score,
                    'away_score' => $m->away_score,
                    'status' => $m->status,
                ]),
            ]);

        return response()->json($rounds);
    }
}
