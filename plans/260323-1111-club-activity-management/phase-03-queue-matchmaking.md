# Phase 3: Queue, Matchmaking & Match Lifecycle

**Priority**: P1 | **Effort**: High | **Status**: Complete
**Depends on**: Phase 2 (Check-in)

## Context

- UX Research: Feature 2 (Queue), Feature 3 partial (Match Assignment)
- Brainstorm Section 7: Auto Matchmaking Algorithm
- Existing: `ClubMatchService` has `generateRotatingDoubles()` with polygon rotation
- Existing: `ClubActivityService` has waitlist logic with lockForUpdate

## Requirements

- Waiting queue page (mobile) with AJAX polling 12s
- Player status: idle -> queued -> playing -> queued (cycle)
- Auto matchmaking by OPRS level (new ClubMatchmakingService)
- Court assignment with match_number tracking
- Match start/end lifecycle
- Return to queue after match (back of queue)

## Related Code Files

### Modify
- `app/Services/ClubMatchService.php` - add `completeMatch()` method
- `app/Http/Controllers/ClubMatchController.php` - add queue/match endpoints

### Create
- `app/Services/ClubMatchmakingService.php`
- `resources/views/front/clubs/queue.blade.php`
- `resources/views/front/clubs/match-assignment.blade.php`
- `public/assets/css/club-activity-queue.css`
- `public/assets/js/club-activity-queue.js`

## Architecture

```
Routes (auth or session-based):
  GET  /clubs/{club:slug}/activities/{activity}/queue          -> queue page
  GET  /clubs/{club:slug}/activities/{activity}/queue-status   -> JSON poll endpoint
  GET  /clubs/{club:slug}/activities/{activity}/my-match       -> current match view
  POST /clubs/{club:slug}/activities/{activity}/trigger-match  -> admin triggers matchmaking
  POST /clubs/{club:slug}/activities/{activity}/start-match/{match}  -> mark match started
  POST /clubs/{club:slug}/activities/{activity}/end-match/{match}    -> mark match ended
```

## Implementation Steps

### Step 1: ClubMatchmakingService (NEW - core algorithm)

```php
class ClubMatchmakingService
{
    /**
     * Generate matches from queued players.
     * @return Collection<ClubActivityMatch> created matches
     */
    public function generateMatches(ClubActivity $activity): Collection
    {
        return DB::transaction(function () use ($activity) {
            // 1. Get queued players sorted by queue_position
            $queued = $activity->participants()
                ->queued()
                ->orderBy('queue_position')
                ->lockForUpdate()
                ->get();

            // 2. Get available courts
            $busyCourts = $activity->matches()
                ->whereNull('ended_at')
                ->whereNotNull('started_at')
                ->pluck('scheduled_court');
            $availableCourts = collect(range(1, $activity->courts_count))
                ->diff($busyCourts);

            if ($queued->count() < 4 || $availableCourts->isEmpty()) {
                return collect();
            }

            // 3. Group into pods based on rotation_mode
            $pods = $this->createPods($activity, $queued, $availableCourts->count());

            // 4. Create matches
            $matches = collect();
            $matchNumber = ($activity->matches()->max('match_number') ?? 0) + 1;

            foreach ($pods as $i => $pod) {
                $court = $availableCourts->values()->get($i);
                $match = $this->createMatchFromPod($activity, $pod, $court, $matchNumber++);
                $matches->push($match);

                // Update player status: queued -> playing
                foreach ($pod as $player) {
                    $player->update(['current_status' => 'playing']);
                }
            }

            return $matches;
        });
    }

    private function createPods(ClubActivity $activity, Collection $queued, int $maxPods): array
    {
        $mode = $activity->rotation_mode;
        $playersNeeded = min($queued->count(), $maxPods * 4);
        $players = $queued->take($playersNeeded);

        if ($mode === 'oprs_based') {
            // Sort by OPRS, group closest 4
            $sorted = $players->sortBy(fn($p) => $p->user->total_oprs ?? 0);
            return $sorted->chunk(4)->filter(fn($c) => $c->count() === 4)->values()->toArray();
        }

        if ($mode === 'random') {
            return $players->shuffle()->chunk(4)->filter(fn($c) => $c->count() === 4)->values()->toArray();
        }

        // round_robin: take by queue_position order
        return $players->chunk(4)->filter(fn($c) => $c->count() === 4)->values()->toArray();
    }

    private function createMatchFromPod(ClubActivity $activity, $pod, int $court, int $matchNumber): ClubActivityMatch
    {
        $players = collect($pod)->values();
        // Pair: 0+1 vs 2+3 (for oprs_based, closest pairs play together)
        return ClubActivityMatch::create([
            'club_activity_id' => $activity->id,
            'round_id' => null, // open_play has no rounds
            'match_type' => 'doubles',
            'match_number' => $matchNumber,
            'scheduled_court' => $court,
            'player1_id' => $players[0]->user_id,
            'player2_id' => $players[1]->user_id,
            'player3_id' => $players[2]->user_id,
            'player4_id' => $players[3]->user_id,
            'started_at' => now(),
        ]);
    }
}
```

### Step 2: Queue Status Endpoint

