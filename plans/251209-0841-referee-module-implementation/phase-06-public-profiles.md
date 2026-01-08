# Phase 6: Public Referee Profiles

**Date**: 2025-12-09
**Status**: Completed
**Completion Date**: 2025-12-09
**Priority**: Medium
**Parent Plan**: [plan.md](./plan.md)
**Depends On**: [Phase 5 - Match Management](./phase-05-match-management.md)

---

## Context

Public-facing referee directory under /academy/referees following instructor profile pattern. List page shows all active referees with avatar, name, matches_officiated count, status. Detail page shows full bio, match history, rating (phase 2). Accessible to all users (no auth required).

---

## Overview

1. Create RefereeProfileController for public pages
2. Add /academy/referees routes (list, detail)
3. Create referee list view with filters (status, search)
4. Create referee detail view with stats and match history
5. Update Academy navigation to include "Trong tai" menu item

---

## Key Insights from Research

**Profile UX Patterns**:
- List view: card/grid layout with avatars, stats
- Detail view: hero section + tabs (bio, stats, history)
- Match history paginated, filterable by tournament
- Rating system (future enhancement: phase 2)

**From Instructor Pattern**:
- Public routes under /academy prefix
- No auth required (public profiles)
- Card grid layout for list view
- Detail view shows related records (matches)

**Performance Considerations**:
- Eager load relationships on list (avoid N+1)
- Cache matches_officiated count (update via observer)
- Paginate match history (20 per page)

---

## Requirements

### Functional

1. /academy/referees shows all active referees
2. List filterable by status, searchable by name
3. /academy/referees/{id} shows referee detail with bio, stats
4. Match history shows completed matches with scores
5. Stats: total matches, completion rate, avg rating (future)

### Non-Functional

1. Public access (no auth required)
2. Responsive layout (mobile + desktop)
3. Performance: Eager loading, caching
4. SEO-friendly URLs

---

## Related Files

### Controllers to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Front/RefereeProfileController.php` | CREATE | Public referee list and detail |

### Routes to Add

| File | Action | Description |
|------|--------|-------------|
| `routes/web.php` | MODIFY | Add /academy/referees routes |

### Views to Create

| File | Action | Description |
|------|--------|-------------|
| `resources/views/front/referees/index.blade.php` | CREATE | Referee list with filters |
| `resources/views/front/referees/show.blade.php` | CREATE | Referee detail with history |

### Views to Modify

| File | Action | Description |
|------|--------|-------------|
| `resources/views/layouts/partials/navigation.blade.php` | MODIFY | Add Academy > Referees link |

---

## Implementation Steps

### Step 1: Create RefereeProfileController

**File**: `app/Http/Controllers/Front/RefereeProfileController.php`

```php
<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MatchModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefereeProfileController extends Controller
{
    /**
     * Display list of active referees
     */
    public function index(Request $request): View
    {
        $query = User::role('referee')
            ->where('referee_status', 'active');

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('referee_status', $request->status);
        }

        $referees = $query->withCount([
            'refereeMatches as matches_completed' => function ($q) {
                $q->where('status', 'completed');
            }
        ])
        ->orderBy('name')
        ->paginate(12);

        return view('front.referees.index', compact('referees'));
    }

    /**
     * Display referee profile detail
     */
    public function show(User $referee): View
    {
        // Verify user has referee role
        if (!$referee->hasRole('referee')) {
            abort(404, 'Referee not found');
        }

        // Load relationships
        $referee->load([
            'refereeMatches' => function ($query) {
                $query->where('status', 'completed')
                    ->with(['tournament', 'athlete1', 'athlete2', 'category'])
                    ->latest('match_date')
                    ->limit(20);
            },
            'refereeTournaments'
        ]);

        // Calculate stats
        $stats = [
            'total_matches' => $referee->refereeMatches()->count(),
            'completed_matches' => $referee->refereeMatches()->where('status', 'completed')->count(),
            'upcoming_matches' => $referee->refereeMatches()
                ->where('status', 'scheduled')
                ->where('match_date', '>=', now())
                ->count(),
            'tournaments' => $referee->refereeTournaments()->count(),
            'completion_rate' => $this->calculateCompletionRate($referee),
            'avg_rating' => $referee->referee_rating ?? 0,
        ];

        return view('front.referees.show', compact('referee', 'stats'));
    }

    /**
     * Calculate completion rate
     */
    private function calculateCompletionRate(User $referee): float
    {
        $total = $referee->refereeMatches()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $referee->refereeMatches()->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 1);
    }
}
```

### Step 2: Add Routes

**File**: `routes/web.php`

Add after existing public routes:

```php
// Public Referee Profiles (Academy section)
Route::prefix('academy')->name('academy.')->group(function () {
    Route::get('referees', [Front\RefereeProfileController::class, 'index'])
        ->name('referees.index');
    Route::get('referees/{referee}', [Front\RefereeProfileController::class, 'show'])
        ->name('referees.show');
});
```

### Step 3: Create Referee List View

