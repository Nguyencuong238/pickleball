# Phase 3: API Endpoints

**Date**: 2026-01-02
**Priority**: High
**Status**: Completed
**Depends on**: Phase 1, Phase 2

## Context Links
- Spec: Quiz Specification v2.0
- Reference: `routes/api.php`
- Reference: `app/Http/Controllers/Api/OprsController.php`

## Overview

Create API endpoints for skill assessment quiz: start, answer, submit, result, eligibility.

## API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | /api/skill-quiz/eligibility | Check if user can take quiz | Yes |
| POST | /api/skill-quiz/start | Start new quiz attempt | Yes |
| GET | /api/skill-quiz/attempt/{id} | Get current attempt status | Yes |
| POST | /api/skill-quiz/answer | Record single answer | Yes |
| POST | /api/skill-quiz/submit | Submit completed quiz | Yes |
| GET | /api/skill-quiz/result/{id} | Get quiz result | Yes |

## Related Code Files

### Create
- `app/Http/Controllers/Api/SkillQuizController.php`
- `app/Http/Requests/SkillQuizAnswerRequest.php`

### Modify
- `routes/api.php`

## Implementation Steps

### Step 1: Create Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SkillQuizAnswerRequest;
use App\Models\SkillQuizAttempt;
use App\Services\SkillQuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillQuizController extends Controller
{
    public function __construct(
        private SkillQuizService $quizService
    ) {}

    /**
     * Check if user is eligible to take quiz
     * GET /api/skill-quiz/eligibility
     */
    public function eligibility(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->quizService->canTakeQuiz($user);

        return response()->json([
            'success' => true,
            'data' => [
                'allowed' => $result['allowed'],
                'reason' => $result['reason'] ?? null,
                'next_allowed_at' => $result['next_allowed_at'] ?? null,
                'days_remaining' => $result['days_remaining'] ?? null,
                'quiz_count' => $user->skill_quiz_count,
                'last_quiz_at' => $user->last_skill_quiz_at,
                'current_elo' => $user->elo_rating,
                'is_provisional' => $user->elo_is_provisional,
            ],
        ]);
    }

    /**
     * Start a new quiz attempt
     * POST /api/skill-quiz/start
     */
    public function start(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check eligibility
        $eligibility = $this->quizService->canTakeQuiz($user);
        if (!$eligibility['allowed']) {
            return response()->json([
                'success' => false,
                'message' => 'Chua du dieu kien lam quiz',
                'data' => $eligibility,
            ], 403);
        }

        try {
            $attempt = $this->quizService->startQuiz($user);
            $questions = $this->quizService->getQuestions();

            return response()->json([
                'success' => true,
                'data' => [
                    'attempt_id' => $attempt->id,
                    'started_at' => $attempt->started_at,
                    'timeout_seconds' => SkillQuizService::TIMEOUT_SECONDS,
                    'min_time_seconds' => SkillQuizService::MIN_TIME_SECONDS,
                    'max_time_seconds' => SkillQuizService::MAX_TIME_SECONDS,
                    'questions' => $questions,
                    'total_questions' => count($questions),
                    'answer_scale' => [
                        ['value' => 0, 'label' => 'Chua lam duoc'],
                        ['value' => 1, 'label' => 'Lam duoc hiem khi'],
                        ['value' => 2, 'label' => 'Lam duoc kha thuong xuyen'],
                        ['value' => 3, 'label' => 'Lam duoc on dinh trong thi dau'],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Khong the bat dau quiz: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current attempt status
     * GET /api/skill-quiz/attempt/{id}
     */
    public function attempt(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $attempt = SkillQuizAttempt::where('id', $id)
            ->where('user_id', $user->id)
            ->with('answers')
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay phien quiz',
            ], 404);
        }

        $elapsedSeconds = now()->diffInSeconds($attempt->started_at);
        $remainingSeconds = max(0, SkillQuizService::TIMEOUT_SECONDS - $elapsedSeconds);

        return response()->json([
            'success' => true,
            'data' => [
                'attempt_id' => $attempt->id,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at,
                'elapsed_seconds' => $elapsedSeconds,
                'remaining_seconds' => $remainingSeconds,
                'answered_count' => $attempt->answers->count(),
                'answered_question_ids' => $attempt->answers->pluck('question_id'),
            ],
        ]);
    }

    /**
     * Record an answer
     * POST /api/skill-quiz/answer
     */
    public function answer(SkillQuizAnswerRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $attempt = SkillQuizAttempt::where('id', $validated['attempt_id'])
            ->where('user_id', $user->id)
            ->where('status', SkillQuizAttempt::STATUS_IN_PROGRESS)
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Phien quiz khong hop le hoac da ket thuc',
            ], 400);
        }

        // Check timeout
        $elapsedSeconds = now()->diffInSeconds($attempt->started_at);
        if ($elapsedSeconds >= SkillQuizService::TIMEOUT_SECONDS) {
            $this->quizService->autoSubmit($attempt);
            return response()->json([
                'success' => false,
                'message' => 'Quiz da het thoi gian',
                'data' => ['auto_submitted' => true],
            ], 400);
        }

        try {
            $answer = $this->quizService->recordAnswer(
                $attempt,
                $validated['question_id'],
                $validated['answer_value'],
                $validated['time_spent_seconds'] ?? 0
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'answer_id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'answer_value' => $answer->answer_value,
                    'answered_at' => $answer->answered_at,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Submit completed quiz
     * POST /api/skill-quiz/submit
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'attempt_id' => 'required|uuid',
        ]);

        $attempt = SkillQuizAttempt::where('id', $request->attempt_id)
            ->where('user_id', $user->id)
            ->where('status', SkillQuizAttempt::STATUS_IN_PROGRESS)
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Phien quiz khong hop le hoac da nop',
            ], 400);
        }

        // Check minimum answers (at least 30 of 36)
        $answeredCount = $attempt->answers()->count();
        if ($answeredCount < 30) {
            return response()->json([
                'success' => false,
                'message' => "Can tra loi it nhat 30 cau. Hien tai: {$answeredCount}/36",
            ], 400);
        }

        try {
            $result = $this->quizService->submitQuiz($attempt);

            return response()->json([
                'success' => true,
                'message' => 'Nop quiz thanh cong',
                'data' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get quiz result
     * GET /api/skill-quiz/result/{id}
     */
    public function result(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        // Verify ownership
        $attempt = SkillQuizAttempt::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay ket qua quiz',
            ], 404);
        }

        if ($attempt->status !== SkillQuizAttempt::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz chua hoan thanh',
            ], 400);
        }

        $result = $this->quizService->getResult($id);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
