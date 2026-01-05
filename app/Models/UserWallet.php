<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    /**
     * Get the user that owns this wallet
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get point transactions for this wallet
     */
    public function transactions()
    {
        return $this->user->pointTransactions();
    }

    /**
     * Add points to wallet
     */
    public function addPoints(int $points, string $type = 'earn', string $description = '', array $metadata = []): void
    {
        $this->increment('points', $points);
        $this->user->pointTransactions()->create([
            'points' => $points,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Deduct points from wallet
     */
    public function deductPoints(int $points, string $type = 'use', string $description = '', array $metadata = []): bool
    {
        if ($this->points < $points) {
            return false;
        }

        $this->decrement('points', $points);
        $this->user->pointTransactions()->create([
            'points' => -$points,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        return true;
    }

    /**
     * Format points for display
     */
    public function getFormattedPoints(): string
    {
        return number_format($this->points);
    }
}