Add to ClubMatchController or new controller:
```php
public function queueStatus(Club $club, ClubActivity $activity)
{
    // Return JSON:
    return response()->json([
        'queue' => $activity->participants()
            ->with('user:id,name,avatar,total_oprs,opr_level')
            ->whereIn('current_status', ['queued', 'playing'])
            ->orderBy('queue_position')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'name' => $p->user->name,
                'avatar' => $p->user->avatar,
                'oprs' => $p->user->total_oprs,
                'status' => $p->current_status,
                'queue_position' => $p->queue_position,
            ]),
        'courts' => $this->getCourtStatus($activity),
        'my_status' => $this->getMyStatus($activity, auth()->id() ?? session('checkin_user_id')),
    ]);
}
```

### Step 3: Match Lifecycle Methods

```php
// In ClubMatchService or ClubMatchController:

public function completeMatch(ClubActivityMatch $match): void
{
    DB::transaction(function () use ($match) {
        $match->update(['ended_at' => now()]);

        // Return players to queue (back of line)
        $playerIds = [$match->player1_id, $match->player2_id, $match->player3_id, $match->player4_id];
        $maxPos = $match->activity->participants()->max('queue_position') ?? 0;

        foreach ($playerIds as $i => $playerId) {
            $match->activity->participants()
                ->where('user_id', $playerId)
                ->update([
                    'current_status' => 'queued',
                    'queue_position' => $maxPos + $i + 1,
                    'matches_played_count' => DB::raw('matches_played_count + 1'),
                    'last_match_ended_at' => now(),
                ]);
        }
    });
}
```

### Step 4: Queue Blade View (queue.blade.php)

```
Layout: minimal mobile layout (same as checkin)
x-data="caQueue({config})"

Sections:
- Fixed top: activity name + "Dang cho" badge + stale indicator
- Hero card: my position/status (large number or court assignment)
- Queue list: compact rows with status dots
- Court strip: grid of court cards
```

### Step 5: Alpine.js (club-activity-queue.js)

```javascript
function caQueue(config) {
    return {
        queue: [], courts: [], myStatus: {},
        lastUpdated: null, isStale: false, _pollTimer: null,

        init() { this._poll(); },

        async _poll() {
            try {
                const res = await fetch(config.statusUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();
                this.queue = data.queue;
                this.courts = data.courts;
                this.myStatus = data.my_status;
                this.lastUpdated = Date.now();
                this.isStale = false;
            } catch { this.isStale = true; }
            finally { this._pollTimer = setTimeout(() => this._poll(), 12000); }
        },

        destroy() { clearTimeout(this._pollTimer); },

        get staleSince() {
            if (!this.lastUpdated) return null;
            return Math.floor((Date.now() - this.lastUpdated) / 1000);
        },
        get estimatedWait() {
            if (!this.myStatus.queue_position) return null;
            const avg = config.avgMatchDuration || 15;
            return Math.ceil(this.myStatus.queue_position / (config.courtsCount * 4)) * avg;
        }
    };
}
```

### Step 6: CSS (club-activity-queue.css)

Per UX research:
- `.ca-queue-position` - clamp(48px, 12vw, 72px) hero number
- `.ca-status-dot` - pulse animation (queued/playing/idle colors)
- `.ca-queue-row` + `.ca-queue-row.is-me` - highlighted own row
- `.ca-stale-badge` - amber/red transition for stale state
- `.ca-court-grid` + `.ca-court-card` - auto-fill responsive grid

### Step 7: Routes

```php
// Club Activity Queue & Match (session-based auth via checkin)
Route::prefix('clubs/{club:slug}/activities/{activity}')->group(function () {
    Route::get('/queue', [ClubMatchController::class, 'queue'])->name('club.activity.queue');
    Route::get('/queue-status', [ClubMatchController::class, 'queueStatus'])->name('club.activity.queue-status');
    Route::get('/my-match', [ClubMatchController::class, 'myMatch'])->name('club.activity.my-match');
    Route::post('/trigger-match', [ClubMatchController::class, 'triggerMatch'])->name('club.activity.trigger-match');
    Route::post('/start-match/{match}', [ClubMatchController::class, 'startMatch'])->name('club.activity.start-match');
    Route::post('/end-match/{match}', [ClubMatchController::class, 'endMatch'])->name('club.activity.end-match');
});
```

## Todo

- [x] Create ClubMatchmakingService with generateMatches()
- [x] Add queue-status endpoint (JSON polling)
- [x] Add match lifecycle methods (start, complete, return-to-queue)
- [x] Create queue.blade.php (mobile)
- [x] Create match-assignment.blade.php (mobile)
- [x] Create club-activity-queue.js (Alpine polling)
- [x] Create club-activity-queue.css
- [x] Add routes
- [x] Handle session-based user identification (checkin sets session)
- [x] Test: matchmaking groups 4 players correctly
- [x] Test: players return to back of queue after match

## Success Criteria

- Queue page shows real-time position via 12s polling
- Admin triggers matchmaking -> creates match with court assignment
- Players see "Dang thi dau - San X" when matched
- After match ends, players return to queue end
- Race condition safe (lockForUpdate on participant records)
- Avoids rematching same 4 players consecutively

## Risk

- Session-based auth for non-logged-in players: need to store user_id in session after checkin
- OPRS data quality: some users may have null OPRS -> fallback to 0
- Odd player count: players without match stay queued (not error state)
