<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityActivity;
use App\Services\OprsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OprsActivityController extends Controller
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Activity list
     */
    public function index(Request $request): View
    {
        $query = CommunityActivity::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('activity_type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(25);

        return view('admin.oprs.activities.index', [
            'activities' => $activities,
            'types' => CommunityActivity::getAllTypes(),
        ]);
    }

    /**
     * Activity detail
     */
    public function show(CommunityActivity $activity): View
    {
        return view('admin.oprs.activities.detail', [
            'activity' => $activity->load('user'),
        ]);
    }

    /**
     * Remove activity (with point reversal)
     */
    public function destroy(Request $request, CommunityActivity $activity): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $user = $activity->user;
        $pointsRemoved = $activity->points_earned;

        // Reverse points
        $user->update([
            'community_score' => max(0, $user->community_score - $pointsRemoved),
        ]);

        // Recalculate OPRS
        $this->oprsService->updateUserOprs(
            $user,
            'admin_removed_activity',
            [
                'activity_id' => $activity->id,
                'activity_type' => $activity->activity_type,
                'points_removed' => $pointsRemoved,
                'reason' => $request->reason,
                'admin_id' => auth()->id(),
            ]
        );

        $activity->delete();

        return back()->with('success', "Activity removed. {$pointsRemoved} points deducted from user.");
    }

    /**
     * Activity stats
     */
    public function stats(): View
    {
        $stats = CommunityActivity::selectRaw('
                activity_type,
                COUNT(*) as count,
                SUM(points_earned) as total_points
            ')
            ->groupBy('activity_type')
            ->get();

        $dailyStats = CommunityActivity::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as count
            ')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.oprs.activities.stats', [
            'stats' => $stats,
            'dailyStats' => $dailyStats,
        ]);
    }
}
