<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\ClubPost;
use App\Models\User;

class ClubPostPolicy
{
    /**
     * Can view public posts (guests) or members_only posts (members)
     */
    public function view(?User $user, ClubPost $post): bool
    {
        if ($post->visibility === 'public') {
            return true;
        }

        // members_only requires membership
        if (!$user) return false;

        return $post->club->isMember($user);
    }

    /**
     * Can create post: Creator, Admin, Moderator only
     */
    public function create(User $user, Club $club): bool
    {
        return $club->isManagement($user);
    }

    /**
     * Can update post: Owner always, Admin for any post
     */
    public function update(User $user, ClubPost $post): bool
    {
        // Owner can always edit
        if ($post->isOwnedBy($user)) {
            return true;
        }

        // Admin/Creator can edit any post
        return $post->club->isAdmin($user);
    }

    /**
     * Can delete post: Owner, Moderator for own, Admin for any
     */
    public function delete(User $user, ClubPost $post): bool
    {
        // Owner can delete own post
        if ($post->isOwnedBy($user)) {
            return true;
        }

        $role = $post->club->getMemberRole($user);

        // Moderator can delete any (but not edit any)
        if ($role === 'moderator') {
            return true;
        }

        // Admin/Creator can delete any
        return in_array($role, ['creator', 'admin']);
    }

    /**
     * Can pin/unpin: Creator and Admin only
     */
    public function pin(User $user, ClubPost $post): bool
    {
        return $post->club->isAdmin($user);
    }

    /**
     * Can react: Any club member
     */
    public function react(User $user, ClubPost $post): bool
    {
        return $post->club->isMember($user);
    }

    /**
     * Can comment: Any club member
     */
    public function comment(User $user, ClubPost $post): bool
    {
        return $post->club->isMember($user);
    }
}
