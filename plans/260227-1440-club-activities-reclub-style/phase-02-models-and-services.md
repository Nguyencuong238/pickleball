# Phase 2: Models & Services

## Context Links
- Current model: `app/Models/ClubActivity.php` (32 lines, skeleton)
- League models: `app/Models/League*.php` (pattern reference)
- League services: `app/Services/League*.php` (reuse pattern)
- Club model: `app/Models/Club.php` (members, roles)

## Overview
- **Priority:** P1
- **Status:** complete
- Update `ClubActivity` model with new fields, relationships, scopes
- Create `ClubActivityParticipant` model
- Create `ClubCompetitionTeam`, `ClubCompetitionMatch`, `ClubCompetitionStanding` models
- Create `ClubActivityService` for RSVP/waitlist logic
- Create `ClubCompetitionService` for round-robin generation and standings

## Architecture

```
ClubActivity (upgraded)
  |-- hasMany -> ClubActivityParticipant (RSVP)
  |-- hasMany -> ClubActivity (children, for recurring instances)
  |-- belongsTo -> ClubActivity (parent, for recurring template)
  |-- hasMany -> ClubCompetitionTeam (competition only)
  |-- hasMany -> ClubCompetitionMatch (competition only)
  |-- hasMany -> ClubCompetitionStanding (competition only)

ClubActivityService
  |-- rsvp(activity, user): handle join/waitlist logic
  |-- cancelRsvp(activity, user): remove + promote waitlisted
  |-- promoteFromWaitlist(activity): auto-promote next in line
  |-- createRecurringInstance(template): create single instance from template

ClubCompetitionService
  |-- generateSchedule(activity, format): dispatch to format-specific method
  |-- generateRoundRobin(activity): create matches (adapted from LeagueScheduleService)
  |-- generatePoolPlay(activity): groups + round-robin per pool + playoff bracket
  |-- generateSingleElimination(activity): seeded bracket generation
  |-- saveMatchScore(match, homeScore, awayScore): score + standings update
  |-- recalculateStandings(activity): full recalc (adapted from LeagueStandingsService)
  |-- initializeStandings(activity): create initial standings rows
<!-- Updated: Validation Session 1 - Added pool play and single elimination formats -->
```

## Related Code Files

### Files to MODIFY:
- `app/Models/ClubActivity.php` -- add fillable, casts, relationships, scopes

### Files to CREATE:
- `app/Models/ClubActivityParticipant.php`
- `app/Models/ClubCompetitionTeam.php`
- `app/Models/ClubCompetitionMatch.php`
- `app/Models/ClubCompetitionStanding.php`
- `app/Services/ClubActivityService.php`
- `app/Services/ClubCompetitionService.php`

## Implementation Steps

### Step 1: Update `ClubActivity` model

```php
// app/Models/ClubActivity.php
protected $fillable = [
    'club_id', 'type', 'created_by', 'title', 'description',
    'activity_date', 'end_time', 'location', 'max_participants',
    'recurrence_day', 'parent_activity_id', 'auto_approve',
    'min_skill_level', 'max_skill_level', 'competition_config', 'status',
];

protected $casts = [
    'activity_date' => 'datetime',
    'auto_approve' => 'boolean',
    'competition_config' => 'array',
    'recurrence_day' => 'integer',
];

// New relationships
public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
public function participants(): HasMany { return $this->hasMany(ClubActivityParticipant::class); }
public function confirmedParticipants(): HasMany {
    return $this->hasMany(ClubActivityParticipant::class)->where('status', 'confirmed');
}
public function waitlistedParticipants(): HasMany {
    return $this->hasMany(ClubActivityParticipant::class)->where('status', 'waitlisted')->orderBy('waitlist_position');
}
public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_activity_id'); }
public function children(): HasMany { return $this->hasMany(self::class, 'parent_activity_id'); }
public function competitionTeams(): HasMany { return $this->hasMany(ClubCompetitionTeam::class); }
public function competitionMatches(): HasMany { return $this->hasMany(ClubCompetitionMatch::class); }
public function competitionStandings(): HasMany { return $this->hasMany(ClubCompetitionStanding::class); }

// Scopes
public function scopeOfType($query, string $type) { return $query->where('type', $type); }
public function scopeRecurringTemplates($query) { return $query->where('type', 'recurring')->whereNull('parent_activity_id'); }
public function scopeUpcoming($query) { return $query->where('status', 'upcoming')->where('activity_date', '>=', now()); }

// Helpers
public function isFull(): bool { return $this->confirmedParticipants()->count() >= $this->max_participants; }
public function isRecurringTemplate(): bool { return $this->type === 'recurring' && $this->parent_activity_id === null; }
public function spotsLeft(): int { return max(0, $this->max_participants - $this->confirmedParticipants()->count()); }
public function userCanJoin(User $user): bool {
    if ($this->min_skill_level && $user->opr_level < $this->min_skill_level) return false;
    if ($this->max_skill_level && $user->opr_level > $this->max_skill_level) return false;
    return true;
}
```

