<?php

namespace App\Services;

use App\Models\ChallengeResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChallengeService
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Submit a challenge result
     *
     * @throws InvalidArgumentException
     */
    public function submitChallenge(User $user, string $type, int $score): ChallengeResult
    {
        // Validate type
        if (!in_array($type, ChallengeResult::getAllTypes())) {
            throw new InvalidArgumentException('Invalid challenge type');
        }

        // Check monthly limit
        if ($type === ChallengeResult::TYPE_MONTHLY_TEST) {
            if (!$this->canSubmitMonthlyTest($user)) {
                throw new InvalidArgumentException('Monthly test already submitted this month');
            }
        }

        return DB::transaction(function () use ($user, $type, $score) {
            // Create challenge result
            $challenge = ChallengeResult::create([
                'user_id' => $user->id,
                'challenge_type' => $type,
                'score' => $score,
                'passed' => false,
                'points_earned' => 0,
            ]);

            // Check if passed
            $challenge->passed = $challenge->checkPassed();
            $challenge->points_earned = $challenge->calculatePoints();
            $challenge->save();

            // If passed, update user score
            if ($challenge->passed) {
                $this->awardPoints($user, $challenge);
            }

            return $challenge;
        });
    }

    /**
     * Verify a challenge (admin action)
     *
     * @throws InvalidArgumentException
     */
    public function verifyChallenge(ChallengeResult $challenge, User $verifier): void
    {
        if ($challenge->verified_at) {
            throw new InvalidArgumentException('Challenge already verified');
        }

        $challenge->update([
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }

    /**
     * Award points to user
     */
    private function awardPoints(User $user, ChallengeResult $challenge): void
    {
        $newScore = $user->challenge_score + $challenge->points_earned;

        $user->update([
            'challenge_score' => $newScore,
        ]);

        // Recalculate OPRS
        $this->oprsService->recalculateAfterChallenge($user, $challenge->id);
    }

    /**
     * Check if user can submit monthly test
     */
    public function canSubmitMonthlyTest(User $user): bool
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return !ChallengeResult::where('user_id', $user->id)
            ->where('challenge_type', ChallengeResult::TYPE_MONTHLY_TEST)
            ->where('created_at', '>=', $startOfMonth)
            ->exists();
    }

    /**
     * Get user's challenge history
     *
     * @return Collection<int, ChallengeResult>
     */
    public function getChallengeHistory(User $user, int $limit = 50): Collection
    {
        return $user->challengeResults()
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get challenge statistics for user
     *
     * @return array{total: int, passed: int, total_points: float, by_type: array<string, array{attempts: int, passed: int, points: float, info: array}>}
     */
    public function getChallengeStats(User $user): array
    {
        $results = $user->challengeResults()
            ->selectRaw('
                challenge_type,
                COUNT(*) as attempts,
                SUM(CASE WHEN passed THEN 1 ELSE 0 END) as passed,
                SUM(points_earned) as points
            ')
            ->groupBy('challenge_type')
            ->get();

        $byType = [];
        $total = 0;
        $passed = 0;
        $totalPoints = 0;

        foreach ($results as $row) {
            $byType[$row->challenge_type] = [
                'attempts' => (int) $row->attempts,
                'passed' => (int) $row->passed,
                'points' => (float) $row->points,
                'info' => ChallengeResult::getChallengeInfo($row->challenge_type),
            ];
            $total += $row->attempts;
            $passed += $row->passed;
            $totalPoints += (float) $row->points;
        }

        return [
            'total' => $total,
            'passed' => $passed,
            'total_points' => $totalPoints,
            'by_type' => $byType,
        ];
    }

    /**
     * Get available challenges for user
     *
     * @return array<string, array{available: bool, reason: string|null, info: array}>
     */
    public function getAvailableChallenges(User $user): array
    {
        $available = [];

        foreach (ChallengeResult::getAllTypes() as $type) {
            $canSubmit = true;
            $reason = null;

            if ($type === ChallengeResult::TYPE_MONTHLY_TEST) {
                $canSubmit = $this->canSubmitMonthlyTest($user);
                if (!$canSubmit) {
                    $reason = 'Already submitted this month';
                }
            }

            $available[$type] = [
                'available' => $canSubmit,
                'reason' => $reason,
                'info' => ChallengeResult::getChallengeInfo($type),
            ];
        }

        return $available;
    }

    /**
     * Get challenges pending verification
     *
     * @return Collection<int, ChallengeResult>
     */
    public function getPendingVerification(int $limit = 50): Collection
    {
        return ChallengeResult::whereNull('verified_at')
            ->with('user')
            ->orderBy('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get all challenge types with info
     *
     * @return array<string, array{name: string, description: string, points: int|string, icon: string}>
     */
    public function getAllChallengeTypes(): array
    {
        $types = [];
        foreach (ChallengeResult::getAllTypes() as $type) {
            $types[$type] = ChallengeResult::getChallengeInfo($type);
        }
        return $types;
    }

    /**
     * Revoke challenge points (admin action for disputes)
     *
     * @throws InvalidArgumentException
     */
    public function revokeChallenge(ChallengeResult $challenge, User $admin, string $reason): void
    {
        if (!$challenge->passed) {
            throw new InvalidArgumentException('Challenge was not passed, nothing to revoke');
        }

        DB::transaction(function () use ($challenge, $admin, $reason) {
            $user = $challenge->user;
            $pointsToDeduct = $challenge->points_earned;

            // Deduct points
            $user->update([
                'challenge_score' => max(0, $user->challenge_score - $pointsToDeduct),
            ]);

            // Mark as not passed
            $challenge->update([
                'passed' => false,
                'points_earned' => 0,
                'notes' => 'Revoked by admin: ' . $reason,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            // Recalculate OPRS
            $this->oprsService->recalculateAfterChallenge($user, $challenge->id);
        });
    }

    /**
     * Get best scores for each challenge type
     *
     * @return array<string, int>
     */
    public function getBestScores(User $user): array
    {
        $results = $user->challengeResults()
            ->selectRaw('challenge_type, MAX(score) as best_score')
            ->groupBy('challenge_type')
            ->pluck('best_score', 'challenge_type')
            ->toArray();

        $bestScores = [];
        foreach (ChallengeResult::getAllTypes() as $type) {
            $bestScores[$type] = $results[$type] ?? 0;
        }

        return $bestScores;
    }
}
