<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'category_id',
        'round_name',
        'round_number',
        'round_type',
        'start_date',
        'end_date',
        'start_time',
        'status',
        'total_matches',
        'completed_matches',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'round_number' => 'integer',
        'total_matches' => 'integer',
        'completed_matches' => 'integer',
    ];

    /**
     * Get the tournament that owns this round.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the category that owns this round.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TournamentCategory::class, 'category_id');
    }

    /**
     * Get the matches in this round.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'round_id');
    }

    /**
     * Get the groups in this round.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'round_id');
    }

    /**
     * Check if round is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' ||
               ($this->total_matches > 0 && $this->completed_matches >= $this->total_matches);
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_matches === 0) {
            return 0;
        }
        return round(($this->completed_matches / $this->total_matches) * 100, 2);
    }
}
