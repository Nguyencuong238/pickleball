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

    public function getIsEditedAttribute(): bool
    {
        return $this->edited_at !== null;
    }

    // Methods
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
