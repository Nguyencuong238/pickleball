<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class OcrUserController extends Controller
{
    /**
     * Get user's Elo rating with OPRS data
     */
    public function elo(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'total_matches' => $user->total_ocr_matches,
                'wins' => $user->ocr_wins,
                'losses' => $user->ocr_losses,
                'win_rate' => $user->win_rate,
                // OPRS data
                'oprs' => [
                    'total' => $user->total_oprs,
                    'level' => $user->opr_level,
                    'challenge_score' => $user->challenge_score,
                    'community_score' => $user->community_score,
                ],
            ],
        ]);
    }

    /**
     * Get user's badges
     */
    public function badges(User $user): JsonResponse
    {
        $badges = $user->badges()->orderBy('earned_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $badges->map(fn($badge) => [
                'type' => $badge->badge_type,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'earned_at' => $badge->earned_at->toISOString(),
                'metadata' => $badge->metadata,
            ]),
        ]);
    }

    /**
     * Get user's OCR stats with OPRS data
     */
    public function stats(User $user): JsonResponse
    {
        $recentHistory = $user->eloHistories()
            ->with('ocrMatch')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'total_matches' => $user->total_ocr_matches,
                'wins' => $user->ocr_wins,
                'losses' => $user->ocr_losses,
                'win_rate' => $user->win_rate,
                'badges_count' => $user->badges()->count(),
                'current_streak' => $user->getCurrentWinStreak(),
                // OPRS data
                'oprs' => [
                    'total' => $user->total_oprs,
                    'level' => $user->opr_level,
                    'level_info' => $user->getOprLevelInfo(),
                    'challenge_score' => $user->challenge_score,
                    'community_score' => $user->community_score,
                    'passed_challenges' => $user->getPassedChallengesCount(),
                ],
                'recent_history' => $recentHistory->map(fn ($h) => [
                    'id' => $h->id,
                    'elo_before' => $h->elo_before,
                    'elo_after' => $h->elo_after,
                    'change_amount' => $h->change_amount,
                    'change_reason' => $h->change_reason,
                    'created_at' => $h->created_at->toISOString(),
                    'match_id' => $h->ocr_match_id,
                ]),
            ],
        ]);
    }
}
