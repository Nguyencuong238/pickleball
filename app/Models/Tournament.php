<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'location',
        'max_participants',
        'price',
        'rules',
        'prizes',
        'status',
        'image',
        'competition_format',
        'tournament_rank',
        'registration_benefits',
        'competition_rules',
        'event_timeline',
        'social_information',
        'organizer_email',
        'organizer_hotline',
        'competition_schedule',
        'results',
        'gallery',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
        'max_participants' => 'integer',
        'gallery' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function athletes()
    {
        return $this->hasMany(TournamentAthlete::class);
    }

    public function athleteCount()
    {
        return $this->athletes()->count();
    }
}
