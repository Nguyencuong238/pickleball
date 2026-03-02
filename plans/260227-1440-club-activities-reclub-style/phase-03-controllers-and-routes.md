# Phase 3: Controllers & Routes

## Context Links
- Current controller: `app/Http/Controllers/ClubActivityController.php` (185 lines)
- Club controller: `app/Http/Controllers/ClubController.php` (351 lines)
- Routes: `routes/web.php` lines 297-305
- ClubPolicy: `app/Policies/ClubPolicy.php`

## Overview
- **Priority:** P1
- **Status:** complete
- Update `ClubActivityController` for type-aware create/store/update
- Create `ClubActivityParticipantController` for RSVP actions
- Create `ClubCompetitionController` for competition management
- Update `ClubPolicy` to use `isManagement()` for activity operations
- Add new routes under existing `clubs/{club}/activities` prefix

## Requirements
- Management (creator/admin/moderator) can create/edit/delete activities
- Any club member can RSVP to activities
- Competition scoring: management only
- All RSVP/competition actions are AJAX (JSON responses)

## Related Code Files

### Files to MODIFY:
- `app/Http/Controllers/ClubActivityController.php` -- add type handling to create/store/update
- `app/Policies/ClubPolicy.php` -- add `manageActivity` method using `isManagement()`
- `routes/web.php` -- add participant + competition routes

### Files to CREATE:
- `app/Http/Controllers/ClubActivityParticipantController.php`
- `app/Http/Controllers/ClubCompetitionController.php`

## Implementation Steps

### Step 1: Update ClubPolicy

```php
// Add to ClubPolicy.php
public function manageActivity(User $user, Club $club): bool
{
    return $club->isManagement($user);
}

public function joinActivity(User $user, Club $club): bool
{
    return $club->isMember($user);
}
```

### Step 2: Update ClubActivityController

**Modify `create()`**: Pass activity types to view.

**Modify `store()`**: Accept type-specific fields.
```php
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'type' => 'required|in:one_off,recurring,competition',
    'activity_date' => 'required|date_format:Y-m-d\TH:i',
    'end_time' => 'nullable|date_format:H:i',
    'location' => 'nullable|string|max:255',
    'max_participants' => 'nullable|integer|min:2|max:200',
    'status' => 'required|in:upcoming,completed,cancelled',
    'auto_approve' => 'boolean',
    'min_skill_level' => 'nullable|numeric|min:1.0|max:6.0',
    'max_skill_level' => 'nullable|numeric|min:1.0|max:6.0|gte:min_skill_level',
    // Recurring-specific
    'recurrence_day' => 'required_if:type,recurring|integer|min:0|max:6',
    // Competition-specific
    'competition_config' => 'nullable|array',
    'competition_config.points_for_win' => 'nullable|integer|min:0',
    'competition_config.points_for_loss' => 'nullable|integer|min:0',
]);
$validated['created_by'] = Auth::id();
```

**Modify authorization**: Use `$this->authorize('manageActivity', $club)` instead of `$this->authorize('update', $club)`.

**Modify `update()`**: Same validation changes, prevent type change after creation.

### Step 3: Create ClubActivityParticipantController

```php
// app/Http/Controllers/ClubActivityParticipantController.php
class ClubActivityParticipantController extends Controller
{
    public function __construct(private ClubActivityService $service) {}

    // POST /clubs/{club}/activities/{activity}/rsvp
    public function rsvp(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('joinActivity', $club);
        $participant = $this->service->rsvp($activity, Auth::user());
        return response()->json([
            'success' => true,
            'status' => $participant->status,
            'message' => $participant->status === 'confirmed'
                ? 'Dang ky thanh cong!'
                : 'Ban da duoc them vao danh sach cho.',
        ]);
    }

    // DELETE /clubs/{club}/activities/{activity}/rsvp
    public function cancelRsvp(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('joinActivity', $club);
        $this->service->cancelRsvp($activity, Auth::user());
        return response()->json(['success' => true, 'message' => 'Da huy dang ky.']);
    }

    // GET /clubs/{club}/activities/{activity}/participants
    public function index(Club $club, ClubActivity $activity): JsonResponse
    {
        $confirmed = $activity->confirmedParticipants()->with('user')->get();
        $waitlisted = $activity->waitlistedParticipants()->with('user')->get();
        return response()->json([
            'confirmed' => $confirmed,
            'waitlisted' => $waitlisted,
            'spots_left' => $activity->spotsLeft(),
        ]);
    }
}
```

### Step 4: Create ClubCompetitionController

