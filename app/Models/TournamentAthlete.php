<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentAthlete extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'category_id',
        'partner_id',
        'user_id',
        'athlete_name',
        'email',
        'phone',
        'status',
        'position',
        'payment_status',
        'group_id',
        'seed_number',
        'matches_played',
        'matches_won',
        'matches_lost',
        'win_rate',
        'total_points',
        'sets_won',
        'sets_lost',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TournamentCategory::class, 'category_id', 'id');
    }

    /**
     * Get the partner athlete (for doubles).
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(TournamentAthlete::class, 'partner_id');
    }

    /**
     * Check if athlete has a partner (for doubles).
     */
    public function hasPartner(): bool
    {
        return !is_null($this->partner_id);
    }

    /**
     * Get pair display name (for doubles).
     */
    public function getPairNameAttribute(): string
    {
        if (!$this->hasPartner()) {
            return $this->athlete_name;
        }
        return $this->athlete_name . ' / ' . ($this->partner->athlete_name ?? 'Unknown');
    }
}
