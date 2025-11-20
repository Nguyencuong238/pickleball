<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Stadium extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'address',
        'phone',
        'email',
        'website',
        'court_surface',
        'image',
        'latitude',
        'longitude',
        'opening_hours',
        'amenities',
        'utilities',
        'regulations',
        'status',
        'featured_status',
        'verified',
        'rating',
        'rating_count',
        'is_featured',
        'is_premium',
    ];

    protected $casts = [
        'amenities' => 'array',
        'utilities' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'rating' => 'float',
        'rating_count' => 'integer',
        'verified' => 'boolean',
        'is_featured' => 'boolean',
        'is_premium' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('banner')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get courts for this stadium
     */
    public function courts()
    {
        return $this->hasMany(Court::class);
    }

    /**
     * Get courts count dynamically
     */
    public function getCourtCountAttribute()
    {
        return $this->courts()->count();
    }

    /**
     * Get the users who favorited this stadium
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'stadium_id', 'user_id');
    }

    /**
     * Get reviews for this stadium
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get average rating with count
     */
    public function updateAverageRating()
    {
        $reviews = $this->reviews()->where('is_verified', true)->get();
        if ($reviews->count() > 0) {
            $this->update([
                'rating' => round($reviews->avg('rating'), 1),
                'rating_count' => $reviews->count(),
            ]);
        }
    }
}
