<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\SyncMediaCollection;

class Stadium extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SyncMediaCollection;

    protected $guarded = [];

    public function getRouteKeyName()
    {
        return 'slug';
    }

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
        $this->addMediaCollection('gallery');

        $this->addMediaCollection('banner')
            ->singleFile();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
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
