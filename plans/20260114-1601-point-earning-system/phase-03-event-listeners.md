# Phase 3: Event Listeners

**Parent**: [plan.md](./plan.md)
**Date**: 2026-01-14 | **Priority**: High | **Status**: COMPLETED ✅ | **Completion**: 2026-01-14

## Context

- Depends on: [Phase 2: Services Core](./phase-02-services-core.md)
- Blocks: None (can parallel with Phase 4-6)
- Related: [Wallet Research](./research/researcher-01-wallet-events.md)

## Overview

Create 9 event listeners for auto-awarding points plus a scheduled command for weekly match bonus. Hook into existing events where available, create new events where needed.

## Key Insights

1. Existing events: OcrMatchConfirmed (dispatch exists but no listeners)
2. Most events need to be created (ClubJoined, StadiumUpdated, etc.)
3. Use queued listeners for non-blocking point awards
4. Referral: Award when referred user completes Skill Quiz (not registration)

---

## Requirements

### Event Listeners (9 total)

| Listener | Event | Task Code | When Triggered |
|----------|-------|-----------|----------------|
| AwardReferralPoints | SkillQuizCompleted | referral | Referred user completes quiz |
| AwardClubJoinPoints | ClubMemberAdded | join_club | User joins first club |
| AwardOcrMatchPoints | OcrMatchConfirmed | create_ocr_match | Match confirmed (challenger only) |
| AwardStadiumUpdatePoints | StadiumUpdated | update_stadium_info | Stadium info updated |
| AwardSocialCreatePoints | SocialCreated | create_social_schedule | Social schedule created |
| AwardTournamentCreatePoints | TournamentCreated | create_tournament | Tournament created |
| AwardRefereeScoringPoints | MatchScored | referee_score_match | Referee scores match |
| AwardExpertVerifyPoints | EloVerified | expert_verify_elo | Expert verifies ELO |
| AwardEventCheckinPoints | EventCheckedIn | join_event | User checks in via QR (uses new Event model) |

### Scheduled Command

| Command | Schedule | Description |
|---------|----------|-------------|
| points:check-weekly | Daily 00:05 | Check and award weekly_5_matches |

---

## Architecture

```
app/Events/
├── OcrMatchConfirmed.php (exists)
├── SkillQuizCompleted.php (create)
├── ClubMemberAdded.php (create)
├── StadiumUpdated.php (create)
├── SocialCreated.php (create)
├── TournamentCreated.php (create)
├── MatchScored.php (create)
├── EloVerified.php (create)
└── EventCheckedIn.php (create - uses NEW Event model, not Social)

app/Listeners/Points/
├── AwardReferralPoints.php
├── AwardClubJoinPoints.php
├── AwardOcrMatchPoints.php
├── AwardStadiumUpdatePoints.php
├── AwardSocialCreatePoints.php
├── AwardTournamentCreatePoints.php
├── AwardRefereeScoringPoints.php
├── AwardExpertVerifyPoints.php
└── AwardEventCheckinPoints.php

app/Console/Commands/
└── CheckWeeklyMatchBonusCommand.php
```

---

## Related Code Files

**Existing Events**:
- `app/Events/OcrMatchConfirmed.php` - Use as reference pattern

**Services to Hook**:
- `app/Services/SkillQuizService.php` - Dispatch SkillQuizCompleted
- `app/Services/OprVerificationService.php` - Dispatch EloVerified in approve()

**Controllers to Hook**:
- `app/Http/Controllers/Front/ClubController.php` - Dispatch ClubMemberAdded
- `app/Http/Controllers/HomeYard/StadiumController.php` - Dispatch StadiumUpdated
- `app/Http/Controllers/HomeYard/SocialController.php` - Dispatch SocialCreated
- `app/Http/Controllers/HomeYard/TournamentController.php` - Dispatch TournamentCreated
- `app/Http/Controllers/Referee/MatchController.php` - Dispatch MatchScored

---

## Implementation Steps

### Step 1: Create New Events

