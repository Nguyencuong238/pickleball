# Phase 8: Admin Panel

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 7: Frontend Views](./phase-07-frontend-views.md)
**Related Docs**: [code-standards.md](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2025-12-05 |
| Description | Build admin management for OPRS system |
| Priority | Medium |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

1. Extend existing admin panel patterns
2. Challenge verification workflow
3. Activity management and adjustments
4. OPRS manual adjustments
5. Reporting and analytics

## Requirements

### Functional
- View/manage challenge submissions
- Verify challenge results
- View/manage community activities
- Manual OPRS adjustments
- User OPRS overview
- Reporting dashboard

### Non-Functional
- Role-based access (admin only)
- Audit logging for adjustments
- Batch operations support

## Architecture

### Admin Routes

```
/admin/oprs/
├── dashboard                    GET    OPRS overview dashboard
├── users                        GET    User OPRS list
├── users/{user}                 GET    User OPRS detail
├── users/{user}/adjust          POST   Manual OPRS adjustment
│
├── challenges                   GET    Challenge list
├── challenges/{id}              GET    Challenge detail
├── challenges/{id}/verify       POST   Verify challenge
├── challenges/{id}/reject       POST   Reject challenge
│
├── activities                   GET    Activity list
├── activities/{id}              DELETE Remove activity
│
└── reports
    ├── levels                   GET    Level distribution
    └── trends                   GET    OPRS trends
```

## Related Code Files

| File | Action | Purpose |
|------|--------|---------|
| `app/Http/Controllers/Admin/OprsController.php` | Create | Admin OPRS management |
| `app/Http/Controllers/Admin/OprsChallengeController.php` | Create | Challenge management |
| `app/Http/Controllers/Admin/OprsActivityController.php` | Create | Activity management |
| `resources/views/admin/oprs/dashboard.blade.php` | Create | OPRS dashboard |
| `resources/views/admin/oprs/users/index.blade.php` | Create | User list |
| `resources/views/admin/oprs/challenges/index.blade.php` | Create | Challenge list |
| `resources/views/admin/oprs/activities/index.blade.php` | Create | Activity list |

## Implementation Steps

### Step 1: Create Admin OprsController

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChallengeResult;
use App\Models\CommunityActivity;
use App\Models\User;
use App\Services\OprsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OprsController extends Controller
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * OPRS Dashboard
     */
    public function dashboard(): View
    {
        $stats = [
            'total_users' => User::where('total_oprs', '>', 0)->count(),
            'level_distribution' => $this->oprsService->getLevelDistribution(),
            'pending_challenges' => ChallengeResult::whereNull('verified_at')->count(),
            'recent_activities' => CommunityActivity::count(),
            'top_players' => $this->oprsService->getLeaderboard(null, 10),
        ];

        return view('admin.oprs.dashboard', compact('stats'));
    }

    /**
     * User OPRS list
     */
    public function users(Request $request): View
    {
        $query = User::query()
            ->where('total_oprs', '>', 0)
            ->orderByDesc('total_oprs');

        if ($request->filled('level')) {
            $query->where('opr_level', $request->level);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->paginate(25);

        return view('admin.oprs.users.index', [
            'users' => $users,
            'levels' => OprsService::OPR_LEVELS,
        ]);
    }

    /**
     * User OPRS detail
     */
    public function userDetail(User $user): View
    {
        $breakdown = $this->oprsService->getOprsBreakdown($user);
        $history = $user->oprsHistories()->orderByDesc('created_at')->take(20)->get();
        $challenges = $user->challengeResults()->orderByDesc('created_at')->take(10)->get();
        $activities = $user->communityActivities()->orderByDesc('created_at')->take(10)->get();

        return view('admin.oprs.users.detail', [
            'user' => $user,
            'breakdown' => $breakdown,
            'history' => $history,
            'challenges' => $challenges,
            'activities' => $activities,
        ]);
    }

    /**
     * Manual OPRS adjustment
     */
    public function adjustUser(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'component' => 'required|in:challenge,community',
            'amount' => 'required|numeric|min:-1000|max:1000',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $this->oprsService->adminAdjustment(
                $user,
                $request->component,
                (float) $request->amount,
                $request->reason
            );

            return back()->with('success', 'OPRS adjusted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Adjustment failed: ' . $e->getMessage());
        }
    }

    /**
     * Level distribution report
     */
    public function levelReport(): View
    {
        $distribution = $this->oprsService->getLevelDistribution();
        $total = array_sum($distribution);

        $data = [];
        foreach (OprsService::OPR_LEVELS as $level => $info) {
            $count = $distribution[$level] ?? 0;
            $data[$level] = [
                'name' => $info['name'],
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        return view('admin.oprs.reports.levels', [
            'data' => $data,
            'total' => $total,
        ]);
    }
}
```

### Step 2: Create Challenge Admin Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChallengeResult;
use App\Services\ChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OprsChallengeController extends Controller
{
    public function __construct(
        private ChallengeService $challengeService
    ) {}

    /**
     * Challenge list
     */
    public function index(Request $request): View
    {
        $query = ChallengeResult::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('verified_at');
            } elseif ($request->status === 'verified') {
                $query->whereNotNull('verified_at');
            }
        }

        if ($request->filled('type')) {
            $query->where('challenge_type', $request->type);
        }

        $challenges = $query->paginate(25);

        return view('admin.oprs.challenges.index', [
            'challenges' => $challenges,
            'types' => ChallengeResult::getAllTypes(),
        ]);
    }

    /**
     * Challenge detail
     */
    public function show(ChallengeResult $challenge): View
    {
        return view('admin.oprs.challenges.detail', [
            'challenge' => $challenge->load('user', 'verifier'),
        ]);
    }

    /**
     * Verify challenge
     */
    public function verify(ChallengeResult $challenge): RedirectResponse
    {
        try {
            $this->challengeService->verifyChallenge($challenge, auth()->user());
            return back()->with('success', 'Challenge verified');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject challenge (remove points)
     */
    public function reject(Request $request, ChallengeResult $challenge): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        // If points were awarded, reverse them
        if ($challenge->passed && $challenge->points_earned > 0) {
            $user = $challenge->user;
            $user->update([
                'challenge_score' => max(0, $user->challenge_score - $challenge->points_earned),
            ]);

            // Recalculate OPRS
            app(\App\Services\OprsService::class)->updateUserOprs(
                $user,
                'admin_rejected_challenge',
                ['challenge_id' => $challenge->id, 'reason' => $request->reason]
            );
        }

        $challenge->delete();

        return redirect()
            ->route('admin.oprs.challenges.index')
            ->with('success', 'Challenge rejected and removed');
    }
}
```

### Step 3: Create Activity Admin Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OprsActivityController extends Controller
{
    /**
     * Activity list
     */
    public function index(Request $request): View
    {
        $query = CommunityActivity::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('activity_type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $activities = $query->paginate(25);

        return view('admin.oprs.activities.index', [
            'activities' => $activities,
            'types' => CommunityActivity::getAllTypes(),
        ]);
    }

    /**
     * Remove activity (with point reversal)
     */
    public function destroy(Request $request, CommunityActivity $activity): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $user = $activity->user;

        // Reverse points
        $user->update([
            'community_score' => max(0, $user->community_score - $activity->points_earned),
        ]);

        // Recalculate OPRS
        app(\App\Services\OprsService::class)->updateUserOprs(
            $user,
            'admin_removed_activity',
            ['activity_id' => $activity->id, 'reason' => $request->reason]
        );

        $activity->delete();

        return back()->with('success', 'Activity removed and points reversed');
    }
}
```

### Step 4: Create Admin Routes

```php
// In routes/web.php, add to admin group:

Route::prefix('admin/oprs')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.oprs.')
    ->group(function () {
        Route::get('/', [Admin\OprsController::class, 'dashboard'])->name('dashboard');

        // User management
        Route::get('users', [Admin\OprsController::class, 'users'])->name('users.index');
        Route::get('users/{user}', [Admin\OprsController::class, 'userDetail'])->name('users.detail');
        Route::post('users/{user}/adjust', [Admin\OprsController::class, 'adjustUser'])->name('users.adjust');

        // Challenge management
        Route::get('challenges', [Admin\OprsChallengeController::class, 'index'])->name('challenges.index');
        Route::get('challenges/{challenge}', [Admin\OprsChallengeController::class, 'show'])->name('challenges.show');
        Route::post('challenges/{challenge}/verify', [Admin\OprsChallengeController::class, 'verify'])->name('challenges.verify');
        Route::post('challenges/{challenge}/reject', [Admin\OprsChallengeController::class, 'reject'])->name('challenges.reject');

        // Activity management
        Route::get('activities', [Admin\OprsActivityController::class, 'index'])->name('activities.index');
        Route::delete('activities/{activity}', [Admin\OprsActivityController::class, 'destroy'])->name('activities.destroy');

        // Reports
        Route::get('reports/levels', [Admin\OprsController::class, 'levelReport'])->name('reports.levels');
    });
```

### Step 5: Create Admin Dashboard View

```blade
{{-- resources/views/admin/oprs/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'OPRS Dashboard')

@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4">OPRS Dashboard</h1>

    {{-- Quick Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Players</h5>
                    <p class="display-4">{{ number_format($stats['total_users']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Pending Challenges</h5>
                    <p class="display-4">{{ $stats['pending_challenges'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Activities Today</h5>
                    <p class="display-4">{{ $stats['recent_activities'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Elite Players</h5>
                    <p class="display-4">{{ $stats['level_distribution']['5.0+'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Level Distribution --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Level Distribution</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Players</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = array_sum($stats['level_distribution']); @endphp
                            @foreach($stats['level_distribution'] as $level => $count)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $level }}</span></td>
                                <td>{{ $count }}</td>
                                <td>{{ $total > 0 ? round(($count/$total)*100, 1) : 0 }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Players --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top 10 Players</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Player</th>
                                <th>OPRS</th>
                                <th>Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['top_players'] as $index => $player)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.oprs.users.detail', $player) }}">
                                        {{ $player->name }}
                                    </a>
                                </td>
                                <td>{{ number_format($player->total_oprs, 0) }}</td>
                                <td><span class="badge bg-primary">{{ $player->opr_level }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.oprs.users.index') }}" class="btn btn-outline-primary me-2">
                        [USERS] Manage Users
                    </a>
                    <a href="{{ route('admin.oprs.challenges.index') }}" class="btn btn-outline-warning me-2">
                        [TARGET] Verify Challenges
                    </a>
                    <a href="{{ route('admin.oprs.activities.index') }}" class="btn btn-outline-info me-2">
                        [LIST] View Activities
                    </a>
                    <a href="{{ route('admin.oprs.reports.levels') }}" class="btn btn-outline-secondary">
                        [CHART] Level Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## Todo List

- [ ] Create Admin OprsController
- [ ] Create Admin OprsChallengeController
- [ ] Create Admin OprsActivityController
- [ ] Create admin routes
- [ ] Create dashboard view
- [ ] Create users list/detail views
- [ ] Create challenges list/detail views
- [ ] Create activities list view
- [ ] Create reports views
- [ ] Add OPRS menu to admin sidebar
- [ ] Test all admin functions

## Success Criteria

1. Dashboard shows key stats
2. User OPRS management works
3. Challenge verification flow works
4. Activity removal with point reversal works
5. Manual adjustments logged properly
6. Reports generate correctly

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Unauthorized adjustment | High | Role check + audit log |
| Point calculation error | Medium | Test reversal logic |
| Missing audit trail | High | Log all adjustments |

## Security Considerations

- Admin role required for all endpoints
- All adjustments logged with reason
- CSRF protection on forms
- Point reversals transaction-safe

## Next Steps

After admin panel complete:
1. Proceed to [Phase 9: Testing](./phase-09-testing.md)
2. Write unit tests for services
3. Write feature tests for endpoints
