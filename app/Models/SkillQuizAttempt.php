<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SkillQuizAttempt extends Model
{
    use HasFactory;
    use HasUuids;

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ABANDONED = 'abandoned';

    protected $fillable = [
        'user_id',
        'started_at',
        'completed_at',
        'duration_seconds',
        'status',
        'domain_scores',
        'quiz_percent',
        'calculated_elo',
        'final_elo',
        'flags',
        'is_provisional',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'domain_scores' => 'array',
        'quiz_percent' => 'decimal:2',
        'flags' => 'array',
        'is_provisional' => 'boolean',
    ];

    /**
     * Get the user for this attempt
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get answers for this attempt
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SkillQuizAnswer::class, 'attempt_id');
    }

    /**
     * Scope: Only completed attempts
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Only in-progress attempts
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope: By user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if attempt is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if attempt is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if attempt has flags
     */
    public function hasFlags(): bool
    {
        return !empty($this->flags);
    }

    /**
     * Get flag count
     */
    public function getFlagCount(): int
    {
        return count($this->flags ?? []);
    }

    /**
     * Get domain score by key
     */
    public function getDomainScore(string $domainKey): ?float
    {
        return $this->domain_scores[$domainKey] ?? null;
    }
}
