<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
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
        return view('home-yard.dashboard', compact('user'));
    }
}
