# Phase 4: Challenge System

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 3: OPRS Service](./phase-03-oprs-service.md)
**Related Docs**: [code-standards.md](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2025-12-05 |
| Description | Implement technical challenge test system |
| Priority | High |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

1. Four challenge types: dinking_rally, drop_shot, serve_accuracy, monthly_test
2. Monthly test limited to 1/month
3. Other challenges unlimited but cumulative points
4. Challenges require verification (admin/staff)
5. Points added to user's challenge_score cumulatively

## Requirements

### Functional
- Submit challenge results
- Verify pass/fail based on thresholds
- Calculate and award points
- Track challenge history
- Monthly test frequency limit
- Admin verification workflow
- Recalculate OPRS after challenge

### Non-Functional
- Validation on score ranges
- Prevent duplicate monthly submissions
- Audit trail for all challenges

## Architecture

### ChallengeService Methods

```
ChallengeService
├── submitChallenge(User, type, score): ChallengeResult
├── verifyChallenge(ChallengeResult, User verifier): void
├── calculatePoints(ChallengeResult): float
├── checkMonthlyLimit(User): bool
├── getChallengeHistory(User): Collection
├── getChallengeStats(User): array
├── getAvailableChallenges(User): array
└── getPendingVerification(): Collection
```

### Challenge Flow

```
User submits challenge score
         │
         ▼
┌─────────────────────┐
│ Validate type/score │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Check monthly limit │ (if monthly_test)
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ Create ChallengeResult │
│ (passed = calculated)  │
└──────────┬──────────┘
           │
           ▼
    ┌──────┴──────┐
    │             │
[Auto-pass]  [Needs verification]
    │             │
    ▼             ▼
┌─────────┐  ┌─────────────┐
│Award pts│  │Wait for     │
│Update   │  │admin verify │
│OPRS     │  └─────────────┘
└─────────┘
```

## Related Code Files

| File | Action | Purpose |
|------|--------|---------|
| `app/Services/ChallengeService.php` | Create | Challenge business logic |
| `app/Http/Controllers/Api/ChallengeController.php` | Create | API endpoints |
| `app/Http/Requests/ChallengeSubmitRequest.php` | Create | Validation |

## Implementation Steps

### Step 1: Create ChallengeService

```php
<?php

namespace App\Services;

use App\Models\ChallengeResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChallengeService
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Submit a challenge result
     */
    public function submitChallenge(User $user, string $type, int $score): ChallengeResult
    {
        // Validate type
        if (!in_array($type, ChallengeResult::getAllTypes())) {
            throw new InvalidArgumentException('Invalid challenge type');
        }

        // Check monthly limit
        if ($type === ChallengeResult::TYPE_MONTHLY_TEST) {
            if (!$this->canSubmitMonthlyTest($user)) {
                throw new InvalidArgumentException('Monthly test already submitted this month');
            }
        }

        return DB::transaction(function () use ($user, $type, $score) {
            // Create challenge result
            $challenge = ChallengeResult::create([
                'user_id' => $user->id,
                'challenge_type' => $type,
                'score' => $score,
                'passed' => false,
                'points_earned' => 0,
            ]);

            // Check if passed
            $challenge->passed = $challenge->checkPassed();
            $challenge->points_earned = $challenge->calculatePoints();
            $challenge->save();

            // If passed, update user score
            if ($challenge->passed) {
                $this->awardPoints($user, $challenge);
            }

            return $challenge;
        });
    }

    /**
     * Verify a challenge (admin action)
     */
    public function verifyChallenge(ChallengeResult $challenge, User $verifier): void
    {
        if ($challenge->verified_at) {
            throw new InvalidArgumentException('Challenge already verified');
        }

        $challenge->update([
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);
    }

    /**
     * Award points to user
     */
    private function awardPoints(User $user, ChallengeResult $challenge): void
    {
        $newScore = $user->challenge_score + $challenge->points_earned;

        $user->update([
            'challenge_score' => $newScore,
        ]);

        // Recalculate OPRS
        $this->oprsService->recalculateAfterChallenge($user, $challenge->id);
    }

    /**
     * Check if user can submit monthly test
     */
    public function canSubmitMonthlyTest(User $user): bool
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        return !ChallengeResult::where('user_id', $user->id)
            ->where('challenge_type', ChallengeResult::TYPE_MONTHLY_TEST)
            ->where('created_at', '>=', $startOfMonth)
            ->exists();
    }

    /**
     * Get user's challenge history
     *
     * @return Collection<int, ChallengeResult>
     */
    public function getChallengeHistory(User $user, int $limit = 50): Collection
    {
        return $user->challengeResults()
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get challenge statistics for user
     *
     * @return array{total: int, passed: int, total_points: float, by_type: array}
     */
    public function getChallengeStats(User $user): array
    {
        $results = $user->challengeResults()
            ->selectRaw('
                challenge_type,
                COUNT(*) as attempts,
                SUM(CASE WHEN passed THEN 1 ELSE 0 END) as passed,
                SUM(points_earned) as points
            ')
            ->groupBy('challenge_type')
            ->get();

        $byType = [];
        $total = 0;
        $passed = 0;
        $totalPoints = 0;

        foreach ($results as $row) {
            $byType[$row->challenge_type] = [
                'attempts' => $row->attempts,
                'passed' => $row->passed,
                'points' => (float) $row->points,
                'info' => ChallengeResult::getChallengeInfo($row->challenge_type),
            ];
            $total += $row->attempts;
            $passed += $row->passed;
            $totalPoints += (float) $row->points;
        }

        return [
            'total' => $total,
            'passed' => $passed,
            'total_points' => $totalPoints,
            'by_type' => $byType,
        ];
    }

    /**
     * Get available challenges for user
     *
     * @return array<string, array{available: bool, reason: string|null, info: array}>
     */
    public function getAvailableChallenges(User $user): array
    {
        $available = [];

        foreach (ChallengeResult::getAllTypes() as $type) {
            $canSubmit = true;
            $reason = null;

            if ($type === ChallengeResult::TYPE_MONTHLY_TEST) {
                $canSubmit = $this->canSubmitMonthlyTest($user);
                if (!$canSubmit) {
                    $reason = 'Already submitted this month';
                }
            }

            $available[$type] = [
                'available' => $canSubmit,
                'reason' => $reason,
                'info' => ChallengeResult::getChallengeInfo($type),
            ];
        }

        return $available;
    }

    /**
     * Get challenges pending verification
     *
     * @return Collection<int, ChallengeResult>
     */
    public function getPendingVerification(int $limit = 50): Collection
    {
        return ChallengeResult::whereNull('verified_at')
            ->with('user')
            ->orderBy('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get all challenge types with info
     *
     * @return array<string, array{name: string, description: string, points: int|string, icon: string}>
     */
    public function getAllChallengeTypes(): array
    {
        $types = [];
        foreach (ChallengeResult::getAllTypes() as $type) {
            $types[$type] = ChallengeResult::getChallengeInfo($type);
        }
        return $types;
    }
}
```

### Step 2: Create ChallengeController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChallengeSubmitRequest;
use App\Services\ChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function __construct(
        private ChallengeService $challengeService
    ) {}

    /**
     * Get available challenge types
     */
    public function types(): JsonResponse
    {
        $types = $this->challengeService->getAllChallengeTypes();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Get user's available challenges
     */
    public function available(Request $request): JsonResponse
    {
        $user = $request->user();
        $available = $this->challengeService->getAvailableChallenges($user);

        return response()->json([
            'success' => true,
            'data' => $available,
        ]);
    }

    /**
     * Submit challenge result
     */
    public function submit(ChallengeSubmitRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $result = $this->challengeService->submitChallenge(
                $user,
                $request->validated('challenge_type'),
                $request->validated('score')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'challenge' => $result,
                    'passed' => $result->passed,
                    'points_earned' => $result->points_earned,
                    'new_challenge_score' => $user->fresh()->challenge_score,
                ],
                'message' => $result->passed
                    ? 'Challenge passed! Points awarded.'
                    : 'Challenge not passed. Try again!',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get user's challenge history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $history = $this->challengeService->getChallengeHistory($user);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get user's challenge stats
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->challengeService->getChallengeStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
```

### Step 3: Create Request Validation

```php
<?php

namespace App\Http\Requests;

use App\Models\ChallengeResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChallengeSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'challenge_type' => [
                'required',
                'string',
                Rule::in(ChallengeResult::getAllTypes()),
            ],
            'score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'challenge_type.in' => 'Invalid challenge type',
            'score.min' => 'Score must be 0 or higher',
            'score.max' => 'Score cannot exceed 100',
        ];
    }
}
```

### Step 4: Add API Routes

```php
// In routes/api.php, add to protected group:

