<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    // Check-in method constants
    public const CHECK_IN_QR_CODE = 'qr_code';
    public const CHECK_IN_MANUAL = 'manual';

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'location',
        'stadium_id',
        'start_datetime',
        'end_datetime',
        'points',
        'max_attendees',
        'is_active',
        'qr_code_data',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'points' => 'integer',
        'max_attendees' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
            if (empty($model->qr_code_data)) {
                $model->qr_code_data = 'EVT-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Use UUID for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the stadium where event is held
     */
    public function stadium(): BelongsTo
    {
        return $this->belongsTo(Stadium::class);
    }

    /**
     * Get the user who created the event
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all check-ins for this event
     */
    public function checkins(): HasMany
    {
        return $this->hasMany(EventCheckin::class);
    }

    /**
     * Check if event is currently ongoing
     */
    public function isOngoing(): bool
    {
        $now = Carbon::now();
        return $this->is_active
            && $this->start_datetime <= $now
            && $this->end_datetime >= $now;
    }

    /**
     * Check if event is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->is_active && $this->start_datetime > Carbon::now();
    }

    /**
     * Check if event has ended
     */
    public function hasEnded(): bool
    {
        return $this->end_datetime < Carbon::now();
    }

    /**
     * Get count of attendees
     */
    public function getAttendeeCount(): int
    {
        return $this->checkins()->count();
    }

    /**
     * Check if event has reached max attendees
     */
    public function hasReachedLimit(): bool
    {
        if ($this->max_attendees === null) {
            return false;
        }
        return $this->getAttendeeCount() >= $this->max_attendees;
    }

    /**
     * Get remaining slots
     */
    public function getRemainingSlots(): ?int
    {
        if ($this->max_attendees === null) {
            return null;
        }
        $remaining = $this->max_attendees - $this->getAttendeeCount();
        return max(0, $remaining);
    }

    /**
     * Check if user has already checked in
     */
    public function hasUserCheckedIn(int $userId): bool
    {
        return $this->checkins()->where('user_id', $userId)->exists();
    }

    /**
     * Scope for active events
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ongoing events
     */
    public function scopeOngoing($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>=', $now);
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('is_active', true)
            ->where('start_datetime', '>', Carbon::now());
    }

    /**
     * Find event by QR code data
     */
    public static function findByQrCode(string $qrCode): ?self
    {
        return static::where('qr_code_data', $qrCode)->first();
    }

    /**
     * Get status label for display
     */
    public function getStatusLabel(): string
    {
        if (!$this->is_active) {
            return 'Khong hoat dong';
        }
        if ($this->hasEnded()) {
            return 'Da ket thuc';
        }
        if ($this->isUpcoming()) {
            return 'Sap dien ra';
        }
        if ($this->isOngoing()) {
            return 'Dang dien ra';
        }
        return 'Khong xac dinh';
    }
}
