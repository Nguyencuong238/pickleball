# Phase 6: API Integration

**Parent**: [plan.md](./plan.md)
**Date**: 2026-01-14 | **Priority**: Medium | **Status**: COMPLETED ✅

## Context

- Depends on: [Phase 2: Services Core](./phase-02-services-core.md)
- Blocks: None
- Related: [API Routes](../routes/api.php)

## Overview

Create REST API endpoints for mobile app integration. Mirror web functionality: task list, proof submission, history, and wallet balance.

## Key Insights

1. Follow existing API controller patterns (ApiController base, JsonResponse)
2. Use Laravel Sanctum for authentication (existing)
3. Image upload via base64 or multipart/form-data
4. Consistent response format with error codes

---

## Requirements

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/points/tasks | Get available tasks with eligibility |
| GET | /api/points/balance | Get wallet balance |
| GET | /api/points/history | Get transaction history |
| GET | /api/points/submissions | Get user submissions |
| POST | /api/points/submissions | Submit proof for approval |
| GET | /api/points/challenges | Get active special challenges |

### Response Format

```json
{
    "success": true,
    "data": { ... },
    "message": "Success message"
}

// Error
{
    "success": false,
    "message": "Error message",
    "errors": { ... }
}
```

---

## Architecture

```
app/Http/Controllers/Api/
└── PointController.php

app/Http/Resources/
├── PointTaskResource.php
├── PointSubmissionResource.php
├── PointTransactionResource.php
└── SpecialChallengeResource.php
```

---

## Related Code Files

**Reference API Controllers**:
- `app/Http/Controllers/Api/OcrController.php` - API controller pattern
- `app/Http/Controllers/Api/OprsController.php` - Resource responses

---

## Implementation Steps

### Step 1: Create API Resources

**File**: `app/Http/Resources/PointTaskResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PointTaskResource extends JsonResource
{
    private bool $canEarn;
    private ?string $reason;

    public function setEligibility(bool $canEarn, ?string $reason): self
    {
        $this->canEarn = $canEarn;
        $this->reason = $reason;
        return $this;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'points' => $this->points,
            'role' => $this->role,
            'category' => $this->category,
            'frequency' => $this->frequency,
            'requires_approval' => $this->requires_approval,
            'proof_type' => $this->proof_type,
            'is_active' => $this->is_active,
            'can_earn' => $this->canEarn ?? true,
            'ineligibility_reason' => $this->reason ?? null,
        ];
    }
}
```

**File**: `app/Http/Resources/PointSubmissionResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PointSubmissionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'task' => [
                'code' => $this->pointTask->code,
                'name' => $this->pointTask->name,
                'points' => $this->pointTask->points,
            ],
            'status' => $this->status,
            'proof_data' => $this->proof_data,
            'points_awarded' => $this->points_awarded,
            'admin_notes' => $this->admin_notes,
            'submitted_at' => $this->created_at->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
        ];
    }
}
```