```php
// app/Http/Controllers/ClubCompetitionController.php
class ClubCompetitionController extends Controller
{
    public function __construct(private ClubCompetitionService $service) {}

    // POST /clubs/{club}/activities/{activity}/competition/teams
    public function addTeam(Request $request, Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'captain_user_id' => 'nullable|exists:users,id',
        ]);
        $team = $activity->competitionTeams()->create($validated + ['status' => 'active']);
        return response()->json(['success' => true, 'team' => $team]);
    }

    // DELETE /clubs/{club}/activities/{activity}/competition/teams/{team}
    public function removeTeam(Club $club, ClubActivity $activity, ClubCompetitionTeam $team): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $team->delete();
        return response()->json(['success' => true]);
    }

    // POST /clubs/{club}/activities/{activity}/competition/generate-schedule
    // <!-- Updated: Validation Session 1 - accepts format parameter for 3 competition formats -->
    public function generateSchedule(Request $request, Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $validated = $request->validate([
            'format' => 'required|in:round_robin,pool_play,single_elimination',
        ]);
        $this->service->generateSchedule($activity, $validated['format']);
        $this->service->initializeStandings($activity);
        return response()->json(['success' => true, 'message' => 'Lich thi dau da duoc tao.']);
    }

    // PUT /clubs/{club}/activities/{activity}/competition/matches/{match}/score
    public function saveScore(Request $request, Club $club, ClubActivity $activity, ClubCompetitionMatch $match): JsonResponse
    {
        $this->authorize('manageActivity', $club);
        $validated = $request->validate([
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
        ]);
        $this->service->saveMatchScore($match, $validated['home_score'], $validated['away_score']);
        return response()->json(['success' => true]);
    }

    // GET /clubs/{club}/activities/{activity}/competition/standings
    public function standings(Club $club, ClubActivity $activity): JsonResponse
    {
        $standings = $activity->competitionStandings()
            ->with('team')
            ->orderByDesc('points')
            ->orderByDesc('wins')
            ->get();
        return response()->json(['standings' => $standings]);
    }

    // GET /clubs/{club}/activities/{activity}/competition/matches
    public function matches(Club $club, ClubActivity $activity): JsonResponse
    {
        $matches = $activity->competitionMatches()
            ->with(['homeTeam', 'awayTeam', 'winnerTeam'])
            ->orderBy('round_number')
            ->get();
        return response()->json(['matches' => $matches]);
    }
}
```

### Step 5: Update Routes

```php
// routes/web.php -- inside auth middleware group
// Replace existing club activities routes with:
Route::prefix('clubs/{club}/activities')->name('clubs.activities.')->group(function () {
    // Existing CRUD
    Route::get('/', [ClubActivityController::class, 'index'])->name('index');
    Route::get('create', [ClubActivityController::class, 'create'])->name('create');
    Route::post('/', [ClubActivityController::class, 'store'])->name('store');
    Route::get('{activity}/edit', [ClubActivityController::class, 'edit'])->name('edit');
    Route::put('{activity}', [ClubActivityController::class, 'update'])->name('update');
    Route::delete('{activity}', [ClubActivityController::class, 'destroy'])->name('destroy');
    Route::get('{activity}', [ClubActivityController::class, 'show'])->name('show');

    // RSVP / Participants
    Route::post('{activity}/rsvp', [ClubActivityParticipantController::class, 'rsvp'])->name('rsvp');
    Route::delete('{activity}/rsvp', [ClubActivityParticipantController::class, 'cancelRsvp'])->name('cancel-rsvp');
    Route::get('{activity}/participants', [ClubActivityParticipantController::class, 'index'])->name('participants');

    // Competition
    Route::prefix('{activity}/competition')->name('competition.')->group(function () {
        Route::post('teams', [ClubCompetitionController::class, 'addTeam'])->name('add-team');
        Route::delete('teams/{team}', [ClubCompetitionController::class, 'removeTeam'])->name('remove-team');
        Route::post('generate-schedule', [ClubCompetitionController::class, 'generateSchedule'])->name('generate-schedule');
        Route::put('matches/{match}/score', [ClubCompetitionController::class, 'saveScore'])->name('save-score');
        Route::get('standings', [ClubCompetitionController::class, 'standings'])->name('standings');
        Route::get('matches', [ClubCompetitionController::class, 'matches'])->name('matches');
    });
});
```

## Todo List
- [x] Update ClubPolicy with manageActivity and joinActivity methods
- [x] Update ClubActivityController create/store/update for type-aware fields
- [x] Change authorization from 'update' to 'manageActivity' in activity controller
- [x] Create ClubActivityParticipantController
- [x] Create ClubCompetitionController (with activity-belongs-to-club validation)
- [x] Update routes/web.php with 16 routes (CRUD + RSVP + Competition)
- [x] Add use statements for new controllers in web.php

## Success Criteria
- All routes accessible and returning correct responses
- Management-only actions properly gated
- Member RSVP works with proper skill level validation
- Competition CRUD + scoring + standings endpoints functional
- AJAX responses match existing pattern (success + message)

## Risk Assessment
- **Route model binding**: `ClubCompetitionMatch` and `ClubCompetitionTeam` need correct route binding
- **Authorization**: Must verify club_activity belongs to club in each controller method
- **Breaking existing routes**: Keep all existing route names intact
