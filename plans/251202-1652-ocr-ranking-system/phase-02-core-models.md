# Phase 2: Core Models

## Context Links

- [Parent Plan](./plan.md)
- [Phase 1: Database Schema](./phase-01-database-schema.md)
- [Existing User Model](../../app/Models/User.php)
- [Code Standards](../../docs/code-standards.md)

## Overview

- **Date**: 2025-12-02
- **Priority**: High
- **Implementation Status**: Pending
- **Review Status**: Pending
- **Dependencies**: Phase 1 (Database Schema)

Create Eloquent models for OCR system with relationships, accessors, and scopes.

## Key Insights

1. User model needs extension with Elo-related methods
2. OcrMatch model handles match state machine
3. HasMedia trait for evidence upload (Spatie)
4. EloHistory and UserBadge are simpler tracking models

## Requirements

### Functional

- User model: Elo accessor, rank calculator, relationships to matches/badges
- OcrMatch model: State transitions, participant validation, score tracking
- EloHistory model: Audit trail
- UserBadge model: Achievement tracking

### Non-Functional

- Follow existing model conventions (see TournamentAthlete.php)
- Type hints on all methods
- Proper relationship definitions

## Architecture

### Model Relationships

```
User
  hasMany OcrMatch (as challenger)
  hasMany OcrMatch (as opponent)
  hasMany EloHistory
  hasMany UserBadge

OcrMatch
  belongsTo User (challenger)
  belongsTo User (challenger_partner)
  belongsTo User (opponent)
  belongsTo User (opponent_partner)
  belongsTo User (result_submitted_by)
  hasMany EloHistory
  morphMany Media (evidence)

EloHistory
  belongsTo User
  belongsTo OcrMatch

UserBadge
  belongsTo User
```

## Related Code Files

### Files to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Models/OcrMatch.php` | Create | OCR match model |
| `app/Models/EloHistory.php` | Create | Elo history model |
| `app/Models/UserBadge.php` | Create | User badge model |

### Files to Modify

| File | Action | Description |
|------|--------|-------------|
| `app/Models/User.php` | Modify | Add Elo methods, relationships |

## Implementation Steps

### Step 1: Create OcrMatch Model

```php
<?php
// app/Models/OcrMatch.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class OcrMatch extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'ocr_matches';

    protected $fillable = [
        'match_type',
        'challenger_id',
        'challenger_partner_id',
        'opponent_id',
        'opponent_partner_id',
        'challenger_score',
        'opponent_score',
        'winner_team',
        'status',
        'scheduled_date',
        'scheduled_time',
        'location',
        'notes',
        'result_submitted_by',
        'result_submitted_at',
        'confirmed_at',
        'disputed_reason',
        'elo_challenger_before',
        'elo_opponent_before',
        'elo_challenger_after',
        'elo_opponent_after',
        'elo_change',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'string',
        'result_submitted_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'challenger_score' => 'integer',
        'opponent_score' => 'integer',
        'elo_challenger_before' => 'integer',
        'elo_opponent_before' => 'integer',
        'elo_challenger_after' => 'integer',
        'elo_opponent_after' => 'integer',
        'elo_change' => 'integer',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESULT_SUBMITTED = 'result_submitted';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DISPUTED = 'disputed';
    public const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function challenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenger_id');
    }

    public function challengerPartner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenger_partner_id');
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function opponentPartner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_partner_id');
    }

    public function resultSubmitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'result_submitted_by');
    }

    public function eloHistories(): HasMany
    {
        return $this->hasMany(EloHistory::class, 'ocr_match_id');
    }

    // Media collection for evidence
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('evidence');
    }

    // Status checks
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isDisputed(): bool
    {
        return $this->status === self::STATUS_DISPUTED;
    }

    // Participant checks
    public function isParticipant(int $userId): bool
    {
        return in_array($userId, $this->getAllParticipantIds());
    }

    public function isChallengerTeam(int $userId): bool
    {
        return $userId === $this->challenger_id || $userId === $this->challenger_partner_id;
    }

    public function isOpponentTeam(int $userId): bool
    {
        return $userId === $this->opponent_id || $userId === $this->opponent_partner_id;
    }

    public function getAllParticipantIds(): array
    {
        return array_filter([
            $this->challenger_id,
            $this->challenger_partner_id,
            $this->opponent_id,
            $this->opponent_partner_id,
        ]);
    }

    // State transitions
    public function accept(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \InvalidArgumentException('Match not in pending status');
        }
        $this->update(['status' => self::STATUS_ACCEPTED]);
    }

    public function startMatch(): void
    {
        if ($this->status !== self::STATUS_ACCEPTED) {
            throw new \InvalidArgumentException('Match not accepted');
        }
        $this->update(['status' => self::STATUS_IN_PROGRESS]);
    }

    public function submitResult(int $submitterId, int $challengerScore, int $opponentScore): void
    {
        $this->update([
            'status' => self::STATUS_RESULT_SUBMITTED,
            'result_submitted_by' => $submitterId,
            'result_submitted_at' => now(),
            'challenger_score' => $challengerScore,
            'opponent_score' => $opponentScore,
            'winner_team' => $challengerScore > $opponentScore ? 'challenger' : 'opponent',
        ]);
    }

    public function confirmResult(): void
    {
        if ($this->status !== self::STATUS_RESULT_SUBMITTED) {
            throw new \InvalidArgumentException('Result not submitted');
        }
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    public function dispute(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_DISPUTED,
            'disputed_reason' => $reason,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('challenger_id', $userId)
              ->orWhere('challenger_partner_id', $userId)
              ->orWhere('opponent_id', $userId)
              ->orWhere('opponent_partner_id', $userId);
        });
    }

    // Accessors
    public function getIsDoublesAttribute(): bool
    {
        return $this->match_type === 'doubles';
    }
}
```

