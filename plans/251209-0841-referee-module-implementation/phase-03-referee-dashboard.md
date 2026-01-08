# Phase 3: Referee Dashboard

**Date**: 2025-12-09
**Status**: Completed
**Completion Date**: 2025-12-09
**Priority**: High
**Parent Plan**: [plan.md](./plan.md)
**Depends On**: [Phase 2 - Models & Relationships](./phase-02-models-relationships.md)

---

## Context

Build referee-facing dashboard for match management and score entry. Route group /referee/* protected by auth + role:referee middleware. Views mirror HomeYard layout structure for consistency. Dashboard shows only assigned matches with filters (tournament, status, date).

---

## Overview

1. Create RefereeController with dashboard, matches list, match detail
2. Add /referee/* route group with middleware
3. Create referee layout extending main layout
4. Build dashboard views: index, matches, match-detail
5. Implement match filters (tournament, status, date range)

---

## Key Insights from Research

**Dashboard UX Patterns**:
- Show only assigned matches (security + UX)
- Filter by tournament, status, date range
- Quick actions: start match, end match, enter score
- Match status badges (scheduled, in_progress, completed)

**From HomeYard Pattern**:
- Use existing layout structure (sidebar + main content)
- Dashboard card layout for match list
- Consistent navigation with conditional menu items

**Authorization Strategy**:
- Middleware: auth + role:referee on route group
- Controller checks: isAssignedToReferee() before showing match
- Policy pattern for score entry authorization

---

## Requirements

### Functional

1. Referee dashboard shows upcoming and recent matches
2. Match list filterable by tournament, status, date
3. Match detail shows full info with score entry form
4. Only assigned matches visible
5. Navigation menu shows "Trong tai" for referee role users

### Non-Functional

1. Responsive layout (mobile + desktop)
2. Performance: Eager load relationships to avoid N+1
3. Pagination for match lists (20 per page)
4. Form validation on score entry

---

## Related Files

### Controllers to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Front/RefereeController.php` | CREATE | Dashboard, matches, match detail |

### Routes to Add

| File | Action | Description |
|------|--------|-------------|
| `routes/web.php` | MODIFY | Add /referee/* route group |

### Views to Create

| File | Action | Description |
|------|--------|-------------|
| `resources/views/referee/dashboard.blade.php` | CREATE | Main dashboard with stats |
| `resources/views/referee/matches/index.blade.php` | CREATE | Match list with filters |
| `resources/views/referee/matches/show.blade.php` | CREATE | Match detail with score form |
| `resources/views/layouts/referee.blade.php` | CREATE | Referee layout with sidebar |
| `resources/views/layouts/partials/referee-sidebar.blade.php` | CREATE | Sidebar navigation |

---

## Implementation Steps

### Step 1: Create RefereeController

**File**: `app/Http/Controllers/Front/RefereeController.php`

```php
<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefereeController extends Controller
{
    /**
     * Dashboard with stats and upcoming matches
     */
    public function dashboard(): View
    {
        $referee = auth()->user();

        $stats = [
            'total_matches' => $referee->refereeMatches()->count(),
            'completed_matches' => $referee->refereeMatches()->where('status', 'completed')->count(),
            'upcoming_matches' => $referee->refereeMatches()
                ->where('status', 'scheduled')
                ->where('match_date', '>=', now())
                ->count(),
            'tournaments' => $referee->refereeTournaments()->count(),
        ];

        $upcomingMatches = $referee->refereeMatches()
            ->with(['tournament', 'athlete1', 'athlete2', 'category', 'court'])
            ->where('status', 'scheduled')
            ->where('match_date', '>=', now())
            ->orderBy('match_date')
            ->orderBy('match_time')
            ->limit(5)
            ->get();

        return view('referee.dashboard', compact('stats', 'upcomingMatches'));
    }

    /**
     * List all assigned matches with filters
     */
    public function matches(Request $request): View
    {
        $referee = auth()->user();

        $query = $referee->refereeMatches()
            ->with(['tournament', 'athlete1', 'athlete2', 'category', 'court']);

        // Filter by tournament
        if ($request->filled('tournament_id')) {
            $query->where('tournament_id', $request->tournament_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('match_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('match_date', '<=', $request->date_to);
        }

        $matches = $query->orderBy('match_date', 'desc')
            ->orderBy('match_time', 'desc')
            ->paginate(20);

        $tournaments = $referee->refereeTournaments;

        return view('referee.matches.index', compact('matches', 'tournaments'));
    }

    /**
     * Show match detail with score entry form
     */
    public function show(MatchModel $match): View
    {
        $referee = auth()->user();

        // Authorization check
        if (!$match->isAssignedToReferee($referee)) {
            abort(403, 'You are not assigned to this match');
        }

        $match->load([
            'tournament',
            'category',
            'round',
            'court',
            'athlete1',
            'athlete2',
            'winner',
        ]);

        return view('referee.matches.show', compact('match'));
    }
}
```

### Step 2: Add Routes

**File**: `routes/web.php`

Add after existing route groups:

```php
// Referee routes
Route::middleware(['auth', 'role:referee'])
    ->prefix('referee')
    ->name('referee.')
    ->group(function () {
        Route::get('dashboard', [Front\RefereeController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('matches', [Front\RefereeController::class, 'matches'])
            ->name('matches.index');
        Route::get('matches/{match}', [Front\RefereeController::class, 'show'])
            ->name('matches.show');
    });
```

### Step 3: Create Referee Layout

**File**: `resources/views/layouts/referee.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            @include('layouts.partials.referee-sidebar')
        </nav>

        {{-- Main content --}}
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('referee-content')
        </main>
    </div>
</div>
@endsection
```

**File**: `resources/views/layouts/partials/referee-sidebar.blade.php`

```blade
<div class="position-sticky pt-3">
    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
        <span>Trong tai Dashboard</span>
    </h6>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('referee.dashboard') ? 'active' : '' }}"
               href="{{ route('referee.dashboard') }}">
                [DASHBOARD] Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('referee.matches.*') ? 'active' : '' }}"
               href="{{ route('referee.matches.index') }}">
                [MATCH] Matches
            </a>
        </li>
    </ul>

    @if(auth()->user()->hasRole('home_yard'))
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Other Roles</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('homeyard.dashboard') }}">
                    [STADIUM] Home Yard
                </a>
            </li>
        </ul>
    @endif
