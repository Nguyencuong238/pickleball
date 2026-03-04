<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Club extends Model
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
        'description',
        'image',
        'banner',
        'founded_date',
        'objectives',
        'type',
        'status',
    ];

    protected $casts = [
        'founded_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be appended to model arrays.
     *
     * @var array
     */
    protected $appends = [
        'image_url',
        'banner_url',
    ];

    /**
     * Get image URL attribute for API responses
     * Returns full URL using storage_url() helper
     */
    protected function imageUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return new \Illuminate\Database\Eloquent\Casts\Attribute(
            get: fn() => $this->image ? storage_url($this->image) : null,
        );
    }

    /**
     * Get banner URL attribute for API responses
     * Returns full URL using storage_url() helper
     */
    protected function bannerUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return new \Illuminate\Database\Eloquent\Casts\Attribute(
            get: fn() => $this->banner ? storage_url($this->banner) : null,
        );
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_members', 'club_id', 'user_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function provinces(): BelongsToMany
    {
        return $this->belongsToMany(Province::class, 'club_provinces', 'club_id', 'province_id')
            ->withTimestamps();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ClubActivity::class);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(ClubJoinRequest::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ClubPost::class);
    }

    // Helper method to get member role
    public function getMemberRole(User $user): ?string
    {
        $member = $this->members()->where('user_id', $user->id)->first();
        return $member?->pivot->role;
    }

    // Check if user is management (can post)
    public function isManagement(User $user): bool
    {
        $role = $this->getMemberRole($user);
        return in_array($role, ['creator', 'admin', 'moderator']);
    }

    // Check if user is admin (creator/admin)
    public function isAdmin(User $user): bool
    {
        $role = $this->getMemberRole($user);
        return in_array($role, ['creator', 'admin']);
    }

    // Check if user is a member
    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