**File**: `app/Events/SkillQuizCompleted.php`

```php
<?php

namespace App\Events;

use App\Models\SkillQuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SkillQuizCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public SkillQuizAttempt $attempt
    ) {}
}
```

**File**: `app/Events/ClubMemberAdded.php`

```php
<?php

namespace App\Events;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClubMemberAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Club $club
    ) {}
}
```

**File**: `app/Events/StadiumUpdated.php`

```php
<?php

namespace App\Events;

use App\Models\Stadium;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StadiumUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Stadium $stadium,
        public User $owner
    ) {}
}
```

**File**: `app/Events/SocialCreated.php`

```php
<?php

namespace App\Events;

use App\Models\Social;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SocialCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Social $social,
        public User $creator
    ) {}
}
```

**File**: `app/Events/TournamentCreated.php`

```php
<?php

namespace App\Events;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TournamentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Tournament $tournament,
        public User $creator
    ) {}
}
```

**File**: `app/Events/MatchScored.php`

```php
<?php

namespace App\Events;

use App\Models\MatchModel;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchScored
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public MatchModel $match,
        public User $referee
    ) {}
}
```

**File**: `app/Events/EloVerified.php`

```php
<?php

namespace App\Events;

use App\Models\OprVerificationRequest;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EloVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public OprVerificationRequest $request,
        public User $verifier,
        public User $verifiedUser
    ) {}
}
```

**File**: `app/Events/EventCheckedIn.php`

```php
<?php

namespace App\Events;

use App\Models\Event;  // NEW Event model, NOT Social
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventCheckedIn
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Event $event  // Changed from Social to Event
    ) {}
}
```

### Step 2: Create Listeners

**File**: `app/Listeners/Points/AwardReferralPoints.php`

```php
<?php

namespace App\Listeners\Points;

use App\Events\SkillQuizCompleted;
use App\Models\PointTask;
use App\Models\Referral;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardReferralPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(SkillQuizCompleted $event): void
    {
        $user = $event->user;

        // Find referral where this user was referred
        $referral = Referral::where('referred_user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$referral) {
            return;
        }

        // Award points to referrer
        $referrer = $referral->referrer;
        if ($referrer) {
            $awarded = $this->pointEarningService->awardPoints(
                $referrer,
                PointTask::CODE_REFERRAL,
                ['referred_user_id' => $user->id]
            );

            if ($awarded) {
                // Mark referral as completed
                $referral->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
        }
    }
}
```

**File**: `app/Listeners/Points/AwardClubJoinPoints.php`

```php
<?php

namespace App\Listeners\Points;

use App\Events\ClubMemberAdded;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardClubJoinPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(ClubMemberAdded $event): void
    {
        // Only award once (first club join)
        $this->pointEarningService->awardPoints(
            $event->user,
            PointTask::CODE_JOIN_CLUB,
            ['club_id' => $event->club->id]
        );
    }
}
```

**File**: `app/Listeners/Points/AwardOcrMatchPoints.php`

```php
<?php

namespace App\Listeners\Points;

use App\Events\OcrMatchConfirmed;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardOcrMatchPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(OcrMatchConfirmed $event): void
    {
        $match = $event->match;

        // Award to challenger (match creator)
        $challenger = $match->challenger;
        if ($challenger) {
            $this->pointEarningService->awardPoints(
                $challenger,
                PointTask::CODE_CREATE_OCR_MATCH,
                ['match_id' => $match->id]
            );
        }
    }
}
```

**File**: `app/Listeners/Points/AwardStadiumUpdatePoints.php`

```php
<?php

namespace App\Listeners\Points;

use App\Events\StadiumUpdated;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardStadiumUpdatePoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(StadiumUpdated $event): void
    {
        // Award once per stadium
        $this->pointEarningService->awardPoints(
            $event->owner,
            PointTask::CODE_UPDATE_STADIUM_INFO,
            ['stadium_id' => $event->stadium->id]
        );
    }
}
```

**File**: `app/Listeners/Points/AwardSocialCreatePoints.php`

