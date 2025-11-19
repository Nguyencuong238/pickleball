<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'category_id',
        'round_id',
        'group_name',
        'group_code',
        'max_participants',
        'current_participants',
        'advancing_count',
        'status',
        'description',
    ];

    protected $casts = [
        'max_participants' => 'integer',
        'current_participants' => 'integer',
        'advancing_count' => 'integer',
    ];

    /**
     * Get the tournament that owns this group.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the category for this group.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TournamentCategory::class, 'category_id');
    }

    /**
     * Get the round for this group.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * Get the athletes in this group.
     */
    public function athletes(): HasMany
    {
        return $this->hasMany(TournamentAthlete::class, 'group_id');
    }

    /**
     * Get the matches in this group.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'group_id');
    }

    /**
     * Get the standings for this group.
     */
    public function standings(): HasMany
    {
        return $this->hasMany(GroupStanding::class, 'group_id')->orderBy('rank_position');
    }

    /**
     * Check if group is full.
     */
    public function isFull(): bool
    {
        return $this->current_participants >= $this->max_participants;
    }

    /**
     * Get available slots.
     */
    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->max_participants - $this->current_participants);
    }

    /**
     * Check if group stage is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get athletes who advanced to next round.
     */
    public function advancedAthletes()
    {
        return $this->standings()
                    ->where('is_advanced', true)
                    ->with('athlete')
                    ->get();
    }
}
