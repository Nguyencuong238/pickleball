# Phase 5: Admin Dashboard, Member Management & Leaderboard

**Priority**: P2-P3 | **Effort**: Medium | **Status**: Complete
**Depends on**: Phase 3 (Queue & Matchmaking)

## Context

- UX Research: Features 4, 5, 6 (Dashboard, Members, Leaderboard)
- Existing: Tournament dashboard pattern (`td-` prefix CSS) - reuse layout approach
- Existing: ClubActivityController has CRUD for activities

## Requirements

### 5A: Admin Activity Dashboard
- Live view: courts status, queue, match progress
- Trigger matchmaking manually
- Mark matches complete
- 3-column desktop layout, single column mobile
- AJAX polling 8s

### 5B: Admin Member Management
- Add members by phone/email
- Set initial OPRS
- Manage member_status (active/inactive/suspended)
- Inline OPRS edit
- Table -> card responsive

### 5C: Club Leaderboard
- Rankings by OPRS (default sort)
- Filter: "Thang nay" / "Tat ca"
- Win rate as hero stat
- Podium for top 3 (desktop)
- Own row sticky on mobile

## Related Code Files

### Modify
- `app/Http/Controllers/ClubActivityController.php` - add dashboard view
- `routes/web.php` - add dashboard/members/leaderboard routes

### Create
- `app/Http/Controllers/ClubLeaderboardController.php`
- `resources/views/home-yard/clubs/activity-dashboard.blade.php`
- `resources/views/home-yard/clubs/members.blade.php`
- `resources/views/front/clubs/leaderboard.blade.php`
- `public/assets/css/club-activity-dashboard.css`
- `public/assets/css/club-activity-members.css`
- `public/assets/css/club-activity-leaderboard.css`
- `public/assets/js/club-activity-dashboard.js`
- `public/assets/js/club-activity-members.js`
- `public/assets/js/club-activity-leaderboard.js`

## Implementation Steps

### 5A: Admin Dashboard

**Controller** (in ClubActivityController):
```php
public function dashboard(Club $club, ClubActivity $activity)
{
    // Auth: must be club admin/moderator
    return view('home-yard.clubs.activity-dashboard', compact('club', 'activity'));
}

public function dashboardState(Club $club, ClubActivity $activity)
{
    // JSON endpoint for polling (8s)
    return response()->json([
        'courts' => $this->getCourtStatus($activity),
        'queue' => $activity->participants()->queued()->with('user:id,name,total_oprs')->orderBy('queue_position')->get(),
        'playing' => $activity->participants()->playing()->with('user:id,name')->get(),
        'stats' => [
            'total_checked_in' => $activity->participants()->checkedIn()->count(),
            'total_matches' => $activity->matches()->count(),
            'queued_count' => $activity->participants()->queued()->count(),
            'playing_count' => $activity->participants()->playing()->count(),
        ],
        'recent_events' => $this->getRecentEvents($activity, 10),
    ]);
}
```

**View**: 3-column grid layout
- Left sidebar: activity controls (start/pause/end), player counts, rotation config
- Main: court cards grid (each card = match info or empty state)
- Right panel: queue list with "Ghep tran tu dong" button, event log

**Alpine**: `caAdminDashboard(config)` with `Alpine.store('activity', {...})`
- 8s recursive setTimeout polling
- triggerMatchmaking() -> POST + confirmation modal
- completeMatch(matchId) -> inline action on court card

**CSS**: 3-column grid `240px 1fr 280px`, collapses at 1024px
- `.ca-dashboard` full-height grid
- `.ca-court-card-admin` with status border-left
- Hover reveals `.ca-court-actions`

### 5B: Member Management

**Controller** (new methods in ClubActivityController or separate):
```php
public function members(Club $club)
{
    $members = $club->members()
        ->withPivot('role', 'initial_oprs', 'notes', 'member_status')
        ->with('user:id,name,phone,email,total_oprs,opr_level')
        ->paginate(20);
    return view('home-yard.clubs.members', compact('club', 'members'));
}

public function addMember(Club $club, Request $request)
{
    // Validate phone or email
    // Lookup user -> if found, add to club_members
    // If not found -> create user + add
    // Set initial_oprs if provided
}

public function updateMemberOprs(Club $club, User $user, Request $request)
{
    // Inline OPRS update
    $club->members()->updateExistingPivot($user->id, [
        'initial_oprs' => $request->initial_oprs,
    ]);
}

public function updateMemberStatus(Club $club, User $user, Request $request)
{
    $club->members()->updateExistingPivot($user->id, [
        'member_status' => $request->member_status,
    ]);
}
```

