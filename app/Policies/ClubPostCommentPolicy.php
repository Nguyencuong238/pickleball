<?php

namespace App\Policies;

use App\Models\ClubPostComment;
use App\Models\User;

class ClubPostCommentPolicy
{
    /**
     * Can update comment: Owner only
     */
    public function update(User $user, ClubPostComment $comment): bool
    {
        return $comment->isOwnedBy($user);
    }

    /**
     * Can delete: Owner, or Moderator+ of the club
     */
    public function delete(User $user, ClubPostComment $comment): bool
    {
        // Owner can delete own comment
        if ($comment->isOwnedBy($user)) {
            return true;
        }

        // Moderator+ can delete any comment
        $role = $comment->post->club->getMemberRole($user);
        return in_array($role, ['creator', 'admin', 'moderator']);
    }
}
