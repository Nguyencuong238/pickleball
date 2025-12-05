<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunityActivity;
use App\Models\Social;
use App\Models\Stadium;
use App\Models\User;
use App\Services\CommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CommunityActivityController extends Controller
{
    public function __construct(
        private CommunityService $communityService
    ) {}

    /**
     * Get all activity types
     */
    public function types(): JsonResponse
    {
        $types = $this->communityService->getAllActivityTypes();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Check in at stadium
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'stadium_id' => 'required|exists:stadiums,id',
        ]);

        $user = $request->user();
        $stadium = Stadium::findOrFail($request->stadium_id);

        try {
            $activity = $this->communityService->checkIn($user, $stadium);

            return response()->json([
                'success' => true,
                'data' => [
                    'activity' => $activity,
                    'new_community_score' => $user->fresh()->community_score,
                ],
                'message' => 'Check-in successful! +' . $activity->points_earned . ' points',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Record event participation
     */
    public function recordEvent(Request $request): JsonResponse
    {
        $request->validate([
            'event_id' => 'required|exists:socials,id',
        ]);

        $user = $request->user();
        $event = Social::findOrFail($request->event_id);

        try {
            $activity = $this->communityService->recordEventParticipation($user, $event);

            return response()->json([
                'success' => true,
                'data' => [
                    'activity' => $activity,
                    'new_community_score' => $user->fresh()->community_score,
                ],
                'message' => 'Event participation recorded! +' . $activity->points_earned . ' points',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Record referral
     */
    public function recordReferral(Request $request): JsonResponse
    {
        $request->validate([
            'referred_user_id' => 'required|exists:users,id',
        ]);

        $referrer = $request->user();
        $referredUser = User::findOrFail($request->referred_user_id);

        if ($referrer->id === $referredUser->id) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot refer yourself',
            ], 422);
        }

        try {
            $activity = $this->communityService->recordReferral($referrer, $referredUser);

            return response()->json([
                'success' => true,
                'data' => [
                    'activity' => $activity,
                    'new_community_score' => $referrer->fresh()->community_score,
                ],
                'message' => 'Referral recorded! +' . $activity->points_earned . ' points',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get activity history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min((int) $request->get('limit', 50), 100);
        $history = $this->communityService->getActivityHistory($user, $limit);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get activity stats
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->communityService->getActivityStats($user);
        $available = $this->communityService->getAvailableActivities($user);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'available_activities' => $available,
                'community_score' => $user->community_score,
            ],
        ]);
    }
}