**File**: `app/Http/Resources/PointTransactionResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PointTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'points' => $this->points,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'description' => $this->description,
            'metadata' => $this->metadata,
            'is_positive' => $this->isPositive(),
            'formatted_points' => $this->getFormattedPoints(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

**File**: `app/Http/Resources/SpecialChallengeResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpecialChallengeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'points' => $this->points,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'is_ongoing' => $this->isOngoing(),
            'participant_count' => $this->getParticipantCount(),
            'max_participants' => $this->max_participants,
            'has_reached_limit' => $this->hasReachedLimit(),
        ];
    }
}
```

### Step 2: Create API Controller

**File**: `app/Http/Controllers/Api/PointController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PointSubmissionResource;
use App\Http\Resources\PointTaskResource;
use App\Http\Resources\PointTransactionResource;
use App\Http\Resources\SpecialChallengeResource;
use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Models\SpecialChallenge;
use App\Services\PointEarningService;
use App\Services\PointSubmissionService;
use App\Services\SocialVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PointController extends Controller
{
    public function __construct(
        private PointEarningService $pointEarningService,
        private PointSubmissionService $submissionService,
        private SocialVerificationService $socialVerificationService
    ) {}

    /**
     * Get available tasks with eligibility
     */
    public function tasks(): JsonResponse
    {
        $user = auth()->user();
        $tasks = $this->pointEarningService->getAvailableTasks($user);

        $resources = $tasks->map(function ($item) {
            return (new PointTaskResource($item['task']))
                ->setEligibility($item['can_earn'], $item['reason']);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tasks' => $resources,
                'social_status' => $this->socialVerificationService->getVerificationStatus($user),
            ],
        ]);
    }

    /**
     * Get wallet balance
     */
    public function balance(): JsonResponse
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet ? $wallet->points : 0,
                'formatted_balance' => $wallet ? $wallet->getFormattedPoints() : '0',
            ],
        ]);
    }

    /**
     * Get transaction history
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = min($request->input('per_page', 20), 50);

        $transactions = $user->pointTransactions()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => PointTransactionResource::collection($transactions),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ],
        ]);
    }

    /**
     * Get user submissions
     */
    public function submissions(Request $request): JsonResponse
    {
        $user = auth()->user();
        $status = $request->input('status');

        $query = PointSubmission::with('pointTask')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $submissions = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'submissions' => PointSubmissionResource::collection($submissions),
                'pending_count' => PointSubmission::where('user_id', $user->id)
                    ->where('status', PointSubmission::STATUS_PENDING)
                    ->count(),
                'pagination' => [
                    'current_page' => $submissions->currentPage(),
                    'last_page' => $submissions->lastPage(),
                    'per_page' => $submissions->perPage(),
                    'total' => $submissions->total(),
                ],
            ],
        ]);
    }

    /**
     * Submit proof for approval
     */
    public function submit(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'task_code' => 'required|string|exists:point_tasks,code',
        ]);

        $task = PointTask::findByCode($request->input('task_code'));

        if (!$task || !$task->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or inactive',
            ], 404);
        }

        if (!$task->requires_approval) {
            return response()->json([
                'success' => false,
                'message' => 'This task does not require manual submission',
            ], 400);
        }

        // Validate proof data based on type
        $proofRules = $this->getProofValidationRules($task);
        $request->validate($proofRules);

        try {
            // Build proof data
            $proofData = $this->buildProofData($request, $task);

            // Create submission
            $submission = $this->submissionService->submit($user, $task->code, $proofData);

            return response()->json([
                'success' => true,
                'message' => 'Submission created successfully. Pending admin review.',
                'data' => [
                    'submission' => new PointSubmissionResource($submission->load('pointTask')),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get active special challenges
     */
    public function challenges(): JsonResponse
    {
        $challenges = SpecialChallenge::ongoing()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'challenges' => SpecialChallengeResource::collection($challenges),
            ],
        ]);
    }

    /**
     * Get validation rules for proof data
     */
    private function getProofValidationRules(PointTask $task): array
    {
        return match ($task->proof_type) {
            'image' => [
                'images' => 'required|array|min:1|max:5',
                'images.*' => 'required|string', // base64 encoded
            ],
            'link' => [
                'url' => 'required|url|max:500',
            ],
            'qr_code' => [
                'qr_data' => 'required|string|max:255',
            ],
            default => [],
        };
    }

    /**
     * Build proof data from request
     */
    private function buildProofData(Request $request, PointTask $task): array
    {
        $proofData = ['type' => $task->proof_type];

        switch ($task->proof_type) {
            case 'image':
                $paths = [];
                foreach ($request->input('images') as $index => $base64) {
                    // Decode and save base64 image
                    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
                    $filename = uniqid('proof_') . '.jpg';
                    $path = 'point-submissions/' . auth()->id() . '/' . $filename;
                    Storage::disk('public')->put($path, $imageData);
                    $paths[] = $path;
                }
                $proofData['paths'] = $paths;
                break;

            case 'link':
                $proofData['url'] = $request->input('url');
                break;

            case 'qr_code':
                $proofData['qr_data'] = $request->input('qr_data');
                break;
        }

        // Special challenge ID
        if ($task->code === PointTask::CODE_SPECIAL_CHALLENGE && $request->filled('challenge_id')) {
            $proofData['challenge_id'] = $request->input('challenge_id');
        }

        return $proofData;
    }
}
```

### Step 3: Add API Routes

**File**: `routes/api.php` (add to auth:sanctum group)

```php
// Point Earning System API
Route::middleware('auth:sanctum')->prefix('points')->group(function () {
    Route::get('tasks', [PointController::class, 'tasks']);
    Route::get('balance', [PointController::class, 'balance']);
    Route::get('history', [PointController::class, 'history']);
    Route::get('submissions', [PointController::class, 'submissions']);
    Route::post('submissions', [PointController::class, 'submit']);
    Route::get('challenges', [PointController::class, 'challenges']);
});
```

### Step 4: API Documentation

**Endpoint Documentation**:

#### GET /api/points/tasks

Get available point tasks with eligibility status.

**Response:**
```json
{
    "success": true,
    "data": {
        "tasks": [
            {
                "id": 1,
                "code": "referral",
                "name": "Gioi thieu ban be",
                "description": "Nhan diem khi ban gioi thieu hoan thanh Skill Quiz",
                "points": 10,
                "role": "user",
                "category": "daily",
                "frequency": "unlimited",
                "requires_approval": false,
                "proof_type": "none",
                "is_active": true,
                "can_earn": true,
                "ineligibility_reason": null
            }
        ],
        "social_status": {
            "facebook": false,
            "youtube": false,
            "tiktok": false
        }
    }
}
```

#### GET /api/points/balance

Get current wallet balance.

**Response:**
```json
{
    "success": true,
    "data": {
        "balance": 150,
        "formatted_balance": "150"
    }
}
```

#### GET /api/points/history

Get point transaction history.

**Query Parameters:**
- `per_page` (int, max 50): Items per page

**Response:**
```json
{
    "success": true,
    "data": {
        "transactions": [
            {
                "id": 1,
                "points": 10,
                "type": "earn",
                "type_label": "Kiem diem",
                "description": "Gioi thieu ban be",
                "metadata": {"task_code": "referral"},
                "is_positive": true,
                "formatted_points": "+10",
                "created_at": "2026-01-14T10:00:00+00:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 20,
            "total": 100
        }
    }
}
```

#### POST /api/points/submissions

Submit proof for approval.

**Request Body (image task):**
```json
{
    "task_code": "check_in_stadium",
    "images": [
        "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
    ]
}
```

**Request Body (link task):**
```json
{
    "task_code": "join_fb_group",
    "url": "https://facebook.com/profile/123456"
}
```

**Request Body (special challenge):**
```json
{
    "task_code": "special_challenge",
    "challenge_id": 5,
    "images": ["data:image/jpeg;base64,..."]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Submission created successfully. Pending admin review.",
    "data": {
        "submission": {
            "id": 1,
            "uuid": "abc-123-def",
            "task": {...},
            "status": "pending",
            "submitted_at": "2026-01-14T10:00:00+00:00"
        }
    }
}
```

#### GET /api/points/challenges

Get active special challenges.

**Response:**
```json
{
    "success": true,
    "data": {
        "challenges": [
            {
                "id": 1,
                "title": "January Challenge",
                "description": "Complete 10 matches",
                "points": 15,
                "start_date": "2026-01-01",
                "end_date": "2026-01-31",
                "is_ongoing": true,
                "participant_count": 25,
                "max_participants": 100,
                "has_reached_limit": false
            }
        ]
    }
}
```

---

## Todo

- [x] Create `PointTaskResource`
- [x] Create `PointSubmissionResource`
- [x] Create `PointTransactionResource`
- [x] Create `SpecialChallengeResource`
- [x] Create `Api/PointController`
- [x] Add API routes to `api.php`
- [x] Test tasks endpoint
- [x] Test balance endpoint
- [x] Test history endpoint with pagination
- [x] Test submissions endpoint
- [x] Test submit with image (base64)
- [x] Test submit with link
- [x] Test challenges endpoint
- [x] Add rate limiting to all endpoints
- [x] Fix N+1 query in submissions
- [x] Add image bomb protection
- [x] Add DoS protection
- [x] Add social verification checks

---

## Success Criteria

1. All endpoints return correct JSON format
2. Authentication required (401 without token)
3. Base64 image upload works
4. Pagination works correctly
5. Error responses follow format
6. Resource transforms work correctly

---

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Large base64 images | Medium | Medium | Limit image count and validate size |
| Rate limiting | Low | Low | Use Laravel Sanctum throttling |

---

## Security Considerations

1. Validate base64 image data (actual image, not malicious)
2. Limit file size via validation
3. Sanctum token authentication required
4. Rate limiting on submission endpoint
5. Validate task_code exists

---

## Next Steps

After all phases complete:
1. Integration testing across all components
2. Documentation update
3. Mobile app team handoff
4. Monitoring setup for point transactions
