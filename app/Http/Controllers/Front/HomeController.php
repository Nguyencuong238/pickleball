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

    public function tournaments(Request $request)
    {
        $query = Tournament::where('status', 'active');
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Status filter
        if ($request->has('statuses') && is_array($request->statuses) && count($request->statuses) > 0) {
            $query->where(function($q) use ($request) {
                foreach($request->statuses as $status) {
                    if ($status === 'open') {
                        $q->orWhere(function($subQ) {
                            $subQ->whereDate('start_date', '>', now());
                        });
                    } elseif ($status === 'coming_soon') {
                        $q->orWhere(function($subQ) {
                            $subQ->whereDate('start_date', '>', now()->addDays(30));
                        });
                    } elseif ($status === 'ongoing') {
                        $q->orWhere(function($subQ) {
                            $subQ->whereDate('start_date', '<=', now())
                                ->whereDate('end_date', '>=', now());
                        });
                    } elseif ($status === 'ended') {
                        $q->orWhere(function($subQ) {
                            $subQ->whereDate('end_date', '<', now());
                        });
                    }
                }
            });
        }
        
        // Location filter
        if ($request->has('location') && $request->location) {
            $query->where('location', $request->location);
        }
        
        // Tournament rank filter
        if ($request->has('ranks') && is_array($request->ranks) && count($request->ranks) > 0) {
            $query->whereIn('tournament_rank', $request->ranks);
        }
        
        // Date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }
        
        // Prizes filter
        if ($request->has('prize_range') && $request->prize_range) {
            if ($request->prize_range === 'low') {
                $query->where('prizes', '<', 100000000);
            } elseif ($request->prize_range === 'mid') {
                $query->whereBetween('prizes', [100000000, 300000000]);
            } elseif ($request->prize_range === 'high') {
                $query->where('prizes', '>', 300000000);
            }
        }
        
        // Default ordering
        $query->orderBy('start_date', 'asc');
        
        $tournaments = $query->paginate(12);
        
        // Calculate statistics
        $totalTournaments = Tournament::where('status', 'active')->count();
        
        // Calculate total athletes from all tournaments
        $allTournaments = Tournament::where('status', 'active')->get();
        $totalAthletes = 0;
        foreach($allTournaments as $tournament) {
            $totalAthletes += $tournament->athleteCount();
        }
        
        $totalPrizes = Tournament::where('status', 'active')->whereNotNull('prizes')->sum('prizes') ?? 0;
        $totalLocations = Tournament::where('status', 'active')->distinct('location')->count('location');
        
        // Status counts
        $statusOpen = Tournament::where('status', 'active')->whereDate('start_date', '>', now())->count();
        $statusEnded = Tournament::where('status', 'active')->whereDate('end_date', '<', now())->count();
        $statusOngoing = Tournament::where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->count();
        $statusComingSoon = Tournament::where('status', 'active')->whereDate('start_date', '>', now()->addDays(30))->count();
        
        // Get unique locations for filter dropdown
        $locations = Tournament::where('status', 'active')->distinct('location')->whereNotNull('location')->pluck('location');
        
        return view('front.tournaments', [
            'tournaments' => $tournaments,
            'totalTournaments' => $totalTournaments,
            'totalAthletes' => $totalAthletes,
            'totalPrizes' => $totalPrizes,
            'totalLocations' => $totalLocations,
            'statusOpen' => $statusOpen,
            'statusComingSoon' => $statusComingSoon,
            'statusOngoing' => $statusOngoing,
            'statusEnded' => $statusEnded,
            'locations' => $locations,
            'filters' => [
                'search' => $request->get('search', ''),
                'statuses' => $request->get('statuses', []),
                'location' => $request->get('location', ''),
                'ranks' => $request->get('ranks', []),
                'start_date' => $request->get('start_date', ''),
                'end_date' => $request->get('end_date', ''),
                'prize_range' => $request->get('prize_range', ''),
            ],
        ]);
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
