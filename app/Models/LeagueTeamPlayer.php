<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueTeamPlayer extends Model
{
    protected $fillable = [
        'league_team_id',
        'user_id',
        'position',
        'gender',
        'status',
        'order',
    ];

    // Relationships

    public function team(): BelongsTo
    {
        return $this->belongsTo(LeagueTeam::class, 'league_team_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
