<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Match extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'category_id',
        'round_id',
        'court_id',
        'group_id',
        'match_number',
        'bracket_position',
        'athlete1_id',
        'athlete1_name',
        'athlete1_score',
        'athlete2_id',
        'athlete2_name',
        'athlete2_score',
        'winner_id',
        'match_date',
        'match_time',
        'actual_start_time',
        'actual_end_time',
        'status',
        'best_of',
        'set_scores',  // JSON array: [{"set": 1, "athlete1": 11, "athlete2": 7}, ...]
        'final_score',
        'notes',
        'next_match_id',
        'winner_advances_to',
    ];

    protected $casts = [
        'match_date' => 'date',
        'match_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'set_scores' => 'array',
        'athlete1_score' => 'integer',
        'athlete2_score' => 'integer',
        'best_of' => 'integer',
        'bracket_position' => 'integer',
    ];

    /**
     * Get the tournament that owns this match.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the category for this match.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TournamentCategory::class, 'category_id');
    }

    /**
     * Get the round for this match.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Get the court for this match.
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Get the group for this match.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get athlete 1.
     */
    public function athlete1(): BelongsTo
    {
        return $this->belongsTo(TournamentAthlete::class, 'athlete1_id');
    }

    /**
     * Get athlete 2.
     */
    public function athlete2(): BelongsTo
    {
        return $this->belongsTo(TournamentAthlete::class, 'athlete2_id');
    }

    /**
     * Get the winner.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(TournamentAthlete::class, 'winner_id');
    }

    /**
     * Get the next match in the bracket.
     */
    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(Match::class, 'next_match_id');
    }

    /**
     * Check if match is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if match is live.
     */
    public function isLive(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if match is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Get the loser of the match.
     */
    public function getLoserIdAttribute(): ?int
    {
        if (!$this->winner_id) {
            return null;
        }
        return $this->winner_id === $this->athlete1_id ? $this->athlete2_id : $this->athlete1_id;
    }

    /**
     * Start the match.
     */
    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'actual_start_time' => now(),
        ]);
    }

    /**
     * End the match.
     */
    public function end(int $winnerId): void
    {
        $this->update([
            'status' => 'completed',
            'actual_end_time' => now(),
            'winner_id' => $winnerId,
        ]);
    }
}
