<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'role_type',
        'status',
        'elo_rating',
        'elo_rank',
        'total_ocr_matches',
        'ocr_wins',
        'ocr_losses',
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
        'elo_rating' => 'integer',
        'total_ocr_matches' => 'integer',
        'ocr_wins' => 'integer',
        'ocr_losses' => 'integer',
    ];

    /**
     * Get the user's favorite stadiums
     */
    public function favoriteStadiums()
    {
        return $this->belongsToMany(Stadium::class, 'favorites', 'user_id', 'stadium_id');
    }

    /**
     * Get the user's favorite instructors
     */
    public function favoriteInstructors()
    {
        return $this->belongsToMany(Instructor::class, 'instructor_favorites', 'user_id', 'instructor_id')->withPivot('created_at');
    }

    /**
     * Get the user's social event participations
     */
    public function socialParticipants()
    {
        return $this->belongsToMany(Social::class, 'social_participants', 'user_id', 'social_id');
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

    /**
     * Get initials from user's name (first character of each word)
     * Example: "John Doe" => "JD", "Nguyễn Văn A" => "NVA"
     *
     * @return string
     */
    public function getInitials()
    {
        $parts = explode(' ', trim($this->name));
        $initials = '';
        
        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(mb_substr($part, 0, 1));
            }
        }
        
        return $initials ?: 'U'; // Default to 'U' for 'User' if name is empty
    }

    /**
     * Get the first/primary role of the user
     * Returns the first assigned role or null if user has no roles
     *
     * @return \Spatie\Permission\Models\Role|null
     */
    public function getFirstRole()
    {
        return $this->roles()->first();
    }

    /**
     * Get the name of the user's first role
     * Example: "admin", "home_yard", "user"
     *
     * @return string|null
     */
    public function getFirstRoleName()
    {
        return $this->getFirstRole()?->role_name;
    }

    // ==================== OCR Relationships ====================

    /**
     * Get OCR matches where user is challenger
     */
    public function ocrMatchesAsChallenger(): HasMany
    {
        return $this->hasMany(OcrMatch::class, 'challenger_id');
    }

    /**
     * Get OCR matches where user is opponent
     */
    public function ocrMatchesAsOpponent(): HasMany
    {
        return $this->hasMany(OcrMatch::class, 'opponent_id');
    }

    /**
     * Get Elo history records
     */
    public function eloHistories(): HasMany
    {
        return $this->hasMany(EloHistory::class);
    }

    /**
     * Get user badges
     */
    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    // ==================== OCR Methods ====================

    /**
     * Get all OCR matches for user (as any role)
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, OcrMatch>
     */
    public function getAllOcrMatches()
    {
        return OcrMatch::forUser($this->id)->get();
    }

    /**
     * Get user win rate percentage
     */
    public function getWinRateAttribute(): float
    {
        if ($this->total_ocr_matches === 0) {
            return 0.0;
        }
        return round(($this->ocr_wins / $this->total_ocr_matches) * 100, 1);
    }

    /**
     * Check if user has specific badge
     */
    public function hasBadge(string $badgeType): bool
    {
        return $this->badges()->where('badge_type', $badgeType)->exists();
    }

    /**
     * Get Elo rank thresholds
     *
     * @return array<string, array{min: int, max: int}>
     */
    public static function getEloRanks(): array
    {
        return [
            'Bronze' => ['min' => 0, 'max' => 1099],
            'Silver' => ['min' => 1100, 'max' => 1299],
            'Gold' => ['min' => 1300, 'max' => 1499],
            'Platinum' => ['min' => 1500, 'max' => 1699],
            'Diamond' => ['min' => 1700, 'max' => 1899],
            'Master' => ['min' => 1900, 'max' => 2099],
            'Grandmaster' => ['min' => 2100, 'max' => PHP_INT_MAX],
        ];
    }

    /**
     * Calculate rank based on current Elo rating
     */
    public function calculateEloRank(): string
    {
        foreach (self::getEloRanks() as $rank => $range) {
            if ($this->elo_rating >= $range['min'] && $this->elo_rating <= $range['max']) {
                return $rank;
            }
        }
        return 'Bronze';
    }

    /**
     * Update Elo rank if changed
     */
    public function updateEloRank(): void
    {
        $newRank = $this->calculateEloRank();
        if ($this->elo_rank !== $newRank) {
            $this->update(['elo_rank' => $newRank]);
        }
    }

    /**
     * Get current win streak count
     */
    public function getCurrentWinStreak(): int
    {
        $streak = 0;
        $histories = $this->eloHistories()
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($histories as $history) {
            if ($history->change_reason === EloHistory::REASON_MATCH_WIN) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Award a badge to user
     *
     * @param string $badgeType
     * @param array<string, mixed>|null $metadata
     */
    public function awardBadge(string $badgeType, ?array $metadata = null): ?UserBadge
    {
        if ($this->hasBadge($badgeType)) {
            return null;
        }

        return $this->badges()->create([
            'badge_type' => $badgeType,
            'earned_at' => now(),
            'metadata' => $metadata,
        ]);
    }
}
