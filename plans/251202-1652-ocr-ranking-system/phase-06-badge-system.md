# Phase 6: Badge System

## Context Links

- [Parent Plan](./plan.md)
- [Phase 5: Match Workflow](./phase-05-match-workflow.md)
- [Phase 3: Elo Service](./phase-03-elo-service.md)
- [Code Standards](../../docs/code-standards.md)

## Overview

- **Date**: 2025-12-02
- **Priority**: Medium
- **Implementation Status**: Pending
- **Review Status**: Pending
- **Dependencies**: Phase 5 (Match Workflow)

Detail the badge/achievement system with criteria, display, and admin management.

## Key Insights

1. Badge system already structured in Phase 2 (UserBadge model)
2. Badge checking in Phase 3 (BadgeService)
3. This phase focuses on badge display and admin tools

## Requirements

### Functional

- Display user badges on profile
- Badge progress tracking
- Admin badge management (manual award/revoke)
- Badge notification on earn

### Non-Functional

- Badges cached for display
- Icon placeholders (no emoji per user instruction)

## Architecture

### Badge Categories

| Category | Badges | Description |
|----------|--------|-------------|
| First Milestones | `first_win` | First match victory |
| Win Streaks | `streak_3`, `streak_5`, `streak_10` | Consecutive wins |
| Rank Achievements | `rank_silver`, `rank_gold`, `rank_platinum`, `rank_diamond` | Reaching ranks |
| Match Count | `matches_10`, `matches_50`, `matches_100` | Total matches played |

### Badge Display

```
┌─────────────────────────────────────────────┐
│  [TROPHY] First Blood                       │
│  Won first match                            │
│  Earned: Dec 1, 2025                        │
└─────────────────────────────────────────────┘
```

## Related Code Files

### Files to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Admin/OcrBadgeController.php` | Create | Admin badge management |
| `resources/views/admin/ocr/badges/index.blade.php` | Create | Badge list view |
| `resources/views/components/ocr-badge.blade.php` | Create | Badge display component |

### Files to Modify

| File | Action | Description |
|------|--------|-------------|
| `app/Services/BadgeService.php` | Modify | Add admin methods |
| `routes/web.php` | Modify | Add admin badge routes |

## Implementation Steps

### Step 1: Extend BadgeService

Add to `app/Services/BadgeService.php`:

```php
/**
 * Revoke a badge from user
 */
public function revokeBadge(User $user, string $badgeType): bool
{
    return $user->badges()->where('badge_type', $badgeType)->delete() > 0;
}

/**
 * Get badge progress for user
 */
public function getBadgeProgress(User $user): array
{
    $progress = [];

    // Streak progress (current streak out of next milestone)
    $currentStreak = $this->getCurrentWinStreak($user);
    $nextStreakMilestone = match (true) {
        $currentStreak < 3 => 3,
        $currentStreak < 5 => 5,
        $currentStreak < 10 => 10,
        default => null,
    };
    if ($nextStreakMilestone) {
        $progress['streak'] = [
            'current' => $currentStreak,
            'target' => $nextStreakMilestone,
            'percent' => round(($currentStreak / $nextStreakMilestone) * 100),
        ];
    }

    // Match count progress
    $total = $user->total_ocr_matches;
    $nextMatchMilestone = match (true) {
        $total < 10 => 10,
        $total < 50 => 50,
        $total < 100 => 100,
        default => null,
    };
    if ($nextMatchMilestone) {
        $progress['matches'] = [
            'current' => $total,
            'target' => $nextMatchMilestone,
            'percent' => round(($total / $nextMatchMilestone) * 100),
        ];
    }

    // Rank progress
    $ranks = User::getEloRanks();
    $currentRank = $user->elo_rank;
    $currentElo = $user->elo_rating;

    // Find next rank
    $rankKeys = array_keys($ranks);
    $currentIndex = array_search($currentRank, $rankKeys);
    if ($currentIndex !== false && $currentIndex < count($rankKeys) - 1) {
        $nextRank = $rankKeys[$currentIndex + 1];
        $nextRankMin = $ranks[$nextRank]['min'];
        $currentRankMin = $ranks[$currentRank]['min'];

        $progress['rank'] = [
            'current_rank' => $currentRank,
            'current_elo' => $currentElo,
            'next_rank' => $nextRank,
            'next_rank_min' => $nextRankMin,
            'points_needed' => $nextRankMin - $currentElo,
            'percent' => round((($currentElo - $currentRankMin) / ($nextRankMin - $currentRankMin)) * 100),
        ];
    }

    return $progress;
}

/**
 * Get all users with specific badge
 */
public function getUsersWithBadge(string $badgeType): \Illuminate\Database\Eloquent\Collection
{
    return User::whereHas('badges', fn($q) => $q->where('badge_type', $badgeType))
        ->with(['badges' => fn($q) => $q->where('badge_type', $badgeType)])
        ->get();
}
```

### Step 2: Create Admin Badge Controller

