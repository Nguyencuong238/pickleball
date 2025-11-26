<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Court;
use App\Models\News;
use App\Models\Social;
use App\Models\Stadium;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $totalTournaments = Tournament::where('status', 'active')->count();

        $totalMembers = User::whereHas('roles', function ($query) {
            $query->where('name', '<>', 'admin');
        })->count();

        $totalSocial = Social::count();

        return view('front.home', [
            'featuredStadiums' => $featuredStadiums,
            'latestNews' => $latestNews,
            'upcomingTournaments' => $upcomingTournaments,
            'totalStadiums' => $totalStadiums,
            'totalTournaments' => $totalTournaments,
            'totalMembers' => $totalMembers,
            'totalSocial' => $totalSocial,
        ]);
    }

    public function booking()
    {
        $courts = Court::where('is_active', true)->get();
        return view('front.booking', compact('courts'));
    }

    public function courts(Request $request)
    {
        $query = Stadium::where('status', 'active');

        // Search functionality - tìm kiếm theo tên hoặc địa chỉ
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Price filter - lọc theo giá
        if ($request->filled('price_min')) {
            $priceMin = (int) $request->input('price_min');
            $query->where('price_per_hour', '>=', $priceMin * 1000);
        }

        if ($request->filled('price_max')) {
            $priceMax = (int) $request->input('price_max');
            $query->where('price_per_hour', '<=', $priceMax * 1000);
        }

        // Location filter - lọc theo địa điểm
        if ($request->filled('location')) {
            $location = $request->input('location');
            $query->where('address', 'like', "%{$location}%");
        }

        // Courts count filter - lọc theo số sân
        if ($request->filled('courts_range')) {
            $range = $request->input('courts_range');
            if ($range === '1-3') {
                $query->whereBetween('courts_count', [1, 3]);
            } elseif ($range === '4-6') {
                $query->whereBetween('courts_count', [4, 6]);
            } elseif ($range === '7+') {
                $query->where('courts_count', '>=', 7);
            }
        }

        // Rating filter - lọc theo đánh giá
        if ($request->filled('rating')) {
            $rating = (float) $request->input('rating');
            $query->where('rating', '>=', $rating);
        }

        // Get total stadiums and courts before pagination
        $totalStadiums = $query->count();
        $totalCourts = Court::whereHas('stadium', function ($q) {
            $q->where('status', 'active');
        })->count();

        // Get unique locations for filter dropdown
        $locations = Stadium::where('status', 'active')
            ->distinct()
            ->pluck('address')
            ->map(function ($address) {
                // Extract city/province from address
                $parts = explode(',', $address);
                return trim(end($parts)); // Get last part (usually province)
            })
            ->unique()
            ->sort()
            ->values();

        // Paginate results
        $stadiums = $query->paginate(10)->appends($request->query());

        // Load user's favorites
        $userFavorites = [];
        if (auth()->check()) {
            $userFavorites = auth()->user()->favoriteStadiums()
                ->pluck('stadium_id')
                ->toArray();
        }

        return view('front.courts', [
            'stadiums' => $stadiums,
            'totalStadiums' => $totalStadiums,
            'totalCourts' => $totalCourts,
            'locations' => $locations,
            'userFavorites' => $userFavorites,
            'filters' => [
                'search' => $request->input('search'),
                'price_min' => $request->input('price_min'),
                'price_max' => $request->input('price_max'),
                'location' => $request->input('location'),
                'courts_range' => $request->input('courts_range'),
                'rating' => $request->input('rating'),
            ],
        ]);
    }

    public function courtsDetail($court_id)
    {
        $stadium = Stadium::findOrFail($court_id);

        $relatedStadiums = Stadium::where('status', 'active')
            ->where('id', '!=', $stadium->id)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return view('front.courts.courts_detail', [
            'stadium' => $stadium,
            'relatedStadiums' => $relatedStadiums,
        ]);
    }

    public function tournaments(Request $request)
    {
        // Only show active tournaments (status = 1)
        $query = Tournament::where('status', 1);

        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Status filter (based on dates, not status field)
        if ($request->has('statuses') && is_array($request->statuses) && count($request->statuses) > 0) {
            $query->where(function ($q) use ($request) {
                foreach ($request->statuses as $status) {
                    if ($status === 'open') {
                        // Đang mở đăng ký: registration_deadline > now
                        $q->orWhere(function ($subQ) {
                            $subQ->where('registration_deadline', '>', now());
                        });
                    } elseif ($status === 'coming_soon') {
                        // Sắp mở: start_date > now + 30 days
                        $q->orWhere(function ($subQ) {
                            $subQ->whereDate('start_date', '>', now());
                        });
                    } elseif ($status === 'ongoing') {
                        // Đang diễn ra: start_date <= now AND end_date >= now
                        $q->orWhere(function ($subQ) {
                            $subQ->whereDate('start_date', '<=', now())
                                ->whereDate('end_date', '>=', now());
                        });
                    } elseif ($status === 'ended') {
                        // Đã kết thúc: end_date < now
                        $q->orWhere(function ($subQ) {
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

        $tournaments = $query->paginate(6);
        
        // Add registration status for each tournament
        if (auth()->check()) {
            $tournaments->getCollection()->transform(function($tournament) {
                $tournament->user_registered = DB::table('tournament_athletes')
                    ->where('tournament_id', $tournament->id)
                    ->where('user_id', auth()->id())
                    ->exists();
                return $tournament;
            });
        } else {
            $tournaments->getCollection()->transform(function($tournament) {
                $tournament->user_registered = false;
                return $tournament;
            });
        }
        
        // Calculate statistics (based on dates, only active tournaments)
        $now = now();
        $activeTournaments = Tournament::where('status', 1);
        $totalTournaments = $activeTournaments->count();

        // Calculate total athletes from all tournaments
        $allTournaments = $activeTournaments->get();
        $totalAthletes = 0;
        foreach ($allTournaments as $tournament) {
            $totalAthletes += $tournament->athleteCount();
        }

        $totalPrizes = $activeTournaments->whereNotNull('prizes')->sum('prizes') ?? 0;
        $totalLocations = $activeTournaments->distinct('location')->count('location');

        // Status counts (based on dates, only active tournaments)
        $statusOpen = Tournament::where('status', 1)->where('registration_deadline', '>', $now)->count();
        $statusEnded = Tournament::where('status', 1)->whereDate('end_date', '<', $now)->count();
        $statusOngoing = Tournament::where('status', 1)->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->count();
        $statusComingSoon = Tournament::where('status', 1)->whereDate('start_date', '>', $now)->count();

        // Get unique locations for filter dropdown
        $locations = Tournament::where('status', 1)->distinct('location')->whereNotNull('location')->pluck('location');

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
    
    public function tournamentsDetail($tournament_id)
    {
        $tournament = Tournament::findOrFail($tournament_id);

        $registered = DB::table('tournament_athletes')
        ->where('tournament_id', $tournament->id)
        ->where('user_id', auth()->id())
        ->exists();

        // Load categories for this tournament
        $tournament->load('categories');
        
        return view('front.tournaments.tournaments_detail', [
            'tournament' => $tournament,
            'registered' => $registered,
        ]);
    }

    public function joinSocial(Social $social)
    {
        // Check if user is already a participant
        if (auth()->user()->socialParticipants()->where('social_id', $social->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã tham gia lịch đấu này rồi'
            ], 422);
        }

        // Check if social is full
        $participantCount = $social->participants()->count();
        if ($social->max_participants && $participantCount >= $social->max_participants) {
            return response()->json([
                'success' => false,
                'message' => 'Lịch đấu đã đủ người tham gia'
            ], 422);
        }

        // Add user as participant with timestamps
        auth()->user()->socialParticipants()->attach($social->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tham gia lịch đấu thành công'
        ]);
    }

    public function social(Request $request)
    {
        $query = Social::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('object', 'like', "%{$search}%");
        }

        // Stadium filter
        if ($request->filled('stadium_id')) {
            $query->where('stadium_id', $request->input('stadium_id'));
        }

        // Object filter
        if ($request->filled('object')) {
            $query->where('object', $request->input('object'));
        }

        // Days of week filter
        if ($request->filled('date')) {
            $day = $request->input('date');
            $query->where('days_of_week', 'like', '%' . $day . '%');
        }

        $socials = $query->paginate(10)->appends($request->query());

        // Add join status for each social event
        if (auth()->check()) {
            $socials->getCollection()->transform(function($social) {
                $social->user_joined = auth()->user()->socialParticipants()
                    ->where('social_id', $social->id)
                    ->exists();
                return $social;
            });
        } else {
            $socials->getCollection()->transform(function($social) {
                $social->user_joined = false;
                return $social;
            });
        }

        // Get unique stadiums for filter dropdown
        $stadiums = Stadium::where('status', 'active')->whereIn('id', $query->get()->pluck('stadium_id'))->get();

        // Statistics
        $totalSocials = Social::count();
        $totalStadiums = $stadiums->count();

        return view('front.social_play', [
            'socials' => $socials,
            'stadiums' => $stadiums,
            'totalSocials' => $totalSocials,
            'totalStadiums' => $totalStadiums,
            'filters' => [
                'search' => $request->input('search'),
                'stadium_id' => $request->input('stadium_id'),
                'object' => $request->input('object'),
                'date' => $request->input('date'),
            ],
        ]);
    }

    public function news(Request $request)
    {
        // Featured news (is_featured = true)
        $featuredNews = News::where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->first();

        // Build query for all news
        $query = News::orderBy('created_at', 'desc');

        // Search filter - tìm kiếm theo tiêu đề
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        // Category filter - lọc theo danh mục
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Paginate results with query parameters preserved
        $news = $query->paginate(6)->appends($request->query());

        // Categories for filter
        $categories = Category::has('news')->get();

        return view('front.news', [
            'news' => $news,
            'featuredNews' => $featuredNews,
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search'),
                'category' => $request->input('category'),
            ],
        ]);
    }
}
