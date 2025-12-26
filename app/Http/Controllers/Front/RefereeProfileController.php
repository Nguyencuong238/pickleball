<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefereeProfileController extends Controller
{
    /**
     * Display list of active referees
     */
    public function index(Request $request): View
    {
        $query = User::role('referee')
            ->where('referee_status', 'active');

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('referee_status', $request->status);
        }

        $referees = $query->withCount([
            'refereeMatches as matches_completed' => function ($q) {
                $q->where('status', 'completed');
            }
        ])
        ->orderBy('name')
        ->paginate(12);

        return view('front.referees.index', compact('referees'));
    }

    /**
     * Display referee profile detail
     */
    public function show(Request $request, $slug): View
    {
        $referee = User::where('slug', $slug)
            ->orWhere('id', $slug) // Fallback to ID for backward compatibility
            ->first();
        
        if (!$referee || !$referee->hasRole('referee')) {
            abort(404, 'Referee not found');
        }

        // Load relationships
        $referee->load([
            'refereeMatches' => function ($query) {
                $query->where('status', 'completed')
                    ->with(['tournament', 'category'])
                    ->latest('match_date')
                    ->limit(20);
            },
            'refereeTournaments'
        ]);

        // Calculate stats
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

        return view('front.referees.show', compact('referee', 'stats'));
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
