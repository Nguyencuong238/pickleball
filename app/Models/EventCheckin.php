<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCheckin extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'checked_in_at',
        'check_in_method',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    /**
     * Get the event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who checked in
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a check-in record
     */
    public static function checkIn(Event $event, User $user, string $method = Event::CHECK_IN_QR_CODE): self
    {
        return static::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'checked_in_at' => Carbon::now(),
            'check_in_method' => $method,
        ]);
    }

    /**
     * Get check-in method label for display
     */
    public function getMethodLabel(): string
    {
        return match ($this->check_in_method) {
            Event::CHECK_IN_QR_CODE => 'QR Code',
            Event::CHECK_IN_MANUAL => 'Thu cong',
            default => $this->check_in_method,
        };
    }

    /**
     * Scope for QR code check-ins
     */
    public function scopeQrCode($query)
    {
        return $query->where('check_in_method', Event::CHECK_IN_QR_CODE);
    }

    /**
     * Scope for manual check-ins
     */
    public function scopeManual($query)
    {
        return $query->where('check_in_method', Event::CHECK_IN_MANUAL);
    }
}