```php
<?php

namespace App\Listeners\Points;

use App\Events\SocialCreated;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardSocialCreatePoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(SocialCreated $event): void
    {
        // Award once per stadium (social is linked to stadium)
        $stadiumId = $event->social->stadium_id;

        $this->pointEarningService->awardPoints(
            $event->creator,
            PointTask::CODE_CREATE_SOCIAL_SCHEDULE,
            ['stadium_id' => $stadiumId, 'social_id' => $event->social->id]
        );
    }
}
```

**File**: `app/Listeners/Points/AwardTournamentCreatePoints.php`

```php
<?php

namespace App\Listeners\Points;

use App\Events\TournamentCreated;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardTournamentCreatePoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(TournamentCreated $event): void
    {
        // Award once per stadium
        $stadiumId = $event->tournament->stadium_id;

        $this->pointEarningService->awardPoints(
            $event->creator,
            PointTask::CODE_CREATE_TOURNAMENT,
            ['stadium_id' => $stadiumId, 'tournament_id' => $event->tournament->id]
        );
    }
}
```

**File**: `app/Listeners/Points/AwardRefereeScoringPoints.php`

```php
<?php

namespace App\Listeners\Points;

use App\Events\MatchScored;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardRefereeScoringPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(MatchScored $event): void
    {
        $this->pointEarningService->awardPoints(
            $event->referee,
            PointTask::CODE_REFEREE_SCORE_MATCH,
            ['match_id' => $event->match->id]
        );
    }
}
```

**File**: `app/Listeners/Points/AwardExpertVerifyPoints.php`

```php
<?php

namespace App\Listeners\Points;

use App\Events\EloVerified;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardExpertVerifyPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(EloVerified $event): void
    {
        $this->pointEarningService->awardPoints(
            $event->verifier,
            PointTask::CODE_EXPERT_VERIFY_ELO,
            [
                'verification_request_id' => $event->request->id,
                'verified_user_id' => $event->verifiedUser->id,
            ]
        );
    }
}
```

**File**: `app/Listeners/Points/AwardEventCheckinPoints.php`

```php
<?php

namespace App\Listeners\Points;

use App\Events\EventCheckedIn;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardEventCheckinPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(EventCheckedIn $event): void
    {
        $this->pointEarningService->awardPoints(
            $event->user,
            PointTask::CODE_JOIN_EVENT,
            ['event_id' => $event->event->id]
        );
    }
}
```

### Step 3: Create Weekly Scheduler Command

**File**: `app/Console/Commands/CheckWeeklyMatchBonusCommand.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\OcrMatch;
use App\Models\PointTask;
use App\Models\User;
use App\Models\UserPointTransaction;
use App\Services\PointEarningService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckWeeklyMatchBonusCommand extends Command
{
    protected $signature = 'points:check-weekly';
    protected $description = 'Check and award weekly 5-match bonus points';

    public function __construct(
        private PointEarningService $pointEarningService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $count = 0;

        // Find users with 5+ confirmed matches this week
        $eligibleUserIds = OcrMatch::where('status', OcrMatch::STATUS_CONFIRMED)
            ->where('confirmed_at', '>=', $startOfWeek)
            ->selectRaw('challenger_id as user_id')
            ->union(
                OcrMatch::where('status', OcrMatch::STATUS_CONFIRMED)
                    ->where('confirmed_at', '>=', $startOfWeek)
                    ->selectRaw('opponent_id as user_id')
            )
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) >= 5')
            ->pluck('user_id');

        foreach ($eligibleUserIds as $userId) {
            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            // Check if already awarded this week
            $alreadyAwarded = UserPointTransaction::where('user_id', $userId)
                ->where('metadata->task_code', PointTask::CODE_WEEKLY_5_MATCHES)
                ->where('created_at', '>=', $startOfWeek)
                ->exists();

            if ($alreadyAwarded) {
                continue;
            }

            try {
                $matchCount = OcrMatch::forUser($userId)
                    ->where('status', OcrMatch::STATUS_CONFIRMED)
                    ->where('confirmed_at', '>=', $startOfWeek)
                    ->count();

                $awarded = $this->pointEarningService->awardPoints(
                    $user,
                    PointTask::CODE_WEEKLY_5_MATCHES,
                    [
                        'matches_played' => $matchCount,
                        'week_start' => $startOfWeek->format('Y-m-d'),
                    ]
                );

                if ($awarded) {
                    $count++;
                    $this->info("Awarded weekly bonus to user #{$userId} ({$matchCount} matches)");
                }
            } catch (\Exception $e) {
                Log::warning("Weekly bonus failed for user {$userId}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Awarded weekly bonus to {$count} users");
        return self::SUCCESS;
    }
}
```

