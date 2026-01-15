<?php

namespace App\Policies;

use App\Models\PointTask;
use App\Models\User;
use App\Services\PointEarningService;

class PointTaskPolicy
{
    /**
     * Admin can view any tasks
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Admin can view task details
     */
    public function view(User $user, PointTask $task): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Admin can update task settings (points, active status)
     */
    public function update(User $user, PointTask $task): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * User can view submission form for this task
     */
    public function showSubmitForm(User $user, PointTask $task): bool
    {
        // Task must be active and require approval
        return $task->is_active && $task->requires_approval;
    }

    /**
     * User can submit proof for this task
     */
    public function submit(User $user, PointTask $task): bool
    {
        // Task must be active
        if (!$task->is_active) {
            return false;
        }

        // Task must require approval (submission-based)
        if (!$task->requires_approval) {
            return false;
        }

        // Check eligibility via service
        $service = app(PointEarningService::class);
        return $service->canEarn($user, $task->code);
    }
}
