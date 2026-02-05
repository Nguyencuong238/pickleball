# Referee Match Flow Analysis - Scout Report

## Summary

Complete end-to-end referee match flow documented across web routes, API endpoints, controllers, models, and Vue.js frontend integration. System handles singles/doubles matches with real-time score tracking, event logging, and state recovery.

---

## File Paths & Architecture

### Route Definitions

#### Web Routes (Match Control UI)
- `/Users/thaopv/Desktop/php/pickleball/routes/web.php` (lines 474-487)
  - `GET /referee/dashboard` → RefereeController::dashboard
  - `GET /referee/matches` → RefereeController::matches
  - `GET /referee/matches/{match}` → RefereeController::show (main control page)
  - `POST /referee/matches/{match}/start` → RefereeController::startMatch
  - `PUT /referee/matches/{match}/update-score` → RefereeController::updateScore
  - `POST /referee/matches/{match}/sync-events` → RefereeController::syncEvents
  - `POST /referee/matches/{match}/end` → RefereeController::endMatch
  - `GET /referee/matches/{match}/state` → RefereeController::getMatchState

#### API Routes (JSON Endpoints)
- `/Users/thaopv/Desktop/php/pickleball/routes/api.php` (lines 227-237)
  - `GET /api/referee/dashboard` → Api\RefereeController::dashboard
  - `GET /api/referee/matches` → Api\RefereeController::matches
  - `GET /api/referee/matches/{match}` → Api\RefereeController::showMatch
  - `POST /api/referee/matches/{match}/start` → Api\RefereeController::startMatch
  - `PUT /api/referee/matches/{match}/score` → Api\RefereeController::updateScore
  - `POST /api/referee/matches/{match}/sync-events` → Api\RefereeController::syncEvents
  - `POST /api/referee/matches/{match}/end` → Api\RefereeController::endMatch
  - `GET /api/referee/matches/{match}/state` → Api\RefereeController::getMatchState

---

## Controllers

### 1. Web Controller: Front/RefereeController
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/RefereeController.php`

#### Key Methods

**dashboard()** (lines 25-48)
- Returns referee dashboard view with stats
- Stats: total_matches, completed_matches, upcoming_matches, tournaments
- Loads next 5 upcoming matches with relationships

**matches()** (lines 54-86)
- Lists all assigned matches with filtering
- Filters: tournament_id, status, date_from, date_to
- Paginated (20 per page), sorted by date/time desc

**show(MatchModel $match)** (lines 91-119)
- Main match control page
- Builds matchData array for Vue.js initialization
- Includes tournament, category, round, court, athletes, partner info
- Checks referee authorization

**startMatch(MatchModel $match)** (lines 125-151)
- Allows status: scheduled, ready, in_progress
- Sets status to in_progress
- Only updates actual_start_time on first start (handles subsequent sets)
- Logs activity

**updateScore(Request $request, MatchModel $match)** (lines 156-208)
- Traditional form-based score update
- Validates set_scores array with per-set athlete scores
- Calculates winner from set comparison
- Updates group standings and athlete stats
- Logs final score

**syncEvents(Request $request, MatchModel $match)** (lines 483-523)
- Batch inserts match events from Vue app
- Validates event type, team (left/right), data, timer_seconds
- Updates match state (current_game, games_won, game_scores, serving_team, server_number, timer)
- Returns event count synced

**endMatch(Request $request, MatchModel $match)** (lines 528-618)
- Finalizes match results from Vue completion flow
- Converts game scores to set_scores format
- Calculates games_won for each athlete
- Clears match_state after completion
- Records match end event
- Dispatches MatchScored event for point earning system
- Returns winner_id and final_score

**getMatchState(MatchModel $match)** (lines 623-637)
- Returns current match state for recovery
- Loads relationships and calls toVueState()
- Used when user refreshes during live match

#### Internal Methods

**calculateWinner()** (lines 213-233)
- Compares set scores to determine match winner
- Returns athlete1_id, athlete2_id, or null (draw)

**formatFinalScore()** (lines 238-245)
- Formats set scores as "11-7, 11-9, 11-8" string

**updateGroupStandingsAndAthleteStats()** (lines 251-283)
- Calls sub-methods to update both group standings and tournament athlete stats
- Wraps with transaction and logging

**updateGroupStandingsWithSets()** (lines 288-370)
- Gets or creates GroupStanding records for both athletes
- Calls GroupStanding::updateAfterMatch() based on winner
- Recalculates group rankings by points and sets_differential
- Logs outcome

**updateTournamentAthleteStats()** (lines 375-449)
- Updates TournamentAthlete records (supports singles/doubles)
- Increments: matches_played, sets_won, sets_lost
- Conditionally increments: matches_won or matches_lost
- For doubles: calls updatePartnerStats() for partner record

**updatePartnerStats()** (lines 642-665)
- Updates partner TournamentAthlete with same stat increments
- Used in doubles matches

**recalculateGroupRankings()** (lines 454-476)
- Sorts standings by [points, sets_differential] desc
- Updates rank_position for each standing

---

### 2. API Controller: Api/RefereeController
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/RefereeController.php`

