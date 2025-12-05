<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChallengeSubmitRequest;
use App\Services\ChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ChallengeController extends Controller
{
    public function __construct(
        private ChallengeService $challengeService
    ) {}

    /**
     * Get available challenge types
     */
    public function types(): JsonResponse
    {
        $types = $this->challengeService->getAllChallengeTypes();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Get user's available challenges
     */
    public function available(Request $request): JsonResponse
    {
        $user = $request->user();
        $available = $this->challengeService->getAvailableChallenges($user);

        return response()->json([
            'success' => true,
            'data' => $available,
        ]);
    }

    /**
     * Submit challenge result
     */
    public function submit(ChallengeSubmitRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $result = $this->challengeService->submitChallenge(
                $user,
                $request->validated('challenge_type'),
                $request->validated('score')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'challenge' => $result,
                    'passed' => $result->passed,
                    'points_earned' => $result->points_earned,
                    'new_challenge_score' => $user->fresh()->challenge_score,
                ],
                'message' => $result->passed
                    ? 'Challenge passed! Points awarded.'
                    : 'Challenge not passed. Try again!',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get user's challenge history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min((int) $request->get('limit', 50), 100);
        $history = $this->challengeService->getChallengeHistory($user, $limit);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get user's challenge stats
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->challengeService->getChallengeStats($user);
        $bestScores = $this->challengeService->getBestScores($user);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'best_scores' => $bestScores,
                'challenge_score' => $user->challenge_score,
            ],
        ]);
    }
}
