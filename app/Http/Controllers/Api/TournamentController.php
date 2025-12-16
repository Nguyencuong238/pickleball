<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TournamentController extends Controller
{
    /**
     * Get all tournaments
     */
    public function index(Request $request)
    {
        $query = Tournament::query();

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->query('status') === 'upcoming') {
                $query->where('start_date', '>', now());
            } elseif ($request->query('status') === 'ongoing') {
                $query->where('start_date', '<=', now())->where(function ($q) {
                    $q->where('end_date', '>=', now())
                        ->orWhereNull('end_date');
                });
            } elseif ($request->query('status') === 'completed') {
                $query->where('end_date', '<', now());
            }
        }

        // Sort
        $sort = $request->get('sort', 'start_date');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        // Pagination
        $per_page = $request->get('per_page', 15);
        $tournaments = $query->paginate($per_page);

        return TournamentResource::collection($tournaments)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Get tournament details
     */
    public function show($id)
    {
        $tournament = Tournament::with('categories', 'athletes', 'rounds', 'groups')->find($id);

        if (!$tournament) {
            return response()->json([
                'success' => false,
                'message' => 'Giải đấu không tồn tại',
            ], 404);
        }

        return new TournamentResource($tournament);
    }

    /**
     * Get tournament standings/leaderboard
     */
    public function standings($id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json([
                'success' => false,
                'message' => 'Giải đấu không tồn tại',
            ], 404);
        }

        $athletes = $tournament->athletes()
            ->orderBy('rank', 'asc')
            ->get();

        return response()->json([
            'data' => $athletes,
        ], 200);
    }

    /**
     * Get tournaments that the authenticated user has registered for
     */
    public function myTournaments(Request $request)
    {
        $user = $request->user();

        $registrations = TournamentAthlete::where('user_id', $user->id)
            ->with(['tournament', 'category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $registrations->map(function ($registration) {
                return [
                    'registration_id' => $registration->id,
                    'status' => $registration->status,
                    'payment_status' => $registration->payment_status,
                    'registered_at' => $registration->registered_at,
                    'category' => $registration->category ? [
                        'id' => $registration->category->id,
                        'name' => $registration->category->name,
                    ] : null,
                    'tournament' => $registration->tournament ? [
                        'id' => $registration->tournament->id,
                        'name' => $registration->tournament->name,
                        'start_date' => $registration->tournament->start_date,
                        'end_date' => $registration->tournament->end_date,
                        'location' => $registration->tournament->location,
                        'status' => $registration->tournament->status,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Register for tournament
     */
    public function register(Request $request, $tournament_id)
    {
        try {
            $tournament = Tournament::find($tournament_id);

            if (!$tournament) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giải đấu không tồn tại',
                ], 404);
            }

            // Validate request
            $validated = $request->validate([
                'athlete_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'category_id' => 'required|exists:tournament_categories,id',
            ]);

            // Check if registration deadline has passed
            if ($tournament->registration_deadline && $tournament->registration_deadline <= now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hạn đăng ký đã đóng',
                ], 400);
            }

            // Verify category belongs to this tournament
            $category = $tournament->categories()->find($validated['category_id']);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung thi đấu không tồn tại',
                ], 400);
            }

            // Check if category is available
            if ($category->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung thi đấu này đã đóng',
                ], 400);
            }

            $currentCategoryCount = $category->athletes()->count();
            if ($currentCategoryCount >= $category->max_participants) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung thi đấu này đã hết chỗ',
                ], 400);
            }

            // Check if tournament is full
            $currentCount = $tournament->athletes()->count();
            if ($tournament->max_participants && $currentCount >= $tournament->max_participants) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giải đấu đã đầy',
                ], 400);
            }

            // Check if athlete already registered with same email
            $existing = TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('email', $validated['email'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email này đã được đăng ký cho giải đấu này',
                ], 400);
            }

            // Update or create user record with registration data
            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                $user = User::create([
                    'email' => $validated['email'],
                    'name' => $validated['athlete_name'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make(Str::random(16))
                ]);
            }

            // Create athlete registration with pending status
            $athlete = TournamentAthlete::create([
                'tournament_id' => $tournament->id,
                'user_id' => $user->id,
                'category_id' => $validated['category_id'],
                'athlete_name' => $validated['athlete_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => 'pending',
                'registered_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công',
                'data' => [
                    'athlete_id' => $athlete->id,
                    'status' => $athlete->status,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi. Vui lòng thử lại.',
            ], 500);
        }
    }
}