### Step 2: Create `ClubActivityParticipant` model

```php
// app/Models/ClubActivityParticipant.php
protected $fillable = ['club_activity_id', 'user_id', 'status', 'waitlist_position', 'responded_at'];
protected $casts = ['responded_at' => 'datetime', 'waitlist_position' => 'integer'];

public function activity(): BelongsTo { return $this->belongsTo(ClubActivity::class, 'club_activity_id'); }
public function user(): BelongsTo { return $this->belongsTo(User::class); }
```

### Step 3: Create competition models

**`ClubCompetitionTeam`**: fillable=[club_activity_id, name, captain_user_id, status]. Relations: activity, captain, matches (home+away), standing.

**`ClubCompetitionMatch`**: fillable=[club_activity_id, round_number, home_team_id, away_team_id, status, home_score, away_score, winner_team_id, completed_at]. Relations: activity, homeTeam, awayTeam, winnerTeam.

**`ClubCompetitionStanding`**: fillable=[club_activity_id, club_competition_team_id, played, wins, losses, draws, points, rank]. Relations: activity, team. Scope `ranked()` same pattern as LeagueStanding.

### Step 4: Create `ClubActivityService`

```php
// app/Services/ClubActivityService.php
class ClubActivityService
{
    public function rsvp(ClubActivity $activity, User $user): ClubActivityParticipant
    {
        // Validate skill level
        if (!$activity->userCanJoin($user)) {
            throw new InvalidArgumentException('Trinh do khong phu hop voi yeu cau.');
        }
        // Check already joined
        $existing = $activity->participants()->where('user_id', $user->id)->first();
        if ($existing && $existing->status !== 'cancelled') {
            throw new InvalidArgumentException('Ban da dang ky tham gia.');
        }
        // Determine status
        $status = 'confirmed';
        if ($activity->isFull()) {
            $status = $activity->auto_approve ? 'waitlisted' : 'waitlisted';
        }
        // waitlist_position for waitlisted
        $waitlistPos = $status === 'waitlisted'
            ? ($activity->waitlistedParticipants()->max('waitlist_position') ?? 0) + 1
            : null;

        return DB::transaction(function () use ($activity, $user, $status, $waitlistPos, $existing) {
            if ($existing) {
                $existing->update(['status' => $status, 'waitlist_position' => $waitlistPos, 'responded_at' => now()]);
                return $existing->fresh();
            }
            return $activity->participants()->create([
                'user_id' => $user->id,
                'status' => $status,
                'waitlist_position' => $waitlistPos,
                'responded_at' => now(),
            ]);
        });
    }

    public function cancelRsvp(ClubActivity $activity, User $user): void
    {
        DB::transaction(function () use ($activity, $user) {
            $participant = $activity->participants()->where('user_id', $user->id)->firstOrFail();
            $wasConfirmed = $participant->status === 'confirmed';
            $participant->update(['status' => 'cancelled', 'waitlist_position' => null]);

            if ($wasConfirmed) {
                $this->promoteFromWaitlist($activity);
            }
        });
    }

    public function promoteFromWaitlist(ClubActivity $activity): void
    {
        if ($activity->isFull()) return;
        $next = $activity->waitlistedParticipants()->first();
        if ($next) {
            $next->update(['status' => 'confirmed', 'waitlist_position' => null]);
        }
    }

    public function createRecurringInstance(ClubActivity $template, Carbon $date): ClubActivity
    {
        return $template->club->activities()->create([
            'type' => 'recurring',
            'created_by' => $template->created_by,
            'title' => $template->title,
            'description' => $template->description,
            'activity_date' => $date->copy()->setTimeFromTimeString($template->activity_date->format('H:i:s')),
            'end_time' => $template->end_time,
            'location' => $template->location,
            'max_participants' => $template->max_participants,
            'parent_activity_id' => $template->id,
            'auto_approve' => $template->auto_approve,
            'min_skill_level' => $template->min_skill_level,
            'max_skill_level' => $template->max_skill_level,
            'status' => 'upcoming',
        ]);
    }
}
```

