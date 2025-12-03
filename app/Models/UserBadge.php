<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    use HasFactory;

    protected $table = 'user_badges';

    protected $fillable = [
        'user_id',
        'badge_type',
        'earned_at',
        'metadata',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Badge type constants
    public const BADGE_FIRST_WIN = 'first_win';
    public const BADGE_STREAK_3 = 'streak_3';
    public const BADGE_STREAK_5 = 'streak_5';
    public const BADGE_STREAK_10 = 'streak_10';
    public const BADGE_RANK_SILVER = 'rank_silver';
    public const BADGE_RANK_GOLD = 'rank_gold';
    public const BADGE_RANK_PLATINUM = 'rank_platinum';
    public const BADGE_RANK_DIAMOND = 'rank_diamond';
    public const BADGE_RANK_MASTER = 'rank_master';
    public const BADGE_RANK_GRANDMASTER = 'rank_grandmaster';
    public const BADGE_MATCHES_10 = 'matches_10';
    public const BADGE_MATCHES_50 = 'matches_50';
    public const BADGE_MATCHES_100 = 'matches_100';

    /**
     * Get badge metadata by type
     *
     * @param string $type
     * @return array{name: string, description: string, icon: string}
     */
    public static function getBadgeInfo(string $type): array
    {
        $badges = [
            self::BADGE_FIRST_WIN => [
                'name' => 'First Blood',
                'description' => 'Won your first match',
                'icon' => '[TROPHY]'
            ],
            self::BADGE_STREAK_3 => [
                'name' => 'On Fire',
                'description' => '3 win streak',
                'icon' => '[FIRE]'
            ],
            self::BADGE_STREAK_5 => [
                'name' => 'Unstoppable',
                'description' => '5 win streak',
                'icon' => '[LIGHTNING]'
            ],
            self::BADGE_STREAK_10 => [
                'name' => 'Legend',
                'description' => '10 win streak',
                'icon' => '[STAR]'
            ],
            self::BADGE_RANK_SILVER => [
                'name' => 'Silver Player',
                'description' => 'Reached Silver rank',
                'icon' => '[SILVER]'
            ],
            self::BADGE_RANK_GOLD => [
                'name' => 'Gold Player',
                'description' => 'Reached Gold rank',
                'icon' => '[GOLD]'
            ],
            self::BADGE_RANK_PLATINUM => [
                'name' => 'Platinum Player',
                'description' => 'Reached Platinum rank',
                'icon' => '[PLATINUM]'
            ],
            self::BADGE_RANK_DIAMOND => [
                'name' => 'Diamond Player',
                'description' => 'Reached Diamond rank',
                'icon' => '[DIAMOND]'
            ],
            self::BADGE_RANK_MASTER => [
                'name' => 'Master Player',
                'description' => 'Reached Master rank',
                'icon' => '[MASTER]'
            ],
            self::BADGE_RANK_GRANDMASTER => [
                'name' => 'Grandmaster',
                'description' => 'Reached Grandmaster rank',
                'icon' => '[GRANDMASTER]'
            ],
            self::BADGE_MATCHES_10 => [
                'name' => 'Regular',
                'description' => 'Played 10 matches',
                'icon' => '[PLAYER]'
            ],
            self::BADGE_MATCHES_50 => [
                'name' => 'Veteran',
                'description' => 'Played 50 matches',
                'icon' => '[VETERAN]'
            ],
            self::BADGE_MATCHES_100 => [
                'name' => 'Pro',
                'description' => 'Played 100 matches',
                'icon' => '[CROWN]'
            ],
        ];

        return $badges[$type] ?? ['name' => 'Unknown', 'description' => '', 'icon' => '[BADGE]'];
    }

    /**
     * Get all available badge types
     *
     * @return array<string>
     */
    public static function getAllBadgeTypes(): array
    {
        return [
            self::BADGE_FIRST_WIN,
            self::BADGE_STREAK_3,
            self::BADGE_STREAK_5,
            self::BADGE_STREAK_10,
            self::BADGE_RANK_SILVER,
            self::BADGE_RANK_GOLD,
            self::BADGE_RANK_PLATINUM,
            self::BADGE_RANK_DIAMOND,
            self::BADGE_RANK_MASTER,
            self::BADGE_RANK_GRANDMASTER,
            self::BADGE_MATCHES_10,
            self::BADGE_MATCHES_50,
            self::BADGE_MATCHES_100,
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getNameAttribute(): string
    {
        return self::getBadgeInfo($this->badge_type)['name'];
    }

    public function getDescriptionAttribute(): string
    {
        return self::getBadgeInfo($this->badge_type)['description'];
    }

    public function getIconAttribute(): string
    {
        return self::getBadgeInfo($this->badge_type)['icon'];
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('badge_type', $type);
    }
}
