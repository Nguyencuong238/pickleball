<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubCompetitionMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_id',
        'round_number',
        'home_team_id',
        'away_team_id',
        'status',
        'home_score',
        'away_score',
        'winner_team_id',
        'pool_label',
        'bracket_position',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'round_number' => 'integer',
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ClubActivity::class, 'club_activity_id');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(ClubCompetitionTeam::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(ClubCompetitionTeam::class, 'away_team_id');
    }

    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(ClubCompetitionTeam::class, 'winner_team_id');
    }
}
