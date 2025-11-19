<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tournament_id',
        'tournament_athlete_id',
        'payment_reference',
        'amount',
        'currency',
        'payment_method',
        'status',
        'transaction_id',
        'payment_details',
        'paid_at',
        'refunded_at',
        'notes',
        'receipt_url',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_reference) {
                $payment->payment_reference = static::generatePaymentReference();
            }
        });
    }

    /**
     * Generate unique payment reference.
     */
    public static function generatePaymentReference(): string
    {
        do {
            $reference = 'PAY-' . strtoupper(Str::random(10));
        } while (static::where('payment_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Get the user that made this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tournament for this payment.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the tournament athlete registration for this payment.
     */
    public function tournamentAthlete(): BelongsTo
    {
        return $this->belongsTo(TournamentAthlete::class);
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(?string $transactionId = null): void
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
            'transaction_id' => $transactionId ?? $this->transaction_id,
        ]);

        // Update tournament athlete payment status
        if ($this->tournamentAthlete) {
            $this->tournamentAthlete->update([
                'payment_status' => 'paid',
                'amount_paid' => $this->amount,
            ]);
        }
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Refund the payment.
     */
    public function refund(): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        // Update tournament athlete payment status
        if ($this->tournamentAthlete) {
            $this->tournamentAthlete->update([
                'payment_status' => 'refunded',
                'amount_paid' => 0,
            ]);
        }
    }

    /**
     * Get formatted amount with currency.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', '.') . ' ' . $this->currency;
    }
}
