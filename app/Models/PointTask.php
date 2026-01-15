<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointTask extends Model
{
    // Task code constants
    public const CODE_REFERRAL = 'referral';
    public const CODE_CHECK_IN_STADIUM = 'check_in_stadium';
    public const CODE_WEEKLY_5_MATCHES = 'weekly_5_matches';
    public const CODE_JOIN_EVENT = 'join_event';
    public const CODE_SPECIAL_CHALLENGE = 'special_challenge';
    public const CODE_JOIN_FB_GROUP = 'join_fb_group';
    public const CODE_FOLLOW_FB_PAGE = 'follow_fb_page';
    public const CODE_SUBSCRIBE_YOUTUBE = 'subscribe_youtube';
    public const CODE_FOLLOW_TIKTOK = 'follow_tiktok';
    public const CODE_JOIN_CLUB = 'join_club';
    public const CODE_CREATE_OCR_MATCH = 'create_ocr_match';
    public const CODE_UPDATE_STADIUM_INFO = 'update_stadium_info';
    public const CODE_CREATE_SOCIAL_SCHEDULE = 'create_social_schedule';
    public const CODE_CREATE_TOURNAMENT = 'create_tournament';
    public const CODE_REFEREE_SCORE_MATCH = 'referee_score_match';
    public const CODE_EXPERT_VERIFY_ELO = 'expert_verify_elo';

    // Role constants
    public const ROLE_USER = 'user';
    public const ROLE_HOME_YARD = 'home_yard';
    public const ROLE_REFEREE = 'referee';
    public const ROLE_EXPERT_HOST = 'expert_host';

    // Category constants
    public const CATEGORY_DAILY = 'daily';
    public const CATEGORY_SOCIAL = 'social';
    public const CATEGORY_EVENT = 'event';
    public const CATEGORY_TOURNAMENT = 'tournament';

    // Frequency constants
    public const FREQ_UNLIMITED = 'unlimited';
    public const FREQ_DAILY = 'daily';
    public const FREQ_WEEKLY = 'weekly';
    public const FREQ_MONTHLY = 'monthly';
    public const FREQ_ONCE = 'once';

    // Proof type constants
    public const PROOF_NONE = 'none';
    public const PROOF_IMAGE = 'image';
    public const PROOF_LINK = 'link';
    public const PROOF_QR_CODE = 'qr_code';

    protected $fillable = [
        'code',
        'name',
        'description',
        'points',
        'role',
        'category',
        'frequency',
        'requires_approval',
        'proof_type',
        'is_active',
    ];

    protected $casts = [
        'points' => 'integer',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get submissions for this task
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(PointSubmission::class);
    }

    /**
     * Find task by code
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Get active tasks for a role
     */
    public static function getActiveByRole(string $role): Collection
    {
        return static::where('role', $role)->where('is_active', true)->get();
    }

    /**
     * Get active tasks by category
     */
    public static function getActiveByCategory(string $category): Collection
    {
        return static::where('category', $category)->where('is_active', true)->get();
    }

    /**
     * Scope for active tasks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for tasks requiring approval
     */
    public function scopeRequiresApproval($query)
    {
        return $query->where('requires_approval', true);
    }

    /**
     * Check if task requires proof
     */
    public function requiresProof(): bool
    {
        return $this->proof_type !== self::PROOF_NONE;
    }

    /**
     * Get action URL for auto-award tasks (redirect user to perform the action)
     * Returns null if task requires manual submission or no action URL available
     */
    public function getActionUrl(): ?string
    {
        // Only auto-award tasks (requires_approval = false) have action URLs
        if ($this->requires_approval) {
            return null;
        }

        return match ($this->code) {
            // User tasks
            self::CODE_REFERRAL => route('user.referral.index'),
            self::CODE_CREATE_OCR_MATCH => route('ocr.matches.create'),
            self::CODE_JOIN_CLUB => route('clubs.index'),
            // HomeYard tasks
            self::CODE_UPDATE_STADIUM_INFO => route('homeyard.stadiums.index'),
            self::CODE_CREATE_SOCIAL_SCHEDULE => route('homeyard.socials.create'),
            self::CODE_CREATE_TOURNAMENT => route('homeyard.tournaments.index'),
            // Referee task
            self::CODE_REFEREE_SCORE_MATCH => route('referee.matches.index'),
            // Expert task
            self::CODE_EXPERT_VERIFY_ELO => route('verifier.requests.index'),
            // Tasks without direct action URL (triggered by system events)
            default => null,
        };
    }

    /**
     * Get action button label for the task
     */
    public function getActionLabel(): string
    {
        return match ($this->code) {
            self::CODE_REFERRAL => 'Mời bạn bè',
            self::CODE_CREATE_OCR_MATCH => 'Tạo trận đấu',
            self::CODE_JOIN_CLUB => 'Tìm CLB',
            self::CODE_UPDATE_STADIUM_INFO => 'Quản lý sân',
            self::CODE_CREATE_SOCIAL_SCHEDULE => 'Tạo lịch Social',
            self::CODE_CREATE_TOURNAMENT => 'Tạo giải đấu',
            self::CODE_REFEREE_SCORE_MATCH => 'Chấm điểm',
            self::CODE_EXPERT_VERIFY_ELO => 'Xác thực',
            default => 'Thực hiện',
        };
    }

    /**
     * Get external URL for social tasks (link to OnePickleball's social pages)
     */
    public function getExternalUrl(): ?string
    {
        return match ($this->code) {
            self::CODE_JOIN_FB_GROUP => 'https://www.facebook.com/groups/congdongonepickleball',
            self::CODE_FOLLOW_FB_PAGE => 'https://www.facebook.com/onepickleballvn',
            self::CODE_SUBSCRIBE_YOUTUBE => 'https://www.youtube.com/@OnePickleballvn',
            self::CODE_FOLLOW_TIKTOK => 'https://www.tiktok.com/@onepickleballvn',
            default => null,
        };
    }
}
