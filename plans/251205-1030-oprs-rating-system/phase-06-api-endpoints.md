# Phase 6: API Endpoints

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 5: Community System](./phase-05-community-system.md)
**Related Docs**: [code-standards.md](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2025-12-05 |
| Description | Consolidate and extend OPRS API endpoints |
| Priority | High |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

1. Extend existing OcrUserController with OPRS data
2. Update OcrLeaderboardController for OPRS rankings
3. Challenge/Community endpoints already created
4. Need OPRS-specific profile endpoint
5. Public endpoints for viewing, auth for modifications

## Requirements

### Functional
- User OPRS profile with breakdown
- OPRS leaderboard with level filtering
- Challenge type listing
- Activity type listing
- History endpoints for all components
- Matchmaking suggestions by OPRS

### Non-Functional
- Consistent JSON response format
- Proper HTTP status codes
- Pagination for lists
- Rate limiting on write endpoints

## Architecture

### API Endpoint Map

```
/api/oprs/
├── profile/{user}                 GET    Public user OPRS profile
├── profile/{user}/breakdown       GET    OPRS breakdown details
├── profile/{user}/history         GET    OPRS change history
├── leaderboard                    GET    OPRS leaderboard
├── leaderboard/level/{level}      GET    Leaderboard by OPR Level
├── leaderboard/distribution       GET    Level distribution stats
│
├── challenges/                    AUTH REQUIRED
│   ├── types                      GET    Available challenge types
│   ├── available                  GET    User's available challenges
│   ├── submit                     POST   Submit challenge result
│   ├── history                    GET    User's challenge history
│   └── stats                      GET    User's challenge stats
│
├── community/                     AUTH REQUIRED
│   ├── check-in                   POST   Check in at stadium
│   ├── referral                   POST   Record referral
│   ├── history                    GET    Activity history
│   └── stats                      GET    Activity stats
│
└── matchmaking/
    ├── suggest/{user}             GET    Suggest opponents by OPRS
    └── estimate                   POST   Estimate OPRS change
```

## Related Code Files

| File | Action | Purpose |
|------|--------|---------|
| `app/Http/Controllers/Api/OprsController.php` | Create | OPRS profile endpoints |
| `app/Http/Controllers/Api/OprsLeaderboardController.php` | Create | OPRS leaderboard |
| `app/Http/Controllers/Api/OcrUserController.php` | Modify | Add OPRS to response |
| `routes/api.php` | Modify | Add OPRS routes |

## Implementation Steps

### Step 1: Create OprsController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OprsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OprsController extends Controller
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Get user's OPRS profile
     */
    public function profile(User $user): JsonResponse
    {
        $breakdown = $this->oprsService->getOprsBreakdown($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar_url ?? null,
                ],
                'oprs' => [
                    'total' => $user->total_oprs,
                    'level' => $user->opr_level,
                    'level_name' => $breakdown['level_info']['name'],
                ],
                'components' => [
                    'elo' => [
                        'score' => $user->elo_rating,
                        'rank' => $user->elo_rank,
                        'weight' => OprsService::WEIGHT_ELO,
                        'weighted_score' => $breakdown['elo']['weighted'],
                    ],
                    'challenge' => [
                        'score' => $user->challenge_score,
                        'weight' => OprsService::WEIGHT_CHALLENGE,
                        'weighted_score' => $breakdown['challenge']['weighted'],
                    ],
                    'community' => [
                        'score' => $user->community_score,
                        'weight' => OprsService::WEIGHT_COMMUNITY,
                        'weighted_score' => $breakdown['community']['weighted'],
                    ],
                ],
                'stats' => [
                    'total_matches' => $user->total_ocr_matches,
                    'wins' => $user->ocr_wins,
                    'losses' => $user->ocr_losses,
                    'win_rate' => $user->win_rate,
                ],
            ],
        ]);
    }

    /**
     * Get OPRS change history
     */
    public function history(User $user, Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 100);

        $history = $user->oprsHistories()
            ->orderByDesc('created_at')
            ->take($limit)
            ->get()
            ->map(fn($h) => [
                'id' => $h->id,
                'elo_score' => $h->elo_score,
                'challenge_score' => $h->challenge_score,
                'community_score' => $h->community_score,
                'total_oprs' => $h->total_oprs,
                'opr_level' => $h->opr_level,
                'change_reason' => $h->change_reason,
                'created_at' => $h->created_at->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Estimate OPRS change for potential action
     */
    public function estimateChange(Request $request): JsonResponse
    {
        $request->validate([
            'component' => 'required|in:elo,challenge,community',
            'change' => 'required|numeric',
        ]);

        $user = $request->user();
        $estimate = $this->oprsService->estimateOprsChange(
            $user,
            $request->component,
            (float) $request->change
        );

        return response()->json([
            'success' => true,
            'data' => $estimate,
        ]);
    }
}
```

### Step 2: Create OprsLeaderboardController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OprsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OprsLeaderboardController extends Controller
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Get OPRS leaderboard
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 50), 100);
        $offset = (int) $request->get('offset', 0);

        $users = $this->oprsService->getLeaderboard(null, $limit, $offset);

        return response()->json([
            'success' => true,
            'data' => $users->map(fn($user, $index) => [
                'rank' => $offset + $index + 1,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar_url ?? null,
                ],
                'oprs' => $user->total_oprs,
                'opr_level' => $user->opr_level,
                'elo_rating' => $user->elo_rating,
                'elo_rank' => $user->elo_rank,
                'stats' => [
                    'matches' => $user->total_ocr_matches,
                    'wins' => $user->ocr_wins,
                    'win_rate' => $user->win_rate,
                ],
            ]),
            'meta' => [
                'offset' => $offset,
                'limit' => $limit,
                'has_more' => $users->count() === $limit,
            ],
        ]);
    }

    /**
     * Get leaderboard by OPR Level
     */
    public function byLevel(string $level, Request $request): JsonResponse
    {
        $validLevels = array_keys(OprsService::OPR_LEVELS);

        if (!in_array($level, $validLevels)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid OPR level',
            ], 422);
        }

        $limit = min((int) $request->get('limit', 50), 100);
        $offset = (int) $request->get('offset', 0);

        $users = $this->oprsService->getLeaderboard($level, $limit, $offset);

        return response()->json([
            'success' => true,
            'data' => $users->map(fn($user, $index) => [
                'rank' => $offset + $index + 1,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'oprs' => $user->total_oprs,
                'opr_level' => $user->opr_level,
                'stats' => [
                    'matches' => $user->total_ocr_matches,
                    'wins' => $user->ocr_wins,
                ],
            ]),
            'meta' => [
                'level' => $level,
                'level_info' => OprsService::OPR_LEVELS[$level],
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Get level distribution
     */
    public function distribution(): JsonResponse
    {
        $distribution = $this->oprsService->getLevelDistribution();

        $data = [];
        foreach (OprsService::OPR_LEVELS as $level => $info) {
            $data[$level] = [
                'name' => $info['name'],
                'min_oprs' => $info['min'],
                'max_oprs' => $info['max'] === PHP_INT_MAX ? null : $info['max'],
                'count' => $distribution[$level] ?? 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get levels info
     */
    public function levels(): JsonResponse
    {
        $levels = [];
        foreach (OprsService::OPR_LEVELS as $level => $info) {
            $levels[$level] = [
                'name' => $info['name'],
                'min_oprs' => $info['min'],
                'max_oprs' => $info['max'] === PHP_INT_MAX ? null : $info['max'],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $levels,
        ]);
    }
}
```

### Step 3: Create Matchmaking Endpoint

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OprsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchmakingController extends Controller
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Suggest opponents by OPRS
     */
    public function suggest(User $user, Request $request): JsonResponse
    {
        $range = (int) $request->get('range', 100); // OPRS range
        $limit = min((int) $request->get('limit', 10), 20);

        $minOprs = max(0, $user->total_oprs - $range);
        $maxOprs = $user->total_oprs + $range;

        $suggestions = User::query()
            ->where('id', '!=', $user->id)
            ->whereBetween('total_oprs', [$minOprs, $maxOprs])
            ->where('total_ocr_matches', '>', 0)
            ->orderByRaw('ABS(total_oprs - ?)', [$user->total_oprs])
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $suggestions->map(fn($opponent) => [
                'user' => [
                    'id' => $opponent->id,
                    'name' => $opponent->name,
                ],
                'oprs' => $opponent->total_oprs,
                'opr_level' => $opponent->opr_level,
                'oprs_diff' => abs($user->total_oprs - $opponent->total_oprs),
                'elo_rating' => $opponent->elo_rating,
                'stats' => [
                    'matches' => $opponent->total_ocr_matches,
                    'wins' => $opponent->ocr_wins,
                    'win_rate' => $opponent->win_rate,
                ],
            ]),
            'meta' => [
                'your_oprs' => $user->total_oprs,
                'search_range' => [$minOprs, $maxOprs],
            ],
        ]);
    }
}
```

### Step 4: Update Routes

```php
// In routes/api.php:

