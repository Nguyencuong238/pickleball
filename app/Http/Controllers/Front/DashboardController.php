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
    public function homeYardDashboard($tournament_id = null)
    {
        $user = auth()->user();
        
        // Get courts for all stadiums owned by this user
        $courts = Court::whereHas('stadium', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get() ?? collect();
        
        // Get tournament - use provided tournament_id if available, otherwise get latest
        $tournament = null;
        if ($tournament_id) {
            $tournament = Tournament::where('id', $tournament_id)
                ->where('user_id', $user->id)
                ->with(['categories', 'rounds', 'groups' => function ($query) {
                    $query->with('category', 'round');
                }])
                ->first();
        }
        
        // If no tournament found with the ID or no ID provided, get the latest
        if (!$tournament) {
            $tournament = Tournament::where('user_id', $user->id)
                ->with(['categories', 'rounds', 'groups' => function ($query) {
                    $query->with('category', 'round');
                }])
                ->latest()
                ->first();
        }
        
        // If still no tournament, redirect to tournaments page
        if (!$tournament) {
            return redirect()->route('homeyard.tournaments')->with('message', 'Vui lòng tạo giải đấu trước');
        }
        
        // Get athletes registered for the current tournament (all statuses for approval)
        $athletes = collect();
        $categories = collect();
        if ($tournament) {
            $athletes = TournamentAthlete::where('tournament_id', $tournament->id)
                ->with('user', 'category')
                ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Get tournament categories (already eager loaded)
            $categories = $tournament->categories ?? collect();
        }
        
        return view('home-yard.dashboard', compact('user', 'courts', 'tournament', 'athletes', 'categories'));
    }
}
