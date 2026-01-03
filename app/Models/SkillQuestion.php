<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SkillQuestion extends Model
{
    protected $fillable = [
        'domain_id',
        'question_vi',
        'question_en',
        'anchor_level',
        'order_in_domain',
        'is_active',
    ];

    protected $casts = [
        'anchor_level' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    /**
     * Get the domain this question belongs to
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(SkillDomain::class, 'domain_id');
    }

    /**
     * Get answers for this question
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SkillQuizAnswer::class, 'question_id');
    }

    /**
     * Scope: Only active questions
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by domain order
     */
    public function scopeOrderedByDomain(Builder $query): Builder
    {
        return $query->orderBy('domain_id')->orderBy('order_in_domain');
    }

    /**
     * Get question text based on locale
     */
    public function getQuestionText(string $locale = 'vi'): string
    {
        if ($locale === 'en' && !empty($this->question_en)) {
            return $this->question_en;
        }
        return $this->question_vi;
    }
}
