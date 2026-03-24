<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubActivityMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_activity_id',
        'round_id',
        'court_number',
        'match_type',
        'player1_id',
        'player2_id',
        'player3_id',
        'player4_id',
        'team1_score',
        'team2_score',
        'status',
        'completed_at',
        'match_number',
        'scheduled_court',
        'started_at',
        'ended_at',
        'result_submitted_by',
        'result_confirmed',
        'oprs_processed',
        'set_scores',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'result_confirmed' => 'boolean',
        'oprs_processed' => 'boolean',
        'set_scores' => 'array',
        'match_number' => 'integer',
        'scheduled_court' => 'integer',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(ClubActivityMatchRound::class, 'round_id');
    }

    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function player3(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player3_id');
    }

    public function player4(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player4_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ClubActivity::class, 'club_activity_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'result_submitted_by');
    }
}
