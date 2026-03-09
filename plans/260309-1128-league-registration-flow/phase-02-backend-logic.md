# Phase 2: Backend - Registration & Approval Logic

## Context Links
- [Phase 1: Database & Models](./phase-01-database-models.md)
- [Existing LeagueService](../../app/Services/LeagueService.php)
- [Existing LeagueTeamController](../../app/Http/Controllers/Front/LeagueTeamController.php)
- [OcrController::searchUsers](../../app/Http/Controllers/Front/OcrController.php)

## Overview
- **Priority**: P1
- **Status**: pending
- **Effort**: 3h

## Key Insights
- Public registration route must NOT require auth (VDV may not have account)
- User match/create by phone -- reuse User model, set random password for new users
- Admin approval = league owner (user_id on league), not system admin
- Existing `addPlayer` in LeagueService already checks "1 VDV per team per league" constraint
- Need to validate unique phone per league — only for pending+approved registrations (rejected can re-register)
- User creation: phone + name + random password only (no email required)
<!-- Updated: Validation Session 1 - Re-registration allowed, phone+name user creation -->

## Requirements

### Functional
- POST public registration with N player forms + 1 payment proof image
- Auto-create user by phone if not exists (name from form, random password)
- Admin (league owner) can list, approve, reject registrations
- Approved players available as pool for team assignment
- "Add group to team" -- add all players from a registration group at once
- Captain auto-set to first player in group when adding whole group

### Non-functional
- DB::transaction for registration + player creation
- File upload validation: image, max 5MB
- Rate limiting on public registration endpoint

## Architecture

### New Controller: `LeagueRegistrationController`
Located at `app/Http/Controllers/Front/LeagueRegistrationController.php`

**Public routes (no auth):**
- `GET /leagues/{league:slug}/register` -- show registration form
- `POST /leagues/{league:slug}/register` -- submit registration

**Auth routes (league owner):**
- `GET /homeyard/leagues/{league}/registrations` -- JSON list for admin tab
- `PUT /homeyard/leagues/{league}/registrations/{registration}/approve` -- approve
- `PUT /homeyard/leagues/{league}/registrations/{registration}/reject` -- reject

**Auth routes (league owner, pool operations):**
- `GET /homeyard/leagues/{league}/registrations/pool` -- approved VDV not yet in any team
- `POST /homeyard/leagues/{league}/teams/{team}/add-group` -- add whole registration group to team

### New Service: `LeagueRegistrationService`
Located at `app/Services/LeagueRegistrationService.php`

Methods:
- `register(League, array $data): LeagueRegistration` -- create registration + players + match/create users
- `approve(LeagueRegistration, ?string $note): void` -- set status=approved
- `reject(LeagueRegistration, ?string $note): void` -- set status=rejected
- `getAvailablePool(League): Collection` -- approved players not assigned to any team
- `addGroupToTeam(LeagueRegistration, LeagueTeam): void` -- add all players, set captain

## Related Code Files

### Files to CREATE
- `app/Http/Controllers/Front/LeagueRegistrationController.php`
- `app/Services/LeagueRegistrationService.php`

### Files to MODIFY
- `routes/web.php` -- add registration routes (public + auth)
- `app/Services/LeagueService.php` -- add `addPlayersFromRegistration()` method (or keep in new service)
- `app/Http/Controllers/Front/HomeYardLeagueController.php` -- update `store`/`update` to handle new league fields

## Implementation Steps

### 1. Create `LeagueRegistrationService`

```php
class LeagueRegistrationService
{
    public function register(League $league, array $data): LeagueRegistration
    {
        // Validate deadline
        // Validate required_players_per_registration count
        // Validate unique phones within this league
        // DB::transaction:
        //   1. Store payment_proof image
        //   2. Create LeagueRegistration
        //   3. For each player:
        //      a. Match user by phone (User::where('phone', $phone)->first())
        //      b. If not found: User::create([name, phone, password=random])
        //      c. Create LeagueRegistrationPlayer with user_id
        //      d. Store player photo if provided
        // Return registration with players loaded
    }

    public function approve(LeagueRegistration $reg, ?string $note = null): void
    {
        $reg->update(['status' => 'approved', 'admin_note' => $note]);
    }

    public function reject(LeagueRegistration $reg, ?string $note = null): void
    {
        $reg->update(['status' => 'rejected', 'admin_note' => $note]);
    }

    public function getAvailablePool(League $league): Collection
    {
        // Get all approved registration players for this league
        // Exclude those whose user_id is already in league_team_players for this league
        // Return grouped by registration_id (to support "add group" UI)
    }

    public function addGroupToTeam(LeagueRegistration $registration, LeagueTeam $team): array
    {
        // DB::transaction:
        // For each player in registration:
        //   - Check not already in a team (skip if so)
        //   - Create LeagueTeamPlayer (user_id, gender from reg player)
        // Set captain = first player's user_id (if team has no captain)
        // Return added players
    }
}
```

