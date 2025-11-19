<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'category_name',
        'category_type',
        'age_group',
        'max_participants',
        'prize_money',
        'description',
        'status',
        'current_participants',
    ];

    protected $casts = [
        'prize_money' => 'decimal:2',
        'max_participants' => 'integer',
        'current_participants' => 'integer',
    ];

    /**
     * Get the tournament that owns this category.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the athletes in this category.
     */
    public function athletes(): HasMany
    {
        return $this->hasMany(TournamentAthlete::class, 'category_id');
    }

    /**
     * Get the rounds in this category.
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class, 'category_id');
    }

    /**
     * Get the matches in this category.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'category_id');
    }

    /**
     * Get the groups in this category.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'category_id');
    }

    /**
     * Check if category is full.
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
}