use App\Http\Controllers\Api\OprsController;
use App\Http\Controllers\Api\OprsLeaderboardController;
use App\Http\Controllers\Api\MatchmakingController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\CommunityController;

/*
|--------------------------------------------------------------------------
| OPRS (OnePickleball Rating Score) Routes
|--------------------------------------------------------------------------
*/

// Public OPRS endpoints
Route::prefix('oprs')->group(function () {
    Route::get('profile/{user}', [OprsController::class, 'profile']);
    Route::get('profile/{user}/history', [OprsController::class, 'history']);
    Route::get('leaderboard', [OprsLeaderboardController::class, 'index']);
    Route::get('leaderboard/levels', [OprsLeaderboardController::class, 'levels']);
    Route::get('leaderboard/level/{level}', [OprsLeaderboardController::class, 'byLevel']);
    Route::get('leaderboard/distribution', [OprsLeaderboardController::class, 'distribution']);
    Route::get('matchmaking/suggest/{user}', [MatchmakingController::class, 'suggest']);
});

// Protected OPRS endpoints
Route::prefix('oprs')->middleware('auth:sanctum')->group(function () {
    Route::post('estimate', [OprsController::class, 'estimateChange']);
});

// Challenge endpoints (auth required)
Route::prefix('challenges')->middleware('auth:sanctum')->group(function () {
    Route::get('types', [ChallengeController::class, 'types']);
    Route::get('available', [ChallengeController::class, 'available']);
    Route::post('submit', [ChallengeController::class, 'submit']);
    Route::get('history', [ChallengeController::class, 'history']);
    Route::get('stats', [ChallengeController::class, 'stats']);
});

