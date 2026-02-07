<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OverflowException;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
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
        'service_fee',
        'status',
        'payment_method',
        'notes',
        'confirmed_at',
        'lock_expires_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'duration_hours' => 'float',
        'hourly_rate' => 'integer',
        'total_price' => 'integer',
        'service_fee' => 'integer',
        'start_time' => 'string',
        'end_time' => 'string',
        'confirmed_at' => 'datetime',
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
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'lock_expires_at' => null,
        ]);
    }

    /**
     * Check if booking is locked (chuyển khoản, chờ xác nhận trong 15 phút)
     * Locked = pending + transfer + lock_expires_at not expired
     */
    public function isLocked(): bool
    {
        return $this->status === 'pending' 
            && $this->payment_method === 'transfer'
            && $this->lock_expires_at !== null
            && $this->lock_expires_at > time();
    }

    /**
     * Check if lock has expired
     */
    public function isLockExpired(): bool
    {
        return $this->lock_expires_at !== null && $this->lock_expires_at <= time();
    }

    /**
     * Generate a unique booking code: BK{courtId:3}{date:YYMMDD}{seq:3}
     * Must be called inside a DB::transaction().
     */
    public static function generateBookingCode(int $courtId, string $bookingDate): string
    {
        if ($courtId < 1 || $courtId > 999) {
            throw new \InvalidArgumentException("Court ID must be between 1 and 999, got {$courtId}");
        }

        $date = Carbon::parse($bookingDate);
        $datePart = $date->format('ymd');
        $courtPart = str_pad($courtId, 3, '0', STR_PAD_LEFT);
        $prefix = 'BK' . $courtPart . $datePart;

        $lastCode = static::where('court_id', $courtId)
            ->whereDate('booking_date', $date->toDateString())
            ->whereNotNull('booking_code')
            ->lockForUpdate()
            ->orderByDesc('booking_code')
            ->value('booking_code');

        if ($lastCode) {
            $lastSeq = (int) substr($lastCode, -3);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        if ($nextSeq > 999) {
            throw new OverflowException("Booking sequence overflow for court {$courtId} on {$bookingDate}");
        }

        return $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get formatted booking code for UI display: BK001-260207-001
     */
    public function getFormattedBookingCodeAttribute(): string
    {
        if (!$this->booking_code) {
            return '';
        }

        // BK001260207001 -> BK001-260207-001
        return substr($this->booking_code, 0, 5) . '-'
             . substr($this->booking_code, 5, 6) . '-'
             . substr($this->booking_code, 11, 3);
    }
}
