# Phase 4: API Controllers

## Context Links

- [Parent Plan](./plan.md)
- [Phase 3: Elo Service](./phase-03-elo-service.md)
- [Existing API Routes](../../routes/api.php)
- [Code Standards](../../docs/code-standards.md)

## Overview

- **Date**: 2025-12-02
- **Priority**: High
- **Implementation Status**: Pending
- **Review Status**: Pending
- **Dependencies**: Phase 3 (Elo Service)

Create RESTful API controllers for OCR match management, Elo retrieval, badges, and leaderboard.

## Key Insights

1. Follow existing API pattern (see `api.php`)
2. Use auth:sanctum for protected endpoints
3. Return consistent JSON response format
4. Form Requests for validation

## Requirements

### Functional

- CRUD for OCR matches
- Match invitation accept/reject
- Result submission and confirmation
- Evidence upload
- User Elo and badge retrieval
- Global leaderboard

### Non-Functional

- RESTful conventions
- Proper HTTP status codes
- Validation via Form Requests
- Authorization via policies

## Architecture

### API Endpoints

```
/api/ocr/
  |-- matches                    (OcrMatchController)
  |   |-- GET    /               - List user's matches
  |   |-- POST   /               - Create match invitation
  |   |-- GET    /:id            - Get match details
  |   |-- PATCH  /:id/accept     - Accept invitation
  |   |-- PATCH  /:id/reject     - Reject invitation
  |   |-- POST   /:id/result     - Submit result
  |   |-- POST   /:id/confirm    - Confirm result
  |   |-- POST   /:id/dispute    - Dispute result
  |   |-- POST   /:id/evidence   - Upload evidence
  |
  |-- users                      (OcrUserController)
  |   |-- GET    /:id/elo        - Get user Elo
  |   |-- GET    /:id/badges     - Get user badges
  |   |-- GET    /:id/stats      - Get user OCR stats
  |
  |-- leaderboard                (OcrLeaderboardController)
      |-- GET    /               - Global rankings
      |-- GET    /:rank          - Rankings by rank tier
```

## Related Code Files

### Files to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Api/OcrMatchController.php` | Create | Match CRUD and workflow |
| `app/Http/Controllers/Api/OcrUserController.php` | Create | User Elo/badges |
| `app/Http/Controllers/Api/OcrLeaderboardController.php` | Create | Leaderboard |
| `app/Http/Requests/OcrMatchStoreRequest.php` | Create | Match creation validation |
| `app/Http/Requests/OcrMatchResultRequest.php` | Create | Result submission validation |
| `app/Policies/OcrMatchPolicy.php` | Create | Authorization policy |

### Files to Modify

| File | Action | Description |
|------|--------|-------------|
| `routes/api.php` | Modify | Add OCR routes |
| `app/Providers/AuthServiceProvider.php` | Modify | Register policy |

## Implementation Steps

### Step 1: Create OcrMatchController