```php
<?php
// app/Http/Controllers/Admin/OcrBadgeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBadge;
use App\Services\BadgeService;
use Illuminate\Http\Request;

class OcrBadgeController extends Controller
{
    public function __construct(private BadgeService $badgeService)
    {
    }

    /**
     * List all badge types and counts
     */
    public function index()
    {
        $badgeTypes = $this->badgeService->getAllBadgeTypes();

        $badges = collect($badgeTypes)->map(function ($type) {
            $info = UserBadge::getBadgeInfo($type);
            return [
                'type' => $type,
                'name' => $info['name'],
                'description' => $info['description'],
                'icon' => $info['icon'],
                'count' => UserBadge::where('badge_type', $type)->count(),
            ];
        });

        return view('admin.ocr.badges.index', compact('badges'));
    }

    /**
     * Show users with specific badge
     */
    public function show(string $badgeType)
    {
        $info = UserBadge::getBadgeInfo($badgeType);
        $users = $this->badgeService->getUsersWithBadge($badgeType);

        return view('admin.ocr.badges.show', compact('badgeType', 'info', 'users'));
    }

    /**
     * Award badge to user
     */
    public function award(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'badge_type' => 'required|string',
        ]);

        $user = User::find($validated['user_id']);

        if ($user->hasBadge($validated['badge_type'])) {
            return back()->with('error', 'User already has this badge');
        }

        $this->badgeService->awardBadge($user, $validated['badge_type'], [
            'awarded_by' => 'admin',
            'awarded_at' => now()->toISOString(),
        ]);

        return back()->with('success', "Badge awarded to {$user->name}");
    }

    /**
     * Revoke badge from user
     */
    public function revoke(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'badge_type' => 'required|string',
        ]);

        $user = User::find($validated['user_id']);

        if (!$user->hasBadge($validated['badge_type'])) {
            return back()->with('error', 'User does not have this badge');
        }

        $this->badgeService->revokeBadge($user, $validated['badge_type']);

        return back()->with('success', "Badge revoked from {$user->name}");
    }
}
```

### Step 3: Create Badge Component

```blade
{{-- resources/views/components/ocr-badge.blade.php --}}
@props(['badge', 'size' => 'md'])

@php
$sizeClasses = [
    'sm' => 'w-8 h-8 text-xs',
    'md' => 'w-12 h-12 text-sm',
    'lg' => 'w-16 h-16 text-base',
];

$containerSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex flex-col items-center']) }}>
    <div class="rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 {{ $containerSize }} flex items-center justify-center text-white font-bold shadow-lg"
         title="{{ $badge->description }}">
        {{ $badge->icon }}
    </div>
    @if($size !== 'sm')
        <span class="mt-1 text-xs font-medium text-gray-700">{{ $badge->name }}</span>
    @endif
</div>
```

### Step 4: Create Admin Badge Index View

```blade
{{-- resources/views/admin/ocr/badges/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'OCR Badges')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">OCR Badge Management</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($badges as $badge)
        <div class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition">
            <a href="{{ route('admin.ocr.badges.show', $badge['type']) }}" class="block">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-white font-bold">
                        {{ $badge['icon'] }}
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $badge['name'] }}</h3>
                        <p class="text-sm text-gray-500">{{ $badge['description'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $badge['count'] }} users</p>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    {{-- Award Badge Form --}}
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Manually Award Badge</h2>
        <form action="{{ route('admin.ocr.badges.award') }}" method="POST" class="flex gap-4 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
                <input type="number" name="user_id" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Badge Type</label>
                <select name="badge_type" class="w-full border rounded px-3 py-2" required>
                    @foreach($badges as $badge)
                        <option value="{{ $badge['type'] }}">{{ $badge['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Award
            </button>
        </form>
    </div>
</div>
@endsection
```

### Step 5: Add Admin Routes

Add to `routes/web.php` in admin group:

```php
// OCR Badge Management
Route::prefix('ocr/badges')->name('ocr.badges.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\OcrBadgeController::class, 'index'])->name('index');
    Route::get('/{badgeType}', [App\Http\Controllers\Admin\OcrBadgeController::class, 'show'])->name('show');
    Route::post('/award', [App\Http\Controllers\Admin\OcrBadgeController::class, 'award'])->name('award');
    Route::post('/revoke', [App\Http\Controllers\Admin\OcrBadgeController::class, 'revoke'])->name('revoke');
});
```

## Todo List

- [ ] Extend BadgeService with progress/revoke methods
- [ ] Create OcrBadgeController
- [ ] Create ocr-badge Blade component
- [ ] Create admin badges index view
- [ ] Create admin badges show view
- [ ] Add admin badge routes
- [ ] Test badge display and admin functions

## Success Criteria

1. Users can view their badges and progress
2. Admin can view all badges and awardees
3. Admin can manually award/revoke badges
4. Badge component displays consistently

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Badge inflation from manual awards | Low | Audit log admin actions |
| Missing badge criteria | Low | Document all criteria |

## Security Considerations

- Only admin can manually award/revoke
- Badge revoke is soft (deletes record)
- No sensitive data in badge metadata

## Next Steps

After badge system complete, proceed to [Phase 7: Frontend Views](./phase-07-frontend-views.md)
