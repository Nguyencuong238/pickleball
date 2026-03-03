<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubActivityMatchStanding extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_id',
        'user_id',
        'matches_played',
        'wins',
        'losses',
        'points_scored',
        'points_against',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ClubActivity::class, 'club_activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPointDifferentialAttribute(): int
    {
        return $this->points_scored - $this->points_against;
    }
}
