<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GemTransaction extends Model
{
    use HasFactory;

    // Loại giao dịch Gems
    public const TYPE_TOP_UP = 'top_up';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_REFUND = 'refund';
    public const TYPE_REFUND_CLAWBACK = 'refund_clawback';
    public const TYPE_ADMIN_ADJUST = 'admin_adjust';

    // Trạng thái giao dịch
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id',
        'gem_wallet_id',
        'counterparty_user_id',
        'counterparty_transaction_id',
        'type',
        'amount',
        'balance_after',
        'platform_fee',
        'reference_type',
        'reference_id',
        'description',
        'metadata',
        'status',
        'available_at',
        'released_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
        'platform_fee' => 'integer',
        'counterparty_user_id' => 'integer',
        'counterparty_transaction_id' => 'integer',
        'metadata' => 'array',
        'available_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(GemWallet::class, 'gem_wallet_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function counterpartyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counterparty_user_id');
    }

    public function counterpartyTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'counterparty_transaction_id');
    }

    // Scopes

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
