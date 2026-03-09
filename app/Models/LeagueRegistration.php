<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeagueRegistration extends Model
{
    protected $fillable = [
        'league_id',
        'payment_proof',
        'status',
        'admin_note',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(LeagueRegistrationPlayer::class);
    }
}
