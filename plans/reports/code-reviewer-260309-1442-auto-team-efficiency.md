# Code Review: Auto-Team Generation - Efficiency

**Scope:** LeagueAutoTeamService, LeagueTeamController::autoGenerate, _tab-teams.blade.php JS
**Focus:** Efficiency only (N+1, redundant queries, unnecessary API calls, memory)

---

## Findings

### HIGH: N+1 Query in `$totalPlayers` Calculation

**File:** `app/Http/Controllers/Front/LeagueTeamController.php:132`

```php
$totalPlayers = collect($teams)->sum(fn ($t) => $t->players()->count());
```

`$t->players()->count()` fires a separate `SELECT COUNT(*)` query per team. For 50 teams, that is 50 queries.

**Fix:** The players were just created in the service, so count them from the chunk size:

```php
$totalPlayers = count($teams) * $validated['players_per_team'];
```

Or if exact count needed (edge case: last chunk partial), load them eagerly:

```php
$totalPlayers = collect($teams)->sum(fn ($t) => $t->loadCount('players')->players_count);
// Still N queries -- better to just use the math approach above
```

Best approach -- compute in service and return alongside teams:

```php
// In service: return ['teams' => $createdTeams, 'player_count' => $totalPlayerCount];
```

---

### MEDIUM: Individual INSERT per Player in Team Creation Loop

**File:** `app/Services/LeagueAutoTeamService.php:83-88`

```php
foreach ($group as $player) {
    $team->players()->create([...]);
}
```

Each `create()` fires a separate INSERT. For 50 teams x 2 players = 100 individual INSERTs.

**Fix:** Use bulk insert per team:

```php
$playerRows = $group->map(fn ($p) => [
    'league_team_id' => $team->id,
    'user_id' => $p->user_id,
    'gender' => $p->gender,
    'status' => 'active',
    'created_at' => now(),
    'updated_at' => now(),
])->toArray();
LeagueTeamPlayer::insert($playerRows);
```

Reduces from N INSERTs to 1 per team. Note: `insert()` skips model events -- acceptable here since no observers are expected on bulk auto-generation.

---

### MEDIUM: Individual UPDATE per Player in Order Endpoint

**File:** `app/Http/Controllers/Front/LeagueTeamController.php:210-214`

```php
foreach ($validated['player_ids'] as $order => $playerId) {
    LeagueTeamPlayer::where('id', $playerId)
        ->where('league_team_id', $team->id)
        ->update(['order' => $order + 1]);
}
```

Fires N queries for N players. Acceptable for small teams (2-4 players), but could use `CASE WHEN` for a single query:

```php
$cases = [];
$ids = [];
foreach ($validated['player_ids'] as $order => $playerId) {
    $cases[] = "WHEN id = {$playerId} THEN " . ($order + 1);
    $ids[] = $playerId;
}
DB::table('league_team_players')
    ->where('league_team_id', $team->id)
    ->whereIn('id', $ids)
    ->update(['order' => DB::raw('CASE ' . implode(' ', $cases) . ' END')]);
```

**Verdict:** Low priority given team sizes are 2-10 players max.

---

### MEDIUM: Duplicate Pool Fetch in JS

**File:** `resources/views/home-yard/leagues/_tab-teams.blade.php:399`

`openAutoGenerateModal()` calls `fetch(poolUrl)` to get pool count. If user previously opened the player modal, `openPlayerModal()` at line 294 already called `fetchPool()` hitting the same endpoint. No caching.

**Fix:** Cache the pool data in a JS variable and reuse:

```js
var cachedPool = null;
var cachedPoolTime = 0;

function getPool(callback) {
    if (cachedPool && (Date.now() - cachedPoolTime < 30000)) {
        callback(cachedPool);
        return;
    }
    fetch(poolUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': poolCsrfToken } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        cachedPool = data;
        cachedPoolTime = Date.now();
        callback(data);
    });
}
```

---

### LOW: `unique('user_id')` Applied After Full Collection Load

**File:** `app/Services/LeagueAutoTeamService.php:41`

```php
->get()
->unique('user_id')
```

Loads all matching rows into memory, then deduplicates in PHP. If registrations have many duplicate user entries, this wastes memory.

**Fix:** Use `groupBy` + `first()` at the query level, or add `DISTINCT` via a subquery. For typical league sizes (< 500 players) this is negligible. Flag only if leagues scale to thousands.

---

## Summary

| # | Severity | Issue | Queries Saved |
|---|----------|-------|---------------|
| 1 | HIGH | `players()->count()` in loop (controller:132) | N queries -> 0 |
| 2 | MEDIUM | Individual INSERT per player (service:83-88) | N -> N/perTeam |
| 3 | MEDIUM | Individual UPDATE per player order (controller:210-214) | Low impact (small N) |
| 4 | MEDIUM | Duplicate pool fetch in JS (blade:294,399) | 1 HTTP request |
| 5 | LOW | In-memory unique after full load (service:41) | Negligible at current scale |

**Recommended action:** Fix #1 immediately (trivial, high impact). Fix #2 if auto-generation is expected for 20+ teams. Others are low priority.
