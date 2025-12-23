<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referrer_name',
        'referred_user_id',
        'status',
        'referred_at',
        'completed_at',
    ];

    protected $casts = [
        'referred_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the referrer user
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the referred user
     */
    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    /**
     * Get status badge display
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '⏳ Đang chờ',
            'completed' => '✓ Đã hoàn thành',
            default => $this->status,
        };
    }
}
