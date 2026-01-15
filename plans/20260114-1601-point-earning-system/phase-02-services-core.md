# Phase 2: Services Core

**Parent**: [plan.md](./plan.md)
**Date**: 2026-01-14 | **Priority**: Critical | **Status**: COMPLETED

## Context

- Depends on: [Phase 1: Database & Models](./phase-01-database-models.md)
- Blocks: Phase 3-6
- Related: [Services Research](./research/researcher-02-services.md)

## Overview

Create 3 service classes: PointEarningService (core logic), PointSubmissionService (admin approval flow), and SocialVerificationService (social profile management). Follow existing CommunityService patterns.

## Key Insights

1. Use `UserWallet.addPoints()` directly - already handles transaction audit
2. Follow DB::transaction pattern from CommunityService
3. Metadata JSON for context (match_id, stadium_id, etc.)
4. Frequency checks must handle "once per stadium" for home_yard tasks

---

## Requirements

### PointEarningService

Core service for point earning logic.

| Method | Description |
|--------|-------------|
| `awardPoints(User, taskCode, metadata)` | Award points immediately (no approval) |
| `canEarn(User, taskCode, context)` | Check frequency limits, role, active status |
| `getAvailableTasks(User)` | Get tasks for user's role with eligibility status |
| `getTaskHistory(User, limit)` | Get user's point earning history |

### PointSubmissionService

Handles proof submissions and admin approval workflow.

| Method | Description |
|--------|-------------|
| `submit(User, taskCode, proofData)` | Create pending submission |
| `approve(submission, admin, notes)` | Approve and award points |
| `reject(submission, admin, reason)` | Reject submission |
| `getPendingSubmissions(filters)` | Admin queue |
| `getUserSubmissions(User)` | User's submission history |

### SocialVerificationService

Manages social profile verifications.

| Method | Description |
|--------|-------------|
| `verifyProfile(User, platform, url, admin)` | Create verification record |
| `isProfileVerified(User, platform)` | Check if platform verified |
| `isUrlAvailable(url, excludeUserId)` | Check URL uniqueness |
| `getUserVerifications(User)` | Get user's verified profiles |

---

## Architecture

```
app/Services/
├── PointEarningService.php      # Core award logic
├── PointSubmissionService.php   # Approval workflow
└── SocialVerificationService.php # Social profile management

Service Dependencies:
┌──────────────────────────────┐
│    PointSubmissionService    │
│             │                │
│             ▼                │
│    PointEarningService ◄─────┼──── Event Listeners
│             │                │
│             ▼                │
│    UserWallet.addPoints()    │
└──────────────────────────────┘
```

---

## Related Code Files

**Reference Services**:
- `app/Services/CommunityService.php` - Pattern for DB::transaction, awardPoints
- `app/Services/OprVerificationService.php` - Approval workflow pattern
- `app/Models/UserWallet.php` - addPoints() method

---

## Implementation Steps

### Step 1: Create PointEarningService

**File**: `app/Services/PointEarningService.php`