**View**: Top bar (search + filters + CTA) + data table
- Responsive table -> card at 768px via `data-label` CSS trick
- Slide-in drawer for "Them thanh vien"

**Alpine**: `caMembers(config)` - search (debounce 300ms), filter, inline edit, drawer toggle

**CSS**: Table styles, inline edit, drawer animation

### 5C: Leaderboard

**Controller**: `ClubLeaderboardController`
```php
public function index(Club $club, Request $request)
{
    $period = $request->get('period', 'month');

    $query = ClubMemberStat::where('club_id', $club->id)
        ->with('user:id,name,avatar,total_oprs,opr_level');

    if ($period === 'month') {
        $query->where('last_played_at', '>=', now()->startOfMonth());
    }

    $leaderboard = $query->orderByDesc('current_oprs')->get();

    if ($request->wantsJson()) {
        return response()->json($leaderboard);
    }

    return view('front.clubs.leaderboard', compact('club', 'leaderboard', 'period'));
}
```

**View**: Filter tabs + podium (desktop) + ranked list/table
- Mobile: card per player with rank number, win bar, OPRS chip
- Own row sticky at bottom

**Alpine**: `caLeaderboard(config)` - period toggle, search, expand row for details

**CSS**: Podium flex layout, rank colors (gold/silver/bronze), win rate bar, sticky own-row

### Routes

```php
// Admin routes (auth + club admin/moderator)
Route::middleware(['auth'])->prefix('homeyard/clubs/{club:slug}')->group(function () {
    Route::get('/activities/{activity}/dashboard', [ClubActivityController::class, 'dashboard'])->name('club.activity.dashboard');
    Route::get('/activities/{activity}/dashboard-state', [ClubActivityController::class, 'dashboardState'])->name('club.activity.dashboard-state');
    Route::get('/members', [ClubActivityController::class, 'members'])->name('club.members');
    Route::post('/members/add', [ClubActivityController::class, 'addMember'])->name('club.members.add');
    Route::patch('/members/{user}/oprs', [ClubActivityController::class, 'updateMemberOprs'])->name('club.members.update-oprs');
    Route::patch('/members/{user}/status', [ClubActivityController::class, 'updateMemberStatus'])->name('club.members.update-status');
});

// Public leaderboard
Route::get('/clubs/{club:slug}/leaderboard', [ClubLeaderboardController::class, 'index'])->name('club.leaderboard');
```

## Todo

### 5A: Dashboard
- [x] Add dashboard + dashboardState to ClubActivityController
- [x] Create activity-dashboard.blade.php (3-column layout)
- [x] Create club-activity-dashboard.js (Alpine store + 8s polling)
- [x] Create club-activity-dashboard.css
- [x] Add matchmaking trigger + confirmation modal
- [x] Add inline match complete action

### 5B: Members
- [x] Add members/addMember/updateMemberOprs/updateMemberStatus methods
- [x] Create members.blade.php (table + drawer)
- [x] Create club-activity-members.js (search, filter, inline edit)
- [x] Create club-activity-members.css (responsive table, drawer)

### 5C: Leaderboard
- [x] Create ClubLeaderboardController
- [x] Create leaderboard.blade.php (podium + ranked list)
- [x] Create club-activity-leaderboard.js (period toggle, expand)
- [x] Create club-activity-leaderboard.css (podium, rank colors, sticky)

### Routes
- [x] Add all routes to web.php

## Success Criteria

- Admin dashboard shows live court/queue/stats with 8s polling
- Admin can trigger matchmaking with preview modal
- Member management: search, inline OPRS edit, status change, add by phone
- Leaderboard: OPRS-sorted, period filter, own-row sticky on mobile
- All views responsive (desktop + mobile)

## Risk

- Dashboard polling at 8s may be too aggressive for many concurrent admins -> consider 10s
- Member management routes need proper authorization middleware
- Leaderboard "month" filter needs index on last_played_at for performance
