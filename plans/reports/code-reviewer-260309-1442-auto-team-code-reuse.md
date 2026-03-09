# Code Review: Auto-Generate Teams - Code Reuse Analysis

## Scope
- Focus: CODE REUSE opportunities only
- Files reviewed: `LeagueAutoTeamService.php`, `LeagueRegistrationService.php`, `LeagueService.php`, `LeagueTeamController.php`, `_tab-teams.blade.php`

## Findings

### 1. DUPLICATE: Fetching unassigned players pool (HIGH)

**`LeagueAutoTeamService::autoGenerateTeams()` lines 31-42** duplicates the core logic of **`LeagueRegistrationService::getAvailablePool()` lines 107-123**.

Both do:
1. Query `LeagueTeamPlayer` to get assigned user IDs for the league
2. Query `LeagueRegistrationPlayer` with approved registrations, excluding assigned users

Differences:
- `getAvailablePool()` returns grouped by registration (Collection of LeagueRegistration with filtered players)
- `autoGenerateTeams()` returns flat list of LeagueRegistrationPlayer, uniqued by user_id

**Recommendation:** Extract a shared method in `LeagueRegistrationService`:

```php
// LeagueRegistrationService.php
public function getUnassignedPlayers(League $league): Collection
{
    $assignedUserIds = LeagueTeamPlayer::whereHas('team', fn ($q) => $q->where('league_id', $league->id))
        ->pluck('user_id')
        ->toArray();

    return LeagueRegistrationPlayer::whereHas('registration', function ($q) use ($league) {
        $q->where('league_id', $league->id)->where('status', 'approved');
    })
        ->whereNotIn('user_id', $assignedUserIds)
        ->get()
        ->unique('user_id')
        ->values();
}
```

Then `getAvailablePool()` can use it internally, and `LeagueAutoTeamService` injects `LeagueRegistrationService` and calls `getUnassignedPlayers()`.

### 2. DUPLICATE: Fetching assigned user IDs query (MEDIUM)

The exact same query pattern appears in **3 places**:

| Location | File:Line |
|---|---|
| `LeagueAutoTeamService::autoGenerateTeams()` | `LeagueAutoTeamService.php:31-33` |
| `LeagueRegistrationService::getAvailablePool()` | `LeagueRegistrationService.php:107-109` |
| `LeagueRegistrationService::addGroupToTeam()` | `LeagueRegistrationService.php:144-146` |

```php
$assignedUserIds = LeagueTeamPlayer::whereHas('team', fn ($q) => $q->where('league_id', $league->id))
    ->pluck('user_id')
    ->toArray();
```

**Recommendation:** Extract to a static or shared method, e.g. `LeagueRegistrationService::getAssignedUserIds(League $league): array`.

### 3. PARTIAL DUPLICATE: Player creation in team (LOW)

**`LeagueAutoTeamService::autoGenerateTeams()` lines 83-88:**
```php
$team->players()->create([
    'user_id' => $player->user_id,
    'gender' => $player->gender,
    'status' => 'active',
]);
```

**`LeagueService::addPlayer()` lines 216-221:**
```php
return $team->players()->create([
    'user_id' => $data['user_id'],
    'position' => $data['position'] ?? null,
    'gender' => $data['gender'],
    'status' => 'active',
]);
```

**`LeagueRegistrationService::addGroupToTeam()` lines 155-159:**
```php
$teamPlayer = $team->players()->create([
    'user_id' => $regPlayer->user_id,
    'gender' => $regPlayer->gender,
    'status' => 'active',
]);
```

The `LeagueService::addPlayer()` includes validation (duplicate check, max players check, cross-team check) that `autoGenerateTeams()` skips entirely. This is intentional since auto-generate works from a pre-filtered pool, but the actual `create()` call is repeated 3 times.

**Recommendation:** LOW priority. The validation differences make full reuse awkward. If refactored, create an internal `createPlayerRecord()` that just does the insert, while `addPlayer()` wraps it with validation. Not urgent.

### 4. NO DUPLICATE: JS functions in blade (NONE)

The auto-generate JS (`openAutoGenerateModal`, `closeAutoGenerateModal`, `submitAutoGenerate`) does NOT duplicate existing patterns. It does reuse `poolUrl` and `poolCsrfToken` variables already declared for the player modal, which is good.

The `fetch` + `toastr` + `location.reload()` pattern repeats across `addGroupToTeam()`, `addPlayerFromPool()`, and `submitAutoGenerate()` JS functions (lines 367-374, 378-389, 450-474), but extracting a helper for 3 small fetch calls would be over-engineering.

### 5. NO ISSUE: Team creation via LeagueService::addTeam() (NONE)

`autoGenerateTeams()` uses `$league->teams()->create()` directly (line 77) instead of `LeagueService::addTeam()`. This is acceptable because `addTeam()` adds `logo` and different defaults. The auto-generate only needs minimal team creation. The max_teams check IS duplicated (line 72 vs `addTeam()` line 152), but the auto-generate version handles it differently (break vs exception).

## Summary

| Priority | Issue | Impact |
|---|---|---|
| HIGH | Unassigned players pool query duplicated | DRY violation, 2 places to maintain |
| MEDIUM | Assigned user IDs query repeated 3x | DRY violation, easy extract |
| LOW | Player create() repeated 3x | Minor, validation differences justify partial duplication |

## Recommended Action

Extract `getAssignedUserIds()` and `getUnassignedPlayers()` into `LeagueRegistrationService`. Inject `LeagueRegistrationService` into `LeagueAutoTeamService` and call the shared methods. This eliminates the HIGH and MEDIUM findings with minimal refactoring.