### Step 4: Register in EventServiceProvider

**File**: `app/Providers/EventServiceProvider.php` (modify)

```php
protected $listen = [
    // ... existing listeners ...

    // Point Earning Listeners
    \App\Events\SkillQuizCompleted::class => [
        \App\Listeners\Points\AwardReferralPoints::class,
    ],
    \App\Events\ClubMemberAdded::class => [
        \App\Listeners\Points\AwardClubJoinPoints::class,
    ],
    \App\Events\OcrMatchConfirmed::class => [
        \App\Listeners\Points\AwardOcrMatchPoints::class,
    ],
    \App\Events\StadiumUpdated::class => [
        \App\Listeners\Points\AwardStadiumUpdatePoints::class,
    ],
    \App\Events\SocialCreated::class => [
        \App\Listeners\Points\AwardSocialCreatePoints::class,
    ],
    \App\Events\TournamentCreated::class => [
        \App\Listeners\Points\AwardTournamentCreatePoints::class,
    ],
    \App\Events\MatchScored::class => [
        \App\Listeners\Points\AwardRefereeScoringPoints::class,
    ],
    \App\Events\EloVerified::class => [
        \App\Listeners\Points\AwardExpertVerifyPoints::class,
    ],
    \App\Events\EventCheckedIn::class => [
        \App\Listeners\Points\AwardEventCheckinPoints::class,
    ],
];
```

### Step 5: Schedule Weekly Command

**File**: `app/Console/Kernel.php` (modify)

```php
protected function schedule(Schedule $schedule): void
{
    // ... existing schedules ...

    // Point Earning - Weekly Match Bonus
    $schedule->command('points:check-weekly')->dailyAt('00:05');
}
```

### Step 6: Dispatch Events in Controllers/Services

**Add to SkillQuizService after successful quiz submit**:
```php
event(new SkillQuizCompleted($user, $attempt));
```

**Add to ClubController after member added**:
```php
event(new ClubMemberAdded($user, $club));
```

**Add to HomeYard StadiumController after update**:
```php
event(new StadiumUpdated($stadium, auth()->user()));
```

**Add to HomeYard SocialController after create**:
```php
event(new SocialCreated($social, auth()->user()));
```

**Add to HomeYard TournamentController after create**:
```php
event(new TournamentCreated($tournament, auth()->user()));
```

**Add to Referee MatchController after scoring**:
```php
event(new MatchScored($match, auth()->user()));
```

**Add to OprVerificationService approve()**:
```php
event(new EloVerified($request, $verifier, $request->user));
```

### Step 7: Create Event Check-in Controller (NEW)

**File**: `app/Http/Controllers/Front/EventCheckinController.php`

```php
<?php

namespace App\Http\Controllers\Front;

use App\Events\EventCheckedIn;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCheckin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventCheckinController extends Controller
{
    /**
     * Check in via QR code
     */
    public function checkinByQr(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string|max:100',
        ]);

        $user = auth()->user();
        $event = Event::findByQrCode($request->input('qr_code'));

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if (!$event->isOngoing()) {
            return response()->json(['error' => 'Event is not ongoing'], 400);
        }

        if ($event->hasUserCheckedIn($user->id)) {
            return response()->json(['error' => 'Already checked in'], 400);
        }

        if ($event->hasReachedLimit()) {
            return response()->json(['error' => 'Event is full'], 400);
        }

        // Create check-in record
        EventCheckin::checkIn($event, $user, Event::CHECK_IN_QR_CODE);

        // Dispatch event for point earning
        event(new EventCheckedIn($user, $event));

        return response()->json([
            'success' => true,
            'message' => "Checked in to {$event->title}",
            'points' => $event->points,
        ]);
    }
}
```