```php
<?php
// app/Http/Controllers/Api/OcrMatchController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OcrMatchStoreRequest;
use App\Http\Requests\OcrMatchResultRequest;
use App\Models\OcrMatch;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\EloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OcrMatchController extends Controller
{
    public function __construct(
        private EloService $eloService,
        private BadgeService $badgeService
    ) {}

    /**
     * List user's matches
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = OcrMatch::forUser($user->id)
            ->with(['challenger', 'opponent', 'challengerPartner', 'opponentPartner'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $matches = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $matches,
        ]);
    }

    /**
     * Create match invitation
     */
    public function store(OcrMatchStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Validate opponent exists
        $opponent = User::find($validated['opponent_id']);
        if (!$opponent) {
            return response()->json([
                'success' => false,
                'error' => 'Opponent not found',
            ], 404);
        }

        // Prevent self-challenge
        if ($opponent->id === $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot challenge yourself',
            ], 422);
        }

        // Create match
        $match = OcrMatch::create([
            'match_type' => $validated['match_type'] ?? 'singles',
            'challenger_id' => $user->id,
            'challenger_partner_id' => $validated['challenger_partner_id'] ?? null,
            'opponent_id' => $validated['opponent_id'],
            'opponent_partner_id' => $validated['opponent_partner_id'] ?? null,
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'scheduled_time' => $validated['scheduled_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => OcrMatch::STATUS_PENDING,
        ]);

        $match->load(['challenger', 'opponent']);

        return response()->json([
            'success' => true,
            'message' => 'Match invitation sent',
            'data' => $match,
        ], 201);
    }

    /**
     * Get match details
     */
    public function show(OcrMatch $match, Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant or admin
        if (!$match->isParticipant($user->id) && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $match->load([
            'challenger',
            'opponent',
            'challengerPartner',
            'opponentPartner',
            'media',
        ]);

        // Add win probability
        $winProbability = null;
        if ($match->isPending() || $match->isAccepted()) {
            $winProbability = [
                'challenger' => $this->eloService->getWinProbability(
                    $match->challenger,
                    $match->opponent
                ),
                'opponent' => $this->eloService->getWinProbability(
                    $match->opponent,
                    $match->challenger
                ),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $match,
            'meta' => [
                'win_probability' => $winProbability,
            ],
        ]);
    }

    /**
     * Accept match invitation
     */
    public function accept(OcrMatch $match, Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$match->isOpponentTeam($user->id)) {
            return response()->json([
                'success' => false,
                'error' => 'Only opponent can accept',
            ], 403);
        }

        if (!$match->isPending()) {
            return response()->json([
                'success' => false,
                'error' => 'Match not pending',
            ], 422);
        }

        $match->accept();

        return response()->json([
            'success' => true,
            'message' => 'Match accepted',
            'data' => $match->fresh(),
        ]);
    }

    /**
     * Reject match invitation
     */
    public function reject(OcrMatch $match, Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$match->isOpponentTeam($user->id)) {
            return response()->json([
                'success' => false,
                'error' => 'Only opponent can reject',
            ], 403);
        }

        if (!$match->isPending()) {
            return response()->json([
                'success' => false,
                'error' => 'Match not pending',
            ], 422);
        }

        $match->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Match rejected',
        ]);
    }

    /**
     * Submit match result
     */
    public function submitResult(OcrMatch $match, OcrMatchResultRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if (!$match->isParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'error' => 'Only participants can submit result',
            ], 403);
        }

        if (!in_array($match->status, [OcrMatch::STATUS_ACCEPTED, OcrMatch::STATUS_IN_PROGRESS])) {
            return response()->json([
                'success' => false,
                'error' => 'Match not in valid state for result submission',
            ], 422);
        }

        $match->submitResult(
            $user->id,
            $validated['challenger_score'],
            $validated['opponent_score']
        );

        return response()->json([
            'success' => true,
            'message' => 'Result submitted, waiting for confirmation',
            'data' => $match->fresh(),
        ]);
    }

    /**
     * Confirm match result
     */
    public function confirmResult(OcrMatch $match, Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$match->isParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'error' => 'Only participants can confirm',
            ], 403);
        }

        // Submitter cannot confirm their own result
        if ($match->result_submitted_by === $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot confirm your own submission',
            ], 422);
        }

        if ($match->status !== OcrMatch::STATUS_RESULT_SUBMITTED) {
            return response()->json([
                'success' => false,
                'error' => 'No result to confirm',
            ], 422);
        }

        try {
            DB::transaction(function () use ($match, $user) {
                // Confirm the result
                $match->confirmResult();

                // Process Elo changes
                $this->eloService->processMatchResult($match);

                // Check badges for all participants
                $challengerWon = $match->winner_team === 'challenger';
                $participants = [
                    ['user' => $match->challenger, 'won' => $challengerWon],
                    ['user' => $match->challengerPartner, 'won' => $challengerWon],
                    ['user' => $match->opponent, 'won' => !$challengerWon],
                    ['user' => $match->opponentPartner, 'won' => !$challengerWon],
                ];

                foreach ($participants as $p) {
                    if ($p['user']) {
                        $p['user']->refresh();
                        $this->badgeService->checkBadgesAfterMatch($p['user'], $match, $p['won']);
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process result: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Match confirmed and Elo updated',
            'data' => $match->fresh()->load(['challenger', 'opponent']),
        ]);
    }

    /**
     * Dispute match result
     */
    public function dispute(OcrMatch $match, Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$match->isParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'error' => 'Only participants can dispute',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $match->dispute($validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Dispute submitted for admin review',
        ]);
    }

    /**
     * Upload evidence
     */
    public function uploadEvidence(OcrMatch $match, Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$match->isParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'error' => 'Only participants can upload evidence',
            ], 403);
        }

        $request->validate([
            'evidence' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:20480', // 20MB max
        ]);

        $match->addMediaFromRequest('evidence')
            ->usingFileName(time() . '_' . $request->file('evidence')->getClientOriginalName())
            ->toMediaCollection('evidence');

        return response()->json([
            'success' => true,
            'message' => 'Evidence uploaded',
            'data' => $match->fresh()->getMedia('evidence'),
        ]);
    }
}
```

