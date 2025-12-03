<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\BadgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OcrBadgeController extends Controller
{
    public function __construct(private BadgeService $badgeService)
    {
    }

    /**
     * List all badge types and counts
     */
    public function index(): View
    {
        $badgeTypes = $this->badgeService->getAllBadgeTypes();

        $badges = collect($badgeTypes)->map(function ($type) {
            $info = UserBadge::getBadgeInfo($type);
            return [
                'type' => $type,
                'name' => $info['name'],
                'description' => $info['description'],
                'icon' => $info['icon'],
                'count' => UserBadge::where('badge_type', $type)->count(),
            ];
        });

        return view('admin.ocr.badges.index', compact('badges'));
    }

    /**
     * Show users with specific badge
     */
    public function show(string $badgeType): View
    {
        $info = UserBadge::getBadgeInfo($badgeType);
        $users = $this->badgeService->getUsersWithBadge($badgeType);

        return view('admin.ocr.badges.show', compact('badgeType', 'info', 'users'));
    }

    /**
     * Award badge to user
     */
    public function award(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'badge_type' => 'required|string',
        ]);

        $user = User::find($validated['user_id']);

        if ($user->hasBadge($validated['badge_type'])) {
            return back()->with('error', 'User already has this badge');
        }

        $this->badgeService->awardBadge($user, $validated['badge_type'], [
            'awarded_by' => 'admin',
            'awarded_at' => now()->toISOString(),
        ]);

        return back()->with('success', "Badge awarded to {$user->name}");
    }

    /**
     * Revoke badge from user
     */
    public function revoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'badge_type' => 'required|string',
        ]);

        $user = User::find($validated['user_id']);

        if (!$user->hasBadge($validated['badge_type'])) {
            return back()->with('error', 'User does not have this badge');
        }

        $this->badgeService->revokeBadge($user, $validated['badge_type']);

        return back()->with('success', "Badge revoked from {$user->name}");
    }
}
