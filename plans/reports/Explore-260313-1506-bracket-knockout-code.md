# Bracket/Knockout Code Exploration Report
**Date:** 2026-03-13  
**Timestamp:** 1506  
**Scope:** Existing bracket & knockout tournament code review

---

## 1. ClubCompetitionService::generateSingleElimination()

**Location:** `/Users/thaopv/Desktop/php/pickleball/app/Services/ClubCompetitionService.php:156-221`

**Method Signature:**
```php
public function generateSingleElimination(ClubActivity $activity): void
```

**Full Logic:**
- Pads team count to next power of 2 (e.g., 3 teams → 4 slots)
- Shuffles team IDs to randomize bracket seeding
- Fills remaining slots with null (byes)
- **First Round Only:** Creates matches with bracket position format `"R{round}M{matchIndex}"`
- Auto-completes bye matches (sets winner_id = non-null team, status = 'completed')
- **Subsequent Rounds:** Created lazily when matches complete via `advanceBracketWinner()` (called from `saveMatchScore()`)
- Uses `DB::transaction()` for atomicity

**Key Pattern:**
```php
$bracketSize = 1;
while ($bracketSize < count($teamIds)) {
    $bracketSize *= 2;
}
// Fill remaining with null (byes)
```

**Bracket Position Format:** `"R{round}M{matchIndex}"` (string, stored in ClubCompetitionMatch.bracket_position)

---

## 2. MatchModel - Bracket-Related Fields

**Location:** `/Users/thaopv/Desktop/php/pickleball/app/Models/MatchModel.php`

**Bracket Fields:**
| Field | Type | Cast | Notes |
|-------|------|------|-------|
| `next_match_id` | FK | - | Foreign key to matches.id, nullable, on delete set null |
| `winner_advances_to` | enum | - | Values: 'athlete1' \| 'athlete2' \| null. Defines which column in next match the winner enters |
| `bracket_position` | integer | integer | Position in bracket tree (can be null) |

**Match Status Enum:**
```php
['scheduled', 'ready', 'in_progress', 'completed', 'cancelled', 'postponed', 'bye']
```

**Relationships:**
```php
public function nextMatch(): BelongsTo  // Self-referential relationship
public function tournament(): BelongsTo
public function round(): BelongsTo
public function group(): BelongsTo
public function athlete1(): BelongsTo  // TournamentAthlete
public function athlete2(): BelongsTo  // TournamentAthlete
public function winner(): BelongsTo    // TournamentAthlete
```

**Key Methods:**
- `isCompleted()` - checks if status === 'completed'
- `getLoserIdAttribute()` - computed attribute returning loser's athlete_id
- `end(int $winnerId)` - marks match completed & sets winner

---

## 3. Round Model - Relationships & Methods

**Location:** `/Users/thaopv/Desktop/php/pickleball/app/Models/Round.php`

**Relationships:**
```php
public function tournament(): BelongsTo
public function category(): BelongsTo
public function matches(): HasMany       // MatchModel
public function groups(): HasMany
```

**Fields:**
- `round_name`, `round_number`, `round_type`
- `start_date`, `end_date`, `start_time`, `status`
- `total_matches`, `completed_matches`

**Helper Methods:**
- `isCompleted()` - returns true if all matches completed
- `getCompletionPercentageAttribute()` - calculates completion %

---

## 4. GroupStanding Model - Advancement Logic

**Location:** `/Users/thaopv/Desktop/php/pickleball/app/Models/GroupStanding.php`

**Advancement Field:**
- `is_advanced` (boolean) - marks athletes advancing to next round

**Key Methods:**
```php
public function markAsAdvanced(): void {
    $this->update(['is_advanced' => true]);
}

public function calculateWinRate(): float {
    if ($this->matches_played === 0) return 0;
    return round(($this->matches_won / $this->matches_played) * 100, 2);
}

public function updateAfterMatch(bool $won, int $setsWon, int $setsLost, 
                                 int $gamesWon, int $gamesLost): void {
    // Updates: matches_played, matches_won/lost, points, 
    //         sets_won/lost, sets_differential, 
    //         games_won/lost, games_differential, win_rate
}
```

**Ranking Fields:**
- `rank_position` (integer)
- `matches_played`, `matches_won`, `matches_lost`, `matches_drawn`
- `points` (3 for win, 0 for loss, configurable draws)
- `win_rate` (decimal:2)
- `sets_won`, `sets_lost`, `sets_differential`
- `games_won`, `games_lost`, `games_differential`

---