### Step 2: Create EloHistory Model

```php
<?php
// app/Models/EloHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EloHistory extends Model
{
    use HasFactory;

    protected $table = 'elo_histories';

    protected $fillable = [
        'user_id',
        'ocr_match_id',
        'elo_before',
        'elo_after',
        'change_amount',
        'change_reason',
    ];

    protected $casts = [
        'elo_before' => 'integer',
        'elo_after' => 'integer',
        'change_amount' => 'integer',
    ];

    // Constants
    public const REASON_MATCH_WIN = 'match_win';
    public const REASON_MATCH_LOSS = 'match_loss';
    public const REASON_ADMIN_ADJUSTMENT = 'admin_adjustment';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ocrMatch(): BelongsTo
    {
        return $this->belongsTo(OcrMatch::class, 'ocr_match_id');
    }

    // Accessors
    public function getIsPositiveAttribute(): bool
    {
        return $this->change_amount > 0;
    }
}
```

### Step 3: Create UserBadge Model

```php
<?php
// app/Models/UserBadge.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    use HasFactory;

    protected $table = 'user_badges';

    protected $fillable = [
        'user_id',
        'badge_type',
        'earned_at',
        'metadata',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Badge type constants
    public const BADGE_FIRST_WIN = 'first_win';
    public const BADGE_STREAK_3 = 'streak_3';
    public const BADGE_STREAK_5 = 'streak_5';
    public const BADGE_STREAK_10 = 'streak_10';
    public const BADGE_RANK_SILVER = 'rank_silver';
    public const BADGE_RANK_GOLD = 'rank_gold';
    public const BADGE_RANK_PLATINUM = 'rank_platinum';
    public const BADGE_RANK_DIAMOND = 'rank_diamond';
    public const BADGE_MATCHES_10 = 'matches_10';
    public const BADGE_MATCHES_50 = 'matches_50';
    public const BADGE_MATCHES_100 = 'matches_100';

    // Badge metadata
    public static function getBadgeInfo(string $type): array
    {
        $badges = [
            self::BADGE_FIRST_WIN => ['name' => 'First Blood', 'description' => 'Won first match', 'icon' => '[TROPHY]'],
            self::BADGE_STREAK_3 => ['name' => 'On Fire', 'description' => '3 win streak', 'icon' => '[FIRE]'],
            self::BADGE_STREAK_5 => ['name' => 'Unstoppable', 'description' => '5 win streak', 'icon' => '[LIGHTNING]'],
            self::BADGE_STREAK_10 => ['name' => 'Legend', 'description' => '10 win streak', 'icon' => '[STAR]'],
            self::BADGE_RANK_SILVER => ['name' => 'Silver Player', 'description' => 'Reached Silver rank', 'icon' => '[SILVER]'],
            self::BADGE_RANK_GOLD => ['name' => 'Gold Player', 'description' => 'Reached Gold rank', 'icon' => '[GOLD]'],
            self::BADGE_RANK_PLATINUM => ['name' => 'Platinum Player', 'description' => 'Reached Platinum rank', 'icon' => '[PLATINUM]'],
            self::BADGE_RANK_DIAMOND => ['name' => 'Diamond Player', 'description' => 'Reached Diamond rank', 'icon' => '[DIAMOND]'],
            self::BADGE_MATCHES_10 => ['name' => 'Regular', 'description' => '10 matches played', 'icon' => '[PLAYER]'],
            self::BADGE_MATCHES_50 => ['name' => 'Veteran', 'description' => '50 matches played', 'icon' => '[VETERAN]'],
            self::BADGE_MATCHES_100 => ['name' => 'Pro', 'description' => '100 matches played', 'icon' => '[CROWN]'],
        ];

        return $badges[$type] ?? ['name' => 'Unknown', 'description' => '', 'icon' => '[BADGE]'];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getNameAttribute(): string
    {
        return self::getBadgeInfo($this->badge_type)['name'];
    }

    public function getDescriptionAttribute(): string
    {
        return self::getBadgeInfo($this->badge_type)['description'];
    }

    public function getIconAttribute(): string
    {
        return self::getBadgeInfo($this->badge_type)['icon'];
    }
}
```

