# Phase 02: Models & Relationships

**Priority:** Critical
**Status:** Pending
**Depends on:** Phase 01

---

## Context

Follow existing model patterns from VideoComment, VideoLike, Club models. Models use:
- HasFactory trait
- Explicit $fillable
- BelongsTo/HasMany/BelongsToMany relationships
- Return type hints

---

## Related Files

**Create:**
- `app/Models/ClubPost.php`
- `app/Models/ClubPostMedia.php`
- `app/Models/ClubPostReaction.php`
- `app/Models/ClubPostComment.php`

**Modify:**
- `app/Models/Club.php` - add posts() relationship

---

## Implementation Steps

### Step 1: Create ClubPost model

```php
// app/Models/ClubPost.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_id',
        'user_id',
        'content',
        'visibility',
        'is_pinned',
        'pinned_at',
        'pinned_by',
        'edited_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'pinned_at' => 'datetime',
        'edited_at' => 'datetime',
    ];

    // Relationships
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pinnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ClubPostMedia::class)->orderBy('order');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ClubPostReaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ClubPostComment::class)->whereNull('parent_id');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(ClubPostComment::class);
    }

    // Scopes
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeFeed($query)
    {
        return $query->orderByDesc('is_pinned')
                     ->orderByDesc('pinned_at')
                     ->orderByDesc('created_at');
    }

    // Accessors
    public function getReactionCountsAttribute(): array
    {
        return $this->reactions()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    public function getTotalReactionsAttribute(): int
    {
        return $this->reactions()->count();
    }

    public function getCommentsCountAttribute(): int
    {
        return $this->allComments()->count();
    }

    // Methods
    public function isEditedAttribute(): bool
    {
        return $this->edited_at !== null;
    }

    public function getUserReaction(?User $user): ?string
    {
        if (!$user) return null;
        $reaction = $this->reactions()->where('user_id', $user->id)->first();
        return $reaction?->type;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
```

### Step 2: Create ClubPostMedia model

```php
// app/Models/ClubPostMedia.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClubPostMedia extends Model
{
    use HasFactory;

    protected $table = 'club_post_media';

    protected $fillable = [
        'club_post_id',
        'type',
        'path',
        'disk',
        'youtube_url',
        'size',
        'order',
    ];

    protected $casts = [
        'size' => 'integer',
        'order' => 'integer',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(ClubPost::class, 'club_post_id');
    }

    // Accessors
    public function getUrlAttribute(): ?string
    {
        if ($this->type === 'youtube') {
            return $this->youtube_url;
        }

        if ($this->path) {
            return Storage::disk($this->disk)->url($this->path);
        }

        return null;
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->type !== 'youtube') return null;

        // Convert YouTube URL to embed URL
        $url = $this->youtube_url;

        // Handle various YouTube URL formats
        if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $url, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}";
        }
        if (preg_match('/youtu\.be\/([^?]+)/', $url, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}";
        }
        if (preg_match('/youtube\.com\/embed\/([^?]+)/', $url, $matches)) {
            return $url; // Already embed URL
        }

        return null;
    }

    // Methods
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isYoutube(): bool
    {
        return $this->type === 'youtube';
    }
}
```

### Step 3: Create ClubPostReaction model

```php
// app/Models/ClubPostReaction.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubPostReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_post_id',
        'user_id',
        'type',
    ];

    public const TYPE_LIKE = 'like';
    public const TYPE_LOVE = 'love';
    public const TYPE_FIRE = 'fire';

    public const TYPES = [
        self::TYPE_LIKE,
        self::TYPE_LOVE,
        self::TYPE_FIRE,
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(ClubPost::class, 'club_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Step 4: Create ClubPostComment model

```php
// app/Models/ClubPostComment.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubPostComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_post_id',
        'user_id',
        'parent_id',
        'content',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(ClubPost::class, 'club_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ClubPostComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ClubPostComment::class, 'parent_id')->with('user');
    }

    // Methods
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
```

### Step 5: Update Club model

Add to `app/Models/Club.php`:

```php
// Add this import at top
use App\Models\ClubPost;

// Add this relationship method
public function posts(): HasMany
{
    return $this->hasMany(ClubPost::class);
}

// Add helper method to get member role
public function getMemberRole(User $user): ?string
{
    $member = $this->members()->where('user_id', $user->id)->first();
    return $member?->pivot->role;
}

// Add helper to check if user is management (can post)
public function isManagement(User $user): bool
{
    $role = $this->getMemberRole($user);
    return in_array($role, ['creator', 'admin', 'moderator']);
}

// Add helper to check if user is admin (creator/admin)
public function isAdmin(User $user): bool
{
    $role = $this->getMemberRole($user);
    return in_array($role, ['creator', 'admin']);
}
```

---

## Todo List

- [ ] Create ClubPost model
- [ ] Create ClubPostMedia model
- [ ] Create ClubPostReaction model
- [ ] Create ClubPostComment model
- [ ] Add posts() relationship to Club model
- [ ] Add helper methods to Club model
- [ ] Test relationships work via Tinker

---

## Success Criteria

- [ ] All models created with correct namespace
- [ ] Relationships return expected data
- [ ] Model casts work correctly
- [ ] Scopes return expected queries

---

## Testing via Tinker

```php
// Test after migrations
$club = \App\Models\Club::first();
$club->posts()->count(); // Should be 0

// Test member role helper
$user = \App\Models\User::first();
$club->getMemberRole($user);
$club->isManagement($user);
```

---

## Next Steps

Proceed to Phase 03: Authorization Policies
