<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'object',
        'stadium_id',
        'start_time',
        'end_time',
        'date',
        'user_id',
        'fee',
        'max_participants',
        'days_of_week'
    ];

    protected $casts = [
        'days_of_week' => 'array',
    ];

    public function stadium()
    {
        return $this->belongsTo(Stadium::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'social_participants', 'social_id', 'user_id');
    }

    public function participantsCount()
    {
        return $this->belongsToMany(User::class, 'social_participants', 'social_id', 'user_id')->count();
    }
}