### Step 5: Create `ClubCompetitionService`

```php
// app/Services/ClubCompetitionService.php
class ClubCompetitionService
{
    public const DEFAULT_CONFIG = [
        'points_for_win' => 3,
        'points_for_loss' => 0,
        'max_teams' => 16,
    ];

    // generateSchedule(ClubActivity $activity, string $format): void
    // -- Dispatches to format-specific method based on $format param
    // -- Validates format is one of: round_robin, pool_play, single_elimination

    // generateRoundRobin(ClubActivity $activity): void
    // -- Same circle method as LeagueScheduleService but creates ClubCompetitionMatch rows
    // -- Teams from $activity->competitionTeams()->active()->get()

    // generatePoolPlay(ClubActivity $activity): void
    // -- Split teams into pools (2-4 teams per pool)
    // -- Generate round-robin within each pool (reuse circle method)
    // -- Mark pool matches with pool_number in round metadata
    // -- Playoff bracket generated after pool phase completes

    // generateSingleElimination(ClubActivity $activity): void
    // -- Seed teams by order or random
    // -- Generate bracket: round 1 has N/2 matches, round 2 has N/4, etc.
    // -- Handle byes for non-power-of-2 team counts

    // initializeStandings(ClubActivity $activity): void
    // -- Create ClubCompetitionStanding for each active team

    // saveMatchScore(ClubCompetitionMatch $match, int $homeScore, int $awayScore): void
    // -- Update match, determine winner, recalculate standings
    // -- For single elimination: auto-advance winner to next round match

    // recalculateStandings(ClubActivity $activity): void
    // -- Same pattern as LeagueStandingsService::recalculateStandings
    // -- Reset all to 0, iterate completed matches, compute rank

    // getConfigValue(ClubActivity $activity, string $key): mixed
    // -- Read from $activity->competition_config with DEFAULT_CONFIG fallback
    <!-- Updated: Validation Session 1 - Added pool play and single elimination methods -->
}
```

## Todo List
- [x] Update ClubActivity model with new fillable, casts, relationships, scopes, helpers
- [x] Create ClubActivityParticipant model
- [x] Create ClubCompetitionTeam model
- [x] Create ClubCompetitionMatch model
- [x] Create ClubCompetitionStanding model
- [x] Create ClubActivityService (RSVP/waitlist/recurring with lockForUpdate)
- [x] Create ClubCompetitionService (round-robin/pool-play/single-elimination/standings)
- [ ] Add `participants` relationship to Club model (through activities)

## Success Criteria
- All models have correct relationships and fillable
- ClubActivityService handles RSVP with auto-waitlist and promotion
- ClubCompetitionService generates valid round-robin schedule
- Skill level filtering works via `userCanJoin()`
- Under 200 lines per file

## Risk Assessment
- **N+1 queries**: Use eager loading in service methods
- **Race conditions on RSVP**: Use DB::transaction + lockForUpdate on participant count check
- **Competition service duplication**: Keep DRY by adapting League service logic, not copying verbatim