</div>
```

### Step 4: Create Dashboard View

**File**: `resources/views/referee/dashboard.blade.php`

```blade
@extends('layouts.referee')

@section('referee-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Referee Dashboard</h1>
</div>

{{-- Stats Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Matches</h5>
                <p class="card-text display-6">{{ $stats['total_matches'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Completed</h5>
                <p class="card-text display-6">{{ $stats['completed_matches'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Upcoming</h5>
                <p class="card-text display-6">{{ $stats['upcoming_matches'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tournaments</h5>
                <p class="card-text display-6">{{ $stats['tournaments'] }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Upcoming Matches --}}
<h3 class="mb-3">Upcoming Matches</h3>
@if($upcomingMatches->isEmpty())
    <div class="alert alert-info">No upcoming matches assigned</div>
@else
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Tournament</th>
                    <th>Match</th>
                    <th>Court</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($upcomingMatches as $match)
                    <tr>
                        <td>{{ $match->match_date->format('Y-m-d') }}</td>
                        <td>{{ $match->match_time }}</td>
                        <td>{{ $match->tournament->name }}</td>
                        <td>
                            {{ $match->athlete1_name }} vs {{ $match->athlete2_name }}
                            <br><small class="text-muted">{{ $match->category->name }}</small>
                        </td>
                        <td>{{ $match->court->name ?? 'TBA' }}</td>
                        <td>
                            <a href="{{ route('referee.matches.show', $match) }}" class="btn btn-sm btn-primary">
                                View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
```

### Step 5: Create Matches List View

**File**: `resources/views/referee/matches/index.blade.php`

```blade
@extends('layouts.referee')

@section('referee-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Matches</h1>
</div>

{{-- Filters --}}
<form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
        <select name="tournament_id" class="form-select">
            <option value="">All Tournaments</option>
            @foreach($tournaments as $tournament)
                <option value="{{ $tournament->id }}"
                    {{ request('tournament_id') == $tournament->id ? 'selected' : '' }}>
                    {{ $tournament->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
    </div>
    <div class="col-md-2">
        <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
    </div>
    <div class="col-md-2">
        <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('referee.matches.index') }}" class="btn btn-secondary">Clear</a>
    </div>
</form>

{{-- Matches Table --}}
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Tournament</th>
                <th>Match</th>
                <th>Status</th>
                <th>Score</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($matches as $match)
                <tr>
                    <td>{{ $match->match_date->format('Y-m-d') }}<br>{{ $match->match_time }}</td>
                    <td>{{ $match->tournament->name }}</td>
                    <td>
                        {{ $match->athlete1_name }} vs {{ $match->athlete2_name }}
                        <br><small class="text-muted">{{ $match->category->name }}</small>
                    </td>
                    <td>
                        @if($match->status == 'scheduled')
                            <span class="badge bg-secondary">Scheduled</span>
                        @elseif($match->status == 'in_progress')
                            <span class="badge bg-warning">In Progress</span>
                        @elseif($match->status == 'completed')
                            <span class="badge bg-success">Completed</span>
                        @endif
                    </td>
                    <td>{{ $match->final_score ?? '-' }}</td>
                    <td>
                        <a href="{{ route('referee.matches.show', $match) }}" class="btn btn-sm btn-primary">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No matches found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $matches->links() }}
@endsection
```

### Step 6: Create Match Detail View (Score Entry in Phase 5)

**File**: `resources/views/referee/matches/show.blade.php`

```blade
@extends('layouts.referee')

@section('referee-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Match Detail</h1>
    <a href="{{ route('referee.matches.index') }}" class="btn btn-secondary">Back to Matches</a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h4>{{ $match->athlete1_name }} vs {{ $match->athlete2_name }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Tournament:</strong> {{ $match->tournament->name }}</p>
                <p><strong>Category:</strong> {{ $match->category->name }}</p>
                <p><strong>Round:</strong> {{ $match->round->name }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Date:</strong> {{ $match->match_date->format('Y-m-d') }}</p>
                <p><strong>Time:</strong> {{ $match->match_time }}</p>
                <p><strong>Court:</strong> {{ $match->court->name ?? 'TBA' }}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <p><strong>Status:</strong>
                    @if($match->status == 'scheduled')
                        <span class="badge bg-secondary">Scheduled</span>
                    @elseif($match->status == 'in_progress')
                        <span class="badge bg-warning">In Progress</span>
                    @elseif($match->status == 'completed')
                        <span class="badge bg-success">Completed</span>
                    @endif
                </p>
                @if($match->isCompleted())
                    <p><strong>Final Score:</strong> {{ $match->final_score }}</p>
                    <p><strong>Winner:</strong> {{ $match->winner->athlete_name ?? 'N/A' }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Score Entry Form (Phase 5) --}}
@if(!$match->isCompleted())
    <div class="card">
        <div class="card-header">
            <h5>Score Entry</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Score entry functionality will be available in Phase 5</p>
        </div>
    </div>
@endif
@endsection
```

---

## Todo List

- [ ] Create RefereeController with dashboard, matches, show methods
- [ ] Add /referee/* route group to web.php
- [ ] Create referee layout extending main layout
- [ ] Create referee sidebar partial
- [ ] Create dashboard view with stats cards
- [ ] Create matches index view with filters
- [ ] Create match detail view
- [ ] Update main navigation to show "Trong tai" menu for referee role
- [ ] Test authorization: non-referee cannot access /referee/*
- [ ] Test match filtering by tournament, status, date
- [ ] Verify pagination works
- [ ] Test responsive layout on mobile

---

## Success Criteria

- /referee/dashboard accessible only to referee role users
- Dashboard shows correct match counts
- Match list shows only assigned matches
- Filters work correctly (tournament, status, date)
- Match detail shows full info
- Authorization blocks access to non-assigned matches
- Responsive layout works on mobile and desktop

---

## Risk Assessment

**Risk**: N+1 query performance with match list
**Mitigation**: Eager load relationships: ->with(['tournament', 'athlete1', 'athlete2'])

**Risk**: Unauthorized access to other referees matches
**Mitigation**: isAssignedToReferee() check in controller

**Risk**: Layout inconsistency with HomeYard
**Mitigation**: Follow same structure, reuse partials where possible

---

## Security Considerations

- Route middleware: auth + role:referee
- Controller authorization check before showing match
- CSRF protection on forms
- No SQL injection (Eloquent ORM)

---

## Next Steps

After dashboard complete:
1. Phase 4: Tournament referee assignment by HomeYard users
2. Phase 5: Implement score entry form and validation
3. Test multi-role users see both HomeYard and Referee menus