**Add route to `routes/api.php`**:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('events/checkin', [EventCheckinController::class, 'checkinByQr']);
});
```

---

## Completion Summary

**Status**: COMPLETED - 2026-01-14

### Events Created (8 files)
- `SkillQuizCompleted` - triggers referral points
- `ClubMemberAdded` - triggers club join points
- `OcrMatchConfirmed` - exists, added listener
- `StadiumUpdated` - triggers stadium update points
- `SocialCreated` - triggers social schedule points
- `TournamentCreated` - triggers tournament create points
- `MatchScored` - triggers referee scoring points
- `EloVerified` - triggers expert verification points
- `EventCheckedIn` - triggers event check-in points (uses NEW Event model)

### Listeners Created (9 files in app/Listeners/Points/)
- AwardReferralPoints
- AwardClubJoinPoints
- AwardOcrMatchPoints
- AwardStadiumUpdatePoints
- AwardSocialCreatePoints
- AwardTournamentCreatePoints
- AwardRefereeScoringPoints
- AwardExpertVerifyPoints
- AwardEventCheckinPoints

### Controllers/Services Updated
- SkillQuizService - dispatch SkillQuizCompleted
- ClubController - dispatch ClubMemberAdded
- HomeYardStadiumController - dispatch StadiumUpdated
- SocialController - dispatch SocialCreated
- HomeYardTournamentController - dispatch TournamentCreated
- RefereeController - dispatch MatchScored
- OprVerificationService - dispatch EloVerified

### New Files Created
- EventCheckinController - API for QR check-in
- CheckWeeklyMatchBonusCommand - daily scheduled command

### Routes Added
- GET /api/events - list ongoing events
- POST /api/events/checkin - check-in via QR code
- GET /api/events/checkin/history - user check-in history

### Configurations
- EventServiceProvider - all 9 event-listener mappings registered
- Kernel - points:check-weekly scheduled daily at 00:05

### Validation
- All syntax checks passed
- Event list verified
- Routes verified
- Schedule verified

## Todo

- [x] Create 8 new event classes (EventCheckedIn uses NEW Event model)
- [x] Create 9 listener classes in `app/Listeners/Points/`
- [x] Create `CheckWeeklyMatchBonusCommand`
- [x] Register listeners in EventServiceProvider
- [x] Schedule weekly command in Kernel
- [x] Add event dispatch to SkillQuizService
- [x] Add event dispatch to ClubController
- [x] Add event dispatch to HomeYard StadiumController
- [x] Add event dispatch to HomeYard SocialController
- [x] Add event dispatch to HomeYard TournamentController
- [x] Add event dispatch to Referee MatchController
- [x] Add event dispatch to OprVerificationService
- [x] Create `EventCheckinController` for QR check-in (NEW)
- [x] Add event check-in API route (NEW)
- [x] Test all auto-award flows

---

## Success Criteria

1. All 9 listeners registered and functional
2. Points awarded automatically on each event
3. No duplicate awards (frequency enforced)
4. Weekly scheduler runs correctly
5. Queue workers process listeners

---

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Missing event dispatch | Medium | Medium | Audit all hook points |
| Queue failures | Low | Low | Use ShouldQueue + retries |
| Race conditions | Low | Medium | Service-level deduplication |

---

## Security Considerations

1. Listeners run in queue context - no user session
2. Validate user/model exists before awarding
3. Log all award attempts for audit

---

## Next Steps

After completion, proceed to [Phase 4: Admin Panel](./phase-04-admin-panel.md)
