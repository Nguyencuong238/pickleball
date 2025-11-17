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
        'courts_count',
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
        'courts_count' => 'integer',
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
}
