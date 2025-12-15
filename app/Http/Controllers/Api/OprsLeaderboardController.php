<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OprsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OprsLeaderboardController extends Controller
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Get OPRS leaderboard
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 50), 100);
        $offset = (int) $request->get('offset', 0);

        $users = $this->oprsService->getLeaderboard(null, $limit, $offset);

        return response()->json([
            'success' => true,
            'data' => $users->map(fn ($user, $index) => [
                'rank' => $offset + $index + 1,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'oprs' => $user->total_oprs,
                'opr_level' => $user->opr_level,
                'oprs_name' => OprsService::OPR_LEVELS[$user->opr_level]['name'] ?? null,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'stats' => [
                    'matches' => $user->total_ocr_matches,
                    'wins' => $user->ocr_wins,
                    'win_rate' => $user->win_rate,
                ],
            ]),
            'meta' => [
                'offset' => $offset,
                'limit' => $limit,
                'has_more' => $users->count() === $limit,
            ],
        ]);
    }

    /**
     * Get leaderboard by OPR Level
     */
    public function byLevel(string $level, Request $request): JsonResponse
    {
        $validLevels = array_keys(OprsService::OPR_LEVELS);

        if (!in_array($level, $validLevels)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid OPR level',
            ], 422);
        }

        $limit = min((int) $request->get('limit', 50), 100);
        $offset = (int) $request->get('offset', 0);

        $users = $this->oprsService->getLeaderboard($level, $limit, $offset);

        return response()->json([
            'success' => true,
            'data' => $users->map(fn ($user, $index) => [
                'rank' => $offset + $index + 1,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'oprs' => $user->total_oprs,
                'opr_level' => $user->opr_level,
                'oprs_name' => OprsService::OPR_LEVELS[$user->opr_level]['name'] ?? null,
                'stats' => [
                    'matches' => $user->total_ocr_matches,
                    'wins' => $user->ocr_wins,
                ],
            ]),
            'meta' => [
                'level' => $level,
                'level_info' => OprsService::OPR_LEVELS[$level],
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Get level distribution
     */
    public function distribution(): JsonResponse
    {
        $distribution = $this->oprsService->getLevelDistribution();
    
        $data = [];
        foreach (OprsService::OPR_LEVELS as $level => $info) {
            $data[] = [
                'level' => $level,
                'name' => $info['name'],
                'min_oprs' => $info['min'],
                'max_oprs' => $info['max'] === PHP_INT_MAX ? null : $info['max'],
                'count' => $distribution[$level] ?? 0,
            ];
        }
    
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get levels info
     */
    public function levels(): JsonResponse
    {
        $levels = [];
        foreach (OprsService::OPR_LEVELS as $level => $info) {
            $levels[$level] = [
                'name' => $info['name'],
                'min_oprs' => $info['min'],
                'max_oprs' => $info['max'] === PHP_INT_MAX ? null : $info['max'],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $levels,
        ]);
    }
}
