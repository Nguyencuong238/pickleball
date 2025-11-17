<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Stadium;
use App\Models\Tournament;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index()
    {
        // Estadiums (Featured courts)
        $featuredStadiums = Stadium::where('status', 'active')
            ->where('is_featured', true)
            ->limit(3)
            ->get();
        
        // News (latest)
        $latestNews = News::orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        
        // Tournaments (upcoming)
        $upcomingTournaments = Tournament::query()
        // ->where('status', 'active')
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->limit(6)
            ->get();
        
        // Statistics
        $totalStadiums = Stadium::where('status', 'active')->count();
        $totalCourts = Stadium::where('status', 'active')->sum('courts_count');
        
        return view('front.home', [
            'featuredStadiums' => $featuredStadiums,
            'latestNews' => $latestNews,
            'upcomingTournaments' => $upcomingTournaments,
            'totalStadiums' => $totalStadiums,
            'totalCourts' => $totalCourts,
        ]);
    }

    public function booking()
    {
        return view('front.booking');
    }
    
    public function courts()
    {
        $stadiums = Stadium::where('status', 'active')->paginate(10);
        $totalStadiums = Stadium::where('status', 'active')->count();
        $totalCourts = Stadium::where('status', 'active')->sum('courts_count');
        
        return view('front.courts', [
            'stadiums' => $stadiums,
            'totalStadiums' => $totalStadiums,
            'totalCourts' => $totalCourts,
        ]);
    }

    public function courtsDetail($court_id)
    {
        $stadium = Stadium::findOrFail($court_id);
        
        return view('front.courts.courts_detail', [
            'stadium' => $stadium,
        ]);
    }

    public function tournaments()
    {
        return view('front.tournaments');
    }
    
    public function tournamentsDetail()
    {
        return view('front.tournaments.tournaments_detail');
    }
    
    public function social()
    {
        return view('front.social_play');
    }

    public function news()
    {
        $news = News::paginate(6);
        return view('front.news', ['news' => $news]);
    }
}
