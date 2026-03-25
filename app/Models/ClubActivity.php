<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ClubActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'type',
        'created_by',
        'title',
        'description',
        'activity_date',
        'end_time',
        'location',
        'max_participants',
        'recurrence_day',
        'parent_activity_id',
        'auto_approve',
        'min_skill_level',
        'max_skill_level',
        'competition_config',
        'status',
        'qr_code',
        'courts_count',
        'avg_match_duration',
        'rotation_mode',
        'gender_preference_enabled',
        'oprs_weight',
        'allow_guests',
        'best_of',
        'points_per_set',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'activity_date' => 'datetime',
        'auto_approve' => 'boolean',
        'competition_config' => 'array',
        'recurrence_day' => 'integer',
        'min_skill_level' => 'float',
        'max_skill_level' => 'float',
        'courts_count' => 'integer',
        'gender_preference_enabled' => 'boolean',
        'oprs_weight' => 'decimal:2',
        'allow_guests' => 'boolean',
        'best_of' => 'integer',
        'points_per_set' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // Relationships
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ClubActivityParticipant::class);
    }

    public function confirmedParticipants(): HasMany
    {
        return $this->hasMany(ClubActivityParticipant::class)->where('status', 'confirmed');
    }

    public function waitlistedParticipants(): HasMany
    {
        return $this->hasMany(ClubActivityParticipant::class)
            ->where('status', 'waitlisted')
            ->orderBy('waitlist_position');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_activity_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_activity_id');
    }

    // Auto-generated post in club feed linked to this activity
    public function post(): HasOne
    {
        return $this->hasOne(ClubPost::class);
    }

    public function competitionTeams(): HasMany
    {
        return $this->hasMany(ClubCompetitionTeam::class);
    }

    public function competitionMatches(): HasMany
    {
        return $this->hasMany(ClubCompetitionMatch::class);
    }

    public function competitionStandings(): HasMany
    {
        return $this->hasMany(ClubCompetitionStanding::class);
    }

    public function matchRounds(): HasMany
    {
        return $this->hasMany(ClubActivityMatchRound::class);
    }

    public function matchStandings(): HasMany
    {
        return $this->hasMany(ClubActivityMatchStanding::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ClubActivityMatch::class);
    }

    // Scopes
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecurringTemplates($query)
    {
        return $query->where('type', 'recurring')->whereNull('parent_activity_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')->where('activity_date', '>=', now());
    }

    // Helpers
    public function isFull(): bool
    {
        if (!$this->max_participants) {
            return false;
        }

        return $this->confirmedParticipants()->count() >= $this->max_participants;
    }

    public function isOpenPlay(): bool
    {
        return $this->type === 'open_play';
    }

    public function generateQrCode(): string
    {
        $this->qr_code = (string) Str::uuid();
        $this->save();
        return $this->qr_code;
    }

    public function isRecurringTemplate(): bool
    {
        return $this->type === 'recurring' && $this->parent_activity_id === null;
    }

    public function spotsLeft(): int
    {
        if (!$this->max_participants) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_participants - $this->confirmedParticipants()->count());
    }

    public function userCanJoin(User $user): bool
    {
        if ($this->min_skill_level && $user->opr_level < $this->min_skill_level) {
            return false;
        }
        if ($this->max_skill_level && $user->opr_level > $this->max_skill_level) {
            return false;
        }

        return true;
    }

    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->competition_config, $key, $default);
    }

    /**
     * Build HTML content for auto-generated post in club feed
     */
    public function buildPostContent(): string
    {
        $typeLabel = match ($this->type) {
            'competition' => 'giải đấu',
            'recurring' => 'lịch hoạt động định kỳ',
            'open_play' => 'buổi chơi mở',
            default => 'hoạt động',
        };

        $safeTitle = e($this->title);
        return "<p>CLB vừa đăng {$typeLabel} mới: <strong>{$safeTitle}</strong></p>";
    }
}
