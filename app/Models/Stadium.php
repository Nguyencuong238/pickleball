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
        'image',
        'latitude',
        'longitude',
        'opening_hours',
        'amenities',
        'status',
    ];

    protected $casts = [
        'amenities' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'courts_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
