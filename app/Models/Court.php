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
        'is_active',
        'daily_matches',
    ];

    protected $casts = [
        'amenities' => 'array',
        'is_active' => 'boolean',
        'daily_matches' => 'integer',
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
}