### Step 4: Update User Model

Add to existing `app/Models/User.php`:

```php
// Add to $fillable array:
'elo_rating',
'elo_rank',
'total_ocr_matches',
'ocr_wins',
'ocr_losses',

// Add to $casts array:
'elo_rating' => 'integer',
'total_ocr_matches' => 'integer',
'ocr_wins' => 'integer',
'ocr_losses' => 'integer',

// Add these relationships:
public function ocrMatchesAsChallenger(): HasMany
{
    return $this->hasMany(OcrMatch::class, 'challenger_id');
}

public function ocrMatchesAsOpponent(): HasMany
{
    return $this->hasMany(OcrMatch::class, 'opponent_id');
}

public function eloHistories(): HasMany
{
    return $this->hasMany(EloHistory::class);
}

public function badges(): HasMany
{
    return $this->hasMany(UserBadge::class);
}

// Add these methods:
public function getAllOcrMatches()
{
    return OcrMatch::forUser($this->id)->get();
}

public function getWinRateAttribute(): float
{
    if ($this->total_ocr_matches === 0) {
        return 0.0;
    }
    return round(($this->ocr_wins / $this->total_ocr_matches) * 100, 1);
}

public function hasBadge(string $badgeType): bool
{
    return $this->badges()->where('badge_type', $badgeType)->exists();
}

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

public function calculateEloRank(): string
{
    foreach (self::getEloRanks() as $rank => $range) {
        if ($this->elo_rating >= $range['min'] && $this->elo_rating <= $range['max']) {
            return $rank;
        }
    }
    return 'Bronze';
}

public function updateEloRank(): void
{
    $newRank = $this->calculateEloRank();
    if ($this->elo_rank !== $newRank) {
        $this->update(['elo_rank' => $newRank]);
    }
}
```

## Todo List

- [ ] Create OcrMatch model file
- [ ] Create EloHistory model file
- [ ] Create UserBadge model file
- [ ] Update User model with Elo fields and relationships
- [ ] Test model relationships work correctly

## Success Criteria

1. All models created following Laravel conventions
2. Relationships work in both directions
3. State transitions enforce valid workflow
4. Media collection for evidence configured
5. Type hints on all methods

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Model bloat in User | Medium | Keep Elo logic in service layer |
| State machine bugs | High | Unit test all transitions |

## Security Considerations

- `isParticipant()` check prevents unauthorized access
- State machine prevents invalid transitions
- No raw SQL, uses Eloquent

## Next Steps

After models complete, proceed to [Phase 3: Elo Service](./phase-03-elo-service.md)