```

### Step 2: Create Form Request

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SkillQuizAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attempt_id' => 'required|uuid|exists:skill_quiz_attempts,id',
            'question_id' => 'required|integer|exists:skill_questions,id',
            'answer_value' => 'required|integer|between:0,3',
            'time_spent_seconds' => 'nullable|integer|min:0|max:600',
        ];
    }

    public function messages(): array
    {
        return [
            'attempt_id.required' => 'Thieu ID phien quiz',
            'attempt_id.uuid' => 'ID phien quiz khong hop le',
            'attempt_id.exists' => 'Phien quiz khong ton tai',
            'question_id.required' => 'Thieu ID cau hoi',
            'question_id.exists' => 'Cau hoi khong ton tai',
            'answer_value.required' => 'Thieu gia tri tra loi',
            'answer_value.between' => 'Gia tri tra loi phai tu 0 den 3',
        ];
    }
}
```

### Step 3: Register Routes

Add to `routes/api.php`:

```php
// Skill Quiz Routes (authenticated)
Route::middleware('auth:api')->prefix('skill-quiz')->name('skill-quiz.')->group(function () {
    Route::get('eligibility', [SkillQuizController::class, 'eligibility'])->name('eligibility');
    Route::post('start', [SkillQuizController::class, 'start'])->name('start');
    Route::get('attempt/{id}', [SkillQuizController::class, 'attempt'])->name('attempt');
    Route::post('answer', [SkillQuizController::class, 'answer'])->name('answer');
    Route::post('submit', [SkillQuizController::class, 'submit'])->name('submit');
    Route::get('result/{id}', [SkillQuizController::class, 'result'])->name('result');
});
```

## API Response Examples

### GET /api/skill-quiz/eligibility

**Success (allowed)**:
```json
{
    "success": true,
    "data": {
        "allowed": true,
        "reason": null,
        "next_allowed_at": null,
        "days_remaining": null,
        "quiz_count": 0,
        "last_quiz_at": null,
        "current_elo": 1000,
        "is_provisional": true
    }
}
```