```php
<?php

namespace App\Services;

use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Models\User;
use App\Models\UserPointTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PointEarningService
{
    /**
     * Award points for a task (auto-award, no approval needed)
     *
     * @throws InvalidArgumentException
     */
    public function awardPoints(User $user, string $taskCode, array $metadata = []): bool
    {
        $task = PointTask::findByCode($taskCode);

        if (!$task || !$task->is_active) {
            throw new InvalidArgumentException("Task not found or inactive: {$taskCode}");
        }

        if ($task->requires_approval) {
            throw new InvalidArgumentException("Task requires approval: {$taskCode}");
        }

        if (!$this->canEarn($user, $taskCode, $metadata)) {
            return false;
        }

        return DB::transaction(function () use ($user, $task, $metadata) {
            // Get or create wallet
            $wallet = $user->wallet ?? $user->wallet()->create(['points' => 0]);

            // Add points via wallet (creates transaction automatically)
            $wallet->addPoints(
                $task->points,
                'earn',
                $task->name,
                array_merge($metadata, [
                    'task_code' => $task->code,
                    'task_id' => $task->id,
                ])
            );

            return true;
        });
    }

    /**
     * Check if user can earn points for this task
     *
     * @param array{stadium_id?: int, match_id?: int, event_id?: int} $context
     */
    public function canEarn(User $user, string $taskCode, array $context = []): bool
    {
        $task = PointTask::findByCode($taskCode);

        if (!$task || !$task->is_active) {
            return false;
        }

        // Check role
        if (!$this->userHasRole($user, $task->role)) {
            return false;
        }

        // Check frequency
        return $this->checkFrequency($user, $task, $context);
    }

    /**
     * Get available tasks for user with eligibility status
     *
     * @return Collection<int, array{task: PointTask, can_earn: bool, reason: string|null}>
     */
    public function getAvailableTasks(User $user): Collection
    {
        // Determine user's primary role for point tasks
        $role = $this->getPrimaryRole($user);
        $tasks = PointTask::getActiveByRole($role);

        return $tasks->map(function ($task) use ($user) {
            $canEarn = $this->canEarn($user, $task->code);
            $reason = $canEarn ? null : $this->getIneligibilityReason($user, $task);

            return [
                'task' => $task,
                'can_earn' => $canEarn,
                'reason' => $reason,
            ];
        });
    }

    /**
     * Get user's point earning history
     */
    public function getTaskHistory(User $user, int $limit = 50): Collection
    {
        return $user->pointTransactions()
            ->where('type', 'earn')
            ->whereNotNull('metadata->task_code')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Check frequency limits
     */
    private function checkFrequency(User $user, PointTask $task, array $context = []): bool
    {
        $query = UserPointTransaction::where('user_id', $user->id)
            ->where('metadata->task_code', $task->code);

        switch ($task->frequency) {
            case PointTask::FREQ_UNLIMITED:
                // Check for duplicate (same context)
                if (!empty($context)) {
                    return !$this->hasDuplicateContext($user, $task, $context);
                }
                return true;

            case PointTask::FREQ_DAILY:
                return !$query->whereDate('created_at', Carbon::today())->exists();

            case PointTask::FREQ_WEEKLY:
                return !$query->where('created_at', '>=', Carbon::now()->startOfWeek())->exists();

            case PointTask::FREQ_MONTHLY:
                return !$query->where('created_at', '>=', Carbon::now()->startOfMonth())->exists();

            case PointTask::FREQ_ONCE:
                // Special handling for home_yard tasks (once per stadium)
                if ($task->role === PointTask::ROLE_HOME_YARD && isset($context['stadium_id'])) {
                    return !$query->where('metadata->stadium_id', $context['stadium_id'])->exists();
                }
                return !$query->exists();

            default:
                return true;
        }
    }

    /**
     * Check for duplicate context (prevent double awards for same action)
     */
    private function hasDuplicateContext(User $user, PointTask $task, array $context): bool
    {
        $query = UserPointTransaction::where('user_id', $user->id)
            ->where('metadata->task_code', $task->code);

        // Check specific context fields
        if (isset($context['match_id'])) {
            return $query->where('metadata->match_id', $context['match_id'])->exists();
        }
        if (isset($context['event_id'])) {
            return $query->where('metadata->event_id', $context['event_id'])->exists();
        }
        if (isset($context['referred_user_id'])) {
            return $query->where('metadata->referred_user_id', $context['referred_user_id'])->exists();
        }
        if (isset($context['verification_request_id'])) {
            return $query->where('metadata->verification_request_id', $context['verification_request_id'])->exists();
        }

        return false;
    }

    /**
     * Get reason why user cannot earn
     */
    private function getIneligibilityReason(User $user, PointTask $task): string
    {
        if (!$this->userHasRole($user, $task->role)) {
            return "Requires {$task->role} role";
        }

        switch ($task->frequency) {
            case PointTask::FREQ_DAILY:
                return 'Already earned today';
            case PointTask::FREQ_WEEKLY:
                return 'Already earned this week';
            case PointTask::FREQ_MONTHLY:
                return 'Already earned this month';
            case PointTask::FREQ_ONCE:
                return 'Already completed';
            default:
                return 'Not eligible';
        }
    }

    /**
     * Check if user has required role
     */
    private function userHasRole(User $user, string $requiredRole): bool
    {
        // All users have 'user' role implicitly
        if ($requiredRole === PointTask::ROLE_USER) {
            return true;
        }

        return $user->hasRole($requiredRole);
    }

    /**
     * Get user's primary role for point tasks
     */
    private function getPrimaryRole(User $user): string
    {
        // Priority: expert_host > referee > home_yard > user
        if ($user->hasRole('expert_host')) {
            return PointTask::ROLE_EXPERT_HOST;
        }
        if ($user->hasRole('referee')) {
            return PointTask::ROLE_REFEREE;
        }
        if ($user->hasRole('home_yard')) {
            return PointTask::ROLE_HOME_YARD;
        }
        return PointTask::ROLE_USER;
    }
}
```

