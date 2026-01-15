<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class SpecialChallenge extends Model
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    private const PARTICIPANT_COUNT_CACHE_TTL = 300;
    protected $fillable = [
        'title',
        'description',
        'points',
        'start_date',
        'end_date',
        'is_active',
        'max_participants',
    ];

    protected $casts = [
        'points' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'max_participants' => 'integer',
    ];

    /**
     * Check if challenge is currently ongoing
     */
    public function isOngoing(): bool
    {
        $today = Carbon::today();
        return $this->is_active
            && $this->start_date <= $today
            && $this->end_date >= $today;
    }

    /**
     * Check if challenge is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->is_active && $this->start_date > Carbon::today();
    }

    /**
     * Check if challenge has ended
     */
    public function hasEnded(): bool
    {
        return $this->end_date < Carbon::today();
    }

    /**
     * Scope for active challenges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ongoing challenges
     */
    public function scopeOngoing($query)
    {
        $today = Carbon::today();
        return $query->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    /**
     * Scope for upcoming challenges
     */
    public function scopeUpcoming($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '>', Carbon::today());
    }

    /**
     * Get submissions for this challenge
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(PointSubmission::class, 'point_task_id')
            ->whereHas('pointTask', fn ($q) => $q->where('code', PointTask::CODE_SPECIAL_CHALLENGE));
    }

    /**
     * Get count of approved participants (cached)
     */
    public function getParticipantCount(): int
    {
        $cacheKey = "special_challenge_{$this->id}_participant_count";

        return Cache::remember($cacheKey, self::PARTICIPANT_COUNT_CACHE_TTL, function () {
            return PointSubmission::whereJsonContains('proof_data->challenge_id', $this->id)
                ->where('status', PointSubmission::STATUS_APPROVED)
                ->count();
        });
    }

    /**
     * Clear participant count cache
     */
    public function clearParticipantCountCache(): void
    {
        Cache::forget("special_challenge_{$this->id}_participant_count");
    }

    /**
     * Check if challenge has reached max participants
     */
    public function hasReachedLimit(): bool
    {
        if ($this->max_participants === null) {
            return false;
        }
        return $this->getParticipantCount() >= $this->max_participants;
    }

    /**
     * Get remaining slots
     */
    public function getRemainingSlots(): ?int
    {
        if ($this->max_participants === null) {
            return null;
        }
        $remaining = $this->max_participants - $this->getParticipantCount();
        return max(0, $remaining);
    }

    /**
     * Get status label for display
     */
    public function getStatusLabel(): string
    {
        if (!$this->is_active) {
            return 'Khong hoat dong';
        }
        if ($this->hasEnded()) {
            return 'Da ket thuc';
        }
        if ($this->isUpcoming()) {
            return 'Sap dien ra';
        }
        if ($this->isOngoing()) {
            return 'Dang dien ra';
        }
        return 'Khong xac dinh';
    }
}
