<?php

namespace App\Services;

use App\Models\OprsHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OprsService
{
    // OPRS component weights
    public const WEIGHT_ELO = 0.7;
    public const WEIGHT_CHALLENGE = 0.2;
    public const WEIGHT_COMMUNITY = 0.1;

    // OPR Level thresholds
    public const OPR_LEVELS = [
        '1.0' => ['name' => 'Beginner', 'min' => 0, 'max' => 599],
        '2.0' => ['name' => 'Novice', 'min' => 600, 'max' => 899],
        '3.0' => ['name' => 'Intermediate', 'min' => 900, 'max' => 1099],
        '3.5' => ['name' => 'Upper Intermediate', 'min' => 1100, 'max' => 1349],
        '4.0' => ['name' => 'Advanced', 'min' => 1350, 'max' => 1599],
        '4.5' => ['name' => 'Pro', 'min' => 1600, 'max' => 1849],
        '5.0+' => ['name' => 'Elite', 'min' => 1850, 'max' => PHP_INT_MAX],
    ];

    /**
     * Calculate total OPRS from components
     * OPRS = (0.7 * Elo) + (0.2 * Challenge) + (0.1 * Community)
     */
    public function calculateOprs(User $user): float
    {
        $oprs = (self::WEIGHT_ELO * $user->elo_rating)
              + (self::WEIGHT_CHALLENGE * $user->challenge_score)
              + (self::WEIGHT_COMMUNITY * $user->community_score);

        return round($oprs, 2);
    }

    /**
     * Determine OPR Level from OPRS score
     */
    public function calculateOprLevel(float $oprs): string
    {
        foreach (self::OPR_LEVELS as $level => $range) {
            if ($oprs >= $range['min'] && $oprs <= $range['max']) {
                return $level;
            }
        }
        return '1.0';
    }

    /**
     * Get level info
     *
     * @return array{name: string, min: int, max: int}
     */
    public function getOprLevelInfo(string $level): array
    {
        return self::OPR_LEVELS[$level] ?? self::OPR_LEVELS['1.0'];
    }

    /**
     * Update user's OPRS and record history
     *
     * @param array<string, mixed>|null $metadata
     */
    public function updateUserOprs(User $user, string $reason, ?array $metadata = null): void
    {
        DB::transaction(function () use ($user, $reason, $metadata) {
            $user->refresh();

            $newOprs = $this->calculateOprs($user);
            $newLevel = $this->calculateOprLevel($newOprs);

            // Record history
            OprsHistory::create([
                'user_id' => $user->id,
                'elo_score' => $user->elo_rating,
                'challenge_score' => $user->challenge_score,
                'community_score' => $user->community_score,
                'total_oprs' => $newOprs,
                'opr_level' => $newLevel,
                'change_reason' => $reason,
                'metadata' => $metadata,
            ]);

            // Update user
            $user->update([
                'total_oprs' => $newOprs,
                'opr_level' => $newLevel,
            ]);
        });
    }

    /**
     * Recalculate after Elo change (match result)
     */
    public function recalculateAfterMatch(User $user, ?int $matchId = null): void
    {
        $this->updateUserOprs(
            $user,
            OprsHistory::REASON_MATCH_RESULT,
            $matchId ? ['ocr_match_id' => $matchId] : null
        );
    }

    /**
     * Recalculate after challenge completion
     */
    public function recalculateAfterChallenge(User $user, ?int $challengeId = null): void
    {
        $this->updateUserOprs(
            $user,
            OprsHistory::REASON_CHALLENGE_COMPLETED,
            $challengeId ? ['challenge_result_id' => $challengeId] : null
        );
    }

    /**
     * Recalculate after community activity
     */
    public function recalculateAfterActivity(User $user, ?int $activityId = null): void
    {
        $this->updateUserOprs(
            $user,
            OprsHistory::REASON_COMMUNITY_ACTIVITY,
            $activityId ? ['community_activity_id' => $activityId] : null
        );
    }

    /**
     * Recalculate after skill quiz completion
     */
    public function recalculateAfterSkillQuiz(User $user, ?string $attemptId = null): void
    {
        $this->updateUserOprs(
            $user,
            OprsHistory::REASON_SKILL_QUIZ,
            $attemptId ? ['skill_quiz_attempt_id' => $attemptId] : null
        );
    }

    /**
     * Batch recalculate all users' OPRS
     * @return int Number of users updated
     */
    public function batchRecalculateAll(): int
    {
        $count = 0;

        User::chunk(100, function (Collection $users) use (&$count) {
            foreach ($users as $user) {
                $this->updateUserOprs(
                    $user,
                    OprsHistory::REASON_INITIAL_CALCULATION
                );
                $count++;
            }
        });

        return $count;
    }

    /**
     * Get OPRS breakdown for display
     *
     * @return array{elo: array{raw: int, weight: float, weighted: float}, challenge: array{raw: float, weight: float, weighted: float}, community: array{raw: float, weight: float, weighted: float}, total: float, level: string, level_info: array}
     */
    public function getOprsBreakdown(User $user): array
    {
        return [
            'elo' => [
                'raw' => $user->elo_rating,
                'weight' => self::WEIGHT_ELO,
                'weighted' => round($user->elo_rating * self::WEIGHT_ELO, 2),
            ],
            'challenge' => [
                'raw' => (float) $user->challenge_score,
                'weight' => self::WEIGHT_CHALLENGE,
                'weighted' => round($user->challenge_score * self::WEIGHT_CHALLENGE, 2),
            ],
            'community' => [
                'raw' => (float) $user->community_score,
                'weight' => self::WEIGHT_COMMUNITY,
                'weighted' => round($user->community_score * self::WEIGHT_COMMUNITY, 2),
            ],
            'total' => (float) $user->total_oprs,
            'level' => $user->opr_level,
            'level_info' => $this->getOprLevelInfo($user->opr_level),
        ];
    }

    /**
     * Estimate OPRS change before action
     *
     * @return array{before: float, after: float, change: float, new_level: string}
     */
    public function estimateOprsChange(User $user, string $component, float $change): array
    {
        $currentOprs = (float) $user->total_oprs;

        $weight = match ($component) {
            'elo' => self::WEIGHT_ELO,
            'challenge' => self::WEIGHT_CHALLENGE,
            'community' => self::WEIGHT_COMMUNITY,
            default => 0,
        };

        $oprsChange = $change * $weight;
        $newOprs = round($currentOprs + $oprsChange, 2);

        return [
            'before' => $currentOprs,
            'after' => $newOprs,
            'change' => round($oprsChange, 2),
            'new_level' => $this->calculateOprLevel($newOprs),
        ];
    }

    /**
     * Admin adjustment of a specific component
     *
     * @throws InvalidArgumentException
     */
    public function adminAdjustment(
        User $user,
        string $component,
        float $amount,
        string $reason = 'Admin adjustment'
    ): void {
        DB::transaction(function () use ($user, $component, $amount, $reason) {
            // Update the specific component
            $field = match ($component) {
                'challenge' => 'challenge_score',
                'community' => 'community_score',
                default => null,
            };

            if (!$field) {
                throw new InvalidArgumentException('Invalid component: ' . $component);
            }

            $newValue = max(0, $user->$field + $amount);
            $user->update([$field => $newValue]);

            // Recalculate OPRS
            $this->updateUserOprs($user, OprsHistory::REASON_ADMIN_ADJUSTMENT, [
                'component' => $component,
                'adjustment' => $amount,
                'admin_reason' => $reason,
            ]);
        });
    }

    /**
     * Get leaderboard data with OPRS
     *
     * @return Collection<int, User>
     */
    public function getLeaderboard(
        ?string $oprLevel = null,
        int $limit = 50,
        int $offset = 0
    ): Collection {
        $query = User::query()
            ->where('total_oprs', '>', 0)
            ->orderByDesc('total_oprs');

        if ($oprLevel) {
            $query->where('opr_level', $oprLevel);
        }

        return $query->skip($offset)->take($limit)->get();
    }

    /**
     * Get level distribution stats
     *
     * @return array<string, int>
     */
    public function getLevelDistribution(): array
    {
        return User::query()
            ->selectRaw('opr_level, COUNT(*) as count')
            ->groupBy('opr_level')
            ->pluck('count', 'opr_level')
            ->toArray();
    }

    /**
     * Get user's OPRS rank position
     */
    public function getUserRank(User $user): int
    {
        return User::query()
            ->where('total_oprs', '>', $user->total_oprs)
            ->count() + 1;
    }

    /**
     * Get all OPR levels for display
     *
     * @return array<string, array{name: string, min: int, max: int}>
     */
    public static function getAllLevels(): array
    {
        return self::OPR_LEVELS;
    }
}
