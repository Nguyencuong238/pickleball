# Phase 7: Frontend Views

## Context Links

- [Parent Plan](./plan.md)
- [Phase 6: Badge System](./phase-06-badge-system.md)
- [Existing Views](../../resources/views/)
- [Code Standards](../../docs/code-standards.md)

## Overview

- **Date**: 2025-12-02
- **Priority**: Medium
- **Implementation Status**: Pending
- **Review Status**: Pending
- **Dependencies**: Phase 6 (Badge System)

Create user-facing frontend views for OCR matches, leaderboard, and user profiles.

## Key Insights

1. Follow existing Blade template patterns
2. Use existing layouts (layouts/frontend.blade.php)
3. Responsive design with Tailwind CSS
4. AJAX for match actions (accept, submit result, etc.)

## Requirements

### Functional

- OCR landing page with leaderboard preview
- Match list with filters
- Match detail with actions
- Create match form
- User OCR profile with stats/badges
- Full leaderboard page

### Non-Functional

- Responsive (mobile-first)
- Loading states for AJAX
- Form validation feedback
- Consistent with existing UI

## Architecture

### View Structure

```
resources/views/
└── front/
    └── ocr/
        ├── index.blade.php          # OCR landing page
        ├── matches/
        │   ├── index.blade.php      # Match list
        │   ├── show.blade.php       # Match detail
        │   └── create.blade.php     # Create match
        ├── leaderboard.blade.php    # Full leaderboard
        └── profile.blade.php        # User OCR profile
```

### Route Structure

```
/ocr                    - Landing page
/ocr/matches           - Match list
/ocr/matches/create    - Create match
/ocr/matches/:id       - Match detail
/ocr/leaderboard       - Full leaderboard
/ocr/profile/:id       - User profile
```

## Related Code Files

### Files to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Front/OcrController.php` | Create | Web frontend controller |
| `resources/views/front/ocr/index.blade.php` | Create | Landing page |
| `resources/views/front/ocr/matches/index.blade.php` | Create | Match list |
| `resources/views/front/ocr/matches/show.blade.php` | Create | Match detail |
| `resources/views/front/ocr/matches/create.blade.php` | Create | Create form |
| `resources/views/front/ocr/leaderboard.blade.php` | Create | Leaderboard |
| `resources/views/front/ocr/profile.blade.php` | Create | User profile |

### Files to Modify

| File | Action | Description |
|------|--------|-------------|
| `routes/web.php` | Modify | Add OCR web routes |

## Implementation Steps

### Step 1: Create OcrController

