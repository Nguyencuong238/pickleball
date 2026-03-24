<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubActivityParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_id',
        'user_id',
        'status',
        'waitlist_position',
        'responded_at',
        'checked_in_at',
        'gender_preference',
        'current_status',
        'queue_position',
        'matches_played_count',
        'last_match_ended_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'waitlist_position' => 'integer',
        'checked_in_at' => 'datetime',
        'last_match_ended_at' => 'datetime',
        'matches_played_count' => 'integer',
        'queue_position' => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ClubActivity::class, 'club_activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeQueued($query)
    {
        return $query->where('current_status', 'queued');
    }

    public function scopePlaying($query)
    {
        return $query->where('current_status', 'playing');
    }

    public function scopeCheckedIn($query)
    {
        return $query->whereNotNull('checked_in_at');
    }
}