### 2. Create `LeagueRegistrationController`

```php
class LeagueRegistrationController extends Controller
{
    // Public methods (no auth middleware)
    public function showForm(League $league) { ... }
    public function store(Request $request, League $league) { ... }

    // Admin methods (auth + ownership check)
    public function listRegistrations(League $league) { ... }
    public function approve(Request $request, League $league, LeagueRegistration $reg) { ... }
    public function reject(Request $request, League $league, LeagueRegistration $reg) { ... }
    public function pool(League $league) { ... }
    public function addGroup(League $league, LeagueTeam $team, LeagueRegistration $reg) { ... }
}
```

### 3. Add Routes to `routes/web.php`

```php
// Public registration (no auth)
Route::get('leagues/{league}/register', [LeagueRegistrationController::class, 'showForm'])
    ->name('leagues.register');
Route::post('leagues/{league}/register', [LeagueRegistrationController::class, 'store'])
    ->name('leagues.register.store');

// Admin registration management (inside homeyard auth group)
Route::get('leagues/{league}/registrations', [LeagueRegistrationController::class, 'listRegistrations'])
    ->name('leagues.registrations.index');
Route::put('leagues/{league}/registrations/{registration}/approve', [LeagueRegistrationController::class, 'approve'])
    ->name('leagues.registrations.approve');
Route::put('leagues/{league}/registrations/{registration}/reject', [LeagueRegistrationController::class, 'reject'])
    ->name('leagues.registrations.reject');
Route::get('leagues/{league}/registrations/pool', [LeagueRegistrationController::class, 'pool'])
    ->name('leagues.registrations.pool');
Route::post('leagues/{league}/teams/{team}/add-group/{registration}', [LeagueRegistrationController::class, 'addGroup'])
    ->name('leagues.teams.addGroup');
```

### 4. Update `HomeYardLeagueController` store/update

Add validation for new fields:
```php
'required_players_per_registration' => 'nullable|integer|in:1,2,4',
'registration_fee' => 'nullable|numeric|min:0',
```

Pass these to `LeagueService::createLeague()` and `updateLeague()`.

### 5. Update `LeagueService::createLeague()`

Add new fields to the create array:
```php
'required_players_per_registration' => $data['required_players_per_registration'] ?? 1,
'registration_fee' => $data['registration_fee'] ?? null,
```

### 6. Validation Rules for Registration Form

```php
$rules = [
    'players' => 'required|array|size:' . $league->required_players_per_registration,
    'players.*.phone' => 'required|string|max:20',
    'players.*.name' => 'required|string|max:255',
    'players.*.skill_level' => 'nullable|string|max:50',
    'players.*.province' => 'nullable|string|max:100',
    'players.*.gender' => 'required|in:male,female',
    'players.*.birthday' => 'nullable|date',
    'players.*.photo' => 'nullable|image|max:2048',
    'players.*.message' => 'nullable|string|max:500',
    'payment_proof' => 'required|image|max:5120',
];
```

## Todo List
- [ ] Create LeagueRegistrationService with all methods
- [ ] Create LeagueRegistrationController (public + admin)
- [ ] Add public routes (no auth)
- [ ] Add admin routes (inside homeyard middleware group)
- [ ] Update HomeYardLeagueController store/update validation
- [ ] Update LeagueService createLeague/updateLeague for new fields
- [ ] Add phone-based unique validation per league
- [ ] Test: registration creates users by phone
- [ ] Test: approve/reject updates status
- [ ] Test: pool excludes assigned players
- [ ] Test: addGroup sets captain correctly

## Success Criteria
- Public form submission creates registration + players + users
- Admin can approve/reject with notes
- Pool endpoint returns only approved, unassigned players
- "Add group" creates LeagueTeamPlayers and sets captain
- Existing "Tim user" flow unchanged

## Risk Assessment
- **Phone format variation**: normalize phone before matching (strip spaces, leading 0 vs +84)
  - Mitigation: normalizePhone() helper — strip non-digits, convert +84/84 prefix to 0. Store as 0xxx format.
<!-- Updated: Validation Session 1 - Phone normalization to 0xxx format -->
- **Race condition on user creation**: two registrations with same phone simultaneously
  - Mitigation: DB::transaction + unique constraint on users.phone (if exists) or firstOrCreate pattern
- **Large file uploads**: payment proof could be large
  - Mitigation: max:5120 (5MB) validation

## Security
- Public route: rate limit (throttle:5,1 per IP)
- Admin routes: ownership check (`$league->user_id === auth()->id()`)
- File upload: validate image type, max size
- XSS: all user input escaped in Blade via `{{ }}` (default)
- CSRF: Laravel default on POST/PUT forms
