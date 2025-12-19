<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Court;
use App\Models\CourtPricing;
use App\Models\Instructor;
use App\Models\News;
use App\Models\Province;
use App\Models\Social;
use App\Models\Stadium;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

    public function booking(Stadium $stadium)
    {
        $courts = $stadium->courts()->where('is_active', 1)->get();
        return view('front.booking', compact('courts', 'stadium'));
    }

    public function getAvailableSlots(Court $court, Request $request)
    {
        try {
            $date = $request->query('date');
            
            if (!$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ngày không hợp lệ'
                ]);
            }

            // Parse the booking date
            $bookingDate = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
            $dayOfWeek = $bookingDate->dayOfWeek;

            // Get booked slots for this date
            $bookings = Booking::where('court_id', $court->id)
                ->where('booking_date', $date)
                ->where('status', '!=', 'cancelled')
                ->get(['start_time', 'end_time', 'status']);

            $bookedSlots = [];
            foreach ($bookings as $booking) {
                $bookedSlots[] = [
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'status' => $booking->status
                ];
            }

            // Get stadium opening hours
            $stadium = $court->stadium;
            $openingHours = $stadium->opening_time . ' - ' . $stadium->closing_time;
            
            // Parse opening hours to get start and end hours
            preg_match('/(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})/', $openingHours, $matches);
            $startHour = isset($matches[1]) ? (int)$matches[1] : 6;
            $endHour = isset($matches[3]) ? (int)$matches[3] : 22;

            // Generate time slots with pricing from court_pricing table
            $timeSlots = [];
            for ($hour = $startHour; $hour < $endHour; $hour++) {
                $slotTime = sprintf('%02d:00', $hour);
                $slotDateTime = \DateTime::createFromFormat('H:i', $slotTime);

                // Get pricing for this hour
                $pricing = CourtPricing::where('court_id', $court->id)
                    ->where('is_active', true)
                    ->where(function ($query) use ($bookingDate) {
                        $query->whereNull('valid_from')
                              ->orWhere('valid_from', '<=', $bookingDate);
                    })
                    ->where(function ($query) use ($bookingDate) {
                        $query->whereNull('valid_to')
                              ->orWhere('valid_to', '>=', $bookingDate);
                    })
                    ->where(function ($query) use ($slotDateTime) {
                        $query->whereRaw('TIME(start_time) <= ?', [$slotDateTime->format('H:i:s')])
                              ->whereRaw('TIME(end_time) > ?', [$slotDateTime->format('H:i:s')]);
                    })
                    ->where(function ($query) use ($dayOfWeek) {
                        $query->whereNull('days_of_week')
                              ->orWhereJsonContains('days_of_week', $dayOfWeek);
                    })
                    ->orderByRaw('TIME(start_time) DESC')
                    ->first();

                // Use pricing if found, otherwise use court's rental_price
                $price = $pricing ? $pricing->price_per_hour : ($court->rental_price ?? 0);

                // Check if this slot is booked
                $isBooked = false;
                $isPending = false;
                $nextHour = $hour + 1;
                $nextSlotTime = sprintf('%02d:00', $nextHour);
                
                foreach ($bookedSlots as $booked) {
                    $bookedStart = \DateTime::createFromFormat('H:i:s', $booked['start_time']);
                    $bookedEnd = \DateTime::createFromFormat('H:i:s', $booked['end_time']);
                    $currentSlotStart = $slotDateTime;
                    $currentSlotEnd = \DateTime::createFromFormat('H:i', $nextSlotTime);
                    
                    // Check if there's any overlap
                    if ($currentSlotStart < $bookedEnd && $currentSlotEnd > $bookedStart && $booked['status'] != 'cancelled') {
                        if($booked['status'] == 'pending') {
                            $isPending = true;
                        } else {
                            $isBooked = true;
                        }
                        break;
                    }
                }

                $timeSlots[] = [
                    'time' => $slotTime,
                    'hour' => $hour,
                    'end_hour' => $nextHour,
                    'price' => $price,
                    'is_booked' => $isBooked,
                    'is_pending' => $isPending,
                ];
            }

            return response()->json([
                'success' => true,
                'available_slots' => $timeSlots,
                'booked_slots' => $bookedSlots,
            ]);
        } catch (\Exception $e) {
            Log::error('Get slots error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu khoảng thời gian: ' . $e->getMessage()
            ]);
        }
    }

    public function bookingCourt(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'court_id' => 'required|exists:courts,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration_hours' => 'required|numeric|min:1',
            'hourly_rate' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer,wallet',
            'notes' => 'nullable|string',
        ]);

        if($validation->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validation->errors()->first()
            ]);
        }

        // Get court details
        $court = Court::findOrFail($request->court_id);

        // Calculate duration in hours
        $startTime = \DateTime::createFromFormat('H:i', $request->start_time);
        
        $durationHours = (int) $request->duration_hours;
        $endTime = $startTime->modify('+' . $durationHours . ' hours');

        // Recalculate total price on server side with multi-price support
        $totalPrice = $this->calculateBookingTotalPrice(
            $court->id, 
            $request->booking_date, 
            $request->start_time, 
            $durationHours
        );

        $hourlyRate = (int) ($totalPrice / $durationHours);
        // Calculate service fee (5% of total price)
        $serviceFee = (int) round($totalPrice * 0.05);

        // Check if slot is already booked
        $existingBooking = Booking::where('court_id', $request->court_id)
            ->where('booking_date', $request->booking_date)
            ->where('status', '!=', 'cancelled')
            ->whereRaw("TIME(start_time) < ? AND TIME(end_time) > ?", [$endTime->format('H:i'), $request->start_time])
            ->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Khoảng thời gian này đã được đặt. Vui lòng chọn thời gian khác.'
            ]);
        }

        // Create booking
        $booking = Booking::create([
            'court_id' => $request->court_id,
            'user_id' => auth()->id() ?? null,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $endTime->format('H:i'),
            'duration_hours' => $durationHours,
            'hourly_rate' => $hourlyRate,
            'total_price' => $totalPrice,
            'service_fee' => $serviceFee,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'notes' => $request->notes ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đặt sân thành công. Đơn đặt của bạn đang chờ xác nhận.',
            'booking' => [
                'id' => $booking->id,
                'booking_id' => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'status' => $booking->status,
            ]
        ]);
        
    }

    /**
     * Calculate total price for a booking with multi-price support
     * This is used internally and by the API
     */
    private function calculateBookingTotalPrice($courtId, $bookingDate, $startTime, $durationHours)
    {
        $court = Court::findOrFail($courtId);
        $bookingDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDate);
        $dayOfWeek = $bookingDate->dayOfWeek;
        
        $startTimeObj = \DateTime::createFromFormat('H:i', $startTime);
        $totalPrice = 0;
        $currentTime = clone $startTimeObj;
        
        for ($i = 0; $i < $durationHours; $i++) {
            $hourStart = clone $currentTime;
            
            // Find matching court pricing for this hour
            $pricing = CourtPricing::where('court_id', $courtId)
                ->where('is_active', true)
                ->where(function ($query) use ($bookingDate) {
                    $query->whereNull('valid_from')
                          ->orWhere('valid_from', '<=', $bookingDate);
                })
                ->where(function ($query) use ($bookingDate) {
                    $query->whereNull('valid_to')
                          ->orWhere('valid_to', '>=', $bookingDate);
                })
                ->where(function ($query) use ($hourStart) {
                    $query->whereRaw('TIME(start_time) <= ?', [$hourStart->format('H:i:s')])
                          ->whereRaw('TIME(end_time) > ?', [$hourStart->format('H:i:s')]);
                })
                ->where(function ($query) use ($dayOfWeek) {
                    $query->whereNull('days_of_week')
                          ->orWhereJsonContains('days_of_week', $dayOfWeek);
                })
                ->orderByRaw('TIME(start_time) DESC')
                ->first();
            
            // Use court pricing if found, otherwise use court's rental_price
            $hourlyRate = $pricing ? $pricing->price_per_hour : ($court->rental_price ?? 150000);
            $totalPrice += $hourlyRate;
            $currentTime->modify('+1 hour');
        }
        
        return $totalPrice;
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
            $query->where('province_id', $request->input('location'));
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

        // Get provinces
        $provinces = Province::all();

        // Paginate results
        $stadiums = $query->latest()->paginate(10)->appends($request->query());

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
            'provinces' => $provinces,
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

    public function courtsDetail($stadium_id)
    {
        $stadium = Stadium::findOrFail($stadium_id);
        $stadium->is_favorited = false;
        if(auth()->check()) {
            $stadium->is_favorited = auth()->user()->favoriteStadiums()->where('stadium_id', $stadium->id)->exists();
        }

        $allPrices = $this->getStadiumPricing($stadium->id);
        
        $relatedStadiums = Stadium::where('status', 'active')
            ->where('id', '!=', $stadium->id)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return view('front.courts.courts_detail', [
            'stadium' => $stadium,
            'allPrices' => $allPrices,
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

        // Default ordering: Ongoing first, then Upcoming, then Completed
        // Using raw SQL CASE statement to prioritize status
        $now = now();
        $query->orderByRaw("
            CASE 
                WHEN start_date <= ? AND end_date >= ? THEN 1
                WHEN start_date > ? THEN 2
                ELSE 3
            END ASC,
            start_date ASC
        ", [$now, $now, $now])
        ->orderBy('start_date', 'asc');

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
    
    public function tournamentsDetail($tournament_slug)
    {
        $tournament = Tournament::with('categories')->where('slug', $tournament_slug)->firstOrFail();

        $registered = DB::table('tournament_athletes')
        ->where('tournament_id', $tournament->id)
        ->where('user_id', auth()->id())
        ->exists();
        
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
        $totalParticipants = DB::table('social_participants')->distinct('user_id')->count('user_id');

        return view('front.social_play', [
            'socials' => $socials,
            'stadiums' => $stadiums,
            'totalSocials' => $totalSocials,
            'totalStadiums' => $totalStadiums,
            'totalParticipants' => $totalParticipants,
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

    public function instructors(Request $request)
    {
        $query = Instructor::with('province');

        // Search by name or ward
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ward', 'like', "%{$search}%");
            });
        }

        // Filter by city (province)
        if ($request->has('city') && $request->city) {
            $query->where('province_id', $request->city);
        }

        // Filter by experience
        if ($request->has('experience') && $request->experience) {
            $experience = $request->experience;
            if ($experience === '1-3') {
                $query->whereBetween('experience', [1, 3]);
            } elseif ($experience === '3-5') {
                $query->whereBetween('experience', [3, 5]);
            } elseif ($experience === '5+') {
                $query->where('experience', '>=', 5);
            }
        }

        // Sort
        $sort = $request->get('sort', 'rating');
        if ($sort === 'experience') {
            $query->orderBy('experience', 'desc');
        } elseif ($sort === 'newest') {
            $query->orderBy('id', 'desc');
        } else {
            // Default: rating (you might need to add a rating column)
            $query->orderBy('id', 'desc');
        }

        $instructors = $query->paginate(6);
        return view('front.instructors', compact('instructors'));
    }

    public function instructorDetail($id)
    {
        $instructor = Instructor::with([
            'province',
            'experiences',
            'certifications',
            'teachingMethods',
            'packages',
            'schedules',
            'reviews',
        ])->findOrFail($id);
        
        // Get similar instructors (same province, limit 3)
        $similarInstructors = Instructor::with('province')
            ->where('province_id', $instructor->province_id)
            ->where('id', '!=', $instructor->id)
            ->inRandomOrder()
            ->limit(3)
            ->get();
        
        return view('front.instructors.instructor_detail', [
            'instructor' => $instructor,
            'similarInstructors' => $similarInstructors,
        ]);
    }

    public function course(Request $request)
    {
        $query = Video::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Level filter
        if ($request->filled('level')) {
            $query->where('level', $request->input('level'));
        }

        // Duration filter
        if ($request->filled('duration')) {
            $duration = $request->input('duration');
            if ($duration === 'short') {
                $query->whereRaw('CAST(SUBSTRING_INDEX(duration, ":", 1) AS UNSIGNED) * 60 + CAST(SUBSTRING_INDEX(duration, ":", -1) AS UNSIGNED) < 600');
            } elseif ($duration === 'medium') {
                $query->whereRaw('CAST(SUBSTRING_INDEX(duration, ":", 1) AS UNSIGNED) * 60 + CAST(SUBSTRING_INDEX(duration, ":", -1) AS UNSIGNED) BETWEEN 600 AND 1800');
            } elseif ($duration === 'long') {
                $query->whereRaw('CAST(SUBSTRING_INDEX(duration, ":", 1) AS UNSIGNED) * 60 + CAST(SUBSTRING_INDEX(duration, ":", -1) AS UNSIGNED) > 1800');
            }
        }

        // Sort
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'views':
                $query->orderBy('views_count', 'desc');
                break;
            default:
                $query->latest();
        }

        // Paginate with filters preserved
        $videos = $query->paginate(6)->appends($request->query());
        
        // Categories for filter
        $categories = Category::has('videos')->get();
        
        // Get unique levels from videos
        $levels = Video::distinct()->pluck('level')->filter()->values();
        
        // Get statistics
        $totalVideos = Video::count();
        
        // Calculate total duration in hours
        $allVideos = Video::all();
        $totalSeconds = 0;
        foreach ($allVideos as $video) {
            if ($video->duration) {
                $parts = explode(':', $video->duration);
                $minutes = isset($parts[0]) ? (int)$parts[0] : 0;
                $seconds = isset($parts[1]) ? (int)$parts[1] : 0;
                $totalSeconds += $minutes * 60 + $seconds;
            }
        }
        $totalHours = round($totalSeconds / 3600, 1);
        
        // Get average rating
        $averageRating = Video::where('rating', '>', 0)->avg('rating') ?? 0;
        $averageRating = round($averageRating, 1);
        
        // Get total users (assuming you have User model)
        $totalUsers = User::count();

        return view('front.course', [
            'videos' => $videos,
            'categories' => $categories,
            'levels' => $levels,
            'stats' => [
                'totalVideos' => $totalVideos,
                'totalHours' => $totalHours,
                'totalUsers' => $totalUsers,
                'averageRating' => $averageRating,
            ],
            'filters' => [
                'search' => $request->input('search'),
                'category' => $request->input('category'),
                'level' => $request->input('level'),
                'duration' => $request->input('duration'),
                'sort' => $request->input('sort'),
            ],
        ]);
    }

    public function courseDetail($id)
    {
        $video = Video::with('category')->findOrFail($id);
        
        // Get related videos (same category, limit 5)
        $relatedVideos = Video::where('category_id', $video->category_id)
            ->where('id', '!=', $video->id)
            ->inRandomOrder()
            ->limit(5)
            ->get();
        
        return view('front.courses.courses_detail', [
            'video' => $video,
            'relatedVideos' => $relatedVideos,
        ]);
    }

    /**
     * Get all rental prices and court pricing for a specific stadium
     */
    public function getStadiumPricing($stadiumId)
    {
        // Get all courts for this stadium
        $courts = Court::where('stadium_id', $stadiumId)->get();

        // Get all rental prices from courts
        $rentalPrices = $courts->pluck('rental_price')->unique()->values();

        // Get all court pricing records for courts in this stadium
        $courtPricings = CourtPricing::whereIn('court_id', $courts->pluck('id'))
            ->where('is_active', true)
            ->pluck('price_per_hour')
            ->unique()
            ->values();

        $allPrices = $rentalPrices->merge($courtPricings);
        
        return [
            'min_price' => $allPrices->min(),
            'max_price' => $allPrices->max(),
        ];
    }

}