// Community endpoints (auth required)
Route::prefix('community')->middleware('auth:sanctum')->group(function () {
    Route::post('check-in', [CommunityController::class, 'checkIn']);
    Route::post('referral', [CommunityController::class, 'referral']);
    Route::get('history', [CommunityController::class, 'history']);
    Route::get('stats', [CommunityController::class, 'stats']);
});
```

## Todo List

- [ ] Create OprsController with profile endpoints
- [ ] Create OprsLeaderboardController
- [ ] Create MatchmakingController
- [ ] Update routes/api.php with OPRS routes
- [ ] Add OPRS data to existing OcrUserController
- [ ] Test all public endpoints
- [ ] Test all protected endpoints
- [ ] Verify response format consistency

## Success Criteria

1. Profile endpoint returns complete OPRS breakdown
2. Leaderboard sorted by OPRS correctly
3. Level filtering works
4. Distribution stats accurate
5. Matchmaking suggestions within range
6. All endpoints use consistent response format

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Performance on leaderboard | Medium | Indexed queries |
| Inconsistent responses | Low | Shared response format |
| Missing auth checks | High | Middleware enforcement |

## Security Considerations

- Public endpoints only expose non-sensitive data
- Auth required for all write operations
- Rate limiting on endpoints
- Input validation on all parameters

## Next Steps

After API complete:
1. Proceed to [Phase 7: Frontend Views](./phase-07-frontend-views.md)
2. Build profile component with OPRS display
3. Update leaderboard view
