<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tournament;

class TournamentPolicy
{
    /**
     * Determine if the user can view the tournament.
     */
    public function view(User $user, Tournament $tournament): bool
    {
        // Admin can view all tournaments
        if ($user->hasRole('admin')) {
            return true;
        }

        // Home yard can only view their own tournaments
        return $tournament->user_id === $user->id;
    }

    /**
     * Determine if the user can create tournaments.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('home_yard') || $user->hasRole('admin');
    }

    /**
     * Determine if the user can update the tournament.
     */
    public function update(User $user, Tournament $tournament): bool
    {
        // Admin can update all tournaments
        if ($user->hasRole('admin')) {
            return true;
        }

        // Home yard can only update their own tournaments
        return $tournament->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the tournament.
     */
    public function delete(User $user, Tournament $tournament): bool
    {
        // Admin can delete all tournaments
        if ($user->hasRole('admin')) {
            return true;
        }

        // Home yard can only delete their own tournaments
        return $tournament->user_id === $user->id;
    }
}
