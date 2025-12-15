<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OprsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OprsController extends Controller
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Get all OPR levels
     */
    public function levels(): JsonResponse
    {
        $levels = OprsService::getAllLevels();

        return response()->json([
            'success' => true,
            'data' => $levels,
        ]);
    }

    /**
     * Get current user's OPRS profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $breakdown = $this->oprsService->getOprsBreakdown($user);
        $rank = $this->oprsService->getUserRank($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'total_oprs' => $user->total_oprs,
                'opr_level' => $user->opr_level,
                'breakdown' => $breakdown,
                'rank' => $rank,
            ],
        ]);
    }

    /**
     * Get current user's OPRS breakdown
     */
    public function breakdown(Request $request): JsonResponse
    {
        $user = $request->user();
        $breakdown = $this->oprsService->getOprsBreakdown($user);

        return response()->json([
            'success' => true,
            'data' => $breakdown,
        ]);
    }

    /**
     * Get current user's OPRS history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min((int) $request->get('limit', 50), 100);

        $history = $user->oprsHistories()
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get OPRS leaderboard
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $oprLevel = $request->get('level');
        $limit = min((int) $request->get('limit', 50), 100);
        $offset = (int) $request->get('offset', 0);

        $users = $this->oprsService->getLeaderboard($oprLevel, $limit, $offset);

        $leaderboard = $users->map(function (User $user, int $index) use ($offset) {
            return [
                'rank' => $offset + $index + 1,
                'user_id' => $user->id,
                'name' => $user->name,
                'total_oprs' => $user->total_oprs,
                'opr_level' => $user->opr_level,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
            ];
        });

        $distribution = $this->oprsService->getLevelDistribution();

        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboard,
                'distribution' => $distribution,
            ],
        ]);
    }

    /**
     * Get specific user's OPRS profile
     */
    public function userProfile(User $user): JsonResponse
    {
        $breakdown = $this->oprsService->getOprsBreakdown($user);
        $rank = $this->oprsService->getUserRank($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'total_oprs' => $user->total_oprs,
                'opr_level' => $user->opr_level,
                'oprs_name' => OprsService::OPR_LEVELS[$user->opr_level]['name'] ?? null,
                'breakdown' => $breakdown,
                'rank' => $rank,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'total_matches' => $user->total_ocr_matches,
                'win_rate' => $user->win_rate,
            ],
        ]);
    }
}