#### Key Methods (Identical Logic, JSON Responses)

**dashboard()** (lines 22-59)
- Returns JSON with stats and upcoming_matches
- Same data as web version

**matches()** (lines 64-108)
- JSON list of assigned matches with pagination
- per_page limited to max 100

**showMatch()** (lines 113-145)
- Single match detail as JSON
- Loads relationships and returns match object

**startMatch()** (lines 151-191)
- JSON response on match start
- Returns fresh match data

**updateScore()** (lines 196-265)
- JSON response on score update
- Includes transaction handling
- Returns updated match with relationships

**syncEvents()** (lines 556-609)
- Batch event sync via JSON endpoint
- Identical to web version
- Returns events_synced count

**endMatch()** (lines 614-718)
- Match completion endpoint
- Validates gameScores array with game number and scores per athlete
- Converts to set_scores format
- Updates match status to completed, sets final_score
- Records match end event and updates standings
- Dispatches MatchScored event
- Returns winner_id and final_score as JSON

**getMatchState()** (lines 723-747)
- Recovery endpoint returning current match state
- Returns toVueState() serialization

#### Shared Internal Methods
Same as web controller: calculateWinner, formatFinalScore, updateGroupStandingsAndAthleteStats, etc.

---

## Models

### 1. MatchModel
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/MatchModel.php`

#### Key Attributes (fillable)
```php
'tournament_id', 'category_id', 'round_id', 'court_id', 'group_id',
'match_number', 'bracket_position',
'athlete1_id', 'athlete1_name', 'athlete1_score',
'athlete2_id', 'athlete2_name', 'athlete2_score',
'winner_id', 'referee_id', 'referee_name',
'match_date', 'match_time', 'actual_start_time', 'actual_end_time',
'status', 'best_of', 'points_per_set', 'set_scores', 'final_score',
'notes', 'next_match_id', 'winner_advances_to',
'match_state', 'current_game', 'games_won_athlete1', 'games_won_athlete2',
'game_scores', 'serving_team', 'server_number', 'timer_seconds'
```

#### Casts
- `set_scores` → array (JSON)
- `game_scores` → array (JSON)
- `match_state` → array (JSON)
- Timestamps: datetime
- Numeric fields: integer

#### Relationships
- `tournament()` → Tournament (BelongsTo)
- `category()` → TournamentCategory (BelongsTo)
- `round()` → Round (BelongsTo)
- `court()` → Court (BelongsTo)
- `group()` → Group (BelongsTo)
- `athlete1()` → TournamentAthlete (BelongsTo)
- `athlete2()` → TournamentAthlete (BelongsTo)
- `winner()` → TournamentAthlete (BelongsTo)
- `referee()` → User (BelongsTo)
- `events()` → MatchEvent (HasMany)

#### Status Values
- `scheduled` - Match not started
- `ready` - Match ready to start
- `in_progress` - Match currently playing
- `completed` - Match finished

#### Key Methods

**isCompleted(): bool** (line 152)
- Returns status === 'completed'

**isLive(): bool** (line 160)
- Returns status === 'in_progress'

**isScheduled(): bool** (line 168)
- Returns status === 'scheduled'

**getLoserIdAttribute(): ?int** (lines 176-182)
- Computed: winner_id === athlete1_id ? athlete2_id : athlete1_id

**isAssignedToReferee(User $user): bool** (lines 228-231)
- Returns referee_id === user->id

**isDoubles(): bool** (lines 292-295)
- Returns category->isDoubles()

**getGameModeAttribute(): string** (lines 300-303)
- Returns 'doubles' or 'singles'

**recordEvent()** (lines 308-311)
- Delegates to MatchEvent::record()

**toVueState(): array** (lines 316-371)
- Serializes match data for Vue initialization
- Returns: id, status, isCompleted, bestOf, pointsPerSet, gameMode
- Athletes: id, name, partnerName (for doubles), pairName
- Tournament, category, round, court info
- Serving team, server number, timer_seconds
- Existing game_scores, set_scores, current_game

**syncState(array $state): void** (lines 376-388)
- Updates match_state JSON column
- Syncs: currentGame, gamesWonAthlete1/2, gameScores, servingTeam, serverNumber, timerSeconds

#### Scopes
- `forReferee(int $refereeId)` → where referee_id = ?
- `unassigned()` → whereNull referee_id

---

### 2. MatchEvent
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/MatchEvent.php`

