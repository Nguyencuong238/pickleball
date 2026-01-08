# Phase 5: Community Activity System

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 4: Challenge System](./phase-04-challenge-system.md)
**Related Docs**: [code-standards.md](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2025-12-05 |
| Description | Implement community activity point system |
| Priority | High |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

1. Five activity types with different point values
2. Some activities have frequency limits (weekly, monthly)
3. Check-in requires stadium/location reference
4. Referral requires tracking referred user
5. Weekly matches checked automatically from OCR history

## Requirements

### Functional
- Check-in at stadiums
- Record event participation
- Track referrals with verification
- Auto-calculate weekly match bonus
- Monthly challenge completion
- OPRS recalculation after activity

### Non-Functional
- Prevent duplicate check-ins same day/location
- Validate referral integrity
- Background job for weekly match check
- Audit trail for all activities

## Architecture

### CommunityService Methods

```
CommunityService
├── checkIn(User, Stadium): CommunityActivity
├── recordEventParticipation(User, eventId): CommunityActivity
├── recordReferral(User, referredUser): CommunityActivity
├── checkWeeklyMatchBonus(User): ?CommunityActivity
├── recordMonthlyChallenge(User, challengeData): CommunityActivity
├── getActivityHistory(User): Collection
├── getActivityStats(User): array
├── checkDailyCheckInLimit(User, stadiumId): bool
└── processWeeklyBonuses(): int
```

### Activity Flow

```
Activity occurs (check-in/event/referral)
         │
         ▼
┌─────────────────────┐
│ Validate eligibility│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Check frequency limit│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────────┐
│ Create CommunityActivity│
│ with reference          │
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────┐
│ Update community_score│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Recalculate OPRS    │
└─────────────────────┘
```

## Related Code Files

| File | Action | Purpose |
|------|--------|---------|
| `app/Services/CommunityService.php` | Create | Community activity logic |
| `app/Http/Controllers/Api/CommunityController.php` | Create | API endpoints |
| `app/Console/Commands/ProcessWeeklyBonusCommand.php` | Create | Weekly bonus job |

## Implementation Steps

### Step 1: Create CommunityService

```php
<?php

namespace App\Services;

use App\Models\CommunityActivity;
use App\Models\OcrMatch;
use App\Models\Social;
use App\Models\Stadium;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CommunityService
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Record check-in at stadium
     */
    public function checkIn(User $user, Stadium $stadium): CommunityActivity
    {
        // Check daily limit for this stadium
        if (!$this->canCheckInToday($user, $stadium->id)) {
            throw new InvalidArgumentException('Already checked in at this location today');
        }

        return DB::transaction(function () use ($user, $stadium) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_CHECK_IN);

            $activity = CommunityActivity::create([
                'user_id' => $user->id,
                'activity_type' => CommunityActivity::TYPE_CHECK_IN,
                'points_earned' => $points,
                'reference_id' => $stadium->id,
                'reference_type' => Stadium::class,
                'metadata' => [
                    'stadium_name' => $stadium->name,
                    'location' => $stadium->address,
                ],
            ]);

            $this->awardPoints($user, $activity);

            return $activity;
        });
    }

    /**
     * Record event participation
     */
    public function recordEventParticipation(User $user, Social $event): CommunityActivity
    {
        // Check if already recorded for this event
        $existing = CommunityActivity::where('user_id', $user->id)
            ->where('activity_type', CommunityActivity::TYPE_EVENT)
            ->where('reference_id', $event->id)
            ->where('reference_type', Social::class)
            ->exists();

        if ($existing) {
            throw new InvalidArgumentException('Already recorded participation for this event');
        }

        return DB::transaction(function () use ($user, $event) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_EVENT);

            $activity = CommunityActivity::create([
                'user_id' => $user->id,
                'activity_type' => CommunityActivity::TYPE_EVENT,
                'points_earned' => $points,
                'reference_id' => $event->id,
                'reference_type' => Social::class,
                'metadata' => [
                    'event_name' => $event->name,
                    'event_date' => $event->date?->format('Y-m-d'),
                ],
            ]);

            $this->awardPoints($user, $activity);

            return $activity;
        });
    }

    /**
     * Record referral
     */
    public function recordReferral(User $referrer, User $referredUser): CommunityActivity
    {
        // Validate referred user is new (created within 7 days)
        if ($referredUser->created_at < Carbon::now()->subDays(7)) {
            throw new InvalidArgumentException('Referred user is not a new registration');
        }

        // Check if already claimed
        $existing = CommunityActivity::where('user_id', $referrer->id)
            ->where('activity_type', CommunityActivity::TYPE_REFERRAL)
            ->where('reference_id', $referredUser->id)
            ->where('reference_type', User::class)
            ->exists();

        if ($existing) {
            throw new InvalidArgumentException('Referral already recorded for this user');
        }

        return DB::transaction(function () use ($referrer, $referredUser) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_REFERRAL);

            $activity = CommunityActivity::create([
                'user_id' => $referrer->id,
                'activity_type' => CommunityActivity::TYPE_REFERRAL,
                'points_earned' => $points,
                'reference_id' => $referredUser->id,
                'reference_type' => User::class,
                'metadata' => [
                    'referred_user_name' => $referredUser->name,
                    'referred_user_email' => $referredUser->email,
                ],
            ]);

            $this->awardPoints($referrer, $activity);

            return $activity;
        });
    }

    /**
     * Check and award weekly match bonus
     */
    public function checkWeeklyMatchBonus(User $user): ?CommunityActivity
    {
        // Check if already awarded this week
        $startOfWeek = Carbon::now()->startOfWeek();

        $alreadyAwarded = CommunityActivity::where('user_id', $user->id)
            ->where('activity_type', CommunityActivity::TYPE_WEEKLY_MATCHES)
            ->where('created_at', '>=', $startOfWeek)
            ->exists();

        if ($alreadyAwarded) {
            return null;
        }

        // Count matches this week
        $matchCount = OcrMatch::forUser($user->id)
            ->where('status', OcrMatch::STATUS_CONFIRMED)
            ->where('confirmed_at', '>=', $startOfWeek)
            ->count();

        if ($matchCount < 5) {
            return null;
        }

        return DB::transaction(function () use ($user, $matchCount) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_WEEKLY_MATCHES);

            $activity = CommunityActivity::create([
                'user_id' => $user->id,
                'activity_type' => CommunityActivity::TYPE_WEEKLY_MATCHES,
                'points_earned' => $points,
                'metadata' => [
                    'matches_played' => $matchCount,
                    'week_start' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                ],
            ]);

            $this->awardPoints($user, $activity);

            return $activity;
        });
    }

    /**
     * Record monthly challenge completion
     *
     * @param array{challenge_id?: int, description?: string} $challengeData
     */
    public function recordMonthlyChallenge(User $user, array $challengeData): CommunityActivity
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        // Check if already completed this month
        $existing = CommunityActivity::where('user_id', $user->id)
            ->where('activity_type', CommunityActivity::TYPE_MONTHLY_CHALLENGE)
            ->where('created_at', '>=', $startOfMonth)
            ->exists();

        if ($existing) {
            throw new InvalidArgumentException('Monthly challenge already completed');
        }

        return DB::transaction(function () use ($user, $challengeData) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_MONTHLY_CHALLENGE);

            $activity = CommunityActivity::create([
                'user_id' => $user->id,
                'activity_type' => CommunityActivity::TYPE_MONTHLY_CHALLENGE,
                'points_earned' => $points,
                'metadata' => array_merge($challengeData, [
                    'month' => Carbon::now()->format('Y-m'),
                ]),
            ]);

            $this->awardPoints($user, $activity);

            return $activity;
        });
    }

    /**
     * Award points and recalculate OPRS
     */
    private function awardPoints(User $user, CommunityActivity $activity): void
    {
        $newScore = $user->community_score + $activity->points_earned;

        $user->update([
            'community_score' => $newScore,
        ]);

        $this->oprsService->recalculateAfterActivity($user, $activity->id);
    }

    /**
     * Check if user can check in today at stadium
     */
    public function canCheckInToday(User $user, int $stadiumId): bool
    {
        $today = Carbon::today();

        return !CommunityActivity::where('user_id', $user->id)
            ->where('activity_type', CommunityActivity::TYPE_CHECK_IN)
            ->where('reference_id', $stadiumId)
            ->where('reference_type', Stadium::class)
            ->whereDate('created_at', $today)
            ->exists();
    }

    /**
     * Get user's activity history
     *
     * @return Collection<int, CommunityActivity>
     */
    public function getActivityHistory(User $user, int $limit = 50): Collection
    {
        return $user->communityActivities()
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get activity statistics
     *
     * @return array{total_points: float, by_type: array, recent_count: int}
     */
    public function getActivityStats(User $user): array
    {
        $byType = $user->communityActivities()
            ->selectRaw('activity_type, COUNT(*) as count, SUM(points_earned) as points')
            ->groupBy('activity_type')
            ->get()
            ->mapWithKeys(fn($row) => [
                $row->activity_type => [
                    'count' => $row->count,
                    'points' => (float) $row->points,
                    'info' => CommunityActivity::getActivityInfo($row->activity_type),
                ],
            ])
            ->toArray();

        $recentCount = $user->communityActivities()
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        return [
            'total_points' => $user->community_score,
            'by_type' => $byType,
            'recent_count' => $recentCount,
        ];
    }

    /**
     * Process weekly bonuses for all eligible users (scheduled job)
     */
    public function processWeeklyBonuses(): int
    {
        $count = 0;
        $startOfWeek = Carbon::now()->startOfWeek();

        // Find users with 5+ matches this week who haven't got bonus
        $eligibleUsers = User::whereHas('ocrMatchesAsChallenger', function ($q) use ($startOfWeek) {
            $q->where('status', OcrMatch::STATUS_CONFIRMED)
              ->where('confirmed_at', '>=', $startOfWeek);
        })
        ->orWhereHas('ocrMatchesAsOpponent', function ($q) use ($startOfWeek) {
            $q->where('status', OcrMatch::STATUS_CONFIRMED)
              ->where('confirmed_at', '>=', $startOfWeek);
        })
        ->get();

        foreach ($eligibleUsers as $user) {
            try {
                $result = $this->checkWeeklyMatchBonus($user);
                if ($result) {
                    $count++;
                }
            } catch (\Exception $e) {
                // Log and continue
                \Log::warning('Weekly bonus failed for user ' . $user->id, [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }
}
```

### Step 2: Create CommunityController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Social;
use App\Models\Stadium;
use App\Models\User;
use App\Services\CommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function __construct(
        private CommunityService $communityService
    ) {}

    /**
     * Check in at stadium
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'stadium_id' => 'required|exists:stadiums,id',
        ]);

        $user = $request->user();
        $stadium = Stadium::findOrFail($request->stadium_id);

        try {
            $activity = $this->communityService->checkIn($user, $stadium);

            return response()->json([
                'success' => true,
                'data' => [
                    'activity' => $activity,
                    'new_community_score' => $user->fresh()->community_score,
                ],
                'message' => 'Check-in successful! +' . $activity->points_earned . ' points',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Record referral
     */
    public function referral(Request $request): JsonResponse
    {
        $request->validate([
            'referred_user_id' => 'required|exists:users,id',
        ]);

        $referrer = $request->user();
        $referredUser = User::findOrFail($request->referred_user_id);

        if ($referrer->id === $referredUser->id) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot refer yourself',
            ], 422);
        }

        try {
            $activity = $this->communityService->recordReferral($referrer, $referredUser);

            return response()->json([
                'success' => true,
                'data' => [
                    'activity' => $activity,
                    'new_community_score' => $referrer->fresh()->community_score,
                ],
                'message' => 'Referral recorded! +' . $activity->points_earned . ' points',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get activity history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $history = $this->communityService->getActivityHistory($user);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get activity stats
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->communityService->getActivityStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
```

### Step 3: Create Weekly Bonus Command

```php
<?php

namespace App\Console\Commands;

use App\Services\CommunityService;
use Illuminate\Console\Command;

class ProcessWeeklyBonusCommand extends Command
{
    protected $signature = 'oprs:weekly-bonus';
    protected $description = 'Process weekly match bonus for eligible users';

    public function handle(CommunityService $communityService): int
    {
        $this->info('Processing weekly match bonuses...');

        $count = $communityService->processWeeklyBonuses();

        $this->info("Awarded weekly bonus to {$count} users");

        return 0;
    }
}
```

### Step 4: Add API Routes

```php
// In routes/api.php:

Route::prefix('community')->middleware('auth:sanctum')->group(function () {
    Route::post('check-in', [CommunityController::class, 'checkIn']);
    Route::post('referral', [CommunityController::class, 'referral']);
    Route::get('history', [CommunityController::class, 'history']);
    Route::get('stats', [CommunityController::class, 'stats']);
});
```

### Step 5: Schedule Weekly Job

```php
// In app/Console/Kernel.php schedule():

$schedule->command('oprs:weekly-bonus')
    ->weeklyOn(0, '23:00') // Sunday 11 PM
    ->withoutOverlapping();
```

## Todo List

- [ ] Create CommunityService with all activity types
- [ ] Implement check-in with daily limit
- [ ] Implement referral with validation
- [ ] Implement weekly match bonus check
- [ ] Create CommunityController API
- [ ] Create ProcessWeeklyBonusCommand
- [ ] Add API routes
- [ ] Schedule weekly bonus job
- [ ] Test all activity types
- [ ] Test OPRS recalculation

## Success Criteria

1. Check-in works with daily limit per stadium
2. Event participation recorded with event reference
3. Referral validates new user (< 7 days)
4. Weekly bonus auto-awards for 5+ matches
5. Monthly challenge respects frequency limit
6. OPRS updates after each activity

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Check-in abuse | Medium | Daily limit per location |
| Fake referrals | High | Validate new user timing |
| Weekly job timing | Low | Run at end of week |

## Security Considerations

- Location validation for check-ins
- Referral self-referral prevention
- Rate limiting on all endpoints
- Activity audit trail

## Next Steps

After community system complete:
1. Proceed to [Phase 6: API Endpoints](./phase-06-api-endpoints.md)
2. Consolidate OPRS-related endpoints
3. Add user profile OPRS data
