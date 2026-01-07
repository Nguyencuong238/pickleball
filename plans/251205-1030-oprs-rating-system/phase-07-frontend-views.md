# Phase 7: Frontend Views

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 6: API Endpoints](./phase-06-api-endpoints.md)
**Related Docs**: [code-standards.md](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2025-12-05 |
| Description | Build frontend Blade views for OPRS system |
| Priority | Medium |
| Implementation Status | Completed |
| Review Status | Completed (2025-12-06: Fixed Tailwind->Custom CSS) |

## Key Insights

1. Extend existing OCR views (profile, leaderboard, matches)
2. Add OPRS components to user profile
3. Create challenge center view
4. Create community hub view
5. Use existing layout and component patterns

## Requirements

### Functional
- Player profile with OPRS breakdown
- OPRS leaderboard with level filtering
- Challenge center for submitting tests
- Community hub for activities
- OPRS history chart
- Level progress indicator

### Non-Functional
- Responsive design
- Consistent with existing UI
- Clear visual hierarchy
- Accessible components

## Architecture

### View Structure

```
resources/views/
├── front/
│   └── ocr/
│       ├── profile.blade.php (modify - add OPRS)
│       ├── leaderboard.blade.php (modify - add OPRS)
│       ├── challenges/
│       │   ├── index.blade.php (new)
│       │   ├── submit.blade.php (new)
│       │   └── history.blade.php (new)
│       └── community/
│           ├── index.blade.php (new)
│           ├── check-in.blade.php (new)
│           └── history.blade.php (new)
└── components/
    └── oprs/
        ├── score-card.blade.php (new)
        ├── level-badge.blade.php (new)
        ├── breakdown-chart.blade.php (new)
        ├── progress-bar.blade.php (new)
        └── history-chart.blade.php (new)
```

## Related Code Files

| File | Action | Purpose |
|------|--------|---------|
| `resources/views/components/oprs/score-card.blade.php` | Create | OPRS score display card |
| `resources/views/components/oprs/level-badge.blade.php` | Create | OPR Level badge |
| `resources/views/components/oprs/breakdown-chart.blade.php` | Create | Component breakdown |
| `resources/views/front/ocr/profile.blade.php` | Modify | Add OPRS section |
| `resources/views/front/ocr/leaderboard.blade.php` | Modify | Add OPRS sorting |
| `resources/views/front/ocr/challenges/index.blade.php` | Create | Challenge center |
| `resources/views/front/ocr/community/index.blade.php` | Create | Community hub |
| `app/Http/Controllers/Front/OcrController.php` | Modify | Add OPRS data |

## Implementation Steps

### Step 1: Create OPRS Score Card Component

```blade
{{-- resources/views/components/oprs/score-card.blade.php --}}
@props(['user', 'breakdown'])

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">OPRS Score</h3>
        <x-oprs.level-badge :level="$user->opr_level" />
    </div>

    <div class="text-center mb-6">
        <span class="text-4xl font-bold text-blue-600">{{ number_format($user->total_oprs, 0) }}</span>
        <p class="text-sm text-gray-500 mt-1">OnePickleball Rating Score</p>
    </div>

    {{-- Component Breakdown --}}
    <div class="space-y-4">
        {{-- Elo Component --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600">[TROPHY]</span>
                <span class="text-sm font-medium">Elo (70%)</span>
            </div>
            <div class="text-right">
                <span class="font-semibold">{{ number_format($breakdown['elo']['weighted'], 0) }}</span>
                <span class="text-xs text-gray-500 ml-1">({{ $breakdown['elo']['raw'] }})</span>
            </div>
        </div>

        {{-- Challenge Component --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600">[TARGET]</span>
                <span class="text-sm font-medium">Challenge (20%)</span>
            </div>
            <div class="text-right">
                <span class="font-semibold">{{ number_format($breakdown['challenge']['weighted'], 0) }}</span>
                <span class="text-xs text-gray-500 ml-1">({{ $breakdown['challenge']['raw'] }})</span>
            </div>
        </div>

        {{-- Community Component --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-gray-600">[USERS]</span>
                <span class="text-sm font-medium">Community (10%)</span>
            </div>
            <div class="text-right">
                <span class="font-semibold">{{ number_format($breakdown['community']['weighted'], 0) }}</span>
                <span class="text-xs text-gray-500 ml-1">({{ $breakdown['community']['raw'] }})</span>
            </div>
        </div>
    </div>

    {{-- Level Progress --}}
    @if(isset($breakdown['next_level']))
    <div class="mt-6 pt-4 border-t">
        <div class="flex justify-between text-sm mb-2">
            <span class="text-gray-600">Next: {{ $breakdown['next_level']['name'] }}</span>
            <span class="text-gray-600">{{ $breakdown['points_to_next'] }} pts needed</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $breakdown['progress_percent'] }}%"></div>
        </div>
    </div>
    @endif
</div>
```

### Step 2: Create Level Badge Component

```blade
{{-- resources/views/components/oprs/level-badge.blade.php --}}
@props(['level', 'size' => 'md'])

@php
$levels = [
    '1.0' => ['name' => 'Beginner', 'color' => 'gray'],
    '2.0' => ['name' => 'Novice', 'color' => 'green'],
    '3.0' => ['name' => 'Intermediate', 'color' => 'blue'],
    '3.5' => ['name' => 'Upper Intermediate', 'color' => 'indigo'],
    '4.0' => ['name' => 'Advanced', 'color' => 'purple'],
    '4.5' => ['name' => 'Pro', 'color' => 'orange'],
    '5.0+' => ['name' => 'Elite', 'color' => 'red'],
];
$info = $levels[$level] ?? $levels['1.0'];
$sizeClasses = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-3 py-1 text-sm',
    'lg' => 'px-4 py-2 text-base',
];
@endphp

<span class="inline-flex items-center rounded-full font-medium bg-{{ $info['color'] }}-100 text-{{ $info['color'] }}-800 {{ $sizeClasses[$size] }}">
    <span class="font-bold mr-1">{{ $level }}</span>
    <span>{{ $info['name'] }}</span>
</span>
```

### Step 3: Update Profile View

```blade
{{-- Add to resources/views/front/ocr/profile.blade.php --}}

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: User Info --}}
        <div class="lg:col-span-1">
            {{-- Existing user card --}}
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="text-center">
                    <div class="w-24 h-24 mx-auto rounded-full bg-gray-200 flex items-center justify-center mb-4">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" class="w-24 h-24 rounded-full object-cover">
                        @else
                            <span class="text-2xl font-bold text-gray-500">{{ $user->getInitials() }}</span>
                        @endif
                    </div>
                    <h2 class="text-xl font-bold">{{ $user->name }}</h2>
                    <x-oprs.level-badge :level="$user->opr_level" size="lg" class="mt-2" />
                </div>
            </div>

            {{-- OPRS Score Card --}}
            <x-oprs.score-card :user="$user" :breakdown="$breakdown" />
        </div>

        {{-- Right Column: Stats & History --}}
        <div class="lg:col-span-2">
            {{-- Quick Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-md p-4 text-center">
                    <span class="text-2xl font-bold text-green-600">{{ $user->ocr_wins }}</span>
                    <p class="text-sm text-gray-500">Wins</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 text-center">
                    <span class="text-2xl font-bold text-red-600">{{ $user->ocr_losses }}</span>
                    <p class="text-sm text-gray-500">Losses</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 text-center">
                    <span class="text-2xl font-bold text-blue-600">{{ $user->win_rate }}%</span>
                    <p class="text-sm text-gray-500">Win Rate</p>
                </div>
            </div>

            {{-- Tabs: Matches, Challenges, Activities --}}
            <div class="bg-white rounded-lg shadow-md">
                <div class="border-b">
                    <nav class="flex -mb-px">
                        <button class="tab-btn active px-6 py-3 font-medium" data-tab="matches">
                            Matches
                        </button>
                        <button class="tab-btn px-6 py-3 font-medium" data-tab="challenges">
                            Challenges
                        </button>
                        <button class="tab-btn px-6 py-3 font-medium" data-tab="activities">
                            Activities
                        </button>
                    </nav>
                </div>

                {{-- Tab Content --}}
                <div id="tab-matches" class="tab-content p-4">
                    {{-- Match history --}}
                </div>
                <div id="tab-challenges" class="tab-content p-4 hidden">
                    {{-- Challenge history --}}
                </div>
                <div id="tab-activities" class="tab-content p-4 hidden">
                    {{-- Activity history --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### Step 4: Create Challenge Center View

```blade
{{-- resources/views/front/ocr/challenges/index.blade.php --}}
@extends('layouts.frontend')

@section('title', 'Challenge Center')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Challenge Center</h1>

    {{-- Current Stats --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">Your Challenge Score</h3>
                <p class="text-3xl font-bold text-blue-600">{{ number_format($user->challenge_score, 0) }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Total Challenges Passed</p>
                <p class="text-2xl font-semibold">{{ $stats['passed'] }}</p>
            </div>
        </div>
    </div>

    {{-- Available Challenges --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @foreach($availableChallenges as $type => $challenge)
        <div class="bg-white rounded-lg shadow-md p-6 {{ !$challenge['available'] ? 'opacity-50' : '' }}">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">{{ $challenge['info']['icon'] }}</span>
                    <div>
                        <h3 class="font-semibold">{{ $challenge['info']['name'] }}</h3>
                        <p class="text-sm text-gray-500">{{ $challenge['info']['description'] }}</p>
                    </div>
                </div>
                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-medium">
                    +{{ $challenge['info']['points'] }} pts
                </span>
            </div>

            @if($challenge['available'])
                <a href="{{ route('ocr.challenges.submit', $type) }}"
                   class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                    Start Challenge
                </a>
            @else
                <p class="text-center text-gray-500 py-2">{{ $challenge['reason'] }}</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Recent Challenge History --}}
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold">Recent Challenges</h3>
        </div>
        <div class="divide-y">
            @forelse($history as $result)
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="{{ $result->passed ? 'text-green-600' : 'text-red-600' }}">
                        {{ $result->passed ? '[CHECK]' : '[X]' }}
                    </span>
                    <div>
                        <p class="font-medium">{{ \App\Models\ChallengeResult::getChallengeInfo($result->challenge_type)['name'] }}</p>
                        <p class="text-sm text-gray-500">Score: {{ $result->score }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($result->passed)
                        <span class="text-green-600 font-medium">+{{ $result->points_earned }}</span>
                    @else
                        <span class="text-gray-400">0</span>
                    @endif
                    <p class="text-xs text-gray-400">{{ $result->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">
                No challenges yet. Start your first challenge!
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
```

### Step 5: Create Community Hub View

```blade
{{-- resources/views/front/ocr/community/index.blade.php --}}
@extends('layouts.frontend')

@section('title', 'Community Hub')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Community Hub</h1>

    {{-- Current Stats --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">Your Community Score</h3>
                <p class="text-3xl font-bold text-purple-600">{{ number_format($user->community_score, 0) }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Activities This Month</p>
                <p class="text-2xl font-semibold">{{ $stats['recent_count'] }}</p>
            </div>
        </div>
    </div>

    {{-- Activity Options --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {{-- Check-in --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center space-x-3 mb-4">
                <span class="text-2xl">[LOCATION]</span>
                <div>
                    <h3 class="font-semibold">Check-in</h3>
                    <p class="text-sm text-gray-500">+2 pts per check-in</p>
                </div>
            </div>
            <a href="{{ route('ocr.community.checkin') }}"
               class="block w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700">
                Check-in Now
            </a>
        </div>

        {{-- Referral --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center space-x-3 mb-4">
                <span class="text-2xl">[USER_PLUS]</span>
                <div>
                    <h3 class="font-semibold">Refer a Friend</h3>
                    <p class="text-sm text-gray-500">+10 pts per referral</p>
                </div>
            </div>
            <button onclick="copyReferralLink()"
                    class="w-full text-center bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700">
                Copy Referral Link
            </button>
        </div>

        {{-- Weekly Challenge --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center space-x-3 mb-4">
                <span class="text-2xl">[CALENDAR]</span>
                <div>
                    <h3 class="font-semibold">Weekly 5 Matches</h3>
                    <p class="text-sm text-gray-500">+5 pts (auto-awarded)</p>
                </div>
            </div>
            <div class="text-center">
                <p class="text-sm">{{ $weeklyMatchCount }}/5 matches this week</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min(100, ($weeklyMatchCount/5)*100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold">Recent Activities</h3>
        </div>
        <div class="divide-y">
            @forelse($history as $activity)
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-xl">{{ \App\Models\CommunityActivity::getActivityInfo($activity->activity_type)['icon'] }}</span>
                    <div>
                        <p class="font-medium">{{ \App\Models\CommunityActivity::getActivityInfo($activity->activity_type)['name'] }}</p>
                        @if($activity->metadata)
                            <p class="text-sm text-gray-500">
                                {{ $activity->metadata['stadium_name'] ?? $activity->metadata['event_name'] ?? '' }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-purple-600 font-medium">+{{ $activity->points_earned }}</span>
                    <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-500">
                No activities yet. Start earning points!
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
```

### Step 6: Update Controller

```php
// In app/Http/Controllers/Front/OcrController.php

use App\Services\OprsService;
use App\Services\ChallengeService;
use App\Services\CommunityService;

class OcrController extends Controller
{
    public function __construct(
        private OprsService $oprsService,
        private ChallengeService $challengeService,
        private CommunityService $communityService
    ) {}

    public function profile(User $user)
    {
        $breakdown = $this->oprsService->getOprsBreakdown($user);

        return view('front.ocr.profile', [
            'user' => $user,
            'breakdown' => $breakdown,
            'matches' => $user->getAllOcrMatches()->take(10),
            'badges' => $user->badges()->orderByDesc('earned_at')->get(),
        ]);
    }

    public function challenges()
    {
        $user = auth()->user();

        return view('front.ocr.challenges.index', [
            'user' => $user,
            'availableChallenges' => $this->challengeService->getAvailableChallenges($user),
            'stats' => $this->challengeService->getChallengeStats($user),
            'history' => $this->challengeService->getChallengeHistory($user, 10),
        ]);
    }

    public function community()
    {
        $user = auth()->user();

        return view('front.ocr.community.index', [
            'user' => $user,
            'stats' => $this->communityService->getActivityStats($user),
            'history' => $this->communityService->getActivityHistory($user, 10),
            'weeklyMatchCount' => // calculate from OCR matches
        ]);
    }
}
```

## Todo List

- [x] Create OPRS component templates (score-card, level-badge)
- [x] Update profile view with OPRS section
- [x] Create challenge center view
- [x] Create community hub view
- [x] Update leaderboard view for OPRS sorting
- [x] Update OcrController with OPRS data
- [x] Add routes for new views
- [x] Test responsive design
- [x] Test all view data bindings
- [x] Fix Tailwind CSS to Custom CSS (2025-12-06)

## Success Criteria

1. Profile shows OPRS breakdown correctly
2. Level badge displays appropriate color
3. Challenge center functional
4. Community hub shows all activity types
5. All views responsive
6. Consistent styling with existing UI

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Styling inconsistency | Low | Use existing patterns |
| Data binding errors | Medium | Test all scenarios |
| Performance on history | Low | Limit records loaded |

## Security Considerations

- No sensitive data in public views
- CSRF on all forms
- Auth check on protected pages

## Next Steps

After frontend complete:
1. Proceed to [Phase 8: Admin Panel](./phase-08-admin-panel.md)
2. Build admin challenge management
3. Build admin activity management
