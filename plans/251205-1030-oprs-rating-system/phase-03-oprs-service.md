# Phase 3: OPRS Service & Core Logic

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 2: Models](./phase-02-models-relationships.md)
**Related Docs**: [code-standards.md](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2025-12-05 |
| Description | Implement OprsService for OPRS calculations |
| Priority | Critical |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

1. OPRS = (0.7 * Elo) + (0.2 * Challenge) + (0.1 * Community)
2. Must integrate with existing EloService
3. Should trigger recalculation on Elo/Challenge/Community changes
4. Need to batch calculate for existing users
5. OprsHistory records every OPRS change

## Requirements

### Functional
- Calculate total OPRS from three components
- Determine OPR Level from OPRS score
- Update user OPRS after any component change
- Record OPRS history for auditing
- Provide estimation methods for UI
- Batch recalculation for existing users

### Non-Functional
- Transaction safety for updates
- Performance for leaderboard queries
- Accurate decimal handling
- Type-safe implementation

## Architecture

### OprsService Methods

```
OprsService
├── calculateOprs(User): float
├── calculateOprLevel(float): string
├── updateUserOprs(User, string reason, ?array metadata): void
├── recalculateAfterMatch(User): void
├── recalculateAfterChallenge(User): void
├── recalculateAfterActivity(User): void
├── batchRecalculateAll(): int
├── getOprsBreakdown(User): array
├── estimateOprsChange(User, component, change): array
└── adminAdjustment(User, component, amount, reason): void
```

### Integration Points

```
EloService::processMatchResult()
  └── calls OprsService::recalculateAfterMatch()

ChallengeService::recordChallenge()
  └── calls OprsService::recalculateAfterChallenge()

CommunityService::recordActivity()
  └── calls OprsService::recalculateAfterActivity()
```

## Related Code Files

| File | Action | Purpose |
|------|--------|---------|
| `app/Services/OprsService.php` | Create | Core OPRS calculation logic |
| `app/Services/EloService.php` | Modify | Call OprsService after match |
| `app/Console/Commands/OprsRecalculateCommand.php` | Create | Batch recalculation CLI |

## Implementation Steps

### Step 1: Create OprsService

```php
<?php

namespace App\Services;

use App\Models\OprsHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OprsService
{
    // OPRS component weights
    public const WEIGHT_ELO = 0.7;
    public const WEIGHT_CHALLENGE = 0.2;
    public const WEIGHT_COMMUNITY = 0.1;

    // OPR Level thresholds
    public const OPR_LEVELS = [
        '1.0' => ['name' => 'Beginner', 'min' => 0, 'max' => 599],
        '2.0' => ['name' => 'Novice', 'min' => 600, 'max' => 899],
        '3.0' => ['name' => 'Intermediate', 'min' => 900, 'max' => 1099],
        '3.5' => ['name' => 'Upper Intermediate', 'min' => 1100, 'max' => 1349],
        '4.0' => ['name' => 'Advanced', 'min' => 1350, 'max' => 1599],
        '4.5' => ['name' => 'Pro', 'min' => 1600, 'max' => 1849],
        '5.0+' => ['name' => 'Elite', 'min' => 1850, 'max' => PHP_INT_MAX],
    ];

    /**
     * Calculate total OPRS from components
     */
    public function calculateOprs(User $user): float
    {
        $oprs = (self::WEIGHT_ELO * $user->elo_rating)
              + (self::WEIGHT_CHALLENGE * $user->challenge_score)
              + (self::WEIGHT_COMMUNITY * $user->community_score);

        return round($oprs, 2);
    }

    /**
     * Determine OPR Level from OPRS score
     */
    public function calculateOprLevel(float $oprs): string
    {
        foreach (self::OPR_LEVELS as $level => $range) {
            if ($oprs >= $range['min'] && $oprs <= $range['max']) {
                return $level;
            }
        }
        return '1.0';
    }

    /**
     * Get level info
     */
    public function getOprLevelInfo(string $level): array
    {
        return self::OPR_LEVELS[$level] ?? self::OPR_LEVELS['1.0'];
    }

    /**
     * Update user's OPRS and record history
     *
     * @param array<string, mixed>|null $metadata
     */
    public function updateUserOprs(User $user, string $reason, ?array $metadata = null): void
    {
        DB::transaction(function () use ($user, $reason, $metadata) {
            $user->refresh();

            $newOprs = $this->calculateOprs($user);
            $newLevel = $this->calculateOprLevel($newOprs);

            // Record history
            OprsHistory::create([
                'user_id' => $user->id,
                'elo_score' => $user->elo_rating,
                'challenge_score' => $user->challenge_score,
                'community_score' => $user->community_score,
                'total_oprs' => $newOprs,
                'opr_level' => $newLevel,
                'change_reason' => $reason,
                'metadata' => $metadata,
            ]);

            // Update user
            $user->update([
                'total_oprs' => $newOprs,
                'opr_level' => $newLevel,
            ]);
        });
    }

    /**
     * Recalculate after Elo change (match result)
     */
    public function recalculateAfterMatch(User $user, ?int $matchId = null): void
    {
        $this->updateUserOprs(
            $user,
            OprsHistory::REASON_MATCH_RESULT,
            $matchId ? ['ocr_match_id' => $matchId] : null
        );
    }

    /**
     * Recalculate after challenge completion
     */
    public function recalculateAfterChallenge(User $user, ?int $challengeId = null): void
    {
        $this->updateUserOprs(
            $user,
            OprsHistory::REASON_CHALLENGE_COMPLETED,
            $challengeId ? ['challenge_result_id' => $challengeId] : null
        );
    }

    /**
     * Recalculate after community activity
     */
    public function recalculateAfterActivity(User $user, ?int $activityId = null): void
    {
        $this->updateUserOprs(
            $user,
            OprsHistory::REASON_COMMUNITY_ACTIVITY,
            $activityId ? ['community_activity_id' => $activityId] : null
        );
    }

    /**
     * Batch recalculate all users' OPRS
     * @return int Number of users updated
     */
    public function batchRecalculateAll(): int
    {
        $count = 0;

        User::chunk(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                $this->updateUserOprs(
                    $user,
                    OprsHistory::REASON_INITIAL_CALCULATION
                );
                $count++;
            }
        });

        return $count;
    }

    /**
     * Get OPRS breakdown for display
     *
     * @return array{elo: array, challenge: array, community: array, total: float, level: string}
     */
    public function getOprsBreakdown(User $user): array
    {
        return [
            'elo' => [
                'raw' => $user->elo_rating,
                'weight' => self::WEIGHT_ELO,
                'weighted' => round($user->elo_rating * self::WEIGHT_ELO, 2),
            ],
            'challenge' => [
                'raw' => $user->challenge_score,
                'weight' => self::WEIGHT_CHALLENGE,
                'weighted' => round($user->challenge_score * self::WEIGHT_CHALLENGE, 2),
            ],
            'community' => [
                'raw' => $user->community_score,
                'weight' => self::WEIGHT_COMMUNITY,
                'weighted' => round($user->community_score * self::WEIGHT_COMMUNITY, 2),
            ],
            'total' => $user->total_oprs,
            'level' => $user->opr_level,
            'level_info' => $this->getOprLevelInfo($user->opr_level),
        ];
    }

    /**
     * Estimate OPRS change before action
     *
     * @return array{before: float, after: float, change: float, new_level: string}
     */
    public function estimateOprsChange(User $user, string $component, float $change): array
    {
        $currentOprs = $user->total_oprs;

        $weight = match ($component) {
            'elo' => self::WEIGHT_ELO,
            'challenge' => self::WEIGHT_CHALLENGE,
            'community' => self::WEIGHT_COMMUNITY,
            default => 0,
        };

        $oprsChange = $change * $weight;
        $newOprs = round($currentOprs + $oprsChange, 2);

        return [
            'before' => $currentOprs,
            'after' => $newOprs,
            'change' => $oprsChange,
            'new_level' => $this->calculateOprLevel($newOprs),
        ];
    }

    /**
     * Admin adjustment of a specific component
     */
    public function adminAdjustment(
        User $user,
        string $component,
        float $amount,
        string $reason = 'Admin adjustment'
    ): void {
        DB::transaction(function () use ($user, $component, $amount, $reason) {
            // Update the specific component
            $field = match ($component) {
                'challenge' => 'challenge_score',
                'community' => 'community_score',
                default => null,
            };

            if (!$field) {
                throw new \InvalidArgumentException('Invalid component: ' . $component);
            }

            $newValue = max(0, $user->$field + $amount);
            $user->update([$field => $newValue]);

            // Recalculate OPRS
            $this->updateUserOprs($user, OprsHistory::REASON_ADMIN_ADJUSTMENT, [
                'component' => $component,
                'adjustment' => $amount,
                'admin_reason' => $reason,
            ]);
        });
    }

    /**
     * Get leaderboard data with OPRS
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getLeaderboard(
        ?string $oprLevel = null,
        int $limit = 50,
        int $offset = 0
    ) {
        $query = User::query()
            ->where('total_oprs', '>', 0)
            ->orderByDesc('total_oprs');

        if ($oprLevel) {
            $query->where('opr_level', $oprLevel);
        }

        return $query->skip($offset)->take($limit)->get();
    }

    /**
     * Get level distribution stats
     *
     * @return array<string, int>
     */
    public function getLevelDistribution(): array
    {
        return User::query()
            ->selectRaw('opr_level, COUNT(*) as count')
            ->groupBy('opr_level')
            ->pluck('count', 'opr_level')
            ->toArray();
    }
}
```

### Step 2: Modify EloService

Add to EloService after Elo update:

```php
// In applyEloChange() method, after user update:
// Trigger OPRS recalculation
app(OprsService::class)->recalculateAfterMatch($user, $match->id);
```

### Step 3: Create Artisan Command

```php
<?php

namespace App\Console\Commands;

use App\Services\OprsService;
use Illuminate\Console\Command;

class OprsRecalculateCommand extends Command
{
    protected $signature = 'oprs:recalculate {--user= : Specific user ID}';
    protected $description = 'Recalculate OPRS for all or specific user';

    public function handle(OprsService $oprsService): int
    {
        $userId = $this->option('user');

        if ($userId) {
            $user = \App\Models\User::find($userId);
            if (!$user) {
                $this->error("User {$userId} not found");
                return 1;
            }

            $oprsService->updateUserOprs(
                $user,
                \App\Models\OprsHistory::REASON_ADMIN_ADJUSTMENT,
                ['reason' => 'Manual recalculation']
            );

            $this->info("Recalculated OPRS for user {$userId}: {$user->total_oprs}");
            return 0;
        }

        $this->info('Recalculating OPRS for all users...');
        $count = $oprsService->batchRecalculateAll();
        $this->info("Recalculated OPRS for {$count} users");

        return 0;
    }
}
```

## Todo List

- [ ] Create OprsService with calculation methods
- [ ] Implement history recording
- [ ] Add batch recalculation method
- [ ] Create OprsRecalculateCommand
- [ ] Modify EloService to trigger OPRS recalc
- [ ] Test calculations match spec formula
- [ ] Test level thresholds correct

## Success Criteria

1. OPRS = 0.7*Elo + 0.2*Challenge + 0.1*Community (verified)
2. OPR Level mapping matches spec thresholds
3. History recorded on every change
4. Batch recalculation works for all users
5. EloService integration triggers OPRS update

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Calculation mismatch | High | Unit tests for formula |
| Transaction deadlock | Medium | Proper locking order |
| Performance on batch | Medium | Chunked processing |

## Security Considerations

- Admin adjustments logged with reason
- No direct score manipulation from API
- Audit trail via OprsHistory

## Next Steps

After OprsService complete:
1. Proceed to [Phase 4: Challenge System](./phase-04-challenge-system.md)
2. Implement ChallengeService for test recording
3. Build challenge submission flow