#### Attributes (fillable)
```php
'match_id', 'event_type', 'team', 'data', 'timer_seconds', 'created_at'
```

#### Event Type Constants
```
TYPE_SCORE = 'score'
TYPE_SIDE_OUT = 'side_out'
TYPE_TIMEOUT = 'timeout'
TYPE_FAULT = 'fault'
TYPE_GAME_END = 'game_end'
TYPE_MATCH_START = 'match_start'
TYPE_MATCH_END = 'match_end'
TYPE_UNDO = 'undo'
TYPE_SERVER_CHANGE = 'server_change'
TYPE_RALLY_WON = 'rally_won'
TYPE_RALLY_LOST = 'rally_lost'
```

#### Team Constants
```
TEAM_LEFT = 'left'
TEAM_RIGHT = 'right'
```

#### Key Methods

**record()** (lines 69-84)
- Static factory: creates single event
- Params: matchId, eventType, team, data, timerSeconds
- Returns: created MatchEvent

**recordBatch()** (lines 89-115)
- Static bulk insert: inserts multiple events atomically
- Parses ISO 8601 created_at timestamps
- Returns: count of inserted records
- Data automatically JSON-encoded

**match()** (lines 61-64)
- Relationship: BelongsTo MatchModel

#### Accessors

**getFormattedMessageAttribute()** (lines 120-138)
- Vietnamese descriptions for event types
- Returns: Human-readable event message

**getTimerDisplayAttribute()** (lines 143-149)
- Formats timer_seconds as "MM:SS"

---

### 3. GroupStanding
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/GroupStanding.php`

#### Attributes (fillable)
```php
'group_id', 'athlete_id', 'rank_position',
'matches_played', 'matches_won', 'matches_lost', 'matches_drawn',
'win_rate', 'points', 'sets_won', 'sets_lost', 'sets_differential',
'games_won', 'games_lost', 'games_differential', 'is_advanced'
```

#### Key Methods

**calculateWinRate(): float** (lines 68-74)
- Returns (matches_won / matches_played) * 100, or 0 if no matches

**updateAfterMatch()** (lines 79-100)
- Increments: matches_played
- If won: matches_won += 1, points += 3 (3-point system)
- If lost: matches_lost += 1
- Adds: sets_won, sets_lost, calculates sets_differential
- Adds: games_won, games_lost, calculates games_differential
- Recalculates win_rate
- Saves record

**markAsAdvanced(): void** (lines 105-108)
- Sets is_advanced = true

**getDisplayRankAttribute(): string** (lines 113-117)
- Returns medal emoji for top 3: 🥇🥈🥉
- Otherwise returns numeric rank_position

---

### 4. TournamentAthlete
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/TournamentAthlete.php`

#### Attributes (fillable)
```php
'tournament_id', 'category_id', 'partner_id', 'user_id',
'athlete_name', 'email', 'phone', 'status', 'position',
'payment_status', 'group_id', 'seed_number',
'matches_played', 'matches_won', 'matches_lost', 'win_rate',
'total_points', 'sets_won', 'sets_lost'
```

#### Key Methods

**partner()** (lines 57-60)
- BelongsTo TournamentAthlete (for doubles)

**hasPartner(): bool** (lines 65-68)
- Returns !is_null(partner_id)