```php
<?php
// app/Http/Controllers/Front/OcrController.php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\OcrMatch;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\EloService;
use Illuminate\Http\Request;

class OcrController extends Controller
{
    public function __construct(
        private EloService $eloService,
        private BadgeService $badgeService
    ) {
    }

    /**
     * OCR landing page
     */
    public function index()
    {
        // Top 10 leaderboard
        $topPlayers = User::where('total_ocr_matches', '>', 0)
            ->orderBy('elo_rating', 'desc')
            ->take(10)
            ->get();

        // Recent matches
        $recentMatches = OcrMatch::where('status', OcrMatch::STATUS_CONFIRMED)
            ->with(['challenger', 'opponent'])
            ->orderBy('confirmed_at', 'desc')
            ->take(5)
            ->get();

        // User's position if logged in
        $userRank = null;
        if (auth()->check()) {
            $userRank = User::where('elo_rating', '>', auth()->user()->elo_rating)
                ->where('total_ocr_matches', '>', 0)
                ->count() + 1;
        }

        return view('front.ocr.index', compact('topPlayers', 'recentMatches', 'userRank'));
    }

    /**
     * Match list
     */
    public function matchIndex(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status');

        $query = OcrMatch::forUser($user->id)
            ->with(['challenger', 'opponent', 'challengerPartner', 'opponentPartner'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $matches = $query->paginate(20);

        return view('front.ocr.matches.index', compact('matches', 'status'));
    }

    /**
     * Match detail
     */
    public function matchShow(OcrMatch $match)
    {
        $user = auth()->user();

        if (!$match->isParticipant($user->id)) {
            abort(403, 'You are not a participant of this match');
        }

        $match->load(['challenger', 'opponent', 'challengerPartner', 'opponentPartner', 'media']);

        // Win probability
        $winProbability = null;
        if (in_array($match->status, [OcrMatch::STATUS_PENDING, OcrMatch::STATUS_ACCEPTED])) {
            $winProbability = [
                'challenger' => $this->eloService->getWinProbability($match->challenger, $match->opponent),
                'opponent' => $this->eloService->getWinProbability($match->opponent, $match->challenger),
            ];
        }

        return view('front.ocr.matches.show', compact('match', 'winProbability'));
    }

    /**
     * Create match form
     */
    public function matchCreate()
    {
        return view('front.ocr.matches.create');
    }

    /**
     * Search users for opponent selection
     */
    public function searchUsers(Request $request)
    {
        $query = $request->query('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', auth()->id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'name', 'email', 'elo_rating', 'elo_rank']);

        return response()->json($users);
    }

    /**
     * Full leaderboard
     */
    public function leaderboard(Request $request)
    {
        $rank = $request->query('rank');

        $query = User::where('total_ocr_matches', '>', 0)
            ->orderBy('elo_rating', 'desc');

        if ($rank && in_array(ucfirst($rank), array_keys(User::getEloRanks()))) {
            $query->where('elo_rank', ucfirst($rank));
        }

        $players = $query->paginate(50);

        // User's position
        $userRank = null;
        if (auth()->check() && auth()->user()->total_ocr_matches > 0) {
            $userRank = User::where('elo_rating', '>', auth()->user()->elo_rating)
                ->where('total_ocr_matches', '>', 0)
                ->count() + 1;
        }

        $ranks = array_keys(User::getEloRanks());

        return view('front.ocr.leaderboard', compact('players', 'rank', 'userRank', 'ranks'));
    }

    /**
     * User OCR profile
     */
    public function profile(User $user)
    {
        $user->load('badges');

        $recentMatches = OcrMatch::forUser($user->id)
            ->where('status', OcrMatch::STATUS_CONFIRMED)
            ->with(['challenger', 'opponent'])
            ->orderBy('confirmed_at', 'desc')
            ->take(10)
            ->get();

        $eloHistory = $user->eloHistories()
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $badgeProgress = $this->badgeService->getBadgeProgress($user);

        // Global rank
        $globalRank = User::where('elo_rating', '>', $user->elo_rating)
            ->where('total_ocr_matches', '>', 0)
            ->count() + 1;

        return view('front.ocr.profile', compact('user', 'recentMatches', 'eloHistory', 'badgeProgress', 'globalRank'));
    }
}
```

### Step 2: Create Landing Page View