### Step 2: Create PointSubmissionService

**File**: `app/Services/PointSubmissionService.php`

```php
<?php

namespace App\Services;

use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PointSubmissionService
{
    public function __construct(
        private PointEarningService $pointEarningService,
        private SocialVerificationService $socialVerificationService
    ) {}

    /**
     * Submit proof for approval
     *
     * @param array{type: string, paths?: array, url?: string, challenge_id?: int} $proofData
     * @throws InvalidArgumentException
     */
    public function submit(User $user, string $taskCode, array $proofData): PointSubmission
    {
        $task = PointTask::findByCode($taskCode);

        if (!$task || !$task->is_active) {
            throw new InvalidArgumentException("Task not found or inactive: {$taskCode}");
        }

        if (!$task->requires_approval) {
            throw new InvalidArgumentException("Task does not require approval: {$taskCode}");
        }

        // Validate proof type
        $this->validateProofData($task, $proofData);

        // Check for existing pending submission
        $existingPending = PointSubmission::where('user_id', $user->id)
            ->where('point_task_id', $task->id)
            ->where('status', PointSubmission::STATUS_PENDING)
            ->exists();

        if ($existingPending) {
            throw new InvalidArgumentException('You already have a pending submission for this task');
        }

        // Check frequency (for once-only tasks)
        if ($task->frequency === PointTask::FREQ_ONCE) {
            $alreadyApproved = PointSubmission::where('user_id', $user->id)
                ->where('point_task_id', $task->id)
                ->where('status', PointSubmission::STATUS_APPROVED)
                ->exists();

            if ($alreadyApproved) {
                throw new InvalidArgumentException('You have already completed this task');
            }
        }

        return PointSubmission::create([
            'user_id' => $user->id,
            'point_task_id' => $task->id,
            'status' => PointSubmission::STATUS_PENDING,
            'proof_data' => $proofData,
        ]);
    }

    /**
     * Approve submission and award points
     *
     * @throws InvalidArgumentException
     */
    public function approve(PointSubmission $submission, User $admin, ?string $notes = null): void
    {
        if (!$submission->isPending()) {
            throw new InvalidArgumentException('Submission is not pending');
        }

        DB::transaction(function () use ($submission, $admin, $notes) {
            $task = $submission->pointTask;
            $user = $submission->user;

            // Update submission
            $submission->update([
                'status' => PointSubmission::STATUS_APPROVED,
                'admin_id' => $admin->id,
                'admin_notes' => $notes,
                'reviewed_at' => Carbon::now(),
                'points_awarded' => $task->points,
            ]);

            // Award points via wallet
            $wallet = $user->wallet ?? $user->wallet()->create(['points' => 0]);
            $wallet->addPoints(
                $task->points,
                'earn',
                $task->name,
                [
                    'task_code' => $task->code,
                    'task_id' => $task->id,
                    'submission_id' => $submission->id,
                    'approved_by' => $admin->id,
                ]
            );

            // Handle social profile verification
            if ($this->isSocialTask($task->code)) {
                $this->createSocialVerification($submission, $admin);
            }
        });
    }

    /**
     * Reject submission
     *
     * @throws InvalidArgumentException
     */
    public function reject(PointSubmission $submission, User $admin, string $reason): void
    {
        if (!$submission->isPending()) {
            throw new InvalidArgumentException('Submission is not pending');
        }

        if (empty($reason)) {
            throw new InvalidArgumentException('Rejection reason is required');
        }

        $submission->update([
            'status' => PointSubmission::STATUS_REJECTED,
            'admin_id' => $admin->id,
            'admin_notes' => $reason,
            'reviewed_at' => Carbon::now(),
        ]);
    }

    /**
     * Get pending submissions for admin
     *
     * @param array{task_code?: string, user_id?: int} $filters
     */
    public function getPendingSubmissions(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PointSubmission::with(['user', 'pointTask'])
            ->where('status', PointSubmission::STATUS_PENDING)
            ->orderBy('created_at', 'asc');

        if (isset($filters['task_code'])) {
            $query->whereHas('pointTask', fn ($q) => $q->where('code', $filters['task_code']));
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get all submissions for admin (with filters)
     *
     * @param array{status?: string, task_code?: string, user_id?: int} $filters
     */
    public function getSubmissions(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PointSubmission::with(['user', 'pointTask', 'admin'])
            ->orderByDesc('created_at');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['task_code'])) {
            $query->whereHas('pointTask', fn ($q) => $q->where('code', $filters['task_code']));
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get user's submissions
     */
    public function getUserSubmissions(User $user, int $limit = 50): Collection
    {
        return PointSubmission::with('pointTask')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get submission stats for admin dashboard
     */
    public function getStats(): array
    {
        return [
            'pending' => PointSubmission::where('status', PointSubmission::STATUS_PENDING)->count(),
            'approved_today' => PointSubmission::where('status', PointSubmission::STATUS_APPROVED)
                ->whereDate('reviewed_at', Carbon::today())->count(),
            'rejected_today' => PointSubmission::where('status', PointSubmission::STATUS_REJECTED)
                ->whereDate('reviewed_at', Carbon::today())->count(),
        ];
    }

    /**
     * Validate proof data matches task requirements
     */
    private function validateProofData(PointTask $task, array $proofData): void
    {
        switch ($task->proof_type) {
            case 'image':
                if (empty($proofData['paths']) || !is_array($proofData['paths'])) {
                    throw new InvalidArgumentException('Image proof required');
                }
                break;

            case 'link':
                if (empty($proofData['url']) || !filter_var($proofData['url'], FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException('Valid URL required');
                }
                break;

            case 'qr_code':
                if (empty($proofData['qr_data'])) {
                    throw new InvalidArgumentException('QR code data required');
                }
                break;
        }
    }

    /**
     * Check if task is a social verification task
     */
    private function isSocialTask(string $taskCode): bool
    {
        return in_array($taskCode, [
            PointTask::CODE_JOIN_FB_GROUP,
            PointTask::CODE_FOLLOW_FB_PAGE,
            PointTask::CODE_SUBSCRIBE_YOUTUBE,
            PointTask::CODE_FOLLOW_TIKTOK,
        ]);
    }

    /**
     * Create social profile verification after approval
     */
    private function createSocialVerification(PointSubmission $submission, User $admin): void
    {
        $taskCode = $submission->pointTask->code;
        $url = $submission->proof_data['url'] ?? null;

        if (!$url) {
            return;
        }

        $platform = match ($taskCode) {
            PointTask::CODE_JOIN_FB_GROUP, PointTask::CODE_FOLLOW_FB_PAGE => 'facebook',
            PointTask::CODE_SUBSCRIBE_YOUTUBE => 'youtube',
            PointTask::CODE_FOLLOW_TIKTOK => 'tiktok',
            default => null,
        };

        if ($platform) {
            $this->socialVerificationService->verifyProfile(
                $submission->user,
                $platform,
                $url,
                $admin
            );
        }
    }
}
```

