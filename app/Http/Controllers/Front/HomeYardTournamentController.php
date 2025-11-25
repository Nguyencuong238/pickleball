<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\ActivityLog;
use App\Models\Court;
use App\Models\CourtPricing;
use App\Models\Stadium;
use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\Booking;
use App\Models\MatchModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HomeYardTournamentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:home_yard']);
    }

    public function index()
    {
        $tournaments = Tournament::where('user_id', auth()->id())->latest()->paginate(10);
        return view('home-yard.tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        return view('home-yard.tournaments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
        ]);

        $data = $request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'location',
            'max_participants',
            'price',
            'rules',
        ]);

        $data['user_id'] = auth()->id();

        $tournament = Tournament::create($data);
        
        // Log activity
        ActivityLog::log("Giải đấu '{$tournament->name}' được tạo", 'Tournament', $tournament->id);


        // Sync gallery images
        $tournament->syncMediaCollection('gallery', 'gallery', $request);

        // Sync banner image
        $tournament->syncMediaCollection('banner', 'banner', $request);

        return redirect()->back()->with('success', 'Giải đấu đã được tạo thành công. Bạn có thể tiếp tục thêm nội dung thi đấu.');
    }

    public function show(Tournament $tournament)
    {
        $this->authorize('view', $tournament);

        // Return JSON for AJAX requests
        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            $imageUrl = $tournament->image ? Storage::disk('public')->url($tournament->image) : null;
            $bannerUrl = $tournament->banner ? Storage::disk('public')->url($tournament->banner) : null;

            return response()->json([
                'id' => $tournament->id,
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => $tournament->start_date,
                'end_date' => $tournament->end_date,
                'location' => $tournament->location,
                'max_participants' => $tournament->max_participants,
                'price' => $tournament->price,
                'prizes' => $tournament->prizes,
                'competition_format' => $tournament->competition_format,
                'competition_rules' => $tournament->competition_rules,
                'registration_benefits' => $tournament->registration_benefits,
                'status' => $tournament->status,
                'image' => $imageUrl,
                'banner' => $bannerUrl,
            ]);
        }

        // Return view for regular requests
        $athletes = $tournament->athletes()->paginate(15);
        return view('home-yard.tournaments.show', compact('tournament', 'athletes'));
    }

    public function edit(Tournament $tournament)
    {
        $this->authorize('update', $tournament);
        return view('home-yard.tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date_format:Y-m-d\TH:i',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
            'prizes' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'competition_format' => 'nullable|string|in:single,double,mixed',
            'tournament_rank' => 'nullable|string|in:beginner,intermediate,advanced,professional',
            'registration_benefits' => 'nullable|string',
            'competition_rules' => 'nullable|string',
            'event_timeline' => 'nullable|string',
            'social_information' => 'nullable|string',
            'organizer_email' => 'nullable|email',
            'organizer_hotline' => 'nullable|string|max:20',
            'competition_schedule' => 'nullable|string',
            'results' => 'nullable|string',
            'gallery_json' => 'nullable|string',
            'gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $data = $request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'registration_deadline',
            'location',
            'max_participants',
            'price',
            'rules',
            'prizes',
            'competition_format',
            'tournament_rank',
            'registration_benefits',
            'competition_rules',
            'event_timeline',
            'social_information',
            'organizer_email',
            'organizer_hotline',
            'competition_schedule',
            'results',
        ]);

        if ($request->hasFile('image')) {
            if ($tournament->image && Storage::disk('public')->exists($tournament->image)) {
                Storage::disk('public')->delete($tournament->image);
            }
            $data['image'] = $request->file('image')->store('tournament_images', 'public');
        }

        // Handle gallery JSON (from form)
        if ($request->has('gallery_json') && !empty($request->gallery_json)) {
            try {
                $data['gallery'] = json_decode($request->gallery_json, true) ?? [];
            } catch (\Exception $e) {
                $data['gallery'] = $tournament->gallery ?? [];
            }
        }
        // Handle gallery file uploads (legacy support)
        elseif ($request->hasFile('gallery')) {
            $gallery = is_array($tournament->gallery) ? $tournament->gallery : (is_string($tournament->gallery) ? json_decode($tournament->gallery, true) ?? [] : []);
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $file->store('tournament_gallery', 'public');
            }
            $data['gallery'] = $gallery;
        }

        $tournament->update($data);

        // Handle AJAX requests
        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'message' => 'Tournament updated successfully']);
        }

        return redirect()->route('homeyard.tournaments.index')->with('success', 'Tournament updated successfully.');
    }

    public function destroy(Tournament $tournament)
    {
        $this->authorize('delete', $tournament);

        if ($tournament->image && Storage::disk('public')->exists($tournament->image)) {
            Storage::disk('public')->delete($tournament->image);
        }

        $tournament->delete();

        return redirect()->route('homeyard.tournaments.index')->with('success', 'Tournament deleted successfully.');
    }

    public function addAthlete(Request $request, Tournament $tournament)
    {
        \Log::info('addAthlete called', [
            'tournament_id' => $tournament->id,
            'all_inputs' => $request->all(),
            'is_json' => $request->wantsJson(),
            'header' => $request->header('X-Requested-With'),
        ]);

        try {
            $this->authorize('update', $tournament);
        } catch (\Exception $e) {
            \Log::error('Authorization failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'athlete_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'category_id' => 'required|exists:tournament_categories,id',
            ]);

            \Log::info('Validation passed', $validated);

            $athlete = TournamentAthlete::create([
                'tournament_id' => $tournament->id,
                'category_id' => $validated['category_id'],
                'user_id' => auth()->id(),  // Set the current user as the creator
                'athlete_name' => $validated['athlete_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => 'approved',  // Home yard organizer adds athletes as approved
                'payment_status' => 'pending',
            ]);

            \Log::info('Athlete created', ['id' => $athlete->id]);
            
            // Log activity
            ActivityLog::log("VĐV '{$athlete->athlete_name}' tham gia giải đấu '{$tournament->name}'", 'TournamentAthlete', $athlete->id);

            // Always return JSON for this endpoint
            return response()->json([
                'success' => true,
                'message' => 'Thêm vận động viên thành công',
                'athlete' => $athlete
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Lỗi validate: ' . collect($e->errors())->flatten()->join(', '),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Create error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateAthlete(Request $request, Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);

        try {
            $validated = $request->validate([
                'athlete_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'category_id' => 'required|exists:tournament_categories,id',
            ]);

            $athlete->update($validated);

            \Log::info('Athlete updated', ['id' => $athlete->id]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật vận động viên thành công',
                'athlete' => $athlete
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Lỗi validate: ' . collect($e->errors())->flatten()->join(', '),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Update error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeAthlete(Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);
        $athlete->delete();
        
        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Vận động viên đã được xóa'
            ]);
        }
        
        return redirect()->back()->with('success', 'Athlete removed successfully.');
    }

    public function updateAthleteStatus(Request $request, Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        $athlete->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Athlete status updated successfully.');
    }

    public function approveAthlete(Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);

        $athlete->update(['status' => 'approved']);

        return redirect()->back()->with('success', "Vận động viên {$athlete->athlete_name} đã được duyệt.");
    }

    public function rejectAthlete(Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);

        $athlete->update(['status' => 'rejected']);

        return redirect()->back()->with('success', "Vận động viên {$athlete->athlete_name} đã bị từ chối.");
    }

    public function listAthletes(Request $request)
    {
        $status = $request->query('status', 'pending');

        $athletes = TournamentAthlete::whereHas('tournament', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->where('status', $status)
            ->with('tournament')
            ->latest()
            ->paginate(15);

        $stats = [
            'pending' => TournamentAthlete::whereHas('tournament', function ($q) {
                $q->where('user_id', auth()->id());
            })->where('status', 'pending')->count(),
            'approved' => TournamentAthlete::whereHas('tournament', function ($q) {
                $q->where('user_id', auth()->id());
            })->where('status', 'approved')->count(),
            'rejected' => TournamentAthlete::whereHas('tournament', function ($q) {
                $q->where('user_id', auth()->id());
            })->where('status', 'rejected')->count(),
        ];

        return view('home-yard.athletes.index', compact('athletes', 'status', 'stats'));
    }

    public function updateTournament(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
            'status' => 'nullable|in:upcoming,ongoing,completed,cancelled',
        ]);

        $data = $request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'location',
            'max_participants',
            'price',
            'rules',
            'status',
        ]);

        // Convert status string to boolean: upcoming/ongoing = true, completed/cancelled = false
        if (isset($data['status'])) {
            $data['status'] = in_array($data['status'], ['upcoming', 'ongoing']) ? true : false;
        }

        $tournament->update($data);

        return redirect()->back()->with('success', 'Thông tin giải đấu đã được cập nhật thành công.');
    }

    public function overview()
    {
        $userId = auth()->id();
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // 1. Tổng giải đấu (tất cả)
        $totalTournaments = Tournament::where('user_id', $userId)->count();
        $lastMonthTournaments = Tournament::where('user_id', $userId)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $tournamentChangePercent = $lastMonthTournaments > 0 
            ? round((($totalTournaments - $lastMonthTournaments) / $lastMonthTournaments) * 100, 2)
            : 0;

        // 2. Giải đấu theo trạng thái
        $now = now();
        $ongoingTournaments = Tournament::where('user_id', $userId)
            ->where('start_date', '<=', $now)
            ->where(function ($q) {
                $q->where('end_date', '>=', now())
                    ->orWhereNull('end_date');
            })
            ->count();

        $upcomingTournaments = Tournament::where('user_id', $userId)
            ->where('start_date', '>', $now)
            ->count();

        $completedTournaments = Tournament::where('user_id', $userId)
            ->where(function ($q) use ($now) {
                $q->where('end_date', '<', $now)
                    ->orWhere('status', 0);
            })
            ->count();

        // 3. Tổng vận động viên
        $totalAthletes = TournamentAthlete::whereHas('tournament', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();

        // 4. VĐV mới trong tháng này
        $newAthletesThisMonth = TournamentAthlete::whereHas('tournament', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        // VĐV mới tháng trước
        $newAthletesLastMonth = TournamentAthlete::whereHas('tournament', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $athleteChangePercent = $newAthletesLastMonth > 0 
            ? round((($newAthletesThisMonth - $newAthletesLastMonth) / $newAthletesLastMonth) * 100, 2)
            : 0;

        // 5. Trận đấu hôm nay
        $todayMatches = MatchModel::whereIn('tournament_id', function ($q) use ($userId) {
            $q->select('id')->from('tournaments')->where('user_id', $userId);
        })
            ->where('match_date', $today)
            ->count();

        $todayLiveMatches = MatchModel::whereIn('tournament_id', function ($q) use ($userId) {
            $q->select('id')->from('tournaments')->where('user_id', $userId);
        })
            ->where('match_date', $today)
            ->where('status', 'in_progress')
            ->count();

        $todayRemainingMatches = MatchModel::whereIn('tournament_id', function ($q) use ($userId) {
            $q->select('id')->from('tournaments')->where('user_id', $userId);
        })
            ->where('match_date', $today)
            ->where('status', 'scheduled')
            ->count();

        $todayDelayedMatches = MatchModel::whereIn('tournament_id', function ($q) use ($userId) {
            $q->select('id')->from('tournaments')->where('user_id', $userId);
        })
            ->where('match_date', $today)
            ->where('status', 'delayed')
            ->count();

        // 6. Doanh thu tháng này
        $monthlyRevenue = 0;
        // Tính từ các bookings hoặc tournament fees
        // Bạn có thể điều chỉnh logic này tùy theo cách tính doanh thu của bạn

        // 7. Lấy danh sách giải đấu gần đây (5 giải mới nhất)
        $recentTournaments = Tournament::where('user_id', $userId)
            ->with('athletes')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($tournament) {
                $athleteCount = $tournament->athletes()->count();
                $matchCount = MatchModel::where('tournament_id', $tournament->id)->count();
                
                // Xác định trạng thái
                $now = now();
                $startDate = \Carbon\Carbon::parse($tournament->start_date);
                $endDate = $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date) : null;
                
                if ($startDate > $now) {
                    $status = 'upcoming';
                } elseif ($startDate <= $now && ($endDate === null || $endDate >= $now)) {
                    $status = 'ongoing';
                } else {
                    $status = 'completed';
                }
                
                return [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'athleteCount' => $athleteCount,
                    'matchCount' => $matchCount,
                    'startDate' => $startDate->format('d/m/Y'),
                    'status' => $status,
                ];
            });

        $stats = [
            'totalTournaments' => $totalTournaments,
            'ongoingTournaments' => $ongoingTournaments,
            'upcomingTournaments' => $upcomingTournaments,
            'completedTournaments' => $completedTournaments,
            'tournamentChangePercent' => $tournamentChangePercent,
            'totalAthletes' => $totalAthletes,
            'newAthletesThisMonth' => $newAthletesThisMonth,
            'athleteChangePercent' => $athleteChangePercent,
            'todayMatches' => $todayMatches,
            'todayLiveMatches' => $todayLiveMatches,
            'todayRemainingMatches' => $todayRemainingMatches,
            'todayDelayedMatches' => $todayDelayedMatches,
            'monthlyRevenue' => $monthlyRevenue,
        ];

        // Get recent activities
        $recentActivities = ActivityLog::where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get();

        // 8. Thống kê giải đấu theo tháng (2 năm gần nhất)
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;
        
        $tournamentsByMonth = [];
        foreach ([$lastYear, $currentYear] as $year) {
            $monthData = [];
            for ($month = 1; $month <= 12; $month++) {
                $count = Tournament::where('user_id', $userId)
                    ->whereYear('start_date', $year)
                    ->whereMonth('start_date', $month)
                    ->count();
                $monthData[] = $count;
            }
            $tournamentsByMonth[$year] = $monthData;
        }

        // 9. Phân bổ vận động viên theo nội dung thi đấu
        $athletesByCategory = TournamentAthlete::whereHas('tournament', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->with('category')
            ->get()
            ->groupBy(function ($athlete) {
                return $athlete->category ? $athlete->category->category_name : 'Không xác định';
            })
            ->map(function ($group) {
                return count($group);
            })
            ->toArray();

        return view('home-yard.tournaments.overview', compact('stats', 'recentTournaments', 'recentActivities', 'tournamentsByMonth', 'athletesByCategory'));
    }

    public function tournaments()
    {
        $tournaments = Tournament::where('user_id', auth()->id())
            ->with(['athletes', 'categories'])
            ->latest()
            ->paginate(12);

        // Calculate stats
        $now = now();
        $stats = [
            'total' => Tournament::where('user_id', auth()->id())->count(),
            'ongoing' => Tournament::where('user_id', auth()->id())
                ->where('start_date', '<=', $now)
                ->where(function ($q) {
                    $q->where('end_date', '>=', now())
                        ->orWhereNull('end_date');
                })
                ->count(),
            'upcoming' => Tournament::where('user_id', auth()->id())
                ->where('start_date', '>', $now)
                ->count(),
            'completed' => Tournament::where('user_id', auth()->id())
                ->where(function ($q) use ($now) {
                    $q->where('end_date', '<', $now)
                        ->orWhere('status', 0);
                })
                ->count(),
        ];

        return view('home-yard.tournaments.tournaments', compact('tournaments', 'stats'));
    }

    public function matches(Request $request)
    {
        // Get all tournaments for the user
        $tournaments = Tournament::where('user_id', auth()->id())->get();
        
        $tournamentIds = $tournaments->pluck('id');
        
        // Get total counts for statistics
        $totalMatches = MatchModel::whereIn('tournament_id', $tournamentIds)->count();
        $liveCount = MatchModel::whereIn('tournament_id', $tournamentIds)->where('status', 'in_progress')->count();
        $upcomingCount = MatchModel::whereIn('tournament_id', $tournamentIds)->where('status', 'scheduled')->count();
        $completedCount = MatchModel::whereIn('tournament_id', $tournamentIds)->where('status', 'completed')->count();
        
        // Paginate matches by status
        // Live: status = 'in_progress'
        $liveMatches = MatchModel::whereIn('tournament_id', $tournamentIds)
            ->where('status', 'in_progress')
            ->where('status', '!=', 'cancelled')
            ->with(['athlete1', 'athlete2', 'winner', 'court', 'round', 'category'])
            ->orderBy('match_date', 'desc')
            ->orderBy('match_time', 'desc')
            ->paginate(5, ['*'], 'live_page');
        
        // Upcoming: status = 'scheduled'
        $upcomingMatches = MatchModel::whereIn('tournament_id', $tournamentIds)
            ->where('status', 'scheduled')
            ->where('status', '!=', 'cancelled')
            ->with(['athlete1', 'athlete2', 'winner', 'court', 'round', 'category'])
            ->orderBy('match_date', 'asc')
            ->orderBy('match_time', 'asc')
            ->paginate(5, ['*'], 'upcoming_page');
        
        // Completed: status = 'completed'
        $completedMatches = MatchModel::whereIn('tournament_id', $tournamentIds)
            ->where('status', 'completed')
            ->with(['athlete1', 'athlete2', 'winner', 'court', 'round', 'category'])
            ->orderBy('match_date', 'desc')
            ->orderBy('match_time', 'desc')
            ->paginate(5, ['*'], 'completed_page');
        
        // Calculate statistics
        $stats = [
            'completed' => $completedCount,
            'live' => $liveCount,
            'upcoming' => $upcomingCount,
            'total' => $totalMatches,
        ];
        
        // Get matches grouped by date for calendar view
        $allMatches = MatchModel::whereIn('tournament_id', $tournamentIds)
            ->where('status', '!=', 'cancelled')
            ->select('match_date')
            ->groupBy('match_date')
            ->pluck('match_date');
        
        // Count matches per date
        $matchesByDate = MatchModel::whereIn('tournament_id', $tournamentIds)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(match_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');
        
        return view('home-yard.tournaments.matches', compact('liveMatches', 'upcomingMatches', 'completedMatches', 'stats', 'matchesByDate'));
    }

    public function athletes()
    {
        return view('home-yard.tournaments.athletes');
    }

    public function rankings()
    {
        return view('home-yard.tournaments.rankings');
    }

    /**
     * Get tournaments list as JSON for filter
     */
    public function getTournamentsListJson()
    {
        try {
            $tournaments = Tournament::where('user_id', auth()->id())
                ->select('id', 'name')
                ->with('categories:id,tournament_id,category_type,category_name')
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'tournaments' => $tournaments
            ]);
        } catch (\Exception $e) {
            Log::error('Get tournaments list error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rankings for all tournaments created by the user
     * Athletes who participated in any of the user's tournaments, sorted by total points
     */
    public function getTournamentStats(Request $request)
    {
        try {
            $userId = auth()->id();
            $tournamentId = $request->get('tournament_id');
            $categoryId = $request->get('category_id');
            $groupId = $request->get('group_id');
            
            if ($tournamentId) {
                // Stats for specific tournament
                $tournaments = [$tournamentId];
            } else {
                // Stats for all tournaments created by user
                $tournaments = Tournament::where('user_id', $userId)
                    ->pluck('id')
                    ->toArray();
            }
            
            if (empty($tournaments)) {
                return response()->json([
                    'total_athletes' => 0,
                    'total_matches' => 0,
                    'avg_win_rate' => 0,
                    'total_wins' => 0
                ]);
            }
            
            // Get standings - same source as rankings display with same filters
            $query = GroupStanding::whereHas('group', function ($q) use ($tournaments) {
                $q->whereIn('tournament_id', $tournaments);
            });
            
            // Filter by category if provided
            if ($categoryId) {
                $query->whereHas('athlete', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }
            
            // Filter by group if provided
            if ($groupId) {
                $query->where('group_id', $groupId);
            }
            
            $standings = $query->get();
            
            // Count unique athletes from standings
            $totalAthletes = $standings->pluck('athlete_id')->unique()->count();
            
            $totalMatches = $standings->sum('matches_played');
            $totalWins = $standings->sum('matches_won');
            $avgWinRate = $totalMatches > 0 ? round(($totalWins / $totalMatches) * 100, 0) : 0;
            
            return response()->json([
                'total_athletes' => $totalAthletes,
                'total_matches' => $totalMatches,
                'avg_win_rate' => $avgWinRate,
                'total_wins' => $totalWins
            ]);
        } catch (\Exception $e) {
            \Log::error('Get tournament stats error: ' . $e->getMessage());
            return response()->json([
                'total_athletes' => 0,
                'total_matches' => 0,
                'avg_win_rate' => 0,
                'total_wins' => 0
            ], 500);
        }
    }

    public function getAllTournamentsRankings(Request $request)
    {
        try {
            $userId = auth()->id();
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            $categoryType = $request->get('category_type'); // Get category type filter (single_men, single_women, etc.)
            
            // Get all tournaments created by the user
            $tournaments = Tournament::where('user_id', $userId)
                ->pluck('id')
                ->toArray();

            if (empty($tournaments)) {
                return response()->json([
                    'success' => true,
                    'standings' => [],
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => 1,
                    'last_page' => 1,
                    'message' => 'Không có giải đấu nào'
                ]);
            }

            // Get all GroupStandings for these tournaments
            $query = GroupStanding::select([
                'id', 'athlete_id', 'group_id', 'points', 'matches_played', 'matches_won',
                'matches_lost', 'matches_drawn', 'win_rate', 'sets_won', 'sets_lost', 
                'sets_differential', 'games_won', 'games_lost', 'games_differential', 'is_advanced'
            ])
            ->with([
                'athlete' => function ($q) {
                    $q->select('id', 'athlete_name', 'email', 'phone', 'category_id');
                },
                'athlete.category' => function ($q) {
                    $q->select('id', 'category_type', 'category_name');
                }
            ])
            ->whereHas('group', function ($q) use ($tournaments) {
                $q->whereIn('tournament_id', $tournaments);
            });
            
            // Filter by category type if provided
            if ($categoryType) {
                $query->whereHas('athlete.category', function ($q) use ($categoryType) {
                    $q->where('category_type', $categoryType);
                });
            }
            
            $standings = $query->get();

            // Group by athlete and sum their points across all tournaments
            $athleteStats = $standings->groupBy('athlete_id')->map(function ($standings, $athleteId) {
                $totalPoints = $standings->sum('points');
                $totalMatches = $standings->sum('matches_played');
                $totalWins = $standings->sum('matches_won');
                $totalLosses = $standings->sum('matches_lost');
                
                $firstAthleteData = $standings->first();
                $athlete = $firstAthleteData->athlete;

                return [
                    'athlete_id' => $athleteId,
                    'athlete' => $athlete ? [
                        'id' => $athlete->id,
                        'athlete_name' => $athlete->athlete_name,
                        'email' => $athlete->email,
                        'phone' => $athlete->phone,
                        'category_id' => $athlete->category_id,
                        'category_name' => $athlete->category ? $athlete->category->category_name : 'N/A'
                    ] : null,
                    'points' => $totalPoints,
                    'matches_played' => $totalMatches,
                    'matches_won' => $totalWins,
                    'matches_lost' => $totalLosses,
                    'win_rate' => $totalMatches > 0 ? round(($totalWins / $totalMatches) * 100, 2) : 0,
                    'tournaments_count' => $standings->count(),
                    'rank_change' => 0
                ];
            });

            // Sort by points (descending), then by wins (descending)
            $sortedStandings = $athleteStats
                ->sortByDesc('points')
                ->sortByDesc('matches_won')
                ->values();

            $total = $sortedStandings->count();
            $lastPage = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $paginatedStandings = $sortedStandings->slice($offset, $perPage)->values();

            return response()->json([
                'success' => true,
                'standings' => $paginatedStandings->all(),
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage
            ]);
        } catch (\Exception $e) {
            Log::error('Get all tournaments rankings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function courts()
    {
        $stadiums = Stadium::where('user_id', auth()->id())->get();
        $courts = Court::whereIn('stadium_id', $stadiums->pluck('id'))->paginate(9);
        return view('home-yard.tournaments.courts', compact('stadiums', 'courts'));
    }

    public function storeCourt(Request $request)
    {
        try {
            $validated = $request->validate([
                'court_name' => 'required|string|max:255',
                'court_number' => 'nullable|string|max:100',
                'court_type' => 'required|string|in:indoor,outdoor',
                'surface_type' => 'required|string|in:acrylic,polyurethane,concrete,sport-court',
                'stadium_id' => 'required|exists:stadiums,id',
                'capacity' => 'required|integer|min:1',
                'rental_price' => 'required|integer|min:1',
                'description' => 'nullable|string',
            ]);

            $court = Court::create([
                'stadium_id' => $validated['stadium_id'],
                'court_name' => $validated['court_name'],
                'court_number' => $validated['court_number'] ?? null,
                'court_type' => $validated['court_type'],
                'size' => $request->size,
                'surface_type' => $validated['surface_type'],
                'capacity' => $validated['capacity'] ?? null,
                'rental_price' => $validated['rental_price'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => 'available',
                'is_active' => true,
            ]);
            
            // Log activity
            ActivityLog::log("Sân '{$validated['court_name']}' được thêm", 'Court', $court->id);

            // Handle pricing tiers
            if ($request->has('pricing_tiers')) {
                $pricingTiers = json_decode($request->pricing_tiers, true);
                
                if (is_array($pricingTiers) && !empty($pricingTiers)) {
                    // Create pricing tiers for the new court
                    foreach ($pricingTiers as $tier) {
                        if (!empty($tier['start_time']) && !empty($tier['end_time']) && !empty($tier['price_per_hour'])) {
                            CourtPricing::create([
                                'court_id' => $court->id,
                                'start_time' => $tier['start_time'],
                                'end_time' => $tier['end_time'],
                                'price_per_hour' => (int) $tier['price_per_hour'],
                                'is_active' => true,
                            ]);
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Sân được thêm thành công']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ]);
        } catch (\Exception $e) {
            Log::error('Court creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo sân. Vui lòng thử lại sau.'
            ]);
        }
    }

    public function saveCourts(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'court_ids' => 'required|array|min:1',
            'court_ids.*' => 'required|integer|exists:courts,id',
        ]);

        // Save selected court IDs as JSON in tournament_courts field
        $tournament->update([
            'tournament_courts' => json_encode($request->court_ids)
        ]);

        return redirect()->back()->with('success', 'Sân thi đấu đã được lưu thành công.')->with('step', 3);
    }

    public function editCourt(Court $court)
    {
        try {
            return response()->json([
                'success' => true,
                'court' => [
                    'id' => $court->id,
                    'court_name' => $court->court_name,
                    'court_number' => $court->court_number,
                    'court_type' => $court->court_type,
                    'surface_type' => $court->surface_type,
                    'stadium_id' => $court->stadium_id,
                    'capacity' => $court->capacity,
                    'rental_price' => $court->rental_price,
                    'description' => $court->description,
                    'size' => $court->size
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Court fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu sân'
            ], 500);
        }
    }

    public function getPricingTiers(Court $court)
    {
        try {
            $pricing = $court->activePricing()
                ->select('id', 'start_time', 'end_time', 'price_per_hour', 'is_active')
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'start_time' => $p->start_time->format('H:i') ?? '',
                        'end_time' => $p->end_time->format('H:i') ?? '',
                        'price_per_hour' => $p->price_per_hour,
                    ];
                });

            return response()->json([
                'success' => true,
                'pricing' => $pricing
            ]);
        } catch (\Exception $e) {
            Log::error('Pricing fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'pricing' => [],
                'message' => 'Không thể tải giá'
            ]);
        }
    }

    public function updateCourt(Request $request, Court $court)
    {
        try {
            $validated = $request->validate([
                'court_name' => 'required|string|max:255',
                'court_number' => 'nullable|string|max:100',
                'court_type' => 'required|string|in:indoor,outdoor',
                'surface_type' => 'required|string|in:acrylic,polyurethane,concrete,sport-court',
                'stadium_id' => 'required|exists:stadiums,id',
                'capacity' => 'required|integer|min:1',
                'rental_price' => 'required|integer|min:1',
                'description' => 'nullable|string',
            ]);

            $court->update([
                'stadium_id' => $validated['stadium_id'],
                'court_name' => $validated['court_name'],
                'court_number' => $validated['court_number'] ?? null,
                'court_type' => $validated['court_type'],
                'surface_type' => $validated['surface_type'],
                'size' => $request->size,
                'capacity' => $validated['capacity'] ?? null,
                'rental_price' => $validated['rental_price'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            // Handle pricing tiers
            if ($request->has('pricing_tiers')) {
                $pricingTiers = json_decode($request->pricing_tiers, true);
                
                if (is_array($pricingTiers) && !empty($pricingTiers)) {
                    // Delete existing pricing tiers
                    CourtPricing::where('court_id', $court->id)->delete();
                    
                    // Create new pricing tiers
                    foreach ($pricingTiers as $tier) {
                        if (!empty($tier['start_time']) && !empty($tier['end_time']) && !empty($tier['price_per_hour'])) {
                            CourtPricing::create([
                                'court_id' => $court->id,
                                'start_time' => $tier['start_time'],
                                'end_time' => $tier['end_time'],
                                'price_per_hour' => (int) $tier['price_per_hour'],
                                'is_active' => true,
                            ]);
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Sân được cập nhật thành công']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ]);
        } catch (\Exception $e) {
            Log::error('Court update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật sân. Vui lòng thử lại sau.'
            ]);
        }
    }

    public function bookings(Request $request)
    {
        $stadiums = Stadium::where('user_id', auth()->id())->get();
        $courts = Court::whereIn('stadium_id', $stadiums->pluck('id'))->where('is_active', 1)->get();
        $date = $request->date ?? now()->format('Y-m-d');

        return view('home-yard.tournaments.bookings', compact('courts', 'date'));
    }

    /**
     * Bốc thăm chia bảng cho một nội dung thi đấu
     */
    public function drawAthletes(Request $request, Tournament $tournament)
    {
        try {
            $this->authorize('update', $tournament);

            $categoryId = $request->input('category_id');
            $numberOfGroups = $request->input('number_of_groups');
            $drawMethod = $request->input('draw_method');

            // Validate manually
            if (!$categoryId || !$numberOfGroups || !$drawMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'category_id, number_of_groups, draw_method are required'
                ], 422);
            }

            if (!in_array($drawMethod, ['auto', 'seeded'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'draw_method must be auto or seeded'
                ], 422);
            }

            Log::info('Draw attempt', [
                'tournament_id' => $tournament->id,
                'category_id' => $categoryId,
                'number_of_groups' => $numberOfGroups,
                'draw_method' => $drawMethod,
                'is_numeric' => is_numeric($numberOfGroups),
                'type' => gettype($numberOfGroups)
            ]);

            if (!is_numeric($numberOfGroups) || $numberOfGroups < 1 || $numberOfGroups > 16) {
                return response()->json([
                    'success' => false,
                    'message' => 'number_of_groups must be between 1 and 16',
                    'debug' => [
                        'is_numeric' => is_numeric($numberOfGroups),
                        'value' => $numberOfGroups,
                        'type' => gettype($numberOfGroups)
                    ]
                ], 422);
            }

            // Lấy danh sách VĐV đã duyệt cho nội dung này
            $approvedAthletes = TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->where('status', 'approved')
                ->get();

            if ($approvedAthletes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có VĐV nào đã duyệt cho nội dung này'
                ], 422);
            }

            // Kiểm tra xem các bảng có tồn tại không
             $existingGroups = Group::where('tournament_id', $tournament->id)
                 ->where('category_id', $categoryId)
                 ->get();
            
             if ($existingGroups->isEmpty()) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Vui lòng tạo bảng trước khi bốc thăm'
                 ], 422);
             }

             // ✅ VALIDATE: Kiểm tra tổng sức chứa của các bảng
             $totalCapacity = $existingGroups->sum('max_participants');
             $totalAthletes = $approvedAthletes->count();

             if ($totalAthletes > $totalCapacity) {
                 return response()->json([
                     'success' => false,
                     'message' => "Không đủ chỗ trống. Bạn có {$totalAthletes} VĐV nhưng các bảng chỉ có sức chứa {$totalCapacity}. Vui lòng tạo thêm bảng hoặc tăng số VĐV tối đa của bảng."
                 ], 422);
             }
            
             // Xử lý bốc thăm
             if ($drawMethod === 'auto') {
                 $this->drawAthletesByRandom($approvedAthletes, $existingGroups);
             } elseif ($drawMethod === 'seeded') {
                 $this->drawAthletesBySeeding($approvedAthletes, $existingGroups);
             }

            // Refresh groups from DB để lấy dữ liệu mới nhất sau update
            $refreshedGroups = Group::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->get();

            // Tạo matches tự động cho mỗi group (round-robin)
            $this->createMatchesForGroups($tournament, $categoryId, $refreshedGroups);

            $results = $this->getGroupedAthletes($refreshedGroups);

            return response()->json([
                'success' => true,
                'message' => 'Bốc thăm thành công',
                'athletes' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Draw athletes error: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi bốc thăm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bốc thăm ngẫu nhiên
     */
    private function drawAthletesByRandom($athletes, $groups)
    {
        // Xáo trộn danh sách VĐV
        $shuffled = $athletes->shuffle();

        // Reset group collection index để tránh sai lệch index
        $groupsCollection = $groups->values();

        // Chia đều vào các bảng
        $groupIndex = 0;
        $drawCount = 0;

        foreach ($shuffled as $athlete) {
            $group = $groupsCollection[$groupIndex % $groupsCollection->count()];

            // Lưu group_id vào tournament_athletes
            $updated = $athlete->update([
                'group_id' => $group->id,
                'seed_number' => null
            ]);

            Log::info('After update - checking DB', [
                'athlete_id' => $athlete->id,
                'db_group_id' => DB::table('tournament_athletes')->where('id', $athlete->id)->value('group_id'),
                'model_group_id' => $athlete->group_id,
                'updated_result' => $updated
            ]);

            if ($updated) {
                $drawCount++;
                Log::info('Draw athlete - Random', [
                    'athlete_id' => $athlete->id,
                    'athlete_name' => $athlete->athlete_name,
                    'category_id' => $athlete->category_id,
                    'group_id' => $group->id,
                    'group_name' => $group->group_name
                ]);
            }

            // Tạo GroupStanding record
            GroupStanding::updateOrCreate(
                [
                    'group_id' => $group->id,
                    'athlete_id' => $athlete->id,
                ],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'win_rate' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                    'is_advanced' => false,
                ]
            );

            $groupIndex++;
        }

        Log::info('Random draw completed', [
            'total_athletes' => $drawCount,
            'total_groups' => $groupsCollection->count()
        ]);

        // Cập nhật số lượng VĐV hiện tại trong từng bảng
        foreach ($groupsCollection as $group) {
            $count = TournamentAthlete::where('group_id', $group->id)->count();

            Log::info('Before update group', [
                'group_id' => $group->id,
                'query_group_id' => $group->id,
                'count_from_query' => $count,
                'all_with_group_id_8' => TournamentAthlete::where('group_id', 8)->count()
            ]);

            $group->update(['current_participants' => $count]);

            Log::info('Group participants updated', [
                'group_id' => $group->id,
                'group_name' => $group->group_name,
                'participants_count' => $count
            ]);
        }
    }

    /**
     * Bốc thăm theo hạt giống (seeding)
     */
    private function drawAthletesBySeeding($athletes, $groups)
    {
        // Sắp xếp VĐV theo position (hạt giống)
        $sorted = $athletes->sortBy('position')->values();

        // Reset group collection index để tránh sai lệch index
        $groupsCollection = $groups->values();

        // Chia vào các bảng theo cách "snake" (snake draft)
        $groupIndex = 0;
        $ascending = true;
        $drawCount = 0;

        foreach ($sorted as $index => $athlete) {
            $group = $groupsCollection[$groupIndex];

            // Lưu group_id và seed_number vào tournament_athletes
            $updated = $athlete->update([
                'group_id' => $group->id,
                'seed_number' => $index + 1
            ]);

            if ($updated) {
                $drawCount++;
                Log::info('Draw athlete - Seeded', [
                    'athlete_id' => $athlete->id,
                    'athlete_name' => $athlete->athlete_name,
                    'category_id' => $athlete->category_id,
                    'group_id' => $group->id,
                    'group_name' => $group->group_name,
                    'seed_number' => $index + 1
                ]);
            }

            // Tạo GroupStanding record
            GroupStanding::updateOrCreate(
                [
                    'group_id' => $group->id,
                    'athlete_id' => $athlete->id,
                ],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'win_rate' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                    'is_advanced' => false,
                ]
            );

            // Thay đổi hướng sau mỗi vòng (snake draft)
            if ($ascending) {
                $groupIndex++;
                if ($groupIndex >= $groups->count()) {
                    $groupIndex = $groups->count() - 1;
                    $ascending = false;
                }
            } else {
                $groupIndex--;
                if ($groupIndex < 0) {
                    $groupIndex = 0;
                    $ascending = true;
                }
            }
        }

        Log::info('Seeded draw completed', [
            'total_athletes' => $drawCount,
            'total_groups' => $groupsCollection->count()
        ]);

        // Cập nhật số lượng VĐV hiện tại trong từng bảng
        foreach ($groupsCollection as $group) {
            $count = TournamentAthlete::where('group_id', $group->id)->count();

            Log::info('Before update group', [
                'group_id' => $group->id,
                'query_group_id' => $group->id,
                'count_from_query' => $count,
                'all_with_group_id_8' => TournamentAthlete::where('group_id', 8)->count()
            ]);

            $group->update(['current_participants' => $count]);

            Log::info('Group participants updated', [
                'group_id' => $group->id,
                'group_name' => $group->group_name,
                'participants_count' => $count
            ]);
        }
    }

    /**
     * Tạo matches tự động cho mỗi group (round-robin)
     */
    private function createMatchesForGroups($tournament, $categoryId, $groups)
    {
        try {
            foreach ($groups as $group) {
                // Lấy tất cả VĐV trong group này
                $athletes = TournamentAthlete::where('group_id', $group->id)
                    ->get();

                if ($athletes->count() < 2) {
                    continue; // Bỏ qua nếu không đủ 2 VĐV
                }

                // Tạo matches round-robin (mỗi VĐV đấu với mọi VĐV khác)
                $matchCount = MatchModel::where('tournament_id', $tournament->id)
                    ->where('category_id', $categoryId)
                    ->where('group_id', $group->id)
                    ->count();

                for ($i = 0; $i < $athletes->count(); $i++) {
                    for ($j = $i + 1; $j < $athletes->count(); $j++) {
                        $athlete1 = $athletes[$i];
                        $athlete2 = $athletes[$j];

                        // Get athlete names
                        $athlete1Name = $athlete1->athlete_name ?? ($athlete1->user ? $athlete1->user->name : 'Unknown');
                        $athlete2Name = $athlete2->athlete_name ?? ($athlete2->user ? $athlete2->user->name : 'Unknown');

                        $matchCount++;

                        MatchModel::create([
                            'tournament_id' => $tournament->id,
                            'athlete1_id' => $athlete1->id,
                            'athlete1_name' => $athlete1Name,
                            'athlete2_id' => $athlete2->id,
                            'athlete2_name' => $athlete2Name,
                            'category_id' => $categoryId,
                            'group_id' => $group->id,
                            'match_number' => 'M' . $matchCount,
                            'status' => 'scheduled',
                            'match_date' => now()->toDateString()
                        ]);

                        Log::info('Auto-created match for group', [
                            'group_id' => $group->id,
                            'group_name' => $group->group_name,
                            'athlete1_id' => $athlete1->id,
                            'athlete1_name' => $athlete1Name,
                            'athlete2_id' => $athlete2->id,
                            'athlete2_name' => $athlete2Name,
                            'match_number' => 'M' . $matchCount
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error creating matches for groups: ' . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách VĐV đã chia theo bảng
     */
    private function getGroupedAthletes($groups)
    {
        $result = [];

        foreach ($groups as $group) {
            $athletes = TournamentAthlete::where('group_id', $group->id)
                ->orderBy('seed_number')
                ->orderBy('id')
                ->get();

            $result[] = [
                'group_id' => $group->id,
                'group_name' => $group->group_name,
                'group_code' => $group->group_code,
                'athletes' => $athletes->map(function ($athlete) {
                    return [
                        'id' => $athlete->id,
                        'name' => $athlete->athlete_name,
                        'seed_number' => $athlete->seed_number,
                        'position' => $athlete->position
                    ];
                })->toArray()
            ];
        }

        return $result;
    }

    /**
     * Lấy kết quả bốc thăm
     */
    public function getDrawResults(Request $request, Tournament $tournament)
    {
        $categoryId = $request->input('category_id');

        if (!$categoryId) {
            return response()->json([
                'success' => false,
                'message' => 'category_id is required'
            ], 422);
        }

        $groups = Group::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $this->getGroupedAthletes($groups)
        ]);
    }

    /**
     * Xóa kết quả bốc thăm (reset)
     */
    public function resetDraw(Request $request, Tournament $tournament)
    {
        try {
            $this->authorize('update', $tournament);

            $categoryId = $request->input('category_id');

            if (!$categoryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'category_id is required'
                ], 422);
            }

            // Xóa gán bảng cho tất cả VĐV của nội dung này
            TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->update([
                    'group_id' => null,
                    'seed_number' => null
                ]);

            // Reset số lượng VĐV trong các bảng
            $groups = Group::where('tournament_id', $tournament->id)
                ->where('category_id', $request->category_id)
                ->get();

            foreach ($groups as $group) {
                $group->update(['current_participants' => 0]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa kết quả bốc thăm'
            ]);
        } catch (\Exception $e) {
            Log::error('Reset draw error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi reset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xuất danh sách VĐV ra CSV
     */
    public function exportAthletes(Tournament $tournament)
    {
        try {
            // Kiểm tra quyền
            $this->authorize('view', $tournament);

            // Lấy danh sách VĐV
            $athletes = TournamentAthlete::where('tournament_id', $tournament->id)
                ->with('category')
                ->get();

            if ($athletes->isEmpty()) {
                return back()->with('error', 'Giải đấu không có vận động viên nào để xuất');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }

        // Tạo tên file
        $fileName = 'VDV_' . str_replace(' ', '_', $tournament->name) . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Tạo CSV content với proper formatting
        $output = fopen('php://temp', 'r+');
        
        // BOM cho UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Header
        $headers = ['STT', 'Tên Vận Động Viên', 'Email', 'Số Điện Thoại', 'Nội Dung Thi Đấu', 'Trạng Thái', 'Trạng Thái Thanh Toán', 'Ngày Đăng Ký'];
        fputcsv($output, $headers);

        // Dữ liệu
        $stt = 1;
        foreach ($athletes as $athlete) {
            $status = $this->getStatusText($athlete->status);
            $paymentStatus = $this->getPaymentStatusText($athlete->payment_status);
            $categoryName = $athlete->category ? $athlete->category->category_name : '-';
            $registeredDate = $athlete->created_at ? $athlete->created_at->format('d/m/Y H:i') : '-';

            $email = $athlete->email ?? '-';
            // Thêm apostrophe để Excel không convert số điện thoại thành scientific notation
            $phone = "'" . ($athlete->phone ?? '-');

            $row = [
                $stt,
                $athlete->athlete_name,
                $email,
                $phone,
                $categoryName,
                $status,
                $paymentStatus,
                $registeredDate
            ];
            fputcsv($output, $row);
            $stt++;
        }

        // Lấy nội dung CSV
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Return as download
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Lấy text trạng thái VĐV
     */
    private function getStatusText($status)
    {
        $statusMap = [
            'pending' => 'Chờ phê duyệt',
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Từ chối'
        ];
        return $statusMap[$status] ?? $status;
    }

    /**
     * Lấy text trạng thái thanh toán
     */
    private function getPaymentStatusText($status)
    {
        $statusMap = [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'unpaid' => 'Chưa thanh toán'
        ];
        return $statusMap[$status] ?? $status;
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
            'duration_hours' => 'required|integer|min:1',
            'hourly_rate' => 'required|integer|min:0',
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
        
        $durationHours = $request->duration_hours;
        $endTime = $startTime->modify('+' . $durationHours . ' hours');

        // Recalculate total price on server side with multi-price support
        $totalPrice = $this->calculateBookingTotalPrice(
            $court->id, 
            $request->booking_date, 
            $request->start_time, 
            $durationHours
        );

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
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $endTime->format('H:i'),
            'duration_hours' => $durationHours,
            'hourly_rate' => $request->hourly_rate,
            'total_price' => $totalPrice,
            'status' => $request->status ?? 'pending',
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

    public function getAvailableSlots(Court $court, Request $request)
    {
        try {
            $date = $request->query('date');

            // Default time slots (6:00 - 21:00)
            $timeSlots = [];
            for ($hour = 6; $hour < 21; $hour++) {
                $time = sprintf('%02d:00', $hour);
                $timeSlots[] = $time;
            }

            // Get booked slots for this date
            $bookings = Booking::where('court_id', $court->id)
                ->where('booking_date', $date)
                ->where('status', '!=', 'cancelled')
                ->get(['start_time', 'end_time']);

            $bookedSlots = [];
            foreach ($bookings as $booking) {
                $bookedSlots[] = [
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
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
                'message' => 'Không thể lấy dữ liệu khoảng thời gian'
            ]);
        }
    }

    public function getBookingsByDate(Request $request)
    {
        $date = $request->query('date');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Default to today if no date provided
        if (!$date && !$dateFrom && !$dateTo) {
            $date = now()->toDateString();
        }

        // Build query
        $query = Booking::where('status', '!=', 'cancelled');

        // Filter by single date or date range
        if ($date) {
            $query->where('booking_date', $date);
        } else {
            if ($dateFrom) {
                $query->whereDate('booking_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('booking_date', '<=', $dateTo);
            }
        }

        // Get bookings
        $bookings = $query->get([
            'id',
            'court_id',
            'customer_name',
            'booking_date',
            'start_time',
            'end_time',
            'status',
        ])
        ->map(function ($booking) {
            return [
                'id' => $booking->id,
                'court_id' => $booking->court_id,
                'customer_name' => $booking->customer_name,
                'booking_date' => $booking->booking_date,
                'start_time' => substr($booking->start_time, 0, 5),
                'end_time' => substr($booking->end_time, 0, 5),
                'status' => $booking->status,
            ];
        });

        return response()->json([
            'success' => true,
            'bookings' => $bookings,
            'date' => $date,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
        
    }

    public function getBookingStats(Request $request)
    {
        $stadiums = Stadium::where('user_id', auth()->id())->pluck('id');
        $courtsOfUser = Court::whereIn('stadium_id', $stadiums)->pluck('id');

        // Get current month's bookings
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $bookings = Booking::whereIn('court_id', $courtsOfUser)
            ->whereBetween('booking_date', [$startOfMonth, $endOfMonth]);

        $total = $bookings->count();
        $confirmed = $bookings->clone()->where('status', 'confirmed')->count();
        $pending = $bookings->clone()->where('status', 'pending')->count();
        $cancelled = Booking::whereIn('court_id', $courtsOfUser)
            ->whereBetween('booking_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'cancelled')
            ->count();

        return response()->json([
            'success' => true,
            'total' => $total,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'cancelled' => $cancelled,
        ]);
    }

    public function getAllBookings(Request $request)
    {
        try {
            $stadiums = Stadium::where('user_id', auth()->id())->pluck('id');
            $courtsOfUser = Court::whereIn('stadium_id', $stadiums)->pluck('id');

            $bookings = Booking::whereIn('court_id', $courtsOfUser)
                ->with('court')
                ->latest('booking_date')
                ->latest('start_time')
                ->get([
                    'id',
                    'court_id',
                    'customer_name',
                    'customer_phone',
                    'booking_date',
                    'start_time',
                    'end_time',
                    'total_price',
                    'status',
                ])
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'court_id' => $booking->court_id,
                        'court_name' => $booking->court?->court_name ?? 'Sân',
                        'customer_name' => $booking->customer_name,
                        'customer_phone' => $booking->customer_phone,
                        'booking_date' => $booking->booking_date,
                        'start_time' => $booking->start_time,
                        'end_time' => $booking->end_time,
                        'total_price' => $booking->total_price,
                        'status' => $booking->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'bookings' => $bookings,
            ]);
        } catch (\Exception $e) {
            Log::error('Get all bookings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách đơn đặt sân'
            ]);
        }
    }

    public function searchBookings(Request $request)
    {
        try {
            $stadiums = Stadium::where('user_id', auth()->id())->pluck('id');
            $courtsOfUser = Court::whereIn('stadium_id', $stadiums)->pluck('id');

            $search = $request->query('search', '');
            $status = $request->query('status', '');
            $courtId = $request->query('court_id', '');
            $dateFrom = $request->query('date_from', '');
            $dateTo = $request->query('date_to', '');
            $page = $request->query('page', 1);
            $perPage = 10;

            // Build query
            $query = Booking::whereIn('court_id', $courtsOfUser)
                ->with('court');

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply court filter
            if ($courtId) {
                $query->where('court_id', $courtId);
            }

            // Apply date range filter
            if ($dateFrom) {
                $query->whereDate('booking_date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('booking_date', '<=', $dateTo);
            }

            // Order and paginate
            $bookings = $query->latest('booking_date')
                ->latest('start_time')
                ->paginate($perPage, ['*'], 'page', $page);

            // Get unique courts for filter dropdown
            $courts = Court::whereIn('stadium_id', $stadiums)
                ->where('is_active', true)
                ->select('id', 'court_name')
                ->orderBy('court_name')
                ->get()
                ->map(function ($court) {
                    return [
                        'id' => $court->id,
                        'name' => $court->court_name
                    ];
                });

            // Format bookings data
            $formattedBookings = $bookings->getCollection()->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'court_id' => $booking->court_id,
                    'court_name' => $booking->court?->court_name ?? 'Sân',
                    'customer_name' => $booking->customer_name,
                    'customer_phone' => $booking->customer_phone,
                    'booking_date' => $booking->booking_date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                ];
            });

            return response()->json([
                'success' => true,
                'bookings' => [
                    'data' => $formattedBookings
                ],
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'courts' => $courts,
            ]);
        } catch (\Exception $e) {
            Log::error('Search bookings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tìm kiếm đơn đặt sân'
            ]);
        }
    }

    public function getBookingDetails($bookingId)
    {
        try {
            $stadiums = Stadium::where('user_id', auth()->id())->pluck('id');
            $courtsOfUser = Court::whereIn('stadium_id', $stadiums)->pluck('id');

            $booking = Booking::whereIn('court_id', $courtsOfUser)
                ->with('court')
                ->findOrFail($bookingId);

            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'court_id' => $booking->court_id,
                    'court_name' => $booking->court?->court_name ?? 'Sân',
                    'customer_name' => $booking->customer_name,
                    'customer_phone' => $booking->customer_phone,
                    'customer_email' => $booking->customer_email,
                    'booking_date' => $booking->booking_date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'duration_hours' => $booking->duration_hours,
                    'hourly_rate' => $booking->hourly_rate,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                    'payment_method' => $booking->payment_method,
                    'notes' => $booking->notes,
                    'created_at' => $booking->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get booking details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải chi tiết đơn đặt'
            ], 404);
        }
    }

    public function storeMatch(Request $request, Tournament $tournament)
    {
        try {
            $this->authorize('update', $tournament);

            $validated = $request->validate([
                'athlete1_id' => 'required|exists:tournament_athletes,id',
                'athlete2_id' => 'required|exists:tournament_athletes,id',
                'category_id' => 'required|exists:tournament_categories,id',
                'round_id' => 'nullable|exists:rounds,id',
                'group_id' => 'nullable|exists:groups,id',
            ]);

            // Ensure both athletes are from this tournament
            $athlete1 = TournamentAthlete::where('id', $validated['athlete1_id'])
                ->where('tournament_id', $tournament->id)
                ->firstOrFail();

            $athlete2 = TournamentAthlete::where('id', $validated['athlete2_id'])
                ->where('tournament_id', $tournament->id)
                ->firstOrFail();

            // Verify both athletes belong to the selected category
            if ($athlete1->category_id != $validated['category_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'VĐV 1 không thuộc nội dung thi đấu đã chọn'
                ], 422);
            }

            if ($athlete2->category_id != $validated['category_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'VĐV 2 không thuộc nội dung thi đấu đã chọn'
                ], 422);
            }

            // Verify athletes are different
            if ($validated['athlete1_id'] == $validated['athlete2_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'VĐV 1 và VĐV 2 phải khác nhau'
                ], 422);
            }

            // Generate match number
            $matchCount = MatchModel::where('tournament_id', $tournament->id)
                ->where('category_id', $validated['category_id'])
                ->count();
            $matchNumber = 'M' . ($matchCount + 1);

            // Get athlete names (prioritize athlete_name from tournament_athletes)
            $athlete1Name = $athlete1->athlete_name ?? ($athlete1->user ? $athlete1->user->name : 'Unknown');
            $athlete2Name = $athlete2->athlete_name ?? ($athlete2->user ? $athlete2->user->name : 'Unknown');

            // Create match
            $match = MatchModel::create([
                'tournament_id' => $tournament->id,
                'athlete1_id' => $validated['athlete1_id'],
                'athlete1_name' => $athlete1Name,
                'athlete2_id' => $validated['athlete2_id'],
                'athlete2_name' => $athlete2Name,
                'category_id' => $validated['category_id'],
                'round_id' => $validated['round_id'],
                'group_id' => $validated['group_id'] ?? null,
                'match_number' => $matchNumber,
                'status' => 'scheduled',
                'match_date' => now()->toDateString()
            ]);

            Log::info('Match created', ['match_id' => $match->id]);

            return response()->json([
                'success' => true,
                'message' => 'Trận đấu đã được tạo thành công',
                'match' => $match
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Lỗi validate: ' . collect($e->errors())->flatten()->join(', '),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Create match error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get match data (for AJAX)
     */
    public function getMatch(Tournament $tournament, $match)
    {
        try {
            $this->authorize('update', $tournament);
            
            // Resolve match by ID
            $match = MatchModel::findOrFail($match);
            
            // Verify match belongs to tournament
            if ($match->tournament_id != $tournament->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trận đấu không thuộc giải đấu này'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'match' => $match
            ]);
        } catch (\Exception $e) {
            Log::error('Get match error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a match (scores, status, and calculate winner)
     */
    public function updateMatch(Request $request, Tournament $tournament, $match)
    {
        try {
            $this->authorize('update', $tournament);
            
            // Resolve match by ID
            $match = MatchModel::findOrFail($match);
            
            // Verify match belongs to tournament
            if ($match->tournament_id != $tournament->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trận đấu không thuộc giải đấu này'
                ], 403);
            }

            // Handle score update (new route)
            if ($request->has('athlete1_score') || $request->has('athlete2_score')) {
                return $this->updateMatchScore($request, $match);
            }

            // Handle original match update (setup/edit)
            $validated = $request->validate([
                'athlete1_id' => 'required|exists:tournament_athletes,id',
                'athlete2_id' => 'required|exists:tournament_athletes,id',
                'category_id' => 'required|exists:tournament_categories,id',
                'round_id' => 'required|exists:rounds,id',
            ]);

            $match->update($validated);

            Log::info('Match updated', ['match_id' => $match->id]);

            return response()->json([
                'success' => true,
                'message' => 'Trận đấu đã được cập nhật thành công',
                'match' => $match
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Lỗi validate: ' . collect($e->errors())->flatten()->join(', '),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update match error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update match score and group standings
     */
    private function updateMatchScore(Request $request, MatchModel $match)
    {
        try {
            $validated = $request->validate([
                'athlete1_score' => 'required|integer|min:0',
                'athlete2_score' => 'required|integer|min:0',
                'status' => 'nullable|in:in_progress,completed,scheduled,cancelled',
                'final_score' => 'nullable|string',
                'action' => 'nullable|in:update,end_set,end_match',
            ]);

            DB::beginTransaction();

            $actionType = $validated['action'] ?? 'update';

            if ($actionType === 'end_set') {
                // Kết thúc set - lưu điểm vào set_scores
                $this->handleEndSet($match, $validated);
            } elseif ($actionType === 'end_match') {
                // Kết thúc trận - cập nhật status thành completed
                $this->handleEndMatch($match, $validated);
            } else {
                // Cập nhật thường (chỉ update điểm, không làm gì thêm)
                $this->handleRegularUpdate($match, $validated);
            }

            DB::commit();

            Log::info('Match score updated', [
                'match_id' => $match->id,
                'action' => $actionType,
                'winner_id' => $match->winner_id,
                'athlete1_score' => $validated['athlete1_score'],
                'athlete2_score' => $validated['athlete2_score']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật kết quả trận đấu thành công',
                'match' => $match
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update match score error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle end set action - save set score to set_scores JSON
     */
    private function handleEndSet(MatchModel $match, array $validated)
    {
        $setScores = $match->set_scores ? json_decode($match->set_scores, true) : [];
        if (!is_array($setScores)) {
            $setScores = [];
        }

        // Thêm set mới vào mảng
        $setScores[] = [
            'athlete1_score' => $validated['athlete1_score'],
            'athlete2_score' => $validated['athlete2_score'],
            'completed_at' => now()->toDateTimeString()
        ];

        // Cập nhật match
        $match->update([
            'set_scores' => json_encode($setScores),
            'status' => 'in_progress'
        ]);

        // Reset điểm cho set kế tiếp
        $match->athlete1_score = 0;
        $match->athlete2_score = 0;
        $match->save();
        
        // Log activity
        $setNumber = count($setScores);
        ActivityLog::log("Set {$setNumber} của trận đấu kết thúc với tỉ số {$validated['athlete1_score']}-{$validated['athlete2_score']}", 'Match', $match->id);
    }

    /**
     * Handle end match action - mark match as completed
     */
    private function handleEndMatch(MatchModel $match, array $validated)
    {
        // Lưu set cuối cùng vào set_scores nếu có điểm
        if ($validated['athlete1_score'] > 0 || $validated['athlete2_score'] > 0) {
            $setScores = $match->set_scores ? json_decode($match->set_scores, true) : [];
            if (!is_array($setScores)) {
                $setScores = [];
            }

            $setScores[] = [
                'athlete1_score' => $validated['athlete1_score'],
                'athlete2_score' => $validated['athlete2_score'],
                'completed_at' => now()->toDateTimeString()
            ];

            $match->set_scores = json_encode($setScores);
        }

        // Cập nhật match - đánh dấu hoàn thành
        $match->athlete1_score = $validated['athlete1_score'];
        $match->athlete2_score = $validated['athlete2_score'];
        $match->status = 'completed';
        $match->final_score = $validated['final_score'];

        // Xác định người thắng cuộc dựa trên final_score
        // Parse final_score (ví dụ "11-9, 11-8" hoặc "11-9")
        $setsWonAthlete1 = 0;
        $setsWonAthlete2 = 0;
        
        if ($validated['final_score']) {
            $sets = explode(',', $validated['final_score']);
            foreach ($sets as $set) {
                $scores = explode('-', trim($set));
                if (count($scores) === 2) {
                    $score1 = (int)trim($scores[0]);
                    $score2 = (int)trim($scores[1]);
                    
                    if ($score1 > $score2) {
                        $setsWonAthlete1++;
                    } elseif ($score2 > $score1) {
                        $setsWonAthlete2++;
                    }
                }
            }
        }

        // Xác định winner
        if ($setsWonAthlete1 > $setsWonAthlete2) {
            $match->winner_id = $match->athlete1_id;
        } elseif ($setsWonAthlete2 > $setsWonAthlete1) {
            $match->winner_id = $match->athlete2_id;
        } else {
            // Hòa set
            $match->winner_id = null;
        }

        // Đánh dấu thời gian kết thúc
        if (!$match->actual_end_time) {
            $match->actual_end_time = now();
        }

        $match->save();
        
        // Log activity
        $winnerName = $match->winner_id ? ($match->winner_id === $match->athlete1_id ? $match->athlete1->athlete_name : $match->athlete2->athlete_name) : 'Hòa';
        ActivityLog::log("Trận đấu kết thúc với kết quả {$validated['final_score']} - Người thắng: {$winnerName}", 'Match', $match->id);

         // Cập nhật standings nếu trận này thuộc một group
          if ($match->group_id && $match->athlete1_id && $match->athlete2_id) {
              $this->updateGroupStandingsWithSets($match, $setsWonAthlete1, $setsWonAthlete2);
          }

          // Cập nhật thống kê vào bảng tournament_athlete
          $this->updateTournamentAthleteStats($match, $setsWonAthlete1, $setsWonAthlete2);
         }

    /**
     * Handle regular update - just update scores without ending match
     */
    private function handleRegularUpdate(MatchModel $match, array $validated)
    {
        // Determine if match was just completed
        $wasCompleted = $match->status === 'completed';
        $isNowCompleted = ($validated['status'] ?? $match->status) === 'completed';
        $justCompleted = !$wasCompleted && $isNowCompleted;

        // Update match scores and status
        $match->update([
            'athlete1_score' => $validated['athlete1_score'],
            'athlete2_score' => $validated['athlete2_score'],
            'status' => $validated['status'] ?? $match->status,
            'final_score' => $validated['final_score'] ?? $match->final_score,
        ]);

        // If match is being marked as completed for the first time
        if ($justCompleted && $match->athlete1_id && $match->athlete2_id) {
            // Determine winner based on scores
            if ($validated['athlete1_score'] > $validated['athlete2_score']) {
                $match->winner_id = $match->athlete1_id;
            } elseif ($validated['athlete2_score'] > $validated['athlete1_score']) {
                $match->winner_id = $match->athlete2_id;
            } else {
                $match->winner_id = null;
            }

            // Mark actual end time
            if (!$match->actual_end_time) {
                $match->actual_end_time = now();
            }

            $match->save();

            // Update group standings if the match has a group
            if ($match->group_id) {
                $this->updateGroupStandings($match);
            }

            // Update tournament athlete statistics
            // For handleRegularUpdate, we use current scores to determine sets won
            // This assumes athlete1_score and athlete2_score are the final set scores
            $setsWonAthlete1 = $validated['athlete1_score'] > $validated['athlete2_score'] ? 1 : 0;
            $setsWonAthlete2 = $validated['athlete2_score'] > $validated['athlete1_score'] ? 1 : 0;
            
            // If scores are equal, treat as draw
            if ($validated['athlete1_score'] === $validated['athlete2_score']) {
                $setsWonAthlete1 = 0;
                $setsWonAthlete2 = 0;
            }

            $this->updateTournamentAthleteStats($match, $setsWonAthlete1, $setsWonAthlete2);
        }
    }

    /**
     * Update group standings with set information
     */
    private function updateGroupStandingsWithSets(MatchModel $match, int $setsWonAthlete1, int $setsWonAthlete2)
    {
        try {
            $athlete1Id = $match->athlete1_id;
            $athlete2Id = $match->athlete2_id;
            $groupId = $match->group_id;

            // Get or create standings for both athletes
            $standing1 = GroupStanding::firstOrCreate(
                ['group_id' => $groupId, 'athlete_id' => $athlete1Id],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                ]
            );

            $standing2 = GroupStanding::firstOrCreate(
                ['group_id' => $groupId, 'athlete_id' => $athlete2Id],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                ]
            );

            // Determine winner and update standings based on sets won
            if ($setsWonAthlete1 > $setsWonAthlete2) {
                // Athlete 1 wins - thêm sets_won cho athlete1, sets_lost cho athlete2
                $standing1->updateAfterMatch(true, $setsWonAthlete1, $setsWonAthlete2, 0, 0);
                $standing2->updateAfterMatch(false, $setsWonAthlete2, $setsWonAthlete1, 0, 0);
            } elseif ($setsWonAthlete2 > $setsWonAthlete1) {
                // Athlete 2 wins
                $standing1->updateAfterMatch(false, $setsWonAthlete1, $setsWonAthlete2, 0, 0);
                $standing2->updateAfterMatch(true, $setsWonAthlete2, $setsWonAthlete1, 0, 0);
            } else {
                // Draw
                $standing1->update([
                    'matches_played' => $standing1->matches_played + 1,
                    'matches_drawn' => $standing1->matches_drawn + 1,
                    'sets_won' => $standing1->sets_won + $setsWonAthlete1,
                    'sets_lost' => $standing1->sets_lost + $setsWonAthlete2,
                ]);
                $standing2->update([
                    'matches_played' => $standing2->matches_played + 1,
                    'matches_drawn' => $standing2->matches_drawn + 1,
                    'sets_won' => $standing2->sets_won + $setsWonAthlete2,
                    'sets_lost' => $standing2->sets_lost + $setsWonAthlete1,
                ]);
            }

            // Recalculate rankings for the group
            $this->recalculateGroupRankings($groupId);

            Log::info('Group standings updated with sets', [
                'group_id' => $groupId,
                'match_id' => $match->id,
                'sets_won_athlete1' => $setsWonAthlete1,
                'sets_won_athlete2' => $setsWonAthlete2,
            ]);
        } catch (\Exception $e) {
            Log::error('Update group standings error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update group standings after a match completes
     */
    private function updateGroupStandings(MatchModel $match)
    {
        try {
            $athlete1Id = $match->athlete1_id;
            $athlete2Id = $match->athlete2_id;
            $groupId = $match->group_id;

            $athlete1Score = $match->athlete1_score;
            $athlete2Score = $match->athlete2_score;

            // Get or create standings for both athletes
            $standing1 = GroupStanding::firstOrCreate(
                ['group_id' => $groupId, 'athlete_id' => $athlete1Id],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                ]
            );

            $standing2 = GroupStanding::firstOrCreate(
                ['group_id' => $groupId, 'athlete_id' => $athlete2Id],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                ]
            );

            // Determine winner and update standings
            if ($athlete1Score > $athlete2Score) {
                // Athlete 1 wins
                $standing1->updateAfterMatch(true, 0, 0, $athlete1Score, $athlete2Score);
                $standing2->updateAfterMatch(false, 0, 0, $athlete2Score, $athlete1Score);
            } elseif ($athlete2Score > $athlete1Score) {
                // Athlete 2 wins
                $standing1->updateAfterMatch(false, 0, 0, $athlete1Score, $athlete2Score);
                $standing2->updateAfterMatch(true, 0, 0, $athlete2Score, $athlete1Score);
            } else {
                // Draw
                $standing1->update([
                    'matches_played' => $standing1->matches_played + 1,
                    'matches_drawn' => $standing1->matches_drawn + 1,
                    'games_won' => $standing1->games_won + $athlete1Score,
                    'games_lost' => $standing1->games_lost + $athlete2Score,
                ]);
                $standing2->update([
                    'matches_played' => $standing2->matches_played + 1,
                    'matches_drawn' => $standing2->matches_drawn + 1,
                    'games_won' => $standing2->games_won + $athlete2Score,
                    'games_lost' => $standing2->games_lost + $athlete1Score,
                ]);
            }

            // Recalculate rankings for the group
            $this->recalculateGroupRankings($groupId);

            Log::info('Group standings updated', [
                'group_id' => $groupId,
                'match_id' => $match->id
            ]);
        } catch (\Exception $e) {
            Log::error('Update group standings error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update tournament athlete statistics
     * Cập nhật matches_played, matches_won, matches_lost, sets_won, sets_lost vào bảng tournament_athlete
     */
    private function updateTournamentAthleteStats(MatchModel $match, int $setsWonAthlete1, int $setsWonAthlete2)
    {
        try {
            $athlete1Id = $match->athlete1_id;
            $athlete2Id = $match->athlete2_id;

            if (!$athlete1Id || !$athlete2Id) {
                return;
            }

            // Get both athletes
            $athlete1 = TournamentAthlete::find($athlete1Id);
            $athlete2 = TournamentAthlete::find($athlete2Id);

            if (!$athlete1 || !$athlete2) {
                return;
            }

            // Determine winner
            $athlete1Wins = $setsWonAthlete1 > $setsWonAthlete2;
            $athlete2Wins = $setsWonAthlete2 > $setsWonAthlete1;

            // Update athlete1 statistics
            $athlete1->matches_played = ($athlete1->matches_played ?? 0) + 1;
            $athlete1->sets_won = ($athlete1->sets_won ?? 0) + $setsWonAthlete1;
            $athlete1->sets_lost = ($athlete1->sets_lost ?? 0) + $setsWonAthlete2;

            if ($athlete1Wins) {
                $athlete1->matches_won = ($athlete1->matches_won ?? 0) + 1;
            } elseif (!$athlete2Wins) {
                // Draw - không tính vào matches_won hay matches_lost
            } else {
                $athlete1->matches_lost = ($athlete1->matches_lost ?? 0) + 1;
            }

            $athlete1->save();

            // Update athlete2 statistics
            $athlete2->matches_played = ($athlete2->matches_played ?? 0) + 1;
            $athlete2->sets_won = ($athlete2->sets_won ?? 0) + $setsWonAthlete2;
            $athlete2->sets_lost = ($athlete2->sets_lost ?? 0) + $setsWonAthlete1;

            if ($athlete2Wins) {
                $athlete2->matches_won = ($athlete2->matches_won ?? 0) + 1;
            } elseif (!$athlete1Wins) {
                // Draw - không tính vào matches_won hay matches_lost
            } else {
                $athlete2->matches_lost = ($athlete2->matches_lost ?? 0) + 1;
            }

            $athlete2->save();

            Log::info('Tournament athlete stats updated', [
                'match_id' => $match->id,
                'athlete1_id' => $athlete1Id,
                'athlete1_matches_played' => $athlete1->matches_played,
                'athlete1_matches_won' => $athlete1->matches_won,
                'athlete1_matches_lost' => $athlete1->matches_lost,
                'athlete1_sets_won' => $athlete1->sets_won,
                'athlete1_sets_lost' => $athlete1->sets_lost,
                'athlete2_id' => $athlete2Id,
                'athlete2_matches_played' => $athlete2->matches_played,
                'athlete2_matches_won' => $athlete2->matches_won,
                'athlete2_matches_lost' => $athlete2->matches_lost,
                'athlete2_sets_won' => $athlete2->sets_won,
                'athlete2_sets_lost' => $athlete2->sets_lost,
            ]);
        } catch (\Exception $e) {
            Log::error('Update tournament athlete stats error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Recalculate rankings for a group
     */
    private function recalculateGroupRankings($groupId)
    {
        try {
            $standings = GroupStanding::where('group_id', $groupId)
                ->get()
                ->sortByDesc('points')
                ->sortByDesc('matches_won')
                ->sortByDesc(function ($standing) {
                    return ($standing->games_won ?? 0) - ($standing->games_lost ?? 0);
                })
                ->values();

            foreach ($standings as $index => $standing) {
                $standing->update([
                    'rank_position' => $index + 1,
                    'win_rate' => $standing->calculateWinRate(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Recalculate group rankings error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a match
     */
    public function destroyMatch(Tournament $tournament, $match)
    {
        try {
            $this->authorize('update', $tournament);
            
            // Resolve match by ID
            $match = MatchModel::findOrFail($match);
            
            // Verify match belongs to tournament
            if ($match->tournament_id != $tournament->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trận đấu không thuộc giải đấu này'
                ], 403);
            }

            $match->delete();

            Log::info('Match deleted', ['match_id' => $match->id]);

            if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Trận đấu đã được xóa'
                ]);
            }

            return redirect()->back()->with('success', 'Trận đấu đã được xóa thành công');
        } catch (\Exception $e) {
            Log::error('Delete match error: ' . $e->getMessage());

            if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Get athletes of a specific category
     */
    public function getCategoryAthletes(Tournament $tournament, $categoryId)
    {
        try {
            $this->authorize('update', $tournament);

            // Get approved athletes for this category
            $athletes = TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->where('status', 'approved')
                ->orderBy('athlete_name')
                ->get(['id', 'athlete_name']);

            return response()->json([
                'success' => true,
                'athletes' => $athletes
            ]);
        } catch (\Exception $e) {
            Log::error('Get category athletes error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get groups for a specific category
     */
    public function getCategoryGroups(Tournament $tournament, $categoryId)
    {
        try {
            $this->authorize('update', $tournament);

            // Get groups for this category
            $groups = Group::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->orderBy('group_name')
                ->get(['id', 'group_name']);

            return response()->json([
                'success' => true,
                'groups' => $groups
            ]);
        } catch (\Exception $e) {
            Log::error('Get category groups error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rankings/leaderboard data
     * Sorts by: Points (desc) > Wins (desc) > Games Differential (desc)
     */
    public function getRankings(Tournament $tournament, Request $request)
    {
        try {
            $this->authorize('view', $tournament);

            $categoryId = $request->query('category_id');
            $groupId = $request->query('group_id');

            // Base query for group standings
             // Join with groups and tournament_athletes to filter by tournament
             $query = GroupStanding::select([
                  'id', 'athlete_id', 'group_id', 'points', 'matches_played', 'matches_won',
                  'matches_lost', 'matches_drawn', 'win_rate', 'sets_won', 'sets_lost', 
                  'sets_differential', 'games_won', 'games_lost', 'games_differential', 'is_advanced'
              ])
             ->with([
                 'athlete' => function ($q) {
                     $q->select('id', 'athlete_name', 'category_id');
                 },
                 'athlete.category' => function ($q) {
                     $q->select('id', 'category_name');
                 }
             ])
             ->whereHas('group', function ($q) use ($tournament) {
                 $q->where('tournament_id', $tournament->id);
             });

            // Filter by category if provided
            if ($categoryId) {
                $query->whereHas('athlete', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }

            // Filter by group if provided
            if ($groupId) {
                $query->where('group_id', $groupId);
            }

            // Get standings and sort by: Points (desc) > Wins (desc) > Games Differential (desc)
             $standings = $query->get()
                 ->sortByDesc('points')
                 ->sortByDesc('matches_won')
                 ->sortByDesc(function ($standing) {
                     return ($standing->games_won ?? 0) - ($standing->games_lost ?? 0);
                 })
                 ->values();

            // Pagination
            $perPage = 10;
            $page = $request->query('page', 1);
            $totalCount = $standings->count();
            $totalPages = ceil($totalCount / $perPage);
            
            // Adjust page if out of range
            if ($page > $totalPages && $totalPages > 0) {
                $page = $totalPages;
            }
            $page = max(1, $page);
            
            // Get paginated results
            $offset = ($page - 1) * $perPage;
            $paginatedStandings = $standings->slice($offset, $perPage)->values();

            // Format ranking data with adjusted ranks for pagination
            $rankings = $paginatedStandings->map(function ($standing, $index) use ($offset) {
                $athlete = $standing->athlete;
                $category = $athlete ? $athlete->category : null;
                
                // Calculate win rate correctly (max 100%)
                $matchesPlayed = $standing->matches_played ?? 0;
                $matchesWon = $standing->matches_won ?? 0;
                $winRate = $matchesPlayed > 0 
                    ? round(($matchesWon / $matchesPlayed) * 100, 2)
                    : 0;

                return [
                    'rank' => $offset + $index + 1,
                    'athlete_id' => $standing->athlete_id,
                    'athlete_name' => $athlete ? $athlete->athlete_name : 'N/A',
                    'category_id' => $athlete ? $athlete->category_id : null,
                    'category_name' => $category ? $category->category_name : 'N/A',
                    'matches_played' => $matchesPlayed,
                    'matches_won' => $matchesWon,
                    'matches_lost' => $standing->matches_lost ?? 0,
                    'matches_drawn' => $standing->matches_drawn ?? 0,
                    'points' => $standing->points ?? 0,
                    'win_rate' => $winRate,
                    'sets_won' => $standing->sets_won ?? 0,
                    'sets_lost' => $standing->sets_lost ?? 0,
                    'sets_differential' => $standing->sets_differential ?? 0,
                    'games_won' => $standing->games_won ?? 0,
                    'games_lost' => $standing->games_lost ?? 0,
                    'games_differential' => $standing->games_differential ?? 0,
                    'is_advanced' => $standing->is_advanced ?? false,
                ];
            });

            // Count total completed matches and athletes (with proper filtering)
            $totalMatches = MatchModel::where('tournament_id', $tournament->id)
                ->where('status', 'completed')
                ->count();

            // Count total athletes with standings (only those in rankings) with filters applied
            $totalAthletesQuery = GroupStanding::whereHas('group', function ($q) use ($tournament) {
                $q->where('tournament_id', $tournament->id);
            });
            if ($categoryId) {
                $totalAthletesQuery = $totalAthletesQuery->whereHas('athlete', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }
            if ($groupId) {
                $totalAthletesQuery = $totalAthletesQuery->where('group_id', $groupId);
            }
            $totalAthletes = $totalAthletesQuery->count();

            return response()->json([
                'success' => true,
                'rankings' => $rankings->values()->all(),
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => $perPage,
                    'total' => $totalCount,
                    'total_pages' => $totalPages
                ],
                'total_matches' => $totalMatches,
                'total_athletes' => $totalAthletes,
                'filter' => [
                    'category_id' => $categoryId,
                    'group_id' => $groupId
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get rankings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportRankingsExcel(Tournament $tournament, Request $request)
    {
        try {
            $this->authorize('view', $tournament);

            $categoryId = $request->query('category_id');
            $groupId = $request->query('group_id');

            // Get standings
            $query = GroupStanding::select([
                'id', 'athlete_id', 'group_id', 'points', 'matches_played', 'matches_won',
                'matches_lost', 'matches_drawn', 'win_rate', 'sets_won', 'sets_lost',
                'sets_differential', 'games_won', 'games_lost', 'games_differential', 'is_advanced'
            ])
            ->with([
                'athlete' => function ($q) {
                    $q->select('id', 'athlete_name', 'category_id');
                },
                'athlete.category' => function ($q) {
                    $q->select('id', 'category_name');
                }
            ])
            ->whereHas('group', function ($q) use ($tournament) {
                $q->where('tournament_id', $tournament->id);
            });

            if ($categoryId) {
                $query->whereHas('athlete', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }

            if ($groupId) {
                $query->where('group_id', $groupId);
            }

            $standings = $query->get()
                ->sortByDesc('points')
                ->sortByDesc('matches_won')
                ->sortByDesc(function ($standing) {
                    return ($standing->games_won ?? 0) - ($standing->games_lost ?? 0);
                })
                ->values();

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Bảng Xếp Hạng');

            // Set headers
            $headers = ['Xếp Hạng', 'Tên VĐV', 'Nội Dung', 'Trận', 'Thắng', 'Thua', 'Điểm', 'Set', 'Hiệu Số Game', '% Thắng'];
            $sheet->fromArray([$headers], null, 'A1');

            // Style header
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

            // Add data
            $rowNum = 2;
            foreach ($standings as $index => $standing) {
                $athlete = $standing->athlete;
                $category = $athlete ? $athlete->category : null;
                $matchesPlayed = $standing->matches_played ?? 0;
                $matchesWon = $standing->matches_won ?? 0;
                $winRate = $matchesPlayed > 0
                    ? round(($matchesWon / $matchesPlayed) * 100, 2)
                    : 0;

                $row = [
                    $index + 1,
                    $athlete ? $athlete->athlete_name : 'N/A',
                    $category ? $category->category_name : 'N/A',
                    $matchesPlayed,
                    $matchesWon,
                    $standing->matches_lost ?? 0,
                    $standing->points ?? 0,
                    ($standing->sets_won ?? 0) . '/' . ($standing->sets_lost ?? 0),
                    ($standing->games_won ?? 0) - ($standing->games_lost ?? 0),
                    $winRate . '%'
                ];

                $sheet->fromArray([$row], null, 'A' . $rowNum);
                $rowNum++;
            }

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getColumnDimension('D')->setWidth(10);
            $sheet->getColumnDimension('E')->setWidth(10);
            $sheet->getColumnDimension('F')->setWidth(10);
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->getColumnDimension('H')->setWidth(12);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(12);

            // Center align numeric columns
            $sheet->getStyle('A2:J' . ($rowNum - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Download
            $writer = new Xlsx($spreadsheet);
            $filename = 'BangXepHang_' . date('Y-m-d_H-i-s') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Export rankings error: ' . $e->getMessage());
            return back()->with('error', 'Lỗi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Xuất danh sách giải đấu ra Excel
     */
    public function exportTournamentsList()
    {
        try {
            // Lấy tất cả giải đấu của user hiện tại
            $tournaments = Tournament::where('user_id', auth()->id())
                ->latest()
                ->get();

            if ($tournaments->isEmpty()) {
                return back()->with('error', 'Không có giải đấu nào để xuất');
            }

            // Tạo spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Danh Sách Giải Đấu');

            // Set headers
            $headers = ['STT', 'Tên Giải Đấu', 'Trạng Thái', 'Loại Giải', 'Địa Điểm', 'Ngày Bắt Đầu', 'Ngày Kết Thúc', 'Số VĐV', 'Lệ Phí', 'Giải Thưởng'];
            $sheet->fromArray([$headers], null, 'A1');

            // Style header
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true]
            ];
            $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Thêm dữ liệu
            $rowNum = 2;
            foreach ($tournaments as $index => $tournament) {
                // Xác định trạng thái
                $now = now();
                $startDate = \Carbon\Carbon::parse($tournament->start_date);
                $endDate = $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date) : null;
                
                if ($startDate > $now) {
                    $status = 'Sắp tới';
                } elseif ($startDate <= $now && ($endDate === null || $endDate >= $now)) {
                    $status = 'Đang diễn ra';
                } else {
                    $status = 'Đã kết thúc';
                }

                // Xác định loại giải
                $formatMap = [
                    'single' => 'Đơn',
                    'double' => 'Đôi',
                    'mixed' => 'Đôi nam nữ'
                ];
                $format = $formatMap[$tournament->competition_format] ?? 'Không xác định';

                // Lấy số VĐV
                $athleteCount = $tournament->athletes()->count();

                $row = [
                    $index + 1,
                    $tournament->name,
                    $status,
                    $format,
                    $tournament->location ?? 'N/A',
                    $tournament->start_date ? \Carbon\Carbon::parse($tournament->start_date)->format('d/m/Y') : 'N/A',
                    $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('d/m/Y') : 'N/A',
                    $athleteCount . '/' . ($tournament->max_participants ?? 'Không giới hạn'),
                    $tournament->price ? number_format($tournament->price, 0, ',', '.') . ' ₫' : 'Miễn phí',
                    $tournament->prizes ? number_format($tournament->prizes, 0, ',', '.') . ' ₫' : 'N/A'
                ];

                $sheet->fromArray([$row], null, 'A' . $rowNum);
                $rowNum++;
            }

            // Đặt độ rộng cột
            $sheet->getColumnDimension('A')->setWidth(8);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(18);
            $sheet->getColumnDimension('J')->setWidth(18);

            // Center align các cột
            $sheet->getStyle('A2:A' . ($rowNum - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C2:H' . ($rowNum - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Freeze header row
            $sheet->freezePane('A2');

            // Download
            $writer = new Xlsx($spreadsheet);
            $filename = 'DanhSachGiaiDau_' . date('Y-m-d_H-i-s') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Export tournaments list error: ' . $e->getMessage());
            return back()->with('error', 'Lỗi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a booking (change status to cancelled)
     */
    public function cancelBooking($bookingId)
    {
        try {
            $stadiums = Stadium::where('user_id', auth()->id())->pluck('id');
            $courtsOfUser = Court::whereIn('stadium_id', $stadiums)->pluck('id');

            $booking = Booking::whereIn('court_id', $courtsOfUser)
                ->findOrFail($bookingId);

            // Check if booking can be cancelled
            if ($booking->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn đặt đã bị hủy rồi'
                ], 422);
            }

            if ($booking->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy đơn đặt đã hoàn thành'
                ], 422);
            }

            // Update booking status to cancelled
            $booking->update(['status' => 'cancelled']);

            Log::info('Booking cancelled', ['booking_id' => $booking->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn đặt đã được hủy thành công',
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            Log::error('Cancel booking error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a booking
     */
    public function deleteBooking($bookingId)
    {
        try {
            $stadiums = Stadium::where('user_id', auth()->id())->pluck('id');
            $courtsOfUser = Court::whereIn('stadium_id', $stadiums)->pluck('id');

            $booking = Booking::whereIn('court_id', $courtsOfUser)
                ->findOrFail($bookingId);

            // Store booking info for logging
            $bookingInfo = [
                'id' => $booking->id,
                'customer_name' => $booking->customer_name,
                'booking_date' => $booking->booking_date,
                'status' => $booking->status
            ];

            // Delete the booking
            $booking->delete();

            Log::info('Booking deleted', ['booking_info' => $bookingInfo, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn đặt đã được xóa thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete booking error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Calculate booking price with multi-price support
     * Returns pricing breakdown by hour or time period
     */
    public function calculateBookingPrice(Request $request)
    {
        try {
            // Trim and validate input
            $startTimeInput = trim($request->input('start_time', ''));
            
            $request->validate([
                'court_id' => 'required|exists:courts,id',
                'booking_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'duration_hours' => 'required|integer|min:1|max:12',
            ]);

            $court = Court::findOrFail($request->court_id);
            
            $bookingDate = \Carbon\Carbon::parse($request->booking_date);
            $dayOfWeek = $bookingDate->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

            // Create DateTime object with trimmed start_time
            $startTime = \DateTime::createFromFormat('H:i', $startTimeInput);
            if (!$startTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid start_time format. Must be H:i (e.g., 14:00)',
                ], 422);
            }
            $durationHours = $request->duration_hours;
            
            // Calculate hourly breakdown
            $priceBreakdown = [];
            $totalPrice = 0;
            $currentTime = clone $startTime;
            
            for ($i = 0; $i < $durationHours; $i++) {
                $hourStart = clone $currentTime;
                $hourEnd = clone $currentTime;
                $hourEnd->modify('+1 hour');
                
                // Find matching court pricing for this hour
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
                
                // Build pricing label - avoid calling getLabel() which may cause JSON serialization issues
                $pricingLabel = 'Giá mặc định';
                if ($pricing) {
                    if ($pricing->description) {
                        $pricingLabel = $pricing->description;
                    } else {
                        // Format times directly from the raw original attributes
                        $startTime = $pricing->getRawOriginal('start_time') ?? '00:00:00';
                        $endTime = $pricing->getRawOriginal('end_time') ?? '23:59:59';
                        $startStr = substr($startTime, 0, 5);
                        $endStr = substr($endTime, 0, 5);
                        $pricingLabel = "{$startStr} - {$endStr}";
                    }
                }
                
                $priceBreakdown[] = [
                    'hour' => $i + 1,
                    'start_time' => $hourStart->format('H:i'),
                    'end_time' => $hourEnd->format('H:i'),
                    'price_per_hour' => $hourlyRate,
                    'pricing_label' => $pricingLabel
                ];
                
                $totalPrice += $hourlyRate;
                $currentTime->modify('+1 hour');
            }
            
            // Check if there are multiple different prices
            $hasMultiPrice = count(array_unique(array_column($priceBreakdown, 'price_per_hour'))) > 1;
            
            // Calculate average hourly rate for fallback
            $averageHourlyRate = $durationHours > 0 ? round($totalPrice / $durationHours) : $totalPrice;
            
            return response()->json([
                'success' => true,
                'total_price' => $totalPrice,
                'average_hourly_rate' => $averageHourlyRate,
                'has_multi_price' => $hasMultiPrice,
                'duration_hours' => $durationHours,
                'price_breakdown' => $priceBreakdown,
                'court' => [
                    'id' => $court->id,
                    'name' => $court->court_name,
                    'default_rental_price' => $court->rental_price ?? 150000
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Calculate booking price error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}