```blade
{{-- resources/views/front/ocr/index.blade.php --}}
@extends('layouts.frontend')

@section('title', 'OCR - OnePickleball Championship Ranking')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Hero Section --}}
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl text-white p-8 mb-8">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">OnePickleball Championship Ranking</h1>
        <p class="text-lg opacity-90 mb-6">Compete, earn Elo points, and climb the leaderboard!</p>
        @auth
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('ocr.matches.create') }}" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Challenge a Player
                </a>
                <a href="{{ route('ocr.matches.index') }}" class="border border-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition">
                    My Matches
                </a>
            </div>
            @if($userRank)
                <p class="mt-4 text-sm opacity-75">Your current rank: #{{ $userRank }}</p>
            @endif
        @else
            <a href="{{ route('login') }}" class="inline-block bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Login to Start Playing
            </a>
        @endauth
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        {{-- Top 10 Leaderboard --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Top Players</h2>
                <a href="{{ route('ocr.leaderboard') }}" class="text-blue-600 text-sm hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @foreach($topPlayers as $index => $player)
                <div class="flex items-center gap-4 p-3 rounded-lg {{ $index < 3 ? 'bg-yellow-50' : 'bg-gray-50' }}">
                    <span class="text-lg font-bold text-gray-500 w-6">#{{ $index + 1 }}</span>
                    <div class="flex-1">
                        <p class="font-semibold">{{ $player->name }}</p>
                        <p class="text-sm text-gray-500">{{ $player->elo_rank }} - {{ $player->elo_rating }} Elo</p>
                    </div>
                    <span class="text-sm text-gray-400">{{ $player->ocr_wins }}W {{ $player->ocr_losses }}L</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Matches --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Recent Matches</h2>
            <div class="space-y-3">
                @forelse($recentMatches as $match)
                <div class="border rounded-lg p-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="{{ $match->winner_team === 'challenger' ? 'font-bold text-green-600' : '' }}">
                                {{ $match->challenger->name }}
                            </span>
                            <span class="text-gray-400 mx-2">vs</span>
                            <span class="{{ $match->winner_team === 'opponent' ? 'font-bold text-green-600' : '' }}">
                                {{ $match->opponent->name }}
                            </span>
                        </div>
                        <span class="text-sm font-semibold">
                            {{ $match->challenger_score }} - {{ $match->opponent_score }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $match->confirmed_at->diffForHumans() }}
                        @if($match->elo_change)
                            - {{ $match->elo_change > 0 ? '+' : '' }}{{ $match->elo_change }} Elo
                        @endif
                    </p>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No matches yet</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- How It Works --}}
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">How OCR Works</h2>
        <div class="grid md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">[1]</span>
                </div>
                <h3 class="font-semibold">Challenge</h3>
                <p class="text-sm text-gray-500">Invite any player to a match</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">[2]</span>
                </div>
                <h3 class="font-semibold">Play</h3>
                <p class="text-sm text-gray-500">Meet and compete</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">[3]</span>
                </div>
                <h3 class="font-semibold">Report</h3>
                <p class="text-sm text-gray-500">Submit and confirm result</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">[4]</span>
                </div>
                <h3 class="font-semibold">Rank Up</h3>
                <p class="text-sm text-gray-500">Earn Elo and badges</p>
            </div>
        </div>
    </div>
</div>
@endsection
```

### Step 3: Create Match List View

