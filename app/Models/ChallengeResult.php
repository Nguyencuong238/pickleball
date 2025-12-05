<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeResult extends Model
{
    // Challenge types
    public const TYPE_DINKING_RALLY = 'dinking_rally';
    public const TYPE_DROP_SHOT = 'drop_shot';
    public const TYPE_SERVE_ACCURACY = 'serve_accuracy';
    public const TYPE_MONTHLY_TEST = 'monthly_test';

    // Points per challenge type
    public const POINTS = [
        self::TYPE_DINKING_RALLY => 10,
        self::TYPE_DROP_SHOT => 8,
        self::TYPE_SERVE_ACCURACY => 6,
        self::TYPE_MONTHLY_TEST => ['min' => 30, 'max' => 50],
    ];

    // Pass thresholds
    public const THRESHOLDS = [
        self::TYPE_DINKING_RALLY => ['rallies' => 20],
        self::TYPE_DROP_SHOT => ['success' => 5, 'total' => 10],
        self::TYPE_SERVE_ACCURACY => ['success' => 7, 'total' => 10],
        self::TYPE_MONTHLY_TEST => ['score' => 70],
    ];

    protected $fillable = [
        'user_id',
        'challenge_type',
        'score',
        'passed',
        'points_earned',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'score' => 'integer',
        'passed' => 'boolean',
        'points_earned' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get all challenge types
     *
     * @return array<string>
     */
    public static function getAllTypes(): array
    {
        return [
            self::TYPE_DINKING_RALLY,
            self::TYPE_DROP_SHOT,
            self::TYPE_SERVE_ACCURACY,
            self::TYPE_MONTHLY_TEST,
        ];
    }

    /**
     * Get challenge info for display
     *
     * @param string $type
     * @return array<string, mixed>
     */
    public static function getChallengeInfo(string $type): array
    {
        $info = [
            self::TYPE_DINKING_RALLY => [
                'name' => 'Dinking Rally Test',
                'description' => 'Rally lien tuc 20 lan khong loi',
                'points' => 10,
                'icon' => '[RALLY]',
            ],
            self::TYPE_DROP_SHOT => [
                'name' => 'Drop Shot Accuracy',
                'description' => '5/10 drop shot vao vung kitchen',
                'points' => 8,
                'icon' => '[DROP]',
            ],
            self::TYPE_SERVE_ACCURACY => [
                'name' => 'Serve Accuracy',
                'description' => '7/10 serve vao vung muc tieu',
                'points' => 6,
                'icon' => '[SERVE]',
            ],
            self::TYPE_MONTHLY_TEST => [
                'name' => 'Monthly Test',
                'description' => 'Bai test ky thuat hang thang',
                'points' => '30-50',
                'icon' => '[TEST]',
            ],
        ];

        return $info[$type] ?? [];
    }

    /**
     * Check if challenge passed based on type and score
     */
    public function checkPassed(): bool
    {
        $threshold = self::THRESHOLDS[$this->challenge_type] ?? null;
        if (!$threshold) {
            return false;
        }

        return match ($this->challenge_type) {
            self::TYPE_DINKING_RALLY => $this->score >= $threshold['rallies'],
            self::TYPE_DROP_SHOT => $this->score >= $threshold['success'],
            self::TYPE_SERVE_ACCURACY => $this->score >= $threshold['success'],
            self::TYPE_MONTHLY_TEST => $this->score >= $threshold['score'],
            default => false,
        };
    }

    /**
     * Calculate points earned based on challenge type and score
     */
    public function calculatePoints(): float
    {
        if (!$this->passed) {
            return 0;
        }

        $points = self::POINTS[$this->challenge_type] ?? 0;

        if (is_array($points)) {
            // Monthly test: scale based on score (70-100 maps to 30-50)
            $minScore = 70;
            $maxScore = 100;
            $scoreRange = $maxScore - $minScore;
            $pointRange = $points['max'] - $points['min'];
            $normalizedScore = min($maxScore, max($minScore, $this->score));
            return $points['min'] + (($normalizedScore - $minScore) / $scoreRange) * $pointRange;
        }

        return (float) $points;
    }

    /**
     * Get base points for a challenge type
     *
     * @param string $type
     * @return int|array<string, int>
     */
    public static function getBasePoints(string $type): int|array
    {
        return self::POINTS[$type] ?? 0;
    }

    /**
     * Check if result is verified
     */
    public function isVerified(): bool
    {
        return $this->verified_by !== null && $this->verified_at !== null;
    }
}
