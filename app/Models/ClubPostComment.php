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