### Step 3: Create SocialVerificationService

**File**: `app/Services/SocialVerificationService.php`

```php
<?php

namespace App\Services;

use App\Models\SocialProfileVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SocialVerificationService
{
    /**
     * Create or update social profile verification
     *
     * @throws InvalidArgumentException
     */
    public function verifyProfile(User $user, string $platform, string $url, User $admin): SocialProfileVerification
    {
        // Validate platform
        if (!in_array($platform, ['facebook', 'youtube', 'tiktok'])) {
            throw new InvalidArgumentException("Invalid platform: {$platform}");
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid URL format');
        }

        // Check if URL is already taken by another user
        if (SocialProfileVerification::isProfileUrlTaken($url, $user->id)) {
            throw new InvalidArgumentException('This profile URL is already verified by another user');
        }

        // Check if user already has this platform verified
        $existing = SocialProfileVerification::where('user_id', $user->id)
            ->where('platform', $platform)
            ->first();

        if ($existing) {
            // Update existing verification
            $existing->update([
                'profile_url' => $url,
                'verified_at' => Carbon::now(),
                'verified_by' => $admin->id,
            ]);
            return $existing;
        }

        // Create new verification
        return SocialProfileVerification::create([
            'user_id' => $user->id,
            'platform' => $platform,
            'profile_url' => $url,
            'verified_at' => Carbon::now(),
            'verified_by' => $admin->id,
        ]);
    }

    /**
     * Check if user has verified a platform
     */
    public function isProfileVerified(User $user, string $platform): bool
    {
        return SocialProfileVerification::hasVerifiedPlatform($user->id, $platform);
    }

    /**
     * Check if URL is available (not taken)
     */
    public function isUrlAvailable(string $url, ?int $excludeUserId = null): bool
    {
        return !SocialProfileVerification::isProfileUrlTaken($url, $excludeUserId);
    }

    /**
     * Get user's verified social profiles
     */
    public function getUserVerifications(User $user): Collection
    {
        return SocialProfileVerification::where('user_id', $user->id)->get();
    }

    /**
     * Get verification by platform for user
     */
    public function getVerification(User $user, string $platform): ?SocialProfileVerification
    {
        return SocialProfileVerification::where('user_id', $user->id)
            ->where('platform', $platform)
            ->first();
    }

    /**
     * Get all verified platforms for user
     *
     * @return array<string, bool>
     */
    public function getVerificationStatus(User $user): array
    {
        $verifications = $this->getUserVerifications($user)->keyBy('platform');

        return [
            'facebook' => $verifications->has('facebook'),
            'youtube' => $verifications->has('youtube'),
            'tiktok' => $verifications->has('tiktok'),
        ];
    }
}
```

