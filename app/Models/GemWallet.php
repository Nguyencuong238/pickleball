<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GemWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'locked_balance',
    ];

    protected $casts = [
        'balance' => 'integer',
        'locked_balance' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(GemTransaction::class);
    }

    /**
     * Số Gems có thể sử dụng = tổng số dư trừ số bị khóa.
     */
    public function getSpendableBalanceAttribute(): int
    {
        return max(0, (int) $this->balance - (int) $this->locked_balance);
    }
}
