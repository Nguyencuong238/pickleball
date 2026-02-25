<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeagueMatch extends Model
{
    protected $table = 'league_matches';

    protected $fillable = [
        'league_round_id',
        'home_team_id',
        'away_team_id',
        'status',
        'home_score',
        'away_score',
        'winner_team_id',
        'scheduled_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];

    // Relationships

    public function round(): BelongsTo
    {
        return $this->belongsTo(LeagueRound::class, 'league_round_id');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(LeagueTeam::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(LeagueTeam::class, 'away_team_id');
    }

    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(LeagueTeam::class, 'winner_team_id');
    }

    public function games(): HasMany
    {
        return $this->hasMany(LeagueMatchGame::class);
    }

    /**
     * Truy cap league thong qua round
     */
    public function getLeagueAttribute(): ?League
    {
        return $this->round?->league;
    }
}
