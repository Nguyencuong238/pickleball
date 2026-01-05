<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class OcrMatch extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'ocr_matches';

    protected $fillable = [
        'match_type',
        'slug',
        'challenger_id',
        'challenger_partner_id',
        'opponent_id',
        'opponent_partner_id',
        'challenger_score',
        'opponent_score',
        'winner_team',
        'status',
        'scheduled_date',
        'scheduled_time',
        'location',
        'notes',
        'result_submitted_by',
        'result_submitted_at',
        'confirmed_at',
        'disputed_reason',
        'elo_challenger_before',
        'elo_opponent_before',
        'elo_challenger_after',
        'elo_opponent_after',
        'elo_change',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'string',
        'result_submitted_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'challenger_score' => 'integer',
        'opponent_score' => 'integer',
        'elo_challenger_before' => 'integer',
        'elo_opponent_before' => 'integer',
        'elo_challenger_after' => 'integer',
        'elo_opponent_after' => 'integer',
        'elo_change' => 'integer',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESULT_SUBMITTED = 'result_submitted';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DISPUTED = 'disputed';
    public const STATUS_CANCELLED = 'cancelled';

    // Match type constants
    public const TYPE_SINGLES = 'singles';
    public const TYPE_DOUBLES = 'doubles';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug = $model->generateSlug();
            }
        });
    }

    private function generateSlug(): string
    {
        $slug = 'match-' . uniqid() . '-' . rand(1000, 9999);
        
        while (self::where('slug', $slug)->exists()) {
            $slug = 'match-' . uniqid() . '-' . rand(1000, 9999);
        }
        
        return $slug;
    }

    // Relationships
    public function challenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenger_id');
    }

    public function challengerPartner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenger_partner_id');
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function opponentPartner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_partner_id');
    }

    public function resultSubmitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'result_submitted_by');
    }

    public function eloHistories(): HasMany
    {
        return $this->hasMany(EloHistory::class, 'ocr_match_id');
    }

    // Media collection for evidence
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('evidence');
    }

    // Status checks
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isResultSubmitted(): bool
    {
        return $this->status === self::STATUS_RESULT_SUBMITTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isDisputed(): bool
    {
        return $this->status === self::STATUS_DISPUTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Participant checks
    public function isParticipant(int $userId): bool
    {
        return in_array($userId, $this->getAllParticipantIds());
    }

    public function isChallengerTeam(int $userId): bool
    {
        return $userId === $this->challenger_id || $userId === $this->challenger_partner_id;
    }

    public function isOpponentTeam(int $userId): bool
    {
        return $userId === $this->opponent_id || $userId === $this->opponent_partner_id;
    }

    /**
     * Get all participant user IDs
     *
     * @return array<int>
     */
    public function getAllParticipantIds(): array
    {
        return array_filter([
            $this->challenger_id,
            $this->challenger_partner_id,
            $this->opponent_id,
            $this->opponent_partner_id,
        ]);
    }

    // State transitions
    public function accept(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \InvalidArgumentException('Match not in pending status');
        }
        $this->update(['status' => self::STATUS_ACCEPTED]);
    }

    public function startMatch(): void
    {
        if ($this->status !== self::STATUS_ACCEPTED) {
            throw new \InvalidArgumentException('Match not accepted');
        }
        $this->update(['status' => self::STATUS_IN_PROGRESS]);
    }

    public function submitResult(int $submitterId, int $challengerScore, int $opponentScore): void
    {
        $this->update([
            'status' => self::STATUS_RESULT_SUBMITTED,
            'result_submitted_by' => $submitterId,
            'result_submitted_at' => now(),
            'challenger_score' => $challengerScore,
            'opponent_score' => $opponentScore,
            'winner_team' => $challengerScore > $opponentScore ? 'challenger' : 'opponent',
        ]);
    }

    public function confirmResult(): void
    {
        if ($this->status !== self::STATUS_RESULT_SUBMITTED) {
            throw new \InvalidArgumentException('Result not submitted');
        }
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    public function dispute(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_DISPUTED,
            'disputed_reason' => $reason,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_RESULT_SUBMITTED,
        ]);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('challenger_id', $userId)
              ->orWhere('challenger_partner_id', $userId)
              ->orWhere('opponent_id', $userId)
              ->orWhere('opponent_partner_id', $userId);
        });
    }

    // Accessors
    public function getIsDoublesAttribute(): bool
    {
        return $this->match_type === self::TYPE_DOUBLES;
    }

    public function getIsSinglesAttribute(): bool
    {
        return $this->match_type === self::TYPE_SINGLES;
    }

    /**
     * Get winner user IDs based on winner_team
     *
     * @return array<int>
     */
    public function getWinnerIds(): array
    {
        if (!$this->winner_team) {
            return [];
        }

        if ($this->winner_team === 'challenger') {
            return array_filter([$this->challenger_id, $this->challenger_partner_id]);
        }

        return array_filter([$this->opponent_id, $this->opponent_partner_id]);
    }

    /**
     * Get loser user IDs based on winner_team
     *
     * @return array<int>
     */
    public function getLoserIds(): array
    {
        if (!$this->winner_team) {
            return [];
        }

        if ($this->winner_team === 'challenger') {
            return array_filter([$this->opponent_id, $this->opponent_partner_id]);
        }

        return array_filter([$this->challenger_id, $this->challenger_partner_id]);
    }
}
