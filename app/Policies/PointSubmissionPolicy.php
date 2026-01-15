<?php

namespace App\Policies;

use App\Models\PointSubmission;
use App\Models\User;

class PointSubmissionPolicy
{
    /**
     * Admin can view any submissions
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Admin can view submission details
     */
    public function view(User $user, PointSubmission $submission): bool
    {
        // Admin can view all
        if ($user->hasRole('admin')) {
            return true;
        }

        // User can view their own submissions
        return $user->id === $submission->user_id;
    }

    /**
     * Admin can approve pending submissions
     */
    public function approve(User $user, PointSubmission $submission): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        return $submission->isPending();
    }

    /**
     * Admin can reject pending submissions
     */
    public function reject(User $user, PointSubmission $submission): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        return $submission->isPending();
    }

    /**
     * Admin can bulk approve submissions
     */
    public function bulkApprove(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
