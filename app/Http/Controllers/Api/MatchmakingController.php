<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EloService;
use App\Services\OprsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchmakingController extends Controller
{
    public function __construct(
        private OprsService $oprsService,
        private EloService $eloService
    ) {}

    /**
     * Suggest opponents by OPRS
     */
    public function suggest(User $user, Request $request): JsonResponse
    {
        $range = min((int) $request->get('range', 100), 500); // OPRS range
        $limit = min((int) $request->get('limit', 10), 20);

        $minOprs = max(0, $user->total_oprs - $range);
        $maxOprs = $user->total_oprs + $range;

        $suggestions = User::query()
            ->where('id', '!=', $user->id)
            ->whereBetween('total_oprs', [$minOprs, $maxOprs])
            ->where('total_ocr_matches', '>', 0)
            ->orderByRaw('ABS(total_oprs - ?)', [$user->total_oprs])
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $suggestions->map(fn ($opponent) => [
                'user' => [
                    'id' => $opponent->id,
                    'name' => $opponent->name,
                ],
                'oprs' => $opponent->total_oprs,
                'opr_level' => $opponent->opr_level,
                'oprs_diff' => abs($user->total_oprs - $opponent->total_oprs),
                'elo_rating' => $opponent->elo_rating,
                'elo_rank' => $opponent->elo_rank,
                'stats' => [
                    'matches' => $opponent->total_ocr_matches,
                    'wins' => $opponent->ocr_wins,
                    'win_rate' => $opponent->win_rate,
                ],
                'matchup' => [
                    'win_probability' => $this->eloService->getWinProbability($user, $opponent),
                    'estimated_elo_change' => $this->eloService->estimateRatingChange($user, $opponent),
                ],
            ]),
            'meta' => [
                'your_oprs' => $user->total_oprs,
                'your_opr_level' => $user->opr_level,
                'search_range' => [$minOprs, $maxOprs],
            ],
        ]);
    }

    /**
     * Estimate OPRS change for potential action
     */
    public function estimateChange(Request $request): JsonResponse
    {
        $request->validate([
            'component' => 'required|in:elo,challenge,community',
            'change' => 'required|numeric',
        ]);

        $user = $request->user();
        $estimate = $this->oprsService->estimateOprsChange(
            $user,
            $request->component,
            (float) $request->change
        );

        return response()->json([
            'success' => true,
            'data' => $estimate,
        ]);
    }
}
