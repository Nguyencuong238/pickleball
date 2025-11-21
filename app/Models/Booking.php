<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'booking_date',
        'start_time',
        'end_time',
        'duration_hours',
        'hourly_rate',
        'total_price',
        'status',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'duration_hours' => 'float',
        'hourly_rate' => 'integer',
        'total_price' => 'integer',
    ];

    /**
     * Get the court associated with this booking.
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Get the user who created this booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if booking is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if booking is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Cancel the booking.
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Confirm the booking.
     */
    public function confirm(): void
    {
        $this->update(['status' => 'confirmed']);
    }
}
