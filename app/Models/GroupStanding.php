<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupStanding extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'athlete_id',
        'rank_position',
        'matches_played',
        'matches_won',
        'matches_lost',
        'matches_drawn',
        'win_rate',
        'points',
        'sets_won',
        'sets_lost',
        'sets_differential',
        'games_won',
        'games_lost',
        'games_differential',
        'is_advanced',
    ];

    protected $casts = [
        'rank_position' => 'integer',
        'matches_played' => 'integer',
        'matches_won' => 'integer',
        'matches_lost' => 'integer',
        'matches_drawn' => 'integer',
        'win_rate' => 'decimal:2',
        'points' => 'integer',
        'sets_won' => 'integer',
        'sets_lost' => 'integer',
        'sets_differential' => 'integer',
        'games_won' => 'integer',
        'games_lost' => 'integer',
        'games_differential' => 'integer',
        'is_advanced' => 'boolean',
    ];

    /**
     * Get the group that owns this standing.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the athlete for this standing.
     */
    public function athlete(): BelongsTo
    {
        return $this->belongsTo(TournamentAthlete::class, 'athlete_id');
    }

    /**
     * Calculate win rate.
     */
    public function calculateWinRate(): float
    {
        if ($this->matches_played === 0) {
            return 0;
        }
        return round(($this->matches_won / $this->matches_played) * 100, 2);
    }

    /**
     * Update standings after a match.
     */
    public function updateAfterMatch(bool $won, int $setsWon, int $setsLost, int $gamesWon, int $gamesLost): void
    {
        $this->increment('matches_played');

        if ($won) {
            $this->increment('matches_won');
            $this->increment('points', 3); // 3 points for a win
        } else {
            $this->increment('matches_lost');
        }

        $this->sets_won += $setsWon;
        $this->sets_lost += $setsLost;
        $this->sets_differential = $this->sets_won - $this->sets_lost;

        $this->games_won += $gamesWon;
        $this->games_lost += $gamesLost;
        $this->games_differential = $this->games_won - $this->games_lost;

        $this->win_rate = $this->calculateWinRate();
        $this->save();
    }

    /**
     * Mark as advanced to next round.
     */
    public function markAsAdvanced(): void
    {
        $this->update(['is_advanced' => true]);
    }

    /**
     * Get display rank with medal for top 3.
     */
    public function getDisplayRankAttribute(): string
    {
        $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
        return $medals[$this->rank_position] ?? (string) $this->rank_position;
    }
}
