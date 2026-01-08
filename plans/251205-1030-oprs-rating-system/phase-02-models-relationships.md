# Phase 2: Models & Relationships

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 1: Database Schema](./phase-01-database-schema.md)
**Related Docs**: [code-standards.md](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2025-12-05 |
| Description | Create Eloquent models for OPRS components |
| Priority | Critical |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

1. Follow existing model patterns (User.php, OcrMatch.php)
2. Use typed properties and return types per code standards
3. Challenge types should have static definitions like UserBadge
4. Community activities need polymorphic reference support
5. Extend User model with OPRS-related methods

## Requirements

### Functional
- ChallengeResult model with type constants and info helpers
- CommunityActivity model with type constants
- OprsHistory model for tracking
- User model extensions for OPRS calculation
- Relationship definitions between all models

### Non-Functional
- Type hints on all methods
- PHPDoc comments for complex methods
- Follow existing code conventions
- No `any` types (use `mixed` with docs or specific types)

## Architecture

### Model Relationships

```
User
├── hasMany ChallengeResult
├── hasMany CommunityActivity
├── hasMany OprsHistory
├── hasMany EloHistory (existing)
├── hasMany UserBadge (existing)
└── hasMany OcrMatch (existing)

ChallengeResult
├── belongsTo User (owner)
└── belongsTo User (verifier)

CommunityActivity
├── belongsTo User
└── morphTo reference (Stadium, Social, User, etc.)

OprsHistory
└── belongsTo User
```

## Related Code Files

| File | Action | Purpose |
|------|--------|---------|
| `app/Models/ChallengeResult.php` | Create | Challenge test results |
| `app/Models/CommunityActivity.php` | Create | Community activity records |
| `app/Models/OprsHistory.php` | Create | OPRS change history |
| `app/Models/User.php` | Modify | Add OPRS fields, methods, relationships |

## Implementation Steps

### Step 1: Create ChallengeResult Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeResult extends Model
{
    // Challenge types
    public const TYPE_DINKING_RALLY = 'dinking_rally';
    public const TYPE_DROP_SHOT = 'drop_shot';
    public const TYPE_SERVE_ACCURACY = 'serve_accuracy';
    public const TYPE_MONTHLY_TEST = 'monthly_test';

    // Points per challenge type
    public const POINTS = [
        self::TYPE_DINKING_RALLY => 10,
        self::TYPE_DROP_SHOT => 8,
        self::TYPE_SERVE_ACCURACY => 6,
        self::TYPE_MONTHLY_TEST => ['min' => 30, 'max' => 50],
    ];

    // Pass thresholds
    public const THRESHOLDS = [
        self::TYPE_DINKING_RALLY => ['rallies' => 20],
        self::TYPE_DROP_SHOT => ['success' => 5, 'total' => 10],
        self::TYPE_SERVE_ACCURACY => ['success' => 7, 'total' => 10],
        self::TYPE_MONTHLY_TEST => ['score' => 70],
    ];

    protected $fillable = [
        'user_id',
        'challenge_type',
        'score',
        'passed',
        'points_earned',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'passed' => 'boolean',
        'points_earned' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Static helpers
    public static function getAllTypes(): array
    {
        return [
            self::TYPE_DINKING_RALLY,
            self::TYPE_DROP_SHOT,
            self::TYPE_SERVE_ACCURACY,
            self::TYPE_MONTHLY_TEST,
        ];
    }

    public static function getChallengeInfo(string $type): array
    {
        $info = [
            self::TYPE_DINKING_RALLY => [
                'name' => 'Dinking Rally Test',
                'description' => 'Rally lien tuc 20 lan khong loi',
                'points' => 10,
                'icon' => '[RALLY]',
            ],
            self::TYPE_DROP_SHOT => [
                'name' => 'Drop Shot Accuracy',
                'description' => '5/10 drop shot vao vung kitchen',
                'points' => 8,
                'icon' => '[DROP]',
            ],
            self::TYPE_SERVE_ACCURACY => [
                'name' => 'Serve Accuracy',
                'description' => '7/10 serve vao vung muc tieu',
                'points' => 6,
                'icon' => '[SERVE]',
            ],
            self::TYPE_MONTHLY_TEST => [
                'name' => 'Monthly Test',
                'description' => 'Bai test ky thuat hang thang',
                'points' => '30-50',
                'icon' => '[TEST]',
            ],
        ];

        return $info[$type] ?? [];
    }

    // Check if passed based on type and score
    public function checkPassed(): bool
    {
        $threshold = self::THRESHOLDS[$this->challenge_type] ?? null;
        if (!$threshold) {
            return false;
        }

        return match ($this->challenge_type) {
            self::TYPE_DINKING_RALLY => $this->score >= $threshold['rallies'],
            self::TYPE_DROP_SHOT => $this->score >= $threshold['success'],
            self::TYPE_SERVE_ACCURACY => $this->score >= $threshold['success'],
            self::TYPE_MONTHLY_TEST => $this->score >= $threshold['score'],
            default => false,
        };
    }

    // Calculate points earned
    public function calculatePoints(): float
    {
        if (!$this->passed) {
            return 0;
        }

        $points = self::POINTS[$this->challenge_type] ?? 0;

        if (is_array($points)) {
            // Monthly test: scale based on score (70-100 maps to 30-50)
            $minScore = 70;
            $maxScore = 100;
            $scoreRange = $maxScore - $minScore;
            $pointRange = $points['max'] - $points['min'];
            $normalizedScore = min($maxScore, max($minScore, $this->score));
            return $points['min'] + (($normalizedScore - $minScore) / $scoreRange) * $pointRange;
        }

        return (float) $points;
    }
}
```

### Step 2: Create CommunityActivity Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunityActivity extends Model
{
    // Activity types
    public const TYPE_CHECK_IN = 'check_in';
    public const TYPE_EVENT = 'event';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_WEEKLY_MATCHES = 'weekly_matches';
    public const TYPE_MONTHLY_CHALLENGE = 'monthly_challenge';

    // Points per activity
    public const POINTS = [
        self::TYPE_CHECK_IN => 2,
        self::TYPE_EVENT => 5,
        self::TYPE_REFERRAL => 10,
        self::TYPE_WEEKLY_MATCHES => 5,
        self::TYPE_MONTHLY_CHALLENGE => 15,
    ];

    protected $fillable = [
        'user_id',
        'activity_type',
        'points_earned',
        'reference_id',
        'reference_type',
        'metadata',
    ];

    protected $casts = [
        'points_earned' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    // Static helpers
    public static function getAllTypes(): array
    {
        return [
            self::TYPE_CHECK_IN,
            self::TYPE_EVENT,
            self::TYPE_REFERRAL,
            self::TYPE_WEEKLY_MATCHES,
            self::TYPE_MONTHLY_CHALLENGE,
        ];
    }

    public static function getActivityInfo(string $type): array
    {
        $info = [
            self::TYPE_CHECK_IN => [
                'name' => 'Check-in San',
                'description' => 'Check-in tai san OnePickleball/doi tac',
                'points' => 2,
                'limit' => null,
                'icon' => '[LOCATION]',
            ],
            self::TYPE_EVENT => [
                'name' => 'Tham gia Su kien',
                'description' => 'Workshop, clinic, su kien cong dong',
                'points' => 5,
                'limit' => 'per_event',
                'icon' => '[EVENT]',
            ],
            self::TYPE_REFERRAL => [
                'name' => 'Gioi thieu Nguoi moi',
                'description' => 'Nguoi duoc gioi thieu dang ky thanh cong',
                'points' => 10,
                'limit' => null,
                'icon' => '[USER_PLUS]',
            ],
            self::TYPE_WEEKLY_MATCHES => [
                'name' => 'Hoan thanh 5 tran/tuan',
                'description' => 'Choi du 5 tran trong tuan',
                'points' => 5,
                'limit' => 'weekly',
                'icon' => '[CALENDAR]',
            ],
            self::TYPE_MONTHLY_CHALLENGE => [
                'name' => 'Thu thach thang',
                'description' => 'Hoan thanh nhiem vu dac biet hang thang',
                'points' => 15,
                'limit' => 'monthly',
                'icon' => '[TROPHY]',
            ],
        ];

        return $info[$type] ?? [];
    }

    public static function getPoints(string $type): float
    {
        return (float) (self::POINTS[$type] ?? 0);
    }
}
```

### Step 3: Create OprsHistory Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OprsHistory extends Model
{
    // Change reasons
    public const REASON_MATCH_RESULT = 'match_result';
    public const REASON_CHALLENGE_COMPLETED = 'challenge_completed';
    public const REASON_COMMUNITY_ACTIVITY = 'community_activity';
    public const REASON_ADMIN_ADJUSTMENT = 'admin_adjustment';
    public const REASON_INITIAL_CALCULATION = 'initial_calculation';

    protected $fillable = [
        'user_id',
        'elo_score',
        'challenge_score',
        'community_score',
        'total_oprs',
        'opr_level',
        'change_reason',
        'metadata',
    ];

    protected $casts = [
        'elo_score' => 'decimal:2',
        'challenge_score' => 'decimal:2',
        'community_score' => 'decimal:2',
        'total_oprs' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get change description
    public function getChangeDescription(): string
    {
        return match ($this->change_reason) {
            self::REASON_MATCH_RESULT => 'Match result processed',
            self::REASON_CHALLENGE_COMPLETED => 'Challenge completed',
            self::REASON_COMMUNITY_ACTIVITY => 'Community activity recorded',
            self::REASON_ADMIN_ADJUSTMENT => 'Admin adjustment',
            self::REASON_INITIAL_CALCULATION => 'Initial OPRS calculation',
            default => 'Unknown',
        };
    }
}
```

### Step 4: Extend User Model

Add to User.php:

```php
// Add to $fillable array:
'challenge_score',
'community_score',
'total_oprs',
'opr_level',

// Add to $casts array:
'challenge_score' => 'decimal:2',
'community_score' => 'decimal:2',
'total_oprs' => 'decimal:2',

// Add relationships:
public function challengeResults(): HasMany
{
    return $this->hasMany(ChallengeResult::class);
}

public function communityActivities(): HasMany
{
    return $this->hasMany(CommunityActivity::class);
}

public function oprsHistories(): HasMany
{
    return $this->hasMany(OprsHistory::class);
}

// Add OPRS methods:
public static function getOprLevels(): array
{
    return [
        '1.0' => ['name' => 'Beginner', 'min' => 0, 'max' => 599],
        '2.0' => ['name' => 'Novice', 'min' => 600, 'max' => 899],
        '3.0' => ['name' => 'Intermediate', 'min' => 900, 'max' => 1099],
        '3.5' => ['name' => 'Upper Intermediate', 'min' => 1100, 'max' => 1349],
        '4.0' => ['name' => 'Advanced', 'min' => 1350, 'max' => 1599],
        '4.5' => ['name' => 'Pro', 'min' => 1600, 'max' => 1849],
        '5.0+' => ['name' => 'Elite', 'min' => 1850, 'max' => PHP_INT_MAX],
    ];
}

public function calculateOprLevel(): string
{
    foreach (self::getOprLevels() as $level => $range) {
        if ($this->total_oprs >= $range['min'] && $this->total_oprs <= $range['max']) {
            return $level;
        }
    }
    return '1.0';
}

public function updateOprLevel(): void
{
    $newLevel = $this->calculateOprLevel();
    if ($this->opr_level !== $newLevel) {
        $this->update(['opr_level' => $newLevel]);
    }
}
```

## Todo List

- [ ] Create ChallengeResult model with constants
- [ ] Create CommunityActivity model with polymorphic
- [ ] Create OprsHistory model
- [ ] Add OPRS fields to User model fillable/casts
- [ ] Add OPRS relationships to User model
- [ ] Add OPRS helper methods to User model
- [ ] Test model relationships work correctly

## Success Criteria

1. All models created with proper typing
2. Relationships work correctly
3. Static helpers return correct data
4. Challenge pass/fail logic works
5. Points calculation accurate

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Model conflict | Low | Follow existing patterns |
| Type casting issues | Low | Test thoroughly |
| Relationship circular | Low | Simple parent-child |

## Security Considerations

- No direct user input to models
- Verified_by tracks accountability
- Points validated before assignment

## Next Steps

After models complete:
1. Proceed to [Phase 3: OPRS Service](./phase-03-oprs-service.md)
2. Implement OprsService for calculations
3. Integrate with existing EloService