**getPairNameAttribute()** (lines 73-79)
- Returns athlete_name if singles
- Returns "athlete_name / partner_name" if doubles

---

## Frontend Integration

### Vue.js Component: show.blade.php
**File**: `/Users/thaopv/Desktop/php/pickleball/resources/views/referee/matches/show.blade.php`

#### Data Flow

**1. Initialization** (lines 636-648)
```javascript
const MATCH_DATA = @json($matchData);  // Server-passed match state
const API_ENDPOINTS = {
  start: "{{ route('referee.matches.start', $match) }}",
  syncEvents: "{{ route('referee.matches.sync-events', $match) }}",
  endMatch: "{{ route('referee.matches.end', $match) }}",
  getState: "{{ route('referee.matches.state', $match) }}",
  backUrl: "{{ route('referee.matches.index') }}"
};
const CSRF_TOKEN = "{{ csrf_token() }}";
```

**2. State Management** (lines 652-831)
- Local state includes: gameMode, status, timer, currentGame, teams (left/right)
- Serving state: team, serverIndex, serverNumber, isFirstServeOfGame
- Match recovery: localStorage with 24-hour TTL
- Functions: saveMatchState(), loadMatchState(), clearMatchState()

**3. Teams Object**
```javascript
teams = {
  left: {
    name, athleteId, score, gamesWon,
    players: [{ name, courtSide }]  // courtSide: 'left' or 'right'
  },
  right: { ... same ... }
}
```

**4. Event Recording** (lines 896-914)
- recordEvent(type, team, data = {})
- Collects: type, team, data with current scores, game number
- timer_seconds captured
- created_at as ISO string
- Pushes to pendingEvents array
- Syncs to server when pendingEvents.length >= SYNC_THRESHOLD (10)

**5. Event Sync** (lines 916-950)
- POST to /referee/matches/{match}/sync-events
- Body: { events: [...], match_state: {...} }
- Updates match state with: currentGame, gamesWonAthlete1/2, gameScores, servingTeam, serverNumber, timerSeconds
- Handles network errors by re-adding events to pending

**6. Scoring Logic**

**rallyWon(winningTeam)** (lines 1161-1185)
- If winning team = serving team: score ++, swap player positions (doubles)
- Records rally_won event
- Checks game win and deciding game switches
- If losing team: records rally_lost, calls handleSideOut()

**handleSideOut()** (lines 1123-1159)
- Singles: switches serving team, updates court side
- Doubles first serve: switches team, resets serverNumber to 1, updates serverIndex
- Doubles second serve: increments serverNumber to 2, swaps serverIndex
- Doubles second server side out: switches team, resets serverNumber to 1

**checkGameWin()** (lines 1195-1203)
- Checks if either team: score >= winScore (11) AND abs(left - right) >= 2
- Calls endGame() if condition met

**7. Match Completion** (lines 1341-1387)
- endGame() determines winner, increments gamesWon
- Saves game score: { game, athlete1, athlete2 }
- Syncs events to server
- Checks if team reached winsNeeded = ceil(bestOf / 2)
- If match complete: calls endMatchAPI()

**8. End Match API** (lines 952-995)
- Syncs remaining events first
- Builds finalState with: winner, winnerId, gameScores, finalScore, totalTimer
- POST to /referee/matches/{match}/end
- Clears localStorage, sets status = 'finished'
- Redirects to matches list after 2s

**9. Match State Recovery** (lines 1515-1542)
- On mount: checks localStorage for saved state
- If match was paused: confirms user wants to resume
- Restores all state: scores, serving, timeout counts, event log

---

## Validation Rules & Pickleball Rules

### Scoring Validation
- **Win condition**: First to 11 points with 2-point margin
- **Best of**: Configurable (typical: best of 3)
- **Set scores**: Array of objects with athlete1 & athlete2 scores
- **Side-out logic**:
  - Singles: side-out on any point loss
  - Doubles: only first server loses serve (side-out), second server serves then side-out
  - After side-out: serving team resets to server 1

### Doubles Specifics
- **Server rotation**: serverNumber (1 or 2) tracks which partner serves
- **Court sides**: 'left' (odd) or 'right' (even) based on score parity
- **Partner tracking**: partner_id links to another TournamentAthlete
- **Player positions**: Swap court sides after each rally won by serving team

