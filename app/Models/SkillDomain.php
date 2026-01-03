<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SkillDomain extends Model
{
    protected $fillable = [
        'key',
        'name',
        'name_vi',
        'description',
        'weight',
        'anchor_min',
        'anchor_max',
        'order',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:4',
        'anchor_min' => 'decimal:1',
        'anchor_max' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    /**
     * Get questions for this domain
     */
    public function questions(): HasMany
    {
        return $this->hasMany(SkillQuestion::class, 'domain_id');
    }

    /**
     * Get active questions for this domain
     */
    public function activeQuestions(): HasMany
    {
        return $this->questions()->where('is_active', true)->orderBy('order_in_domain');
    }

    /**
     * Scope: Only active domains
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by display order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    /**
     * Get anchor range for this domain
     *
     * @return array{min: float, max: float}
     */
    public function getAnchorRange(): array
    {
        return [
            'min' => (float) $this->anchor_min,
            'max' => (float) $this->anchor_max,
        ];
    }
}