## 5. TournamentStandingService::recalculateGroupRankings()

**Location:** `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/TournamentStandingService.php:133-160`

**Method Signature:**
```php
public function recalculateGroupRankings(int $groupId): void
```

**Logic:**
1. Fetches all GroupStanding records for the group
2. Sorts by:
   - Points DESC
   - Matches Won DESC  
   - Games/Sets Differential DESC (or similar tiebreaker)
3. Updates `rank_position` (1-indexed)
4. Recalculates `win_rate` via `calculateWinRate()` method
5. **Determines advancement:** Compares `index + 1` against `Group->advancing_count`
   ```php
   $advancingCount = Group::find($groupId)?->advancing_count ?? 1;
   foreach ($standings as $index => $standing) {
       $standing->update(['is_advanced' => ($index + 1) <= $advancingCount]);
   }
   ```

**Related Methods:**
- `updateGroupStandings(MatchModel $match)` - updates after match completion
- `updateGroupStandingsWithSets(MatchModel $match, int $setsWon1, int $setsWon2)` - variant with sets

---

## 6. Tournament Configuration Structure

**Location:** `/Users/thaopv/Desktop/php/pickleball/app/Models/ClubActivity.php`

**Competition Config Field:**
```php
protected $casts = [
    'competition_config' => 'array',  // JSON stored as array
];

public function getConfigValue(string $key, mixed $default = null): mixed {
    return data_get($this->competition_config, $key, $default);
}
```

**Default Config (ClubCompetitionService):**
```php
public const DEFAULT_CONFIG = [
    'points_for_win' => 3,
    'points_for_draw' => 1,
    'points_for_loss' => 0,
    'max_teams' => 16,
];
```

**No Pre-Existing Fields for:**
- Third-place match
- Third-place playoff toggle
- Tournament format settings (would need to be added to migration)

---

## 7. ClubCompetitionMatch Model

**Location:** `/Users/thaopv/Desktop/php/pickleball/app/Models/ClubCompetitionMatch.php`

**Key Bracket Fields:**
- `bracket_position` (string, nullable)
- `pool_label` (string, nullable) - for pool play
- `round_number` (unsignedInteger)

**Status Enum:**
```php
['scheduled', 'in_progress', 'completed']
```

**Tournament Bracket Fields (Migration):**
```php
$table->string('bracket_position')->nullable();
$table->foreignId('next_match_id')->nullable()->constrained('matches')->onDelete('set null');
$table->enum('winner_advances_to', ['athlete1', 'athlete2'])->nullable();
```

---

## 8. Group Model - Advancement Count

**Location:** `/Users/thaopv/Desktop/php/pickleball/app/Models/Group.php`

**Critical Field:**
```php
protected $fillable = [
    'advancing_count',  // How many advance from group play
    // ...
];

public function advancedAthletes() {
    return $this->standings()
                ->where('is_advanced', true)
                ->with('athlete')
                ->get();
}
```

**Usage in TournamentStandingService:**
```php
$advancingCount = Group::find($groupId)?->advancing_count ?? 1;
```

---

## 9. Existing Bracket UI/CSS/JS

**No Dedicated Bracket Visualization Found**

Searched for:
- `resources/views/**/*bracket*` - no files
- `resources/**/*.css` with bracket styles - no files  
- `resources/**/*.js` with bracket logic - no files

**Existing Match UI Components:**
- `/resources/views/home-yard/tournaments/partials/_matches.blade.php` - list-based match display
- `/resources/views/clubs/activities/partials/_matches-scripts.blade.php` - vanilla JS for score management
- Match display is **horizontal list format**, not tree visualization

**Match Rendering Pattern (from _matches-scripts.blade.php):**
```javascript
function renderMatch(m, roundStatus) {
    // Renders: Court badge, Team names (vs separator), Score display/input
    // Uses classes: match-card, match-court, match-teams, match-score-display
}
```

---

## 10. Tournament Format & Schedule Generation

**Location:** `/Users/thaopv/Desktop/php/pickleball/app/Services/ClubCompetitionService.php:23-31`

**Dispatch Method:**
```php
public function generateSchedule(ClubActivity $activity, string $format): void {
    match ($format) {
        'round_robin' => $this->generateRoundRobin($activity),
        'pool_play' => $this->generatePoolPlay($activity),
        'single_elimination' => $this->generateSingleElimination($activity),
        default => throw new InvalidArgumentException("Format invalid"),
    };
}
```

**Supported Formats:**
1. `round_robin` - all teams play all teams
2. `pool_play` - teams split into pools, round-robin per pool
3. `single_elimination` - standard knockout bracket (power-of-2 padded)

