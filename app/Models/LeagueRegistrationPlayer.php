<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueRegistrationPlayer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'league_registration_id',
        'user_id',
        'phone',
        'name',
        'skill_level',
        'province',
        'gender',
        'birthday',
        'photo',
        'message',
    ];

    protected $casts = [
        'birthday' => 'date',
        'created_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(LeagueRegistration::class, 'league_registration_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
