<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'google_id',
        'facebook_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the user's favorite stadiums
     */
    public function favoriteStadiums()
    {
        return $this->belongsToMany(Stadium::class, 'favorites', 'user_id', 'stadium_id');
    }

    /**
     * Check if user has favorited a stadium
     */
    public function hasFavorited(Stadium $stadium)
    {
        return $this->favoriteStadiums()->where('stadium_id', $stadium->id)->exists();
    }

    /**
     * Get reviews written by this user
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if user already reviewed a stadium
     */
    public function hasReviewed(Stadium $stadium)
    {
        return $this->reviews()->where('stadium_id', $stadium->id)->exists();
    }
}
