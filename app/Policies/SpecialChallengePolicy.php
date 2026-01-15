<?php

namespace App\Policies;

use App\Models\SpecialChallenge;
use App\Models\User;

class SpecialChallengePolicy
{
    /**
     * Admin can view any challenges
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Admin can view challenge details
     */
    public function view(User $user, SpecialChallenge $challenge): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Admin can create challenges
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Admin can update challenges
     */
    public function update(User $user, SpecialChallenge $challenge): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Admin can delete challenges
     */
    public function delete(User $user, SpecialChallenge $challenge): bool
    {
        return $user->hasRole('admin');
    }
}
