<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunityActivity extends Model
{
    // Activity types
    public const TYPE_CHECK_IN = 'check_in';
    public const TYPE_EVENT = 'event';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_WEEKLY_MATCHES = 'weekly_matches';
    public const TYPE_MONTHLY_CHALLENGE = 'monthly_challenge';

    // Points per activity
    public const POINTS = [
        self::TYPE_CHECK_IN => 2,
        self::TYPE_EVENT => 5,
        self::TYPE_REFERRAL => 10,
        self::TYPE_WEEKLY_MATCHES => 5,
        self::TYPE_MONTHLY_CHALLENGE => 15,
    ];

    protected $fillable = [
        'user_id',
        'activity_type',
        'points_earned',
        'reference_id',
        'reference_type',
        'metadata',
    ];

    protected $casts = [
        'points_earned' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all activity types
     *
     * @return array<string>
     */
    public static function getAllTypes(): array
    {
        return [
            self::TYPE_CHECK_IN,
            self::TYPE_EVENT,
            self::TYPE_REFERRAL,
            self::TYPE_WEEKLY_MATCHES,
            self::TYPE_MONTHLY_CHALLENGE,
        ];
    }

    /**
     * Get activity info for display
     *
     * @param string $type
     * @return array<string, mixed>
     */
    public static function getActivityInfo(string $type): array
    {
        $info = [
            self::TYPE_CHECK_IN => [
                'name' => 'Check-in San',
                'description' => 'Check-in tai san OnePickleball/doi tac',
                'points' => 2,
                'limit' => null,
                'icon' => '[LOCATION]',
            ],
            self::TYPE_EVENT => [
                'name' => 'Tham gia Su kien',
                'description' => 'Workshop, clinic, su kien cong dong',
                'points' => 5,
                'limit' => 'per_event',
                'icon' => '[EVENT]',
            ],
            self::TYPE_REFERRAL => [
                'name' => 'Gioi thieu Nguoi moi',
                'description' => 'Nguoi duoc gioi thieu dang ky thanh cong',
                'points' => 10,
                'limit' => null,
                'icon' => '[USER_PLUS]',
            ],
            self::TYPE_WEEKLY_MATCHES => [
                'name' => 'Hoan thanh 5 tran/tuan',
                'description' => 'Choi du 5 tran trong tuan',
                'points' => 5,
                'limit' => 'weekly',
                'icon' => '[CALENDAR]',
            ],
            self::TYPE_MONTHLY_CHALLENGE => [
                'name' => 'Thu thach thang',
                'description' => 'Hoan thanh nhiem vu dac biet hang thang',
                'points' => 15,
                'limit' => 'monthly',
                'icon' => '[TROPHY]',
            ],
        ];

        return $info[$type] ?? [];
    }

    /**
     * Get points for an activity type
     *
     * @param string $type
     * @return float
     */
    public static function getPoints(string $type): float
    {
        return (float) (self::POINTS[$type] ?? 0);
    }

    /**
     * Check if activity type has a limit
     *
     * @param string $type
     * @return string|null
     */
    public static function getLimit(string $type): ?string
    {
        $info = self::getActivityInfo($type);
        return $info['limit'] ?? null;
    }
}
