<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'stadium_id',
        'tournament_id',
        'court_name',
        'court_number',
        'court_type',
        'surface_type',
        'status',
        'description',
        'amenities',
        'capacity',  
        'size',
        'is_active',
        'daily_matches',
        'rental_price',
    ];

    protected $casts = [
        'amenities' => 'array',
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'daily_matches' => 'integer',
        'rental_price' => 'integer',
    ];

    /**
     * Get the stadium that owns this court.
     */
    public function stadium(): BelongsTo
    {
        return $this->belongsTo(Stadium::class);
    }

    /**
     * Get the tournament that uses this court.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the matches scheduled on this court.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'court_id');
    }

    /**
     * Get the pricing tiers for this court.
     */
    public function pricing(): HasMany
    {
        return $this->hasMany(CourtPricing::class, 'court_id');
    }

    /**
     * Get active pricing tiers for this court.
     */
    public function activePricing(): HasMany
    {
        return $this->pricing()->where('is_active', true);
    }

    /**
     * Check if court is available.
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->status === 'available';
    }

    /**
     * Get matches scheduled for today.
     */
    public function todayMatches()
    {
        return $this->matches()
                    ->whereDate('match_date', today())
                    ->orderBy('match_time')
                    ->get();
    }

    /**
     * Mark court as in use.
     */
    public function markInUse(): void
    {
        $this->update(['status' => 'in_use']);
    }

    /**
     * Mark court as available.
     */
    public function markAvailable(): void
    {
        $this->update(['status' => 'available']);
    }

    /**
     * Get the price for a specific time on a given date.
     * Returns the applicable pricing, or the default rental_price if no specific pricing found.
     */
    public function getPriceForTime(\DateTime $dateTime): int
    {
        $dayOfWeek = (int) $dateTime->format('w');

        // Find active pricing that covers this time and day
        $pricing = $this->activePricing()
            ->get()
            ->first(function ($p) use ($dateTime, $dayOfWeek) {
                return $p->isValid() && $p->appliesOnDay($dayOfWeek) && $p->coversTime($dateTime);
            });

        if ($pricing) {
            return $pricing->price_per_hour;
        }

        // Fallback to default rental price
        return $this->rental_price ?? 0;
    }

    /**
     * Get all applicable pricing tiers for a given date.
     */
    public function getPricingForDate(\DateTime $date): \Illuminate\Support\Collection
    {
        $dayOfWeek = (int) $date->format('w');

        return $this->activePricing()
            ->get()
            ->filter(function ($p) use ($date, $dayOfWeek) {
                return $p->isValid() && $p->appliesOnDay($dayOfWeek);
            })
            ->sortBy('start_time');
    }
}
