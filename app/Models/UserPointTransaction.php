<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points',
        'type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'points' => 'integer',
        'metadata' => 'json',
    ];

    /**
     * Get the user that made this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get type label for display
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'earn' => 'Kiếm điểm',
            'use' => 'Sử dụng',
            'refund' => 'Hoàn lại',
            'admin' => 'Cấp bởi admin',
            default => $this->type,
        };
    }

    /**
     * Check if transaction is positive (earning points)
     */
    public function isPositive(): bool
    {
        return $this->points > 0;
    }

    /**
     * Get formatted points
     */
    public function getFormattedPoints(): string
    {
        $sign = $this->points > 0 ? '+' : '';
        return $sign . number_format($this->points);
    }
}