### Step 2: Create OcrUserController

```php
<?php
// app/Http/Controllers/Api/OcrUserController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class OcrUserController extends Controller
{
    /**
     * Get user's Elo rating
     */
    public function elo(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'total_matches' => $user->total_ocr_matches,
                'wins' => $user->ocr_wins,
                'losses' => $user->ocr_losses,
                'win_rate' => $user->win_rate,
            ],
        ]);
    }

    /**
     * Get user's badges
     */
    public function badges(User $user): JsonResponse
    {
        $badges = $user->badges()->orderBy('earned_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $badges->map(fn($badge) => [
                'type' => $badge->badge_type,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'earned_at' => $badge->earned_at->toISOString(),
                'metadata' => $badge->metadata,
            ]),
        ]);
    }

    /**
     * Get user's OCR stats
     */
    public function stats(User $user): JsonResponse
    {
        $recentHistory = $user->eloHistories()
            ->with('ocrMatch')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'total_matches' => $user->total_ocr_matches,
                'wins' => $user->ocr_wins,
                'losses' => $user->ocr_losses,
                'win_rate' => $user->win_rate,
                'badges_count' => $user->badges()->count(),
                'recent_history' => $recentHistory,
            ],
        ]);
    }
}
```

### Step 3: Create OcrLeaderboardController

```php
<?php
// app/Http/Controllers/Api/OcrLeaderboardController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OcrLeaderboardController extends Controller
{
    /**
     * Get global leaderboard
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min(100, (int) $request->query('limit', 50));

        $users = User::where('total_ocr_matches', '>', 0)
            ->orderBy('elo_rating', 'desc')
            ->take($limit)
            ->get(['id', 'name', 'elo_rating', 'elo_rank', 'total_ocr_matches', 'ocr_wins', 'ocr_losses']);

        $ranked = $users->map(function ($user, $index) {
            return [
                'rank' => $index + 1,
                'user_id' => $user->id,
                'name' => $user->name,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'total_matches' => $user->total_ocr_matches,
                'wins' => $user->ocr_wins,
                'losses' => $user->ocr_losses,
                'win_rate' => $user->win_rate,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $ranked,
        ]);
    }

    /**
     * Get leaderboard by rank tier
     */
    public function byRank(string $rank, Request $request): JsonResponse
    {
        $validRanks = array_keys(User::getEloRanks());

        if (!in_array(ucfirst($rank), $validRanks)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid rank',
            ], 422);
        }

        $limit = min(100, (int) $request->query('limit', 50));

        $users = User::where('elo_rank', ucfirst($rank))
            ->where('total_ocr_matches', '>', 0)
            ->orderBy('elo_rating', 'desc')
            ->take($limit)
            ->get(['id', 'name', 'elo_rating', 'elo_rank', 'total_ocr_matches', 'ocr_wins', 'ocr_losses']);

        $ranked = $users->values()->map(function ($user, $index) {
            return [
                'rank_in_tier' => $index + 1,
                'user_id' => $user->id,
                'name' => $user->name,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'total_matches' => $user->total_ocr_matches,
                'wins' => $user->ocr_wins,
                'losses' => $user->ocr_losses,
                'win_rate' => $user->win_rate,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $ranked,
        ]);
    }
}
```

