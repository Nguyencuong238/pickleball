<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instructor extends Model
{
    use HasFactory;

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'image',
        'bio',
        'description',
        'experience',
        'experience_years',
        'student_count',
        'total_hours',
        'specialties',
        'phone',
        'zalo',
        'email',
        'price_per_session',
        'is_verified',
        'is_certified',
        'rating',
        'reviews_count',
        'ward',
        'province_id',
    ];

    protected $casts = [
        'specialties' => 'array',
        'experience_years' => 'integer',
        'student_count' => 'integer',
        'total_hours' => 'integer',
        'price_per_session' => 'decimal:0',
        'is_verified' => 'boolean',
        'is_certified' => 'boolean',
        'rating' => 'decimal:1',
        'reviews_count' => 'integer',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the user account linked to this instructor
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if instructor is linked to a user account
     */
    public function isLinkedToUser(): bool
    {
        return !is_null($this->user_id);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(InstructorExperience::class)->orderBy('sort_order');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(InstructorCertification::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(InstructorReview::class)->where('is_approved', true)->latest();
    }

    public function packages(): HasMany
    {
        return $this->hasMany(InstructorPackage::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(InstructorLocation::class)->orderBy('sort_order');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(InstructorSchedule::class)->orderBy('sort_order');
    }

    public function teachingMethods(): HasMany
    {
        return $this->hasMany(InstructorTeachingMethod::class)->orderBy('sort_order');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(InstructorFavorite::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'instructor_favorites')->withPivot('created_at');
    }
}
