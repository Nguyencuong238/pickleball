<?php

namespace App\Services;

use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Models\User;
use App\Models\UserPointTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PointEarningService
{
    /**
     * Award points for a task (auto-award, no approval needed)
     *
     * @param array<string, mixed> $metadata
     * @throws InvalidArgumentException
     */
    public function awardPoints(User $user, string $taskCode, array $metadata = []): bool
    {
        $task = PointTask::findByCode($taskCode);

        if (!$task || !$task->is_active) {
            throw new InvalidArgumentException("Task not found or inactive: {$taskCode}");
        }

        if ($task->requires_approval) {
            throw new InvalidArgumentException("Task requires approval: {$taskCode}");
        }

        if (!$this->canEarn($user, $taskCode, $metadata)) {
            return false;
        }

        return DB::transaction(function () use ($user, $task, $metadata) {
            // Get or create wallet
            $wallet = $user->wallet ?? $user->wallet()->create(['points' => 0]);

            // Add points via wallet (creates transaction automatically)
            $wallet->addPoints(
                $task->points,
                'earn',
                $task->name,
                array_merge($metadata, [
                    'task_code' => $task->code,
                    'task_id' => $task->id,
                ])
            );

            return true;
        });
    }

    /**
     * Check if user can earn points for this task
     *
     * @param array<string, mixed> $context
     */
    public function canEarn(User $user, string $taskCode, array $context = []): bool
    {
        $task = PointTask::findByCode($taskCode);

        if (!$task || !$task->is_active) {
            return false;
        }

        // Check role
        if (!$this->userHasRole($user, $task->role)) {
            return false;
        }

        // Check frequency
        return $this->checkFrequency($user, $task, $context);
    }

    /**
     * Get available tasks for user with eligibility status
     *
     * @return Collection<int, array{task: PointTask, can_earn: bool, reason: string|null}>
     */
    public function getAvailableTasks(User $user): Collection
    {
        // Determine user's primary role for point tasks
        $role = $this->getPrimaryRole($user);
        $tasks = PointTask::getActiveByRole($role);

        return $tasks->map(function ($task) use ($user) {
            $canEarn = $this->canEarn($user, $task->code);
            $reason = $canEarn ? null : $this->getIneligibilityReason($user, $task);

            return [
                'task' => $task,
                'can_earn' => $canEarn,
                'reason' => $reason,
            ];
        });
    }

    /**
     * Get all tasks for user (includes all roles if user has multiple)
     *
     * @return Collection<int, array{task: PointTask, can_earn: bool, reason: string|null}>
     */
    public function getAllAvailableTasks(User $user): Collection
    {
        $roles = $this->getUserRoles($user);
        $tasks = PointTask::whereIn('role', $roles)->where('is_active', true)->get();

        return $tasks->map(function ($task) use ($user) {
            $canEarn = $this->canEarn($user, $task->code);
            $reason = $canEarn ? null : $this->getIneligibilityReason($user, $task);

            return [
                'task' => $task,
                'can_earn' => $canEarn,
                'reason' => $reason,
            ];
        });
    }

    /**
     * Get user's point earning history
     */
    public function getTaskHistory(User $user, int $limit = 50): Collection
    {
        return $user->pointTransactions()
            ->where('type', 'earn')
            ->whereNotNull('metadata->task_code')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get summary of earned points by task
     *
     * @return array<string, array{count: int, total_points: int}>
     */
    public function getEarningSummary(User $user): array
    {
        $transactions = $user->pointTransactions()
            ->where('type', 'earn')
            ->whereNotNull('metadata->task_code')
            ->get();

        $summary = [];
        foreach ($transactions as $tx) {
            $taskCode = $tx->metadata['task_code'] ?? 'unknown';
            if (!isset($summary[$taskCode])) {
                $summary[$taskCode] = ['count' => 0, 'total_points' => 0];
            }
            $summary[$taskCode]['count']++;
            $summary[$taskCode]['total_points'] += $tx->points;
        }

        return $summary;
    }

    /**
     * Check frequency limits
     *
     * @param array<string, mixed> $context
     */
    private function checkFrequency(User $user, PointTask $task, array $context = []): bool
    {
        $query = UserPointTransaction::where('user_id', $user->id)
            ->where('metadata->task_code', $task->code);

        switch ($task->frequency) {
            case PointTask::FREQ_UNLIMITED:
                // Check for duplicate (same context)
                if (!empty($context)) {
                    return !$this->hasDuplicateContext($user, $task, $context);
                }
                return true;

            case PointTask::FREQ_DAILY:
                return !$query->whereDate('created_at', Carbon::today())->exists();

            case PointTask::FREQ_WEEKLY:
                return !$query->where('created_at', '>=', Carbon::now()->startOfWeek())->exists();

            case PointTask::FREQ_MONTHLY:
                return !$query->where('created_at', '>=', Carbon::now()->startOfMonth())->exists();

            case PointTask::FREQ_ONCE:
                // Special handling for home_yard tasks (once per stadium)
                if ($task->role === PointTask::ROLE_HOME_YARD && isset($context['stadium_id'])) {
                    return !$query->where('metadata->stadium_id', $context['stadium_id'])->exists();
                }
                return !$query->exists();

            default:
                return true;
        }
    }

    /**
     * Check for duplicate context (prevent double awards for same action)
     *
     * @param array<string, mixed> $context
     */
    private function hasDuplicateContext(User $user, PointTask $task, array $context): bool
    {
        $query = UserPointTransaction::where('user_id', $user->id)
            ->where('metadata->task_code', $task->code);

        // Check specific context fields
        if (isset($context['match_id'])) {
            return $query->where('metadata->match_id', $context['match_id'])->exists();
        }
        if (isset($context['event_id'])) {
            return $query->where('metadata->event_id', $context['event_id'])->exists();
        }
        if (isset($context['referred_user_id'])) {
            return $query->where('metadata->referred_user_id', $context['referred_user_id'])->exists();
        }
        if (isset($context['verification_request_id'])) {
            return $query->where('metadata->verification_request_id', $context['verification_request_id'])->exists();
        }
        if (isset($context['social_id'])) {
            return $query->where('metadata->social_id', $context['social_id'])->exists();
        }
        if (isset($context['tournament_id'])) {
            return $query->where('metadata->tournament_id', $context['tournament_id'])->exists();
        }

        return false;
    }

    /**
     * Get reason why user cannot earn
     */
    private function getIneligibilityReason(User $user, PointTask $task): string
    {
        if (!$this->userHasRole($user, $task->role)) {
            return "Yeu cau quyen {$task->role}";
        }

        switch ($task->frequency) {
            case PointTask::FREQ_DAILY:
                return 'Đã nhận trong ngày';
            case PointTask::FREQ_WEEKLY:
                return 'Đã nhận trong tuần';
            case PointTask::FREQ_MONTHLY:
                return 'Đã nhận trong tháng';
            case PointTask::FREQ_ONCE:
                return 'Đã hoàn thành';
            default:
                return 'Không đủ điều kiện';
        }
    }

    /**
     * Check if user has required role
     */
    private function userHasRole(User $user, string $requiredRole): bool
    {
        // All users have 'user' role implicitly
        if ($requiredRole === PointTask::ROLE_USER) {
            return true;
        }

        return $user->hasRole($requiredRole);
    }

    /**
     * Get user's primary role for point tasks
     */
    private function getPrimaryRole(User $user): string
    {
        // Priority: expert_host > referee > home_yard > user
        if ($user->hasRole('expert_host')) {
            return PointTask::ROLE_EXPERT_HOST;
        }
        if ($user->hasRole('referee')) {
            return PointTask::ROLE_REFEREE;
        }
        if ($user->hasRole('home_yard')) {
            return PointTask::ROLE_HOME_YARD;
        }
        return PointTask::ROLE_USER;
    }

    /**
     * Get all roles user has for point tasks
     *
     * @return array<string>
     */
    private function getUserRoles(User $user): array
    {
        $roles = [PointTask::ROLE_USER]; // Everyone has user role

        if ($user->hasRole('home_yard')) {
            $roles[] = PointTask::ROLE_HOME_YARD;
        }
        if ($user->hasRole('referee')) {
            $roles[] = PointTask::ROLE_REFEREE;
        }
        if ($user->hasRole('expert_host')) {
            $roles[] = PointTask::ROLE_EXPERT_HOST;
        }

        return $roles;
    }
}