```blade
{{-- resources/views/front/ocr/matches/index.blade.php --}}
@extends('layouts.frontend')

@section('title', 'My Matches - OCR')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">My Matches</h1>
        <a href="{{ route('ocr.matches.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            New Match
        </a>
    </div>

    {{-- Filters --}}
    <div class="mb-6 flex gap-2 flex-wrap">
        <a href="{{ route('ocr.matches.index') }}"
           class="px-4 py-2 rounded-lg {{ !$status ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
            All
        </a>
        @foreach(['pending', 'accepted', 'result_submitted', 'confirmed', 'disputed'] as $s)
        <a href="{{ route('ocr.matches.index', ['status' => $s]) }}"
           class="px-4 py-2 rounded-lg {{ $status === $s ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
            {{ ucfirst(str_replace('_', ' ', $s)) }}
        </a>
        @endforeach
    </div>

    {{-- Match List --}}
    <div class="space-y-4">
        @forelse($matches as $match)
        <a href="{{ route('ocr.matches.show', $match) }}" class="block bg-white rounded-lg shadow p-4 hover:shadow-lg transition">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ match($match->status) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'accepted' => 'bg-blue-100 text-blue-800',
                                'in_progress' => 'bg-purple-100 text-purple-800',
                                'result_submitted' => 'bg-orange-100 text-orange-800',
                                'confirmed' => 'bg-green-100 text-green-800',
                                'disputed' => 'bg-red-100 text-red-800',
                                'cancelled' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100'
                            } }}">
                            {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                        </span>
                        <span class="text-sm text-gray-500">{{ $match->match_type }}</span>
                    </div>
                    <p class="font-semibold">
                        {{ $match->challenger->name }}
                        @if($match->challengerPartner)
                            & {{ $match->challengerPartner->name }}
                        @endif
                        <span class="text-gray-400 mx-2">vs</span>
                        {{ $match->opponent->name }}
                        @if($match->opponentPartner)
                            & {{ $match->opponentPartner->name }}
                        @endif
                    </p>
                    @if($match->scheduled_date)
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $match->scheduled_date->format('M d, Y') }}
                        @if($match->scheduled_time)
                            at {{ $match->scheduled_time }}
                        @endif
                        @if($match->location)
                            - {{ $match->location }}
                        @endif
                    </p>
                    @endif
                </div>
                @if($match->status === 'confirmed')
                <div class="text-right">
                    <p class="text-2xl font-bold">{{ $match->challenger_score }} - {{ $match->opponent_score }}</p>
                    @if($match->elo_change)
                    <p class="text-sm {{ $match->winner_team === 'challenger' && $match->isChallengerTeam(auth()->id()) ? 'text-green-600' : 'text-red-600' }}">
                        {{ $match->elo_change > 0 ? '+' : '' }}{{ $match->elo_change }} Elo
                    </p>
                    @endif
                </div>
                @endif
            </div>
        </a>
        @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500 mb-4">No matches found</p>
            <a href="{{ route('ocr.matches.create') }}" class="text-blue-600 hover:underline">Create your first match</a>
        </div>
        @endforelse
    </div>

    {{ $matches->links() }}
</div>
@endsection
```

### Step 4: Add Web Routes

Add to `routes/web.php`:

```php
// OCR Frontend Routes
Route::prefix('ocr')->name('ocr.')->group(function () {
    // Public routes
    Route::get('/', [App\Http\Controllers\Front\OcrController::class, 'index'])->name('index');
    Route::get('/leaderboard', [App\Http\Controllers\Front\OcrController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/profile/{user}', [App\Http\Controllers\Front\OcrController::class, 'profile'])->name('profile');

    // Auth required routes
    Route::middleware('auth')->group(function () {
        Route::get('/matches', [App\Http\Controllers\Front\OcrController::class, 'matchIndex'])->name('matches.index');
        Route::get('/matches/create', [App\Http\Controllers\Front\OcrController::class, 'matchCreate'])->name('matches.create');
        Route::get('/matches/{match}', [App\Http\Controllers\Front\OcrController::class, 'matchShow'])->name('matches.show');
        Route::get('/users/search', [App\Http\Controllers\Front\OcrController::class, 'searchUsers'])->name('users.search');
    });
});
```

## Todo List

- [ ] Create OcrController (web frontend)
- [ ] Create OCR landing page view
- [ ] Create match list view
- [ ] Create match detail view
- [ ] Create match create form view
- [ ] Create leaderboard view
- [ ] Create user profile view
- [ ] Add web routes
- [ ] Test all pages responsive

## Success Criteria

1. All pages render correctly
2. Mobile responsive
3. AJAX actions work (accept, submit result)
4. Navigation intuitive
5. Consistent with existing UI

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Inconsistent UI | Medium | Follow existing patterns |
| AJAX errors not handled | Medium | Toast notifications |
| SEO issues | Low | Proper meta tags |

## Security Considerations

- Auth middleware on sensitive pages
- CSRF tokens on forms
- User search limited to non-sensitive fields
- Match access restricted to participants

## Next Steps

After frontend complete, consider:
1. Unit tests for services
2. Feature tests for API
3. E2E tests for critical flows
4. Performance optimization
5. Documentation update

## Unresolved Questions

1. Should match creation use AJAX or form submit?
2. Need real-time updates for match status? (WebSockets)
3. Mobile app considerations for API design?
