<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStanding extends Model
{
    protected $fillable = [
        'league_id',
        'league_team_id',
        'played',
        'wins',
        'losses',
        'draws',
        'games_won',
        'games_lost',
        'points',
        'rank',
    ];

    protected $casts = [
        'played' => 'integer',
        'wins' => 'integer',
        'losses' => 'integer',
        'draws' => 'integer',
        'games_won' => 'integer',
        'games_lost' => 'integer',
        'points' => 'integer',
        'rank' => 'integer',
    ];

    // Relationships

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(LeagueTeam::class, 'league_team_id');
    }

    // Scopes

    /**
     * Sap xep theo thu hang: diem > hieu so game > so game thang
     */
    public function scopeRanked($query)
    {
        return $query->orderByDesc('points')
            ->orderByRaw('(CAST(games_won AS SIGNED) - CAST(games_lost AS SIGNED)) DESC')
            ->orderByDesc('games_won');
    }

    /**
     * Cap nhat standings sau mot tran dau (su dung increment thuan DB)
     */
    public function updateAfterMatch(bool $won, int $gamesWon, int $gamesLost, int $pointsForWin, int $pointsForLoss): void
    {
        $this->increment('played');
        $this->increment('games_won', $gamesWon);
        $this->increment('games_lost', $gamesLost);

        if ($won) {
            $this->increment('wins');
            $this->increment('points', $pointsForWin);
        } else {
            $this->increment('losses');
            $this->increment('points', $pointsForLoss);
        }
    }
}
