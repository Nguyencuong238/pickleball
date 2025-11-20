<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Stadium;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
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

        // Redirect based on role
        if ($user->hasRole('admin')) {
            return redirect('/admin/dashboard');
        } elseif ($user->hasRole('home_yard')) {
            return $this->homeYardDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    // Normal User Dashboard
    public function userDashboard()
    {
        $user = auth()->user();
        return view('user.dashboard', compact('user'));
    }

    // Home Yard User Dashboard
    public function homeYardDashboard()
    {
        $user = auth()->user();
        
        // Get courts for all stadiums owned by this user
        $courts = Court::whereHas('stadium', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get() ?? collect();
        
        // Get current tournament (latest tournament) with relationships
        $tournament = Tournament::where('user_id', $user->id)
            ->with('categories', 'rounds', 'groups')
            ->latest()
            ->first();
        
        // Get athletes registered for the current tournament
        $athletes = collect();
        $categories = collect();
        if ($tournament) {
            $athletes = TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('status', 'approved')
                ->with('user', 'category')
                ->get();
            
            // Get tournament categories (already eager loaded)
            $categories = $tournament->categories ?? collect();
        }
        
        return view('home-yard.dashboard', compact('user', 'courts', 'tournament', 'athletes', 'categories'));
    }
}
