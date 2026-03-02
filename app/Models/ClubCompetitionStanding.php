<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubCompetitionStanding extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_id',
        'club_competition_team_id',
        'played',
        'wins',
        'losses',
        'draws',
        'points',
        'rank',
    ];

    protected $casts = [
        'played' => 'integer',
        'wins' => 'integer',
        'losses' => 'integer',
        'draws' => 'integer',
        'points' => 'integer',
        'rank' => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ClubActivity::class, 'club_activity_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(ClubCompetitionTeam::class, 'club_competition_team_id');
    }

    public function scopeRanked($query)
    {
        return $query->orderBy('rank');
    }
}