**Success (cooldown)**:
```json
{
    "success": true,
    "data": {
        "allowed": false,
        "reason": "cooldown",
        "next_allowed_at": "2026-02-01T00:00:00.000000Z",
        "days_remaining": 30,
        "quiz_count": 1,
        "last_quiz_at": "2026-01-02T10:00:00.000000Z",
        "current_elo": 1150,
        "is_provisional": true
    }
}
```

### POST /api/skill-quiz/start

**Success**:
```json
{
    "success": true,
    "data": {
        "attempt_id": "550e8400-e29b-41d4-a716-446655440000",
        "started_at": "2026-01-02T10:00:00.000000Z",
        "timeout_seconds": 1200,
        "min_time_seconds": 180,
        "max_time_seconds": 900,
        "questions": [
            {
                "id": 1,
                "domain_key": "rules",
                "domain_name": "Luat & Vi tri",
                "question": "Toi hieu va ap dung dung luat double bounce rule trong tran",
                "order_in_domain": 1
            }
        ],
        "total_questions": 36,
        "answer_scale": [
            {"value": 0, "label": "Chua lam duoc"},
            {"value": 1, "label": "Lam duoc hiem khi"},
            {"value": 2, "label": "Lam duoc kha thuong xuyen"},
            {"value": 3, "label": "Lam duoc on dinh trong thi dau"}
        ]
    }
}
```

### POST /api/skill-quiz/answer

**Request**:
```json
{
    "attempt_id": "550e8400-e29b-41d4-a716-446655440000",
    "question_id": 1,
    "answer_value": 2,
    "time_spent_seconds": 15
}
```

**Success**:
```json
{
    "success": true,
    "data": {
        "answer_id": "660e8400-e29b-41d4-a716-446655440001",
        "question_id": 1,
        "answer_value": 2,
        "answered_at": "2026-01-02T10:01:00.000000Z"
    }
}
```

### POST /api/skill-quiz/submit

**Success**:
```json
{
    "success": true,
    "message": "Nop quiz thanh cong",
    "data": {
        "final_elo": 1120,
        "quiz_percent": 65.5,
        "domain_scores": {
            "rules": 78.0,
            "consistency": 65.0,
            "serve_return": 55.5,
            "dink_net": 60.0,
            "reset_defense": 58.0,
            "tactics": 70.0
        },
        "flags": [],
        "duration": 540,
        "is_provisional": true,
        "skill_level": "3.8 - 4.0"
    }
}
```

### GET /api/skill-quiz/result/{id}

**Success**:
```json
{
    "success": true,
    "data": {
        "attempt_id": "550e8400-e29b-41d4-a716-446655440000",
        "user_id": 123,
        "started_at": "2026-01-02T10:00:00.000000Z",
        "completed_at": "2026-01-02T10:09:00.000000Z",
        "duration_seconds": 540,
        "domain_scores": {
            "rules": 78.0,
            "consistency": 65.0,
            "serve_return": 55.5,
            "dink_net": 60.0,
            "reset_defense": 58.0,
            "tactics": 70.0
        },
        "quiz_percent": 65.5,
        "calculated_elo": 1175,
        "final_elo": 1120,
        "skill_level": "3.8 - 4.0",
        "flags": [
            {
                "type": "INCONSISTENT",
                "message": "...",
                "adjustment": -55
            }
        ],
        "is_provisional": true,
        "recommendations": [
            {
                "domain": "Giao bong & Tra giao",
                "score": 55.5,
                "priority": "medium",
                "message": "Co the cai thien them Giao bong & Tra giao"
            }
        ]
    }
}
```

## Todo List

- [x] Create SkillQuizController
- [x] Create SkillQuizAnswerRequest
- [x] Add eligibility endpoint
- [x] Add start endpoint
- [x] Add attempt endpoint
- [x] Add answer endpoint
- [x] Add submit endpoint
- [x] Add result endpoint
- [x] Register routes in api.php (6 routes)
- [x] Test all endpoints with Postman/curl
- [x] Handle edge cases (timeout, invalid attempt)

## Success Criteria

- [x] All 6 endpoints working
- [x] Auth required for all endpoints
- [x] Proper error responses
- [x] Timeout handling works
- [x] Answer validation works
- [x] Result matches submitted answers

## Risk Assessment

| Risk | Mitigation |
|------|------------|
| Concurrent answers | UUID primary key, updateOrCreate |
| Timeout race condition | Check timeout before each action |
| Large response payload | Paginate questions if needed |

## Next Steps

After Phase 3:
- Phase 4: Frontend Implementation
