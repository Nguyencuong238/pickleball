<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentAthlete extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'category_id',
        'user_id',
        'athlete_name',
        'email',
        'phone',
        'status',
        'position',
        'payment_status',
        'group_id',
        'seed_number',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(TournamentCategory::class);
    }
}
