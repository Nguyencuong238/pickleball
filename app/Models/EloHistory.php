<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EloHistory extends Model
{
    use HasFactory;

    protected $table = 'elo_histories';

    protected $fillable = [
        'user_id',
        'ocr_match_id',
        'elo_before',
        'elo_after',
        'change_amount',
        'change_reason',
    ];

    protected $casts = [
        'elo_before' => 'integer',
        'elo_after' => 'integer',
        'change_amount' => 'integer',
    ];

    // Constants
    public const REASON_MATCH_WIN = 'match_win';
    public const REASON_MATCH_LOSS = 'match_loss';
    public const REASON_ADMIN_ADJUSTMENT = 'admin_adjustment';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ocrMatch(): BelongsTo
    {
        return $this->belongsTo(OcrMatch::class, 'ocr_match_id');
    }

    // Accessors
    public function getIsPositiveAttribute(): bool
    {
        return $this->change_amount > 0;
    }

    public function getIsNegativeAttribute(): bool
    {
        return $this->change_amount < 0;
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWins($query)
    {
        return $query->where('change_reason', self::REASON_MATCH_WIN);
    }

    public function scopeLosses($query)
    {
        return $query->where('change_reason', self::REASON_MATCH_LOSS);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
