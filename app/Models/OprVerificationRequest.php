<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class OprVerificationRequest extends Model implements HasMedia
{
    use InteractsWithMedia;

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // Media constraints
    public const MAX_IMAGES = 5;
    public const MAX_VIDEOS = 3;

    protected $fillable = [
        'user_id',
        'status',
        'media_paths',
        'links',
        'notes',
        'verifier_id',
        'verifier_notes',
        'verified_at',
    ];

    protected $casts = [
        'media_paths' => 'array',
        'links' => 'array',
        'verified_at' => 'datetime',
    ];

    // ==================== Relationships ====================

    /**
     * Get the user who requested verification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the verifier who processed this request
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    // ==================== Scopes ====================

    /**
     * Scope to get only pending requests
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get requests for a specific user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ==================== Media Collections ====================

    /**
     * Register media collections for verification evidence
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('verification_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('verification_videos')
            ->acceptsMimeTypes(['video/mp4', 'video/quicktime', 'video/webm']);
    }

    // ==================== Status Helpers ====================

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    // ==================== Link Helpers ====================

    /**
     * Get YouTube links from submitted links
     */
    public function getYoutubeLinks(): array
    {
        return collect($this->links ?? [])
            ->filter(fn($link) => ($link['type'] ?? '') === 'youtube')
            ->values()
            ->toArray();
    }

    /**
     * Get Facebook links from submitted links
     */
    public function getFacebookLinks(): array
    {
        return collect($this->links ?? [])
            ->filter(fn($link) => ($link['type'] ?? '') === 'facebook')
            ->values()
            ->toArray();
    }

    /**
     * Get TikTok links from submitted links
     */
    public function getTiktokLinks(): array
    {
        return collect($this->links ?? [])
            ->filter(fn($link) => ($link['type'] ?? '') === 'tiktok')
            ->values()
            ->toArray();
    }

    /**
     * Get status label in Vietnamese
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Đang chờ duyệt',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Đã từ chối',
            default => 'Không xác định',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
