<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialProfileVerification extends Model
{
    // Platform constants
    public const PLATFORM_FACEBOOK = 'facebook';
    public const PLATFORM_YOUTUBE = 'youtube';
    public const PLATFORM_TIKTOK = 'tiktok';

    protected $fillable = [
        'user_id',
        'platform',
        'profile_url',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user who owns this verification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who verified
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if profile URL is already taken by another user
     */
    public static function isProfileUrlTaken(string $url, ?int $excludeUserId = null): bool
    {
        $query = static::where('profile_url', $url);
        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }
        return $query->exists();
    }

    /**
     * Check if user has verified a specific platform
     */
    public static function hasVerifiedPlatform(int $userId, string $platform): bool
    {
        return static::where('user_id', $userId)
            ->where('platform', $platform)
            ->whereNotNull('verified_at')
            ->exists();
    }

    /**
     * Get all verified platforms for a user
     */
    public static function getVerifiedPlatforms(int $userId): array
    {
        return static::where('user_id', $userId)
            ->whereNotNull('verified_at')
            ->pluck('platform')
            ->toArray();
    }

    /**
     * Get platform label for display
     */
    public function getPlatformLabel(): string
    {
        return match ($this->platform) {
            self::PLATFORM_FACEBOOK => 'Facebook',
            self::PLATFORM_YOUTUBE => 'YouTube',
            self::PLATFORM_TIKTOK => 'TikTok',
            default => $this->platform,
        };
    }

    /**
     * Scope for verified profiles
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope for unverified profiles
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_at');
    }
}
