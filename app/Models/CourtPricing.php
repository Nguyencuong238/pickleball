<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CourtPricing extends Model
{
    use HasFactory;

    protected $table = 'court_pricing';

    protected $fillable = [
        'court_id',
        'start_time',
        'end_time',
        'price_per_hour',
        'days_of_week',
        'is_active',
        'valid_from',
        'valid_to',
        'description',
    ];

    protected $casts = [
        'price_per_hour' => 'integer',
        'days_of_week' => 'array',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    /**
     * Get the court that owns this pricing.
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    /**
     * Check if this pricing is currently valid.
     */
    public function isValid(): bool
    {
        $today = today();

        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && $today < $this->valid_from) {
            return false;
        }

        if ($this->valid_to && $today > $this->valid_to) {
            return false;
        }

        return true;
    }

    /**
     * Check if this pricing applies to a specific day of week.
     * Days: 0 = Sunday, 1 = Monday, ..., 6 = Saturday
     */
    public function appliesOnDay(int $dayOfWeek): bool
    {
        if ($this->days_of_week === null || empty($this->days_of_week)) {
            return true; // Applies to all days if not specified
        }

        return in_array($dayOfWeek, $this->days_of_week);
    }

    /**
     * Check if a time falls within this pricing period.
     */
    public function coversTime(\DateTime $time): bool
    {
        $timeStr = $time->format('H:i');
        $startStr = $this->start_time ?? '00:00';
        $endStr = $this->end_time ?? '24:00';

        return $timeStr >= $startStr && $timeStr < $endStr;
    }

    /**
     * Get the price label (e.g., "Peak hours", "Off-peak").
     */
    public function getLabel(): string
    {
        if ($this->description) {
            return $this->description;
        }

        $startTime = $this->start_time ?? '00:00';
        $endTime = $this->end_time ?? '24:00';

        return "{$startTime} - {$endTime}";
    }

    /**
     * Format price for display.
     */
    public function getFormattedPrice(): string
    {
        return number_format($this->price_per_hour, 0, ',', '.') . ' VND';
    }

    /**
     * Get days of week as readable text.
     */
    public function getDaysOfWeekText(): string
    {
        if (empty($this->days_of_week)) {
            return 'Tất cả các ngày';
        }

        $dayNames = [
            0 => 'Chủ Nhật',
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
        ];

        if (count($this->days_of_week) === 7) {
            return 'Tất cả các ngày';
        }

        return implode(', ', array_map(fn($day) => $dayNames[$day], $this->days_of_week));
    }
}