### Step 4: Register Services in AppServiceProvider

**File**: `app/Providers/AppServiceProvider.php` (modify)

```php
// Add to register() method:
$this->app->singleton(PointEarningService::class);
$this->app->singleton(SocialVerificationService::class);
$this->app->singleton(PointSubmissionService::class, function ($app) {
    return new PointSubmissionService(
        $app->make(PointEarningService::class),
        $app->make(SocialVerificationService::class)
    );
});
```

---

## Todo

- [x] Create `PointEarningService` with awardPoints, canEarn methods
- [x] Create `PointSubmissionService` with submit, approve, reject methods
- [x] Create `SocialVerificationService` with verifyProfile method
- [x] Register services in AppServiceProvider
- [x] Test frequency checks (daily, weekly, monthly, once)
- [x] Test "once per stadium" logic for home_yard tasks
- [x] Test social verification on approval

## Completion Notes

**Completed**: 2026-01-14

**Files Created**:
- `app/Services/PointEarningService.php`
- `app/Services/PointSubmissionService.php`
- `app/Services/SocialVerificationService.php`

**Files Modified**:
- `app/Providers/AppServiceProvider.php` - Registered all 3 services as singletons

**Key Features Implemented**:
- `PointEarningService`: awardPoints(), canEarn(), getAvailableTasks(), getAllAvailableTasks(), getTaskHistory(), getEarningSummary()
- `PointSubmissionService`: submit(), approve(), reject(), getPendingSubmissions(), getSubmissions(), getUserSubmissions(), getStats(), getStatsByTask()
- `SocialVerificationService`: verifyProfile(), isProfileVerified(), isUrlAvailable(), getUserVerifications(), getVerificationStatus()

**Verification**:
- All services registered and injectable via DI
- Services tested via artisan tinker

---

## Success Criteria

1. PointEarningService correctly awards points via UserWallet
2. Frequency limits enforced for all task types
3. "Once per stadium" works for home_yard tasks
4. Approval flow creates transaction audit
5. Social verification auto-created on social task approval
6. Duplicate context detection prevents double awards

---

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Race conditions | Low | Medium | DB::transaction wrapping |
| Metadata query failures | Medium | Low | Index JSON columns in MySQL 8+ |

---

## Security Considerations

1. Validate proof_type matches task requirement
2. URL validation for social links
3. Admin permission check in approval methods
4. Prevent self-approval (optional, depends on requirement)

---

## Next Steps

After completion, proceed to [Phase 3: Event Listeners](./phase-03-event-listeners.md)