### Deciding Game (Final Set)
- If bestOf = 3 and tied 1-1, third game is deciding game
- At 6 points in deciding game: **side switch** (both teams swap courts)
- Flag: hasSwitchedSidesInDecidingGame prevents double-switch

### Group Standings
- **3-point system**: Win = 3 pts, Loss = 0 pts, Draw = 1 pt
- **Tiebreakers**: sets_differential (sets_won - sets_lost)
- **Ranking**: Sorted by [points DESC, sets_differential DESC]

### Referee Authorization
- Check: match->isAssignedToReferee(auth()->user())
- Prevents unauthorized referees from modifying matches
- Returns 403 error if unauthorized

---

## Data Sync Flow

### Real-Time Event Tracking
1. Vue app records every action (score, side-out, timeout, fault, etc.)
2. Events buffered locally in pendingEvents array
3. When 10+ events accumulated OR game ends: syncEventsToServer()
4. Server records events atomically via MatchEvent::recordBatch()
5. Server also updates match_state JSON column with current scores/serving info

### Match State Persistence
- Stored in match_state JSON column on MatchModel
- Tracks: currentGame, gamesWonAthlete1/2, gameScores, servingTeam, serverNumber, timerSeconds
- Used when referee refreshes page (getMatchState endpoint)
- Vue restores from toVueState() serialization

### Completion Flow
1. endGame() triggers when win condition met
2. Syncs final events to server
3. endMatchAPI() collects all gameScores and calls /referee/matches/{match}/end
4. Server: updates match.status = completed, calculates final_score
5. Server: calls updateGroupStandingsAndAthleteStats() in transaction
6. Server: dispatches MatchScored event for point earning
7. Vue: clears localStorage, redirects to matches list

---

## Error Handling & Recovery

### Network Resilience
- Event sync failures: pendingEvents re-queued (union with new events)
- Toast notifications on sync failures
- Automatic retry on match end

### State Recovery
- Match state auto-saved to localStorage every state change
- On refresh: checks localStorage for saved state (max age 24h)
- If found: restores all scores, serving, timeout counts, event log
- Prompts user to resume if match was paused

### Authorization
- Referee check on every endpoint
- Returns 403 if not assigned
- Web controller returns 403 abort, API returns JSON error

---

## Related Services & Events

### Point Earning Integration
- **Event**: MatchScored (dispatched in endMatch controller methods)
- **Listener**: Likely triggers point reward for referee officiating
- **Location**: app/Events/MatchScored.php

### Activity Logging
- ActivityLog::log() called on:
  - Match start: "Trận đấu #{id} bắt đầu bởi trọng tài"
  - Score update: "Tỉ số trận đấu #{id} được cập nhật: {finalScore}"
  - Match end: "Trận đấu #{id} kết thúc: {finalScore}"

---

## Unresolved Questions

1. **Serve order modal skip**: Comment in toggleMatch() (line 1283) mentions "Skip serve order modal for singles matches" - confirm this is implemented correctly
2. **Coin flip logic**: Determines serving team but doubles also show serve order modal - confirm flow
3. **Maximum matches per page**: Web controller uses hardcoded 20, API uses min(per_page, 100) - should these sync?
4. **Timeout reset**: Doubles players can timeout per-game; reset code (lines 1405-1408) resets to 2 each game - confirm per-game scope
5. **Partner stats updates**: Only triggered for doubles (isDoubles() check) - confirm singles matches don't have partners

---

## Summary Table

| Component | Type | File | Purpose |
|-----------|------|------|---------|
| RefereeController | Web | Front/RefereeController.php | Match control page, form-based score entry |
| RefereeController | API | Api/RefereeController.php | JSON endpoints for mobile/app |
| MatchModel | Model | MatchModel.php | Core match entity, status tracking, state serialization |
| MatchEvent | Model | MatchEvent.php | Event logging (score, side-out, timeout, etc.) |
| GroupStanding | Model | GroupStanding.php | Group leaderboard standings |
| TournamentAthlete | Model | TournamentAthlete.php | Athlete stats in tournament |
| show.blade.php | View | referee/matches/show.blade.php | Vue.js match control UI |
| API Endpoints | Routes | routes/api.php | 8 referee match endpoints |
| Web Routes | Routes | routes/web.php | 8 referee match routes |

