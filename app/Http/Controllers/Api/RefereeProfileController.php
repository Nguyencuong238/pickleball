<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefereeProfileController extends Controller
{
    /**
     * List active referees
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::role('referee')
            ->where('referee_status', 'active');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('status')) {
            $query->where('referee_status', $request->status);
        }

        $perPage = min((int) $request->get('per_page', 12), 50);

        $referees = $query->withCount([
            'refereeMatches as matches_completed' => function ($q) {
                $q->where('status', 'completed');
            }
        ])
        ->orderBy('name')
        ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $referees,
        ]);
    }

    /**
     * Show referee profile
     */
    public function show(User $referee): JsonResponse
    {
        if (!$referee->hasRole('referee')) {
            return response()->json([
                'success' => false,
                'message' => 'Referee not found.',
            ], 404);
        }

        $referee->load([
            'refereeMatches' => function ($query) {
                $query->where('status', 'completed')
                    ->with(['tournament', 'category'])
                    ->latest('match_date')
                    ->limit(20);
            },
            'refereeTournaments'
        ]);

        $stats = [
            'total_matches' => $referee->refereeMatches()->count(),
            'completed_matches' => $referee->refereeMatches()->where('status', 'completed')->count(),
            'upcoming_matches' => $referee->refereeMatches()
                ->where('status', 'scheduled')
                ->where('match_date', '>=', now())
                ->count(),
            'tournaments' => $referee->refereeTournaments()->count(),
            'completion_rate' => $this->calculateCompletionRate($referee),
            'avg_rating' => $referee->referee_rating ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'referee' => [
                    'id' => $referee->id,
                    'name' => $referee->name,
                    'referee_bio' => $referee->referee_bio,
                    'referee_status' => $referee->referee_status,
                    'referee_rating' => $referee->referee_rating,
                    'matches_officiated' => $referee->matches_officiated,
                ],
                'stats' => $stats,
                'recent_matches' => $referee->refereeMatches,
                'tournaments' => $referee->refereeTournaments,
            ],
        ]);
    }

    /**
     * Calculate completion rate
     */
    private function calculateCompletionRate(User $referee): float
    {
        $total = $referee->refereeMatches()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $referee->refereeMatches()->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 1);
    }
}