**No Config Field Found** for selecting tournament format in Tournament or ClubActivity models.

---

## 11. Reusable Code Patterns Identified

### Pattern 1: Power-of-2 Bracket Padding
```php
$bracketSize = 1;
while ($bracketSize < count($teamIds)) {
    $bracketSize *= 2;
}
while (count($teamIds) < $bracketSize) {
    $teamIds[] = null;
}
```

### Pattern 2: Bye Auto-Completion
```php
if ($home === null || $away === null) {
    $winnerId = $home ?? $away;
    $activity->competitionMatches()->create([
        'status' => 'completed',
        'winner_team_id' => $winnerId,
        'completed_at' => now(),
    ]);
    continue;
}
```

### Pattern 3: Standings Recalculation (Multi-Sort)
```php
->sort(function ($a, $b) {
    return ($b->points <=> $a->points)
        ?: ($b->matches_won <=> $a->matches_won)
        ?: (($b->games_won - $b->games_lost) <=> ($a->games_won - $a->games_lost));
})
```

### Pattern 4: Advancement Logic (Threshold-Based)
```php
$advancingCount = Group::find($groupId)?->advancing_count ?? 1;
foreach ($standings as $index => $standing) {
    $standing->update(['is_advanced' => ($index + 1) <= $advancingCount]);
}
```

### Pattern 5: Configuration Access
```php
public function getConfigValue(string $key, mixed $default = null): mixed {
    return data_get($this->competition_config, $key, $default);
}
```

---

## 12. Key Database Columns Summary

### Matches Table
- `bracket_position` INT - position in bracket tree
- `next_match_id` FK - points to next match in bracket
- `winner_advances_to` ENUM('athlete1', 'athlete2') - which column winner fills

### Club Competition Matches Table
- `bracket_position` VARCHAR - format "R{round}M{matchIndex}"
- `pool_label` VARCHAR - for pool play identification
- `round_number` INT - round sequence

### Group Table
- `advancing_count` INT - threshold for advancement to next round

### GroupStanding Table
- `is_advanced` BOOLEAN - whether athlete advanced
- `rank_position` INT - final ranking in group
- Various stats: matches_played, points, sets_differential, games_differential, win_rate

---

## 13. Tournament Controller/Service Pattern

**Key Services Found:**
- `ClubCompetitionService` - handles schedule generation, standings
- `TournamentStandingService` - handles group ranking recalculation
- `TournamentDrawService` - handles athlete/pair draw assignments
- `TournamentMatchService` - (exists, not fully reviewed)

**Key Controllers Found:**
- `TournamentManualDrawController` - manual draw assignment
- `TournamentGroupController` - group management
- `TournamentMatchController` - match management

---

## Summary for Planning

### Ready to Reuse
- Bracket position format logic: `"R{round}M{matchIndex}"`
- Power-of-2 padding algorithm
- Bye auto-completion pattern
- Standings recalculation with multi-sort tiebreakers
- Group advancement threshold logic

### Needs Implementation
1. **Third-Place Match Support**
   - Extend tournament config to include `third_place_match_enabled`
   - Add logic to create R3M1 match between semifinals losers
   - Advancement logic modification

2. **Bracket Visualization UI**
   - No existing bracket tree visualization
   - Need Vue/Alpine component or bracket CSS library

3. **Next Match Chain Creation**
   - `next_match_id` field exists but logic to populate it is TBD
   - Need algorithm to map winners to next round matches
   - Handle third-place match wiring

4. **Tournament Format Config**
   - Store format choice in tournament/category config
   - Currently inferred from schedule generation calls

---

## Unresolved Questions

1. **Winner Advancement Routing**: How exactly are winners routed to next match? Is `advanceBracketWinner()` method implemented in ClubCompetitionService (not shown in initial read)?

2. **Third-Place Playoff Current Support**: Is there any existing third-place match logic in the codebase, or is this entirely new?

3. **Multi-Round Knockout**: Does existing single_elimination support multiple brackets (e.g., 8-team bracket generates 2 separate brackets of 4)? Or always one bracket?

4. **Bracket Visualization Library**: Any preference for bracket rendering (CSS Grid, SVG library, custom Vue component)?

5. **Tournament Format Selection UI**: Where do users select format? Admin panel migration needed?

---

**Report Generated:** 2026-03-13 15:06  
**Files Reviewed:** 12  
**Key Models:** 7  
**Services:** 3  
**Methods:** 15+
