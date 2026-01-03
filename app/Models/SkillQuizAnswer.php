<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillQuizAnswer extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer_value',
        'answered_at',
        'time_spent_seconds',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    /**
     * Get the attempt this answer belongs to
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(SkillQuizAttempt::class, 'attempt_id');
    }

    /**
     * Get the question this answer is for
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SkillQuestion::class, 'question_id');
    }

    /**
     * Get the answer value as a percentage (0-100)
     */
    public function getAnswerPercent(): float
    {
        // 0 = 0%, 1 = 33.33%, 2 = 66.67%, 3 = 100%
        return ($this->answer_value / 3) * 100;
    }

    /**
     * Check if answer indicates skill level matches anchor
     */
    public function matchesAnchorLevel(): bool
    {
        // Answer 2 or 3 indicates user has this skill at anchor level
        return $this->answer_value >= 2;
    }
}
