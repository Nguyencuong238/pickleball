<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class League extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'season_name',
        'config',
        'status',
        'start_date',
        'end_date',
        'registration_deadline',
        'logo',
    ];

    protected $casts = [
        'config' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (League $league) {
            if (empty($league->slug)) {
                $league->slug = Str::slug($league->name);
                // Append random suffix if slug already exists
                if (static::where('slug', $league->slug)->exists()) {
                    $league->slug .= '-' . Str::random(5);
                }
            }
        });
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(LeagueTeam::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(LeagueRound::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(LeagueStanding::class);
    }

    // Scopes

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Helpers

    /**
     * Lay gia tri config voi default fallback
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