Route::prefix('challenges')->middleware('auth:sanctum')->group(function () {
    Route::get('types', [ChallengeController::class, 'types']);
    Route::get('available', [ChallengeController::class, 'available']);
    Route::post('submit', [ChallengeController::class, 'submit']);
    Route::get('history', [ChallengeController::class, 'history']);
    Route::get('stats', [ChallengeController::class, 'stats']);
});
```

## Todo List

- [ ] Create ChallengeService with submit logic
- [ ] Implement pass/fail checking
- [ ] Implement points calculation
- [ ] Add monthly test limit check
- [ ] Create ChallengeController API
- [ ] Create ChallengeSubmitRequest validation
- [ ] Add API routes
- [ ] Test challenge submission flow
- [ ] Test OPRS recalculation trigger

## Success Criteria

1. All 4 challenge types submittable
2. Pass/fail calculated correctly per thresholds
3. Points awarded per spec (10/8/6/30-50)
4. Monthly test limited to once per month
5. OPRS updates after challenge
6. History and stats available via API

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Score manipulation | High | Server-side validation |
| Monthly limit bypass | Medium | DB check before insert |
| Points calculation error | Medium | Unit tests |

## Security Considerations

- Score validation (0-100 range)
- Rate limiting on submit endpoint
- User can only submit own challenges
- Admin verification for disputes

## Next Steps

After challenge system complete:
1. Proceed to [Phase 5: Community System](./phase-05-community-system.md)
2. Implement community activity tracking
3. Build check-in and referral flows
