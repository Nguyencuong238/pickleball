# Phase 03: Authorization Policies

**Priority:** Critical
**Status:** Pending
**Depends on:** Phase 02

---

## Context

Permissions per spec:

| Role | Post | Edit Own | Edit Any | Delete Own | Delete Any | Pin |
|------|------|----------|----------|------------|------------|-----|
| Creator | Yes | Yes | Yes | Yes | Yes | Yes |
| Admin | Yes | Yes | Yes | Yes | Yes | Yes |
| Moderator | Yes | Yes | No | Yes | Yes | No |
| Member | No | - | No | - | No | No |

Comments: All members can comment/react. Only owner can edit own comment. Moderator+ can delete any comment.

---

## Related Files

**Create:**
- `app/Policies/ClubPostPolicy.php`
- `app/Policies/ClubPostCommentPolicy.php`

**Modify:**
- `app/Providers/AuthServiceProvider.php` - register policies

---

## Implementation Steps

### Step 1: Create ClubPostPolicy

```php
// app/Policies/ClubPostPolicy.php
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

        return $post->club->members()->where('user_id', $user->id)->exists();
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
        return $post->club->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Can comment: Any club member
     */
    public function comment(User $user, ClubPost $post): bool
    {
        return $post->club->members()->where('user_id', $user->id)->exists();
    }
}
```

### Step 2: Create ClubPostCommentPolicy

```php
// app/Policies/ClubPostCommentPolicy.php
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
```

### Step 3: Register Policies

Update `app/Providers/AuthServiceProvider.php`:

```php
// Add imports at top
use App\Models\ClubPost;
use App\Models\ClubPostComment;
use App\Policies\ClubPostPolicy;
use App\Policies\ClubPostCommentPolicy;

// Add to $policies array
protected $policies = [
    // ... existing policies
    ClubPost::class => ClubPostPolicy::class,
    ClubPostComment::class => ClubPostCommentPolicy::class,
];
```

---

## Todo List

- [ ] Create ClubPostPolicy
- [ ] Create ClubPostCommentPolicy
- [ ] Register policies in AuthServiceProvider
- [ ] Clear config cache: `php artisan config:clear`

---

## Success Criteria

- [ ] Creator/Admin can create, edit any, delete any, pin
- [ ] Moderator can create, edit own, delete any, cannot pin
- [ ] Member cannot create posts
- [ ] All members can comment and react
- [ ] Comment owners can edit/delete own comments
- [ ] Moderator+ can delete any comment

---

## Testing via Tinker

```php
// Get instances
$club = \App\Models\Club::first();
$creator = $club->members()->wherePivot('role', 'creator')->first();
$member = $club->members()->wherePivot('role', 'member')->first();

// Test create permission
Gate::forUser($creator)->allows('create', [\App\Models\ClubPost::class, $club]); // true
Gate::forUser($member)->allows('create', [\App\Models\ClubPost::class, $club]); // false
```

---

## Next Steps

Proceed to Phase 04: Controllers & Services
