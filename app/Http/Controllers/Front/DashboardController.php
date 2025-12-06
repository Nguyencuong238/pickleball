<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Stadium;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\MatchModel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        return view('user.dashboard', compact('user'));
    }
}