**File**: `resources/views/front/referees/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Referees Directory - Pickleball Platform')

@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Referees Directory</h1>
    </div>

    {{-- Search and Filter --}}
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by name"
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('academy.referees.index') }}" class="btn btn-secondary">Clear</a>
        </div>
    </form>

    {{-- Referee Cards --}}
    @if($referees->isEmpty())
        <div class="alert alert-info">No referees found</div>
    @else
        <div class="row">
            @foreach($referees as $referee)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            {{-- Avatar --}}
                            @if($referee->avatar)
                                <img src="{{ asset('storage/' . $referee->avatar) }}"
                                     alt="{{ $referee->name }}"
                                     class="rounded-circle mb-3"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 100px; height: 100px; font-size: 2rem;">
                                    {{ substr($referee->name, 0, 1) }}
                                </div>
                            @endif

                            <h5 class="card-title">{{ $referee->name }}</h5>

                            @if($referee->location)
                                <p class="text-muted small">[LOCATION] {{ $referee->location }}</p>
                            @endif

                            <div class="mb-3">
                                <span class="badge bg-primary">
                                    {{ $referee->matches_completed ?? 0 }} matches officiated
                                </span>

                                @if($referee->referee_status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>

                            @if($referee->referee_rating)
                                <div class="mb-3">
                                    <span class="text-warning">[STAR]</span>
                                    <strong>{{ number_format($referee->referee_rating, 1) }}</strong> / 5.0
                                </div>
                            @endif

                            <a href="{{ route('academy.referees.show', $referee) }}" class="btn btn-primary btn-sm">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $referees->links() }}
    @endif
</div>
@endsection
```

### Step 4: Create Referee Detail View

**File**: `resources/views/front/referees/show.blade.php`

```blade
@extends('layouts.app')

@section('title', $referee->name . ' - Referee Profile')

@section('content')
<div class="container my-5">
    {{-- Back Button --}}
    <a href="{{ route('academy.referees.index') }}" class="btn btn-secondary mb-3">
        [ARROW-LEFT] Back to Referees
    </a>

    {{-- Profile Header --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    @if($referee->avatar)
                        <img src="{{ asset('storage/' . $referee->avatar) }}"
                             alt="{{ $referee->name }}"
                             class="rounded-circle mb-3"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mb-3"
                             style="width: 150px; height: 150px; font-size: 3rem;">
                            {{ substr($referee->name, 0, 1) }}
                        </div>
                    @endif

                    @if($referee->referee_status == 'active')
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>

                <div class="col-md-9">
                    <h1>{{ $referee->name }}</h1>

                    @if($referee->location)
                        <p class="text-muted">[LOCATION] {{ $referee->location }}</p>
                    @endif

                    @if($referee->referee_bio)
                        <div class="mt-3">
                            <h5>About</h5>
                            <p>{{ $referee->referee_bio }}</p>
                        </div>
                    @endif

                    @if($referee->referee_rating)
                        <div class="mt-3">
                            <span class="text-warning">[STAR]</span>
                            <strong>{{ number_format($referee->referee_rating, 1) }}</strong> / 5.0 Rating
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $stats['total_matches'] }}</h3>
                    <p class="mb-0">Total Matches</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $stats['completed_matches'] }}</h3>
                    <p class="mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $stats['completion_rate'] }}%</h3>
                    <p class="mb-0">Completion Rate</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning">{{ $stats['tournaments'] }}</h3>
                    <p class="mb-0">Tournaments</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Matches --}}
    <div class="card">
        <div class="card-header">
            <h4>Recent Matches</h4>
        </div>
        <div class="card-body">
            @if($referee->refereeMatches->isEmpty())
                <p class="text-muted">No completed matches yet</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Tournament</th>
                                <th>Match</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referee->refereeMatches as $match)
                                <tr>
                                    <td>{{ $match->match_date->format('Y-m-d') }}</td>
                                    <td>
                                        {{ $match->tournament->name }}
                                        <br><small class="text-muted">{{ $match->category->name }}</small>
                                    </td>
                                    <td>{{ $match->athlete1_name }} vs {{ $match->athlete2_name }}</td>
                                    <td>{{ $match->final_score ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
```

### Step 5: Update Navigation

**File**: `resources/views/layouts/partials/navigation.blade.php`

Add to Academy section (if exists) or create new dropdown:

```blade
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
        Academy
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="{{ route('academy.referees.index') }}">
            [USER] Referees
        </a></li>
        {{-- Other academy links if exist --}}
    </ul>
</li>
```

---

## Todo List

- [x] Create RefereeProfileController with index and show methods
- [x] Add calculateCompletionRate() helper method
- [x] Add /academy/referees routes
- [x] Create referee list view with search/filter
- [x] Create referee detail view with stats and match history
- [x] Update navigation with Academy > Referees link
- [x] Test search by name functionality
- [x] Test filter by status functionality
- [x] Verify pagination works
- [x] Test detail page shows correct match history
- [x] Verify responsive layout on mobile
- [x] Test public access (no auth required)

---

## Success Criteria

- /academy/referees accessible without auth
- List shows all active referees with stats
- Search by name filters correctly
- Detail page shows full profile with bio and match history
- Stats cards show accurate counts
- Match history paginated and displays scores
- Responsive layout works on mobile and desktop
- Navigation includes Academy > Referees link

---

## Risk Assessment

**Risk**: N+1 queries with match history
**Mitigation**: Eager load relationships: ->with(['tournament', 'athlete1', 'athlete2'])

**Risk**: Slow page load with many matches
**Mitigation**: Limit match history to 20, paginate if needed

**Risk**: SEO issues with referee profiles
**Mitigation**: Add meta tags, structured data (future enhancement)

---

## Security Considerations

- Public access allowed (no auth required)
- Verify user has referee role in show() method
- No sensitive data exposed (only public profile info)
- XSS protection via Blade escaping
- Rate limiting on search endpoints (optional)

---

## Unresolved Questions

1. **Rating System**: Should rating be based on tournament director feedback or automated?
2. **Profile Editing**: Should referees edit own bio via dashboard or admin only?
3. **Advanced Stats**: Add win rate for athletes, avg match duration, dispute rate?
4. **Certification Display**: Show referee certifications/qualifications on public profile?

---

## Future Enhancements (Phase 2)

- Rating/review system for referees
- Referee certification badges
- Advanced analytics (match duration avg, dispute rate)
- Export referee stats to PDF
- Referee availability calendar on public profile
