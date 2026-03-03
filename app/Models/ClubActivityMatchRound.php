<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClubActivityMatchRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_id',
        'round_number',
        'status',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ClubActivity::class, 'club_activity_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ClubActivityMatch::class, 'round_id');
    }
}
