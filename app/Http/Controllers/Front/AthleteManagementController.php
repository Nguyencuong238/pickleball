<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\TournamentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AthleteManagementController extends Controller
{
    /**
     * Display the athletes list page
     */
    public function index()
    {
        $user = Auth::user();
        $tournaments = Tournament::where('user_id', $user->id)->get();

        return view('home-yard.tournaments.athletes', [
            'tournaments' => $tournaments
        ]);
    }

    /**
     * Get user's tournaments for filter dropdown
     */
    public function getTournamentsForFilter()
    {
        $user = Auth::user();
        Log::info('getTournamentsForFilter', ['user_id' => $user->id]);
        
        $tournaments = Tournament::where('user_id', $user->id)
            ->select('id', 'name')
            ->get();

        Log::info('Tournaments found', ['count' => $tournaments->count()]);

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'tournaments' => $tournaments
        ]);
    }

    /**
     * Get athletes grouped by tournament and category with statistics
     */
    public function getAthletesByUserTournaments(Request $request)
    {
        $user = Auth::user();

        // Debug info
        Log::info('AthleteManagement Request', [
            'user_id' => $user->id,
            'search' => $request->get('search'),
            'tournament_id' => $request->get('tournament_id'),
        ]);

        // Get filter parameters
        $search = $request->get('search');
        $tournamentId = $request->get('tournament_id');
        $categoryId = $request->get('category_id');
        $status = $request->get('status');
        $contentType = $request->get('content_type');

        // Build query - Get tournaments owned by current user
        $query = TournamentAthlete::whereHas('tournament', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('athlete_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Get athletes with relationships
        $athletes = $query->with([
            'tournament',
            'category',
            'user'
        ])
            ->orderBy('position', 'asc')
            ->orderBy('tournament_id', 'desc')
            ->paginate(15);

        // Transform data with statistics
        $athletesData = $athletes->map(function ($athlete) {
            $category = $athlete->category;
            return [
                'id' => $athlete->id,
                'user_id' => $athlete->user_id,
                'tournament_id' => $athlete->tournament_id,
                'tournament_name' => $athlete->tournament->name ?? 'N/A',
                'category_id' => $athlete->category_id,
                'category_name' => $category?->category_name ?? 'N/A',
                'category_type' => $category?->category_type ?? 'N/A',
                'athlete_name' => $athlete->athlete_name,
                'email' => $athlete->email,
                'phone' => $athlete->phone,
                'status' => $athlete->status,
                'position' => $athlete->position,
                'payment_status' => $athlete->payment_status,
                'statistics' => [
                    'matches_played' => (int)($athlete->matches_played ?? 0),
                    'matches_won' => (int)($athlete->matches_won ?? 0),
                    'matches_lost' => (int)($athlete->matches_lost ?? 0),
                    'win_rate' => $this->calculateWinRate($athlete->matches_won, $athlete->matches_played),
                    'total_points' => (int)($athlete->total_points ?? 0),
                    'sets_won' => (int)($athlete->sets_won ?? 0),
                    'sets_lost' => (int)($athlete->sets_lost ?? 0),
                    'seed_number' => $athlete->seed_number,
                    'group' => $athlete->group_id,
                ]
            ];
        });

        // Get summary statistics
        $summary = [
            'total_athletes' => $athletes->total(),
            'total_by_status' => [
                'approved' => TournamentAthlete::whereHas('tournament', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('status', 'approved')->count(),
                'pending' => TournamentAthlete::whereHas('tournament', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('status', 'pending')->count(),
                'rejected' => TournamentAthlete::whereHas('tournament', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('status', 'rejected')->count(),
            ],
            'active_athletes' => TournamentAthlete::whereHas('tournament', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'approved')->count(),
            'pending_approval' => TournamentAthlete::whereHas('tournament', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'pending')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $athletesData,
            'summary' => $summary,
            'pagination' => [
                'total' => $athletes->total(),
                'per_page' => $athletes->perPage(),
                'current_page' => $athletes->currentPage(),
                'last_page' => $athletes->lastPage(),
                'from' => $athletes->firstItem(),
                'to' => $athletes->lastItem(),
            ]
        ]);
    }

    /**
     * Get athletes for a specific tournament
     */
    public function getTournamentAthletes($tournamentId)
    {
        $user = Auth::user();

        // Check if user owns the tournament
        $tournament = Tournament::where('id', $tournamentId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Get athletes grouped by category
        $categories = TournamentCategory::where('tournament_id', $tournamentId)
            ->with(['athletes' => function ($q) {
                $q->orderBy('position', 'asc');
            }])
            ->get();

        $categoriesData = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->category_name,
                'type' => $category->category_type,
                'max_participants' => $category->max_participants,
                'current_participants' => $category->current_participants,
                'available_slots' => max(0, $category->max_participants - $category->current_participants),
                'athletes' => $category->athletes->map(function ($athlete) {
                    return $this->formatAthleteData($athlete);
                })
            ];
        });

        return response()->json([
            'success' => true,
            'tournament' => [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'start_date' => $tournament->start_date,
                'end_date' => $tournament->end_date,
            ],
            'categories' => $categoriesData,
        ]);
    }

    /**
     * Get athletes statistics by category
     */
    public function getCategoryStatistics($tournamentId, $categoryId)
    {
        $user = Auth::user();

        // Verify ownership
        Tournament::where('id', $tournamentId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $athletes = TournamentAthlete::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->where('status', 'approved')
            ->with(['category'])
            ->get();

        $stats = [
            'total_athletes' => $athletes->count(),
            'total_matches' => $athletes->sum('matches_played'),
            'total_wins' => $athletes->sum('matches_won'),
            'total_losses' => $athletes->sum('matches_lost'),
            'total_points' => $athletes->sum('total_points'),
            'total_sets_won' => $athletes->sum('sets_won'),
            'total_sets_lost' => $athletes->sum('sets_lost'),
            'average_win_rate' => $athletes->count() > 0
                ? round($athletes->avg('win_rate'), 2)
                : 0,
            'athletes' => $athletes->map(function ($athlete) {
                return $this->formatAthleteData($athlete);
            })
        ];

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $categoryId,
                'name' => $athletes->first()?->category->category_name ?? ''
            ],
            'statistics' => $stats
        ]);
    }

    /**
     * Format athlete data with complete statistics
     */
    private function formatAthleteData($athlete)
    {
        return [
            'id' => $athlete->id,
            'name' => $athlete->athlete_name,
            'email' => $athlete->email,
            'phone' => $athlete->phone,
            'position' => $athlete->position,
            'status' => $athlete->status,
            'payment_status' => $athlete->payment_status,
            'statistics' => [
                'matches_played' => $athlete->matches_played ?? 0,
                'matches_won' => $athlete->matches_won ?? 0,
                'matches_lost' => $athlete->matches_lost ?? 0,
                'win_rate' => $this->calculateWinRate($athlete->matches_won, $athlete->matches_played),
                'win_rate_percentage' => $this->calculateWinRatePercentage($athlete->matches_won, $athlete->matches_played),
                'total_points' => $athlete->total_points ?? 0,
                'sets_won' => $athlete->sets_won ?? 0,
                'sets_lost' => $athlete->sets_lost ?? 0,
                'seed_number' => $athlete->seed_number,
                'group_id' => $athlete->group_id,
            ]
        ];
    }

    /**
     * Calculate win rate as decimal
     */
    private function calculateWinRate($wins, $played)
    {
        if (!$played || $played == 0) {
            return 0;
        }
        return round(($wins / $played) * 100, 2);
    }

    /**
     * Calculate win rate as percentage string
     */
    private function calculateWinRatePercentage($wins, $played)
    {
        $rate = $this->calculateWinRate($wins, $played);
        return $rate . '%';
    }

    /**
     * Delete an athlete
     */
    public function deleteAthlete($athleteId)
    {
        $user = Auth::user();
        
        // Find athlete and verify user owns the tournament
        $athlete = TournamentAthlete::findOrFail($athleteId);
        $tournament = Tournament::findOrFail($athlete->tournament_id);
        
        // Check authorization
        if ($tournament->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa vận động viên này'
            ], 403);
        }
        
        try {
            $athlete->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Vận động viên đã được xóa thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting athlete', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa vận động viên: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an athlete
     */
    public function updateAthlete(Request $request, $athleteId)
    {
        $user = Auth::user();
        
        // Find athlete and verify user owns the tournament
        $athlete = TournamentAthlete::findOrFail($athleteId);
        $tournament = Tournament::findOrFail($athlete->tournament_id);
        
        // Check authorization
        if ($tournament->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật vận động viên này'
            ], 403);
        }
        
        try {
            $validated = $request->validate([
                'athlete_name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'status' => 'nullable|in:pending,approved,rejected',
            ]);
            
            $athlete->update(array_filter($validated));
            
            return response()->json([
                'success' => true,
                'message' => 'Vận động viên đã được cập nhật thành công',
                'athlete' => $this->formatAthleteData($athlete)
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating athlete', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật vận động viên: ' . $e->getMessage()
            ], 500);
        }
    }
}
