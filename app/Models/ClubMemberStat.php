<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubMemberStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'user_id',
        'total_matches',
        'total_wins',
        'total_losses',
        'total_points_scored',
        'total_points_against',
        'activities_participated',
        'current_oprs',
        'last_played_at',
    ];

    protected $casts = [
        'last_played_at' => 'datetime',
        'current_oprs' => 'decimal:2',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getWinRateAttribute(): float
    {
        if ($this->total_matches <= 0) {
            return 0;
        }

        return round($this->total_wins / $this->total_matches * 100, 1);
    }
}
