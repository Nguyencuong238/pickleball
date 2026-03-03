<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeagueRound extends Model
{
    protected $fillable = [
        'league_id',
        'round_number',
        'name',
        'scheduled_date',
        'scheduled_time',
        'venue',
        'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'round_number' => 'integer',
    ];

    // Relationships

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class);
    }
}