### Step 4: Create Form Requests

```php
<?php
// app/Http/Requests/OcrMatchStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OcrMatchStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by route middleware
    }

    public function rules(): array
    {
        return [
            'match_type' => 'required|in:singles,doubles',
            'opponent_id' => 'required|exists:users,id',
            'challenger_partner_id' => 'nullable|required_if:match_type,doubles|exists:users,id',
            'opponent_partner_id' => 'nullable|required_if:match_type,doubles|exists:users,id',
            'scheduled_date' => 'nullable|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'opponent_id.required' => 'Opponent is required',
            'opponent_id.exists' => 'Opponent not found',
            'challenger_partner_id.required_if' => 'Partner required for doubles',
            'opponent_partner_id.required_if' => 'Opponent partner required for doubles',
        ];
    }
}
```

```php
<?php
// app/Http/Requests/OcrMatchResultRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OcrMatchResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challenger_score' => 'required|integer|min:0|max:99',
            'opponent_score' => 'required|integer|min:0|max:99|different:challenger_score',
        ];
    }

    public function messages(): array
    {
        return [
            'opponent_score.different' => 'Cannot have a tie score',
        ];
    }
}
```

### Step 5: Update API Routes

Add to `routes/api.php`:

```php
// OCR (OnePickleball Championship Ranking) Routes
Route::prefix('ocr')->middleware('auth:sanctum')->group(function () {
    // Match management
    Route::get('matches', [App\Http\Controllers\Api\OcrMatchController::class, 'index']);
    Route::post('matches', [App\Http\Controllers\Api\OcrMatchController::class, 'store']);
    Route::get('matches/{match}', [App\Http\Controllers\Api\OcrMatchController::class, 'show']);
    Route::patch('matches/{match}/accept', [App\Http\Controllers\Api\OcrMatchController::class, 'accept']);
    Route::patch('matches/{match}/reject', [App\Http\Controllers\Api\OcrMatchController::class, 'reject']);
    Route::post('matches/{match}/result', [App\Http\Controllers\Api\OcrMatchController::class, 'submitResult']);
    Route::post('matches/{match}/confirm', [App\Http\Controllers\Api\OcrMatchController::class, 'confirmResult']);
    Route::post('matches/{match}/dispute', [App\Http\Controllers\Api\OcrMatchController::class, 'dispute']);
    Route::post('matches/{match}/evidence', [App\Http\Controllers\Api\OcrMatchController::class, 'uploadEvidence']);
});

// Public OCR endpoints (no auth required for viewing)
Route::prefix('ocr')->group(function () {
    Route::get('users/{user}/elo', [App\Http\Controllers\Api\OcrUserController::class, 'elo']);
    Route::get('users/{user}/badges', [App\Http\Controllers\Api\OcrUserController::class, 'badges']);
    Route::get('users/{user}/stats', [App\Http\Controllers\Api\OcrUserController::class, 'stats']);
    Route::get('leaderboard', [App\Http\Controllers\Api\OcrLeaderboardController::class, 'index']);
    Route::get('leaderboard/{rank}', [App\Http\Controllers\Api\OcrLeaderboardController::class, 'byRank']);
});
```

## Todo List

- [ ] Create OcrMatchController
- [ ] Create OcrUserController
- [ ] Create OcrLeaderboardController
- [ ] Create OcrMatchStoreRequest
- [ ] Create OcrMatchResultRequest
- [ ] Update api.php routes
- [ ] Test all endpoints with Postman/curl

## Success Criteria

1. All endpoints return proper JSON
2. Auth middleware protects match operations
3. Validation prevents invalid data
4. Elo processed on confirmation
5. Evidence upload works with Spatie Media

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Race conditions on confirm | High | DB transaction |
| File upload abuse | Medium | File size/type limits |
| Elo manipulation | High | Confirmation requirement |

## Security Considerations

- Sanctum middleware on write operations
- Participant check on all match operations
- Cannot confirm own result submission
- File type/size validation

## Next Steps

After controllers complete, proceed to [Phase 5: Match Workflow](./phase-05-match-workflow.md)
