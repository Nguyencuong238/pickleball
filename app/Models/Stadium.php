<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stadium extends Model
{
    use HasFactory;

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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
