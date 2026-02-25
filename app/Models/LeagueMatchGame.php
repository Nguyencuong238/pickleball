<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueMatchGame extends Model
{
    protected $fillable = [
        'league_match_id',
        'game_number',
        'game_type',
        'home_score',
        'away_score',
        'winner_team_id',
        'status',
    ];

    protected $casts = [
        'game_number' => 'integer',
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];

    // Relationships

    public function match(): BelongsTo
    {
        return $this->belongsTo(LeagueMatch::class, 'league_match_id');
    }

    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(LeagueTeam::class, 'winner_team_id');
    }
}
