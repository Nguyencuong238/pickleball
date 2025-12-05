<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChallengeResult;
use App\Models\CommunityActivity;
use App\Models\User;
use App\Services\OprsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OprsController extends Controller
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * OPRS Dashboard
     */
    public function dashboard(): View
    {
        $stats = [
            'total_users' => User::where('total_oprs', '>', 0)->count(),
            'level_distribution' => $this->oprsService->getLevelDistribution(),
            'pending_challenges' => ChallengeResult::whereNull('verified_at')->count(),
            'recent_activities' => CommunityActivity::whereDate('created_at', today())->count(),
            'top_players' => $this->oprsService->getLeaderboard(null, 10),
        ];

        return view('admin.oprs.dashboard', compact('stats'));
    }

    /**
     * User OPRS list
     */
    public function users(Request $request): View
    {
        $query = User::query()
            ->orderByDesc('total_oprs');

        if ($request->filled('level')) {
            $query->where('opr_level', $request->level);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->paginate(25);

        return view('admin.oprs.users.index', [
            'users' => $users,
            'levels' => OprsService::OPR_LEVELS,
        ]);
    }

    /**
     * User OPRS detail
     */
    public function userDetail(User $user): View
    {
        $breakdown = $this->oprsService->getOprsBreakdown($user);
        $history = $user->oprsHistories()->orderByDesc('created_at')->take(20)->get();
        $challenges = $user->challengeResults()->orderByDesc('created_at')->take(10)->get();
        $activities = $user->communityActivities()->orderByDesc('created_at')->take(10)->get();

        return view('admin.oprs.users.detail', [
            'user' => $user,
            'breakdown' => $breakdown,
            'history' => $history,
            'challenges' => $challenges,
            'activities' => $activities,
        ]);
    }

    /**
     * Manual OPRS adjustment
     */
    public function adjustUser(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'component' => 'required|in:challenge,community',
            'amount' => 'required|numeric|min:-1000|max:1000',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $this->oprsService->adminAdjustment(
                $user,
                $request->component,
                (float) $request->amount,
                $request->reason . ' (by ' . auth()->user()->name . ')'
            );

            return back()->with('success', 'OPRS adjusted successfully. New total: ' . $user->fresh()->total_oprs);
        } catch (\Exception $e) {
            return back()->with('error', 'Adjustment failed: ' . $e->getMessage());
        }
    }

    /**
     * Level distribution report
     */
    public function levelReport(): View
    {
        $distribution = $this->oprsService->getLevelDistribution();
        $total = array_sum($distribution);

        $data = [];
        foreach (OprsService::OPR_LEVELS as $level => $info) {
            $count = $distribution[$level] ?? 0;
            $data[$level] = [
                'name' => $info['name'],
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                'min' => $info['min'],
                'max' => $info['max'] === PHP_INT_MAX ? null : $info['max'],
            ];
        }

        return view('admin.oprs.reports.levels', [
            'data' => $data,
            'total' => $total,
        ]);
    }

    /**
     * Recalculate all users OPRS
     */
    public function recalculateAll(): RedirectResponse
    {
        $count = $this->oprsService->batchRecalculateAll();

        return back()->with('success', "Recalculated OPRS for {$count} users");
    }
}
