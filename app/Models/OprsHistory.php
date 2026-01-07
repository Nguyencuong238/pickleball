<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OprsHistory extends Model
{
    // Change reasons
    public const REASON_MATCH_RESULT = 'match_result';
    public const REASON_CHALLENGE_COMPLETED = 'challenge_completed';
    public const REASON_COMMUNITY_ACTIVITY = 'community_activity';
    public const REASON_ADMIN_ADJUSTMENT = 'admin_adjustment';
    public const REASON_INITIAL_CALCULATION = 'initial_calculation';
    public const REASON_SKILL_QUIZ = 'skill_quiz';
    public const REASON_ELO_VERIFIED = 'elo_verified';

    protected $fillable = [
        'user_id',
        'elo_score',
        'challenge_score',
        'community_score',
        'total_oprs',
        'opr_level',
        'change_reason',
        'metadata',
    ];

    protected $casts = [
        'elo_score' => 'decimal:2',
        'challenge_score' => 'decimal:2',
        'community_score' => 'decimal:2',
        'total_oprs' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable change description
     */
    public function getChangeDescription(): string
    {
        return match ($this->change_reason) {
            self::REASON_MATCH_RESULT => 'Match result processed',
            self::REASON_CHALLENGE_COMPLETED => 'Challenge completed',
            self::REASON_COMMUNITY_ACTIVITY => 'Community activity recorded',
            self::REASON_ADMIN_ADJUSTMENT => 'Admin adjustment',
            self::REASON_INITIAL_CALCULATION => 'Initial OPRS calculation',
            self::REASON_SKILL_QUIZ => 'Skill assessment quiz completed',
            self::REASON_ELO_VERIFIED => 'ELO verified by verifier',
            default => 'Unknown',
        };
    }

    /**
     * Get all valid change reasons
     *
     * @return array<string>
     */
    public static function getAllReasons(): array
    {
        return [
            self::REASON_MATCH_RESULT,
            self::REASON_CHALLENGE_COMPLETED,
            self::REASON_COMMUNITY_ACTIVITY,
            self::REASON_ADMIN_ADJUSTMENT,
            self::REASON_INITIAL_CALCULATION,
            self::REASON_SKILL_QUIZ,
            self::REASON_ELO_VERIFIED,
        ];
    }

    /**
     * Get OPRS change from previous record
     */
    public function getOprsChange(): float
    {
        $previous = self::where('user_id', $this->user_id)
            ->where('id', '<', $this->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$previous) {
            return 0;
        }

        return $this->total_oprs - $previous->total_oprs;
    }
}
