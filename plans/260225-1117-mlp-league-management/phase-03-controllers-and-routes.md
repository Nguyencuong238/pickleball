# Phase 3: Controllers & Routes

## Context Links
- [Plan Overview](./plan.md)
- [Phase 2: Service Layer](./phase-02-service-layer.md)
- [Code Standards - Controllers](../../docs/code-standards.md#controller-organization)
- Reference: `app/Http/Controllers/Front/HomeYardTournamentController.php`, `routes/web.php` line 349+

## Overview
- **Priority**: P1
- **Status**: completed
- **Description**: Create 3 Front controllers for league management (organizer-facing) and 1 Admin controller for oversight. Register routes under homeyard prefix.

## Key Insights
- Existing homeyard routes use `Route::middleware(['auth'])->prefix('homeyard')->name('homeyard.')` (role:home_yard middleware currently excluded)
- HomeYardTournamentController uses constructor injection for services
- Tournament CRUD follows standard resource pattern with additional custom routes
- Views extend `layouts.front` layout
- Controller methods return `view()` or `redirect()` with flash messages

## Requirements

### Functional
- League CRUD (index, create, store, show, edit, update, destroy)
- Team CRUD within a league context
- Player add/remove within team context
- Match list, score entry (game-by-game and direct)
- Schedule generation trigger
- Status transition actions
- Admin: list all leagues, view details

### Non-functional
- Authorization: verify league ownership on all mutating operations
- Input validation on all store/update methods
- Flash messages for success/error feedback
- Pagination on list views

## Architecture

```
Front/HomeYardLeagueController (resource + custom)
├── index()          GET  /homeyard/leagues
├── create()         GET  /homeyard/leagues/create
├── store()          POST /homeyard/leagues
├── show()           GET  /homeyard/leagues/{league}
├── edit()           GET  /homeyard/leagues/{league}/edit
├── update()         PUT  /homeyard/leagues/{league}
├── destroy()        DELETE /homeyard/leagues/{league}
├── updateStatus()   PATCH /homeyard/leagues/{league}/status
└── generateSchedule() POST /homeyard/leagues/{league}/schedule

Front/LeagueTeamController
├── store()          POST /homeyard/leagues/{league}/teams
├── update()         PUT  /homeyard/leagues/{league}/teams/{team}
├── destroy()        DELETE /homeyard/leagues/{league}/teams/{team}
├── addPlayer()      POST /homeyard/leagues/{league}/teams/{team}/players
└── removePlayer()   DELETE /homeyard/leagues/{league}/teams/{team}/players/{player}

Front/LeagueMatchController
├── index()          GET  /homeyard/leagues/{league}/matches
├── updateScore()    PUT  /homeyard/leagues/{league}/matches/{match}/score
└── updateGameScore() PUT /homeyard/leagues/{league}/matches/{match}/games/{game}/score

Admin/LeagueController
├── index()          GET  /admin/leagues
└── show()           GET  /admin/leagues/{league}
```

## Related Code Files

| File | Action |
|------|--------|
| `app/Http/Controllers/Front/HomeYardLeagueController.php` | create |
| `app/Http/Controllers/Front/LeagueTeamController.php` | create |
| `app/Http/Controllers/Front/LeagueMatchController.php` | create |
| `app/Http/Controllers/Admin/LeagueController.php` | create |
| `routes/web.php` | modify (add league routes in homeyard group + admin group) |
| `app/Http/Controllers/Api/LeagueApiController.php` | create |
| `routes/api.php` | modify (add league API routes) |

## Implementation Steps

### HomeYardLeagueController (~180 lines)

1. Create `app/Http/Controllers/Front/HomeYardLeagueController.php`:
   - Namespace: `App\Http\Controllers\Front`
   - Extends: `App\Http\Controllers\Controller`
   - Constructor: inject `LeagueService`, `LeagueScheduleService`, `LeagueStandingsService`
   - Middleware: `auth` in constructor

2. Implement `index()`:
   - Query leagues where `user_id = auth()->id()`, with team count, latest first, paginate(12)
   - Calculate stats: total, active, completed counts
   - Return `view('home-yard.leagues.index', compact('leagues', 'stats'))`

3. Implement `create()`:
   - Return `view('home-yard.leagues.create')`

4. Implement `store(Request $request)`:
   - Validate: name (required|string|max:255), description (nullable|string), season_name (nullable|string|max:100), start_date (nullable|date), end_date (nullable|date|after:start_date), registration_deadline (nullable|date), config (nullable|array), config.match_format (nullable|array), config.max_teams (nullable|integer|min:2|max:32), config.max_players_per_team (nullable|integer|min:2|max:20), config.points_for_win (nullable|integer|min:0), config.points_for_loss (nullable|integer|min:0)
   - Call `$this->leagueService->createLeague(auth()->user(), $validated)`
   - Redirect to `homeyard.leagues.show` with success message

5. Implement `show(League $league)`:
   - Verify ownership: `abort_if($league->user_id !== auth()->id(), 403)`
   - Eager load: teams.players.user, rounds.matches.homeTeam, rounds.matches.awayTeam, standings.team
   - Sort standings by rank
   - Return `view('home-yard.leagues.show', compact('league'))`

6. Implement `edit(League $league)`:
   - Verify ownership
   - Return `view('home-yard.leagues.edit', compact('league'))`

7. Implement `update(Request $request, League $league)`:
   - Verify ownership
   - Validate same rules as store
   - Call `$this->leagueService->updateLeague($league, $validated)`
   - Redirect back with success

8. Implement `destroy(League $league)`:
   - Verify ownership
   - Call `$this->leagueService->deleteLeague($league)`
   - Redirect to index with success (or error if not draft)

9. Implement `updateStatus(Request $request, League $league)`:
   - Verify ownership
   - Validate: status (required|in:draft,registration,active,completed,cancelled)
   - Call `$this->leagueService->updateStatus($league, $request->status)`
   - Redirect back with success

10. Implement `generateSchedule(League $league)`:
    - Verify ownership
    - Require minimum 2 active teams
    - Call `$this->leagueScheduleService->generateRoundRobin($league)`
    - Call `$this->leagueStandingsService->initializeStandings($league)`
    - Redirect back with success

### LeagueTeamController (~120 lines)

11. Create `app/Http/Controllers/Front/LeagueTeamController.php`:
    - Constructor: inject `LeagueService`
    - All methods receive League via route model binding, verify ownership

12. Implement `store(Request $request, League $league)`:
    - Validate: name (required|string|max:255), captain_user_id (nullable|exists:users,id), logo (nullable|image|max:2048)
    - Handle logo upload to `storage/app/public/leagues/teams/`
    - Call `$this->leagueService->addTeam($league, $validated)`
    - Return JSON response (for AJAX) or redirect back

13. Implement `update(Request $request, League $league, LeagueTeam $team)`:
    - Validate same as store (all nullable)
    - Call `$this->leagueService->updateTeam($team, $validated)`
    - Return JSON or redirect

14. Implement `destroy(League $league, LeagueTeam $team)`:
    - Call `$this->leagueService->removeTeam($team)`
    - Return JSON or redirect

15. Implement `addPlayer(Request $request, League $league, LeagueTeam $team)`:
    - Validate: user_id (required|exists:users,id), position (nullable|string), gender (required|in:male,female)
    - Validate user not already on another team in same league
    - Call `$this->leagueService->addPlayer($team, $validated)`
    - Return JSON or redirect

16. Implement `removePlayer(League $league, LeagueTeam $team, LeagueTeamPlayer $player)`:
    - Call `$this->leagueService->removePlayer($player)`
    - Return JSON or redirect

### LeagueMatchController (~100 lines)

17. Create `app/Http/Controllers/Front/LeagueMatchController.php`:
    - Constructor: inject `LeagueStandingsService`

18. Implement `index(League $league)`:
    - Verify ownership
    - Load rounds with matches, eager load teams
    - Return `view('home-yard.leagues.matches', compact('league'))`

19. Implement `updateScore(Request $request, League $league, LeagueMatch $match)`:
    - Verify ownership (through match->round->league)
    - Validate: home_score (required|integer|min:0), away_score (required|integer|min:0)
    - Call `$this->leagueStandingsService->updateMatchResult($match, ...)`
    - Return JSON response with updated match data

20. Implement `updateGameScore(Request $request, League $league, LeagueMatch $match, LeagueMatchGame $game)`:
    - Verify ownership
    - Validate: home_score (required|integer|min:0), away_score (required|integer|min:0)
    - Call `$this->leagueStandingsService->saveGameScore($game, ...)`
    - Return JSON response

### Admin/LeagueController (~60 lines)

21. Create `app/Http/Controllers/Admin/LeagueController.php`:
    - Middleware: `auth`, `role:admin`

22. Implement `index()`:
    - List all leagues with organizer info, paginate(20)
    - Return `view('admin.leagues.index', compact('leagues'))`

23. Implement `show(League $league)`:
    - Eager load all relationships
    - Return `view('admin.leagues.show', compact('league'))`

### Routes

24. Add league routes in `routes/web.php` inside the homeyard group (after tournament routes, ~line 423):
    ```php
    // League Management
    Route::resource('leagues', HomeYardLeagueController::class);
    Route::patch('leagues/{league}/status', [HomeYardLeagueController::class, 'updateStatus'])->name('leagues.status');
    Route::post('leagues/{league}/schedule', [HomeYardLeagueController::class, 'generateSchedule'])->name('leagues.schedule.generate');

    // League Teams
    Route::post('leagues/{league}/teams', [LeagueTeamController::class, 'store'])->name('leagues.teams.store');
    Route::put('leagues/{league}/teams/{team}', [LeagueTeamController::class, 'update'])->name('leagues.teams.update');
    Route::delete('leagues/{league}/teams/{team}', [LeagueTeamController::class, 'destroy'])->name('leagues.teams.destroy');
    Route::post('leagues/{league}/teams/{team}/players', [LeagueTeamController::class, 'addPlayer'])->name('leagues.teams.players.store');
    Route::delete('leagues/{league}/teams/{team}/players/{player}', [LeagueTeamController::class, 'removePlayer'])->name('leagues.teams.players.destroy');

    // League Matches
    Route::get('leagues/{league}/matches', [LeagueMatchController::class, 'index'])->name('leagues.matches.index');
    Route::put('leagues/{league}/matches/{match}/score', [LeagueMatchController::class, 'updateScore'])->name('leagues.matches.score');
    Route::put('leagues/{league}/matches/{match}/games/{game}/score', [LeagueMatchController::class, 'updateGameScore'])->name('leagues.matches.games.score');
    ```

<!-- Updated: Validation Session 1 - Add read-only API endpoints (mixed auth) -->

### Api/LeagueApiController (~80 lines)

25. Create `app/Http/Controllers/Api/LeagueApiController.php`:
    - Namespace: `App\Http\Controllers\Api`
    - No constructor dependencies needed (read-only queries)

26. Implement `index(Request $request)` (authenticated):
    - Require auth:sanctum middleware
    - Return paginated leagues for authenticated user
    - Response: JSON with league list (id, name, slug, status, season_name, teams_count, start_date)

27. Implement `show(League $league)` (public):
    - No auth required
    - Return league details with config, team count, status
    - Response: JSON with league data

28. Implement `standings(League $league)` (public):
    - No auth required
    - Return standings sorted by rank with team names
    - Response: JSON array of standings

29. Implement `schedule(League $league)` (public):
    - No auth required
    - Return rounds with matches (team names, scores, status)
    - Response: JSON array of rounds with nested matches

### API Routes

30. Add API routes in `routes/api.php`:
    ```php
    // League API (mixed auth)
    Route::get('leagues', [LeagueApiController::class, 'index'])->middleware('auth:sanctum');
    Route::get('leagues/{league}', [LeagueApiController::class, 'show']);
    Route::get('leagues/{league}/standings', [LeagueApiController::class, 'standings']);
    Route::get('leagues/{league}/schedule', [LeagueApiController::class, 'schedule']);
    ```

31. Add admin routes inside the admin group:
    ```php
    Route::resource('leagues', \App\Http\Controllers\Admin\LeagueController::class)->only(['index', 'show']);
    ```

26. Add use statements at top of `routes/web.php`:
    ```php
    use App\Http\Controllers\Front\HomeYardLeagueController;
    use App\Http\Controllers\Front\LeagueTeamController;
    use App\Http\Controllers\Front\LeagueMatchController;
    ```

## Todo List
- [ ] Create HomeYardLeagueController with CRUD
- [ ] Implement league status update action
- [ ] Implement schedule generation action
- [ ] Create LeagueTeamController
- [ ] Implement player management in LeagueTeamController
- [ ] Create LeagueMatchController with score entry
- [ ] Create Admin/LeagueController
- [ ] Add all routes to web.php
- [ ] Create Api/LeagueApiController with 4 read-only endpoints
- [ ] Add API routes to routes/api.php (mixed auth)
- [ ] Verify all routes with `php artisan route:list | grep league`

## Success Criteria
- All routes registered and accessible
- League CRUD works end-to-end
- Authorization enforced (403 for non-owners)
- Input validation rejects invalid data
- Flash messages display on redirect
- Schedule generation creates correct rounds/matches
- Score entry triggers standings update

## Risk Assessment
- **Route model binding**: League uses slug, ensure `{league}` resolves correctly. LeagueTeam/LeagueMatch use default id binding.
- **Nested resource authorization**: Must verify league ownership even when accessing teams/matches. Middleware or manual check in each method.
- **AJAX vs form submissions**: Team/player/score operations may use AJAX. Return JSON when `$request->expectsJson()`, else redirect.

## Security Considerations
- Ownership check on every mutating operation (`abort_if($league->user_id !== auth()->id(), 403)`)
- Input validation with Laravel's validator
- CSRF protection on all form submissions
- No mass assignment vulnerabilities (explicit `$fillable` on models)
- Logo uploads validated for type and size
