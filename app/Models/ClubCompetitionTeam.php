<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClubCompetitionTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_id',
        'name',
        'captain_user_id',
        'status',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ClubActivity::class, 'club_activity_id');
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captain_user_id');
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(ClubCompetitionMatch::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(ClubCompetitionMatch::class, 'away_team_id');
    }

    public function standing(): HasOne
    {
        return $this->hasOne(ClubCompetitionStanding::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
