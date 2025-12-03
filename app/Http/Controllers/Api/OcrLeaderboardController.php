<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OcrLeaderboardController extends Controller
{
    /**
     * Get global leaderboard
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min(100, max(1, (int) $request->query('limit', 50)));

        $users = User::where('total_ocr_matches', '>', 0)
            ->orderBy('elo_rating', 'desc')
            ->take($limit)
            ->get(['id', 'name', 'elo_rating', 'elo_rank', 'total_ocr_matches', 'ocr_wins', 'ocr_losses']);

        $ranked = $users->map(function ($user, $index) {
            return [
                'rank' => $index + 1,
                'user_id' => $user->id,
                'name' => $user->name,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'total_matches' => $user->total_ocr_matches,
                'wins' => $user->ocr_wins,
                'losses' => $user->ocr_losses,
                'win_rate' => $user->win_rate,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $ranked,
        ]);
    }

    /**
     * Get leaderboard by rank tier
     */
    public function byRank(string $rank, Request $request): JsonResponse
    {
        $validRanks = array_keys(User::getEloRanks());
        $normalizedRank = ucfirst(strtolower($rank));

        if (!in_array($normalizedRank, $validRanks)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid rank. Valid ranks: ' . implode(', ', $validRanks),
            ], 422);
        }

        $limit = min(100, max(1, (int) $request->query('limit', 50)));

        $users = User::where('elo_rank', $normalizedRank)
            ->where('total_ocr_matches', '>', 0)
            ->orderBy('elo_rating', 'desc')
            ->take($limit)
            ->get(['id', 'name', 'elo_rating', 'elo_rank', 'total_ocr_matches', 'ocr_wins', 'ocr_losses']);

        $ranked = $users->values()->map(function ($user, $index) {
            return [
                'rank_in_tier' => $index + 1,
                'user_id' => $user->id,
                'name' => $user->name,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'total_matches' => $user->total_ocr_matches,
                'wins' => $user->ocr_wins,
                'losses' => $user->ocr_losses,
                'win_rate' => $user->win_rate,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $ranked,
            'meta' => [
                'rank_tier' => $normalizedRank,
                'total_players' => $users->count(),
            ],
        ]);
    }

    /**
     * Get rank distribution statistics
     */
    public function distribution(): JsonResponse
    {
        $ranks = User::getEloRanks();
        $distribution = [];

        foreach ($ranks as $rankName => $range) {
            $count = User::where('elo_rank', $rankName)
                ->where('total_ocr_matches', '>', 0)
                ->count();

            $distribution[] = [
                'rank' => $rankName,
                'min_elo' => $range['min'],
                'max_elo' => $range['max'] === PHP_INT_MAX ? null : $range['max'],
                'player_count' => $count,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $distribution,
        ]);
    }
}
