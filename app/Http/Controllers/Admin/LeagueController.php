<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\League;

class LeagueController extends Controller
{
    public function index()
    {
        $leagues = League::with('user')
            ->withCount('teams')
            ->latest()
            ->paginate(20);

        return view('admin.leagues.index', compact('leagues'));
    }

    public function show(League $league)
    {
        $league->load([
            'user',
            'teams.players.user',
            'rounds.matches.homeTeam',
            'rounds.matches.awayTeam',
            'standings.team',
        ]);

        return view('admin.leagues.show', compact('league'));
    }
}
