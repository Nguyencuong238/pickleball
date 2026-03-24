# Club Activity System Exploration Report

**Date:** 2026-03-23 | **Scope:** Current codebase structure & extensibility

---

## 1. MODELS & DATABASE SCHEMA

### Core Models

#### `ClubActivity` (/app/Models/ClubActivity.php)
**Fillable Fields:**
- `club_id, type, created_by, title, description, activity_date, end_time, location, max_participants, recurrence_day, parent_activity_id, auto_approve, min_skill_level, max_skill_level, competition_config, status`

**Key Relationships:**
- `club()` – BelongsTo Club
- `creator()` – BelongsTo User (created_by)
- `participants()`, `confirmedParticipants()`, `waitlistedParticipants()` – HasMany ClubActivityParticipant
- `parent()`, `children()` – Self-referential (recurring templates)
- `competitionTeams()`, `competitionMatches()`, `competitionStandings()` – HasMany (team-based competitions)
- `matchRounds()`, `matchStandings()` – HasMany (casual match tracking)
- `post()` – HasOne ClubPost (auto-generated feed post)

**Important Methods:**
- `isFull()`, `spotsLeft()`, `userCanJoin($user)` – Capacity/skill validation
- `isRecurringTemplate()` – Template identification
- `getConfigValue($key)` – Access nested competition config
- `buildPostContent()` – Generate HTML for club feed post

**Types:** `one_off`, `recurring`, `competition`  
**Status:** `upcoming`, `completed`, `cancelled`  
**Casts:** `activity_date` (datetime), `auto_approve` (boolean), `competition_config` (array), skill levels (float)

---

#### `ClubActivityParticipant` (/app/Models/ClubActivityParticipant.php)
**Fillable:** `club_activity_id, user_id, status, waitlist_position, responded_at`  
**Status:** `confirmed`, `waitlisted`, `cancelled`  
**Relationships:** `activity()` → ClubActivity, `user()` → User  
**Unique Constraint:** (club_activity_id, user_id)  
**Index:** (club_activity_id, status)

---

#### `ClubActivityMatch` (/app/Models/ClubActivityMatch.php)
**Fillable:** `round_id, court_number, match_type, player1_id, player2_id, player3_id, player4_id, team1_score, team2_score, status, completed_at`  
**Match Types:** `singles` (2 players), `doubles` (4 players)  
**Status:** `scheduled`, `in_progress`, `completed`  
**Relationships:** `round()` → ClubActivityMatchRound, `player1/2/3/4()` → User  
**Notes:** Teams determined by player position (team1 = p1+p2, team2 = p3+p4)

---

#### `ClubActivityMatchRound` (/app/Models/ClubActivityMatchRound.php)
**Fillable:** `club_activity_id, round_number, status`  
**Status:** `pending`, `in_progress`, `completed`  
**Relationships:** `activity()` → ClubActivity, `matches()` → HasMany ClubActivityMatch

---

#### `ClubActivityMatchStanding` (/app/Models/ClubActivityMatchStanding.php)
**Fillable:** `club_activity_id, user_id, matches_played, wins, losses, points_scored, points_against`  
**Unique:** (club_activity_id, user_id)  
**Calculated Attribute:** `point_differential` (points_scored - points_against)

---

#### `ClubCompetitionTeam` (/app/Models/ClubCompetitionTeam.php)
**Fillable:** `club_activity_id, name, captain_user_id, status`  
**Status:** `active`  
**Relationships:** 
- `activity()` → ClubActivity
- `captain()` → User
- `homeMatches()`, `awayMatches()` → HasMany ClubCompetitionMatch
- `standing()` → HasOne ClubCompetitionStanding

---

#### `ClubCompetitionMatch` (/app/Models/ClubCompetitionMatch.php)
**Fillable:** `club_activity_id, round_number, home_team_id, away_team_id, status, home_score, away_score, winner_team_id, pool_label, bracket_position, completed_at`  
**Relationships:**
- `activity()` → ClubActivity
- `homeTeam()`, `awayTeam()`, `winnerTeam()` → ClubCompetitionTeam

---

#### `Club` (/app/Models/Club.php)
**Activity Relationships:**
- `activities()` → HasMany ClubActivity
- `joinRequests()` → HasMany ClubJoinRequest
- `posts()` → HasMany ClubPost

**Helper Methods:**
- `getMemberRole($user)`, `isManagement($user)`, `isAdmin($user)`, `isMember($user)`

---

## 2. DATABASE SCHEMA SUMMARY

### Main Tables

| Table | Key Fields | Notes |
|-------|-----------|-------|
| `club_activities` | id, club_id, type, created_by, title, description, activity_date, end_time, location, max_participants, status, recurrence_day, parent_activity_id, auto_approve, min_skill_level, max_skill_level, competition_config | Core activity table; supports recurring & competition configs |
| `club_activity_participants` | id, club_activity_id, user_id, status, waitlist_position, responded_at | RSVP tracking; handles waitlist |
| `club_activity_match_rounds` | id, club_activity_id, round_number, status | Organizes matches into rounds |
| `club_activity_matches` | id, round_id, court_number, match_type, player1_id, player2_id, player3_id, player4_id, team1_score, team2_score, status, completed_at | Singles/doubles match results |
| `club_activity_match_standings` | id, club_activity_id, user_id, matches_played, wins, losses, points_scored, points_against | Per-activity standings |
| `club_competition_teams` | id, club_activity_id, name, captain_user_id, status | Team-based competitions |
| `club_competition_matches` | id, club_activity_id, round_number, home_team_id, away_team_id, home_score, away_score, winner_team_id, status, pool_label, bracket_position, completed_at | Team match scheduling |
| `club_competition_standings` | id, club_activity_id, team_id, ... | Team standings (from migration reference) |

---

## 3. SERVICES

### ClubActivityService (/app/Services/ClubActivityService.php)

**Key Methods:**

- `rsvp(ClubActivity, User)` → ClubActivityParticipant
  - Validates skill level constraints
  - Auto-confirms or adds to waitlist based on capacity
  - Prevents duplicate RSVPs
  - Uses transaction + lockForUpdate for race condition safety

- `cancelRsvp(ClubActivity, User)` → void
  - Marks participant as cancelled
  - Auto-promotes next waitlisted participant if spot opens

- `promoteFromWaitlist(ClubActivity)` → void
  - Elevates first waitlisted participant to confirmed
  - Respects max_participants constraint

- `createRecurringInstance(ClubActivity, Carbon)` → ClubActivity
  - Generates next occurrence of recurring template
  - Copies all settings except parent_activity_id (set to template id)
  - Preserves skill level constraints and config

---

### ClubMatchService (/app/Services/ClubMatchService.php)

**Key Methods:**

- `generateMatches(ClubActivity, string $format, int $courtCount)` → void
  - Supports: `singles_rr`, `rotating_doubles`, `fixed_doubles`
  - Validates minimum player count (2 for singles, 4 for doubles)
  - Deletes existing rounds/standings before generation
  - Uses polygon rotation algorithm for round-robin pairing

- `saveScore(ClubActivity, ClubActivityMatch, int, int)` → void
  - Marks match as completed
  - Recalculates all affected player standings atomically

- `recalculateStandings(ClubActivity, array $userIds)` → void (private)
  - Sums wins/losses/points from all completed matches
  - Handles ties (neither win nor loss counted)
  - Creates/updates ClubActivityMatchStanding records

- `getStandings(ClubActivity)` → array
  - Returns array sorted by wins desc, then point_differential desc
  - Includes rank, user, matches_played, record, points

- `createCustomMatch(ClubActivity, string $matchType, int p1, ?int p2, int p3, ?int p4, ?int court, ?int roundId)` → ClubActivityMatch
  - Creates ad-hoc matches outside generated schedules
  - Auto-creates new round if roundId not provided
  - Validates all players are confirmed participants
  - Prevents duplicate players in same match

**Match Generation Algorithms:**
- **Singles Round-Robin:** Polygon rotation, handles odd number with bye
- **Rotating Doubles:** Shuffles partnerships each round, avoids repeated partners when possible
- **Fixed Doubles:** Forms permanent pairs, applies polygon rotation on pairs

---

### EloService (/app/Services/EloService.php - Relevant Methods)

**K-Factor by Experience:**
- 0-30 matches: K=40 (K_NEW_PLAYER)
- 31-100 matches: K=24 (K_INTERMEDIATE)
- 100+ matches: K=16 (K_EXPERIENCED)

**Key Methods:**
- `getKFactor(User)` → int
- `calculateExpectedScore(int ratingA, int ratingB)` → float
  - Formula: E = 1 / (1 + 10^((Rb - Ra) / 400))
- `calculateRatingChange(int rating, int opponentRating, bool won, int kFactor)` → int
  - Formula: Change = K * (S - E) where S = 1 (win) or 0 (loss)
- `getTeamElo(User p1, ?User p2)` → int
  - Average of both players for doubles matches

---

### OprsService (/app/Services/OprsService.php - Relevant Methods)

**OPRS Calculation:**
- Formula: OPRS = (0.7 × Elo) + (0.2 × Challenge) + (0.1 × Community)

**OPR Levels:**
| Level | Name | Range |
|-------|------|-------|
| 1.0 | Beginner | 0–599 |
| 2.0 | Novice | 600–899 |
| 3.0 | Intermediate | 900–1099 |
| 3.5 | Upper Intermediate | 1100–1349 |
| 4.0 | Advanced | 1350–1599 |
| 4.5 | Pro | 1600–1849 |
| 5.0+ | Elite | 1850+ |

**Key Methods:**
- `calculateOprs(User)` → float
- `calculateOprLevel(float)` → string
- `updateUserOprs(User, string $reason, ?array $metadata)` → void
  - Records OprsHistory with old/new values

---

## 4. CONTROLLERS

### ClubActivityController (/app/Http/Controllers/ClubActivityController.php)

**Routes & Methods:**
- `index(Club)` – List activities (paginated, filterable by status/type)
- `create(Club)`, `store(Request, Club)` – Create activity
- `edit(Club, ClubActivity)`, `update(Request, Club, ClubActivity)` – Edit
- `destroy(Club, ClubActivity)` – Delete
- `show(Club, ClubActivity)` – View details

**Validation Rules (store):**
```
title: required|string|max:255
type: required|in:one_off,recurring,competition
activity_date: required|date_format:Y-m-d\TH:i
location: nullable|string|max:255
max_participants: required|integer|min:2|max:200
auto_approve: boolean
min_skill_level: nullable|numeric|min:1.0|max:6.0
max_skill_level: nullable|numeric|min:1.0|max:6.0|gte:min_skill_level
recurrence_day: required_if:type,recurring|integer|min:0|max:6
competition_config: nullable|array
competition_config.format: nullable|in:round_robin,pool_play,single_elimination
```

**Auto-generates ClubPost** linked to activity after creation.

---

### ClubMatchController (/app/Http/Controllers/ClubMatchController.php)

**Routes & Methods (JSON responses):**
- `index(Club, ClubActivity)` – GET rounds + matches with player names
- `generate(Request, Club, ClubActivity)` – POST generate matches
  - Validates: `format` (rotating_doubles|fixed_doubles|singles_rr), `court_count` (1-10)
- `saveScore(Request, Club, ClubActivity, ClubActivityMatch)` – PUT score
  - Validates: `team1_score`, `team2_score` (0-99)
- `standings(Club, ClubActivity)` – GET ranked standings
- `createCustom(Request, Club, ClubActivity)` – POST custom match
- `completeRound(Club, ClubActivity, ClubActivityMatchRound)` – PUT mark round done
- `reset(Club, ClubActivity)` – DELETE all rounds/standings

---

### ClubActivityParticipantController (/app/Http/Controllers/ClubActivityParticipantController.php)

**Likely Routes:**
- `rsvp(Club, ClubActivity)` – POST join activity
- `cancelRsvp(Club, ClubActivity)` – DELETE cancel participation
- `index(Club, ClubActivity)` – GET all participants

---

### API Controllers

Located in `/app/Http/Controllers/Api/` – mirror web controllers with JSON responses:
- `Api/ClubActivityController`
- `Api/ClubActivityParticipantController`
- `Api/ClubCompetitionController`

---

## 5. ROUTES

### Web Routes (/routes/web.php)

```
clubs/{club}/activities
├── GET / → index (list)
├── GET create → create form
├── POST / → store
├── GET {activity} → show
├── GET {activity}/edit → edit form
├── PUT {activity} → update
├── DELETE {activity} → destroy
├── POST {activity}/rsvp → join
├── DELETE {activity}/rsvp → cancel
├── GET {activity}/participants → list participants
└── {activity}/competition/...
    ├── GET teams
    ├── POST teams (add team)
    ├── DELETE teams/{team} (remove)
    ├── POST generate-schedule
    ├── PUT matches/{match}/score
    ├── GET standings
    └── GET matches
    
{activity}/matches/...
├── GET rounds → all matches in rounds
├── POST generate → schedule matches
├── PUT rounds/{round}/complete → mark round done
├── DELETE rounds → reset all
├── PUT {match}/score → save match score
├── GET standings → player rankings
└── POST custom → add ad-hoc match
```

### API Routes (/routes/api.php)

Same structure as web routes but JSON-only, under `/api/clubs/{club}/activities` prefix.

---

## 6. VIEWS

Located in `/resources/views/clubs/activities/`:
- `index.blade.php` – List activities
- `create.blade.php` – Create form
- `edit.blade.php` – Edit form
- `show.blade.php` – Activity detail (likely, not verified)

Supporting views in `/resources/views/clubs/`:
- `tabs/_events.blade.php` – Events tab on club page
- `posts/_*` – Post-related partials

---

## 7. CURRENT CAPABILITIES SUMMARY

### Activity Management
✓ Create one-off, recurring (templated), or competition activities  
✓ Auto-approve or manual approval flow  
✓ Skill level filtering (min/max OPR)  
✓ Max participant cap with waitlist promotion  
✓ Recurring template spawning  

### Match Scheduling (Casual)
✓ Singles round-robin (polygon rotation)  
✓ Rotating doubles (partner variety, avoids repeats)  
✓ Fixed doubles (permanent pairs)  
✓ Custom ad-hoc match creation  
✓ Court assignment / distribution  

### Competition (Team-Based)
✓ Team creation with captains  
✓ Team-level match scheduling  
✓ Home/away match structure  
✓ Round/pool/bracket labeling  

### Standings & Results
✓ Individual match standings (wins, losses, points)  
✓ Ranked by wins then point differential  
✓ Team standings (from schema reference)  

### Integration
✓ Auto-generated club feed posts for activities  
✓ OPRS level validation for skill filtering  
✓ User waitlist management with auto-promotion  

---

## 8. EXTENSION POINTS & GAPS

### Potential Enhancements
1. **Skill Rating Integration** – OPR/Elo syncing with match results (currently only RSVP filtering)
2. **Team Roster Management** – Club Competition Teams exist but player assignment/roster endpoints unclear
3. **Schedule Persistence** – No explicit schedule save/load beyond round generation
4. **Bracket Visualization** – Bracket position field exists but no display logic identified
5. **Match Attendance** – No attendance/no-show tracking beyond completion
6. **Post-Match Analytics** – Elo/skill adjustments could be auto-applied after match completion
7. **Recurring Instance Limits** – No built-in end date or max instances for recurring templates
8. **Conflict Detection** – No overlap checking between activities/availability
9. **Notification System** – Waitlist, RSVP approval, match scheduling notifications absent
10. **Export/Reporting** – No standings export, statistics, or historical reports identified

### Database Schema Notes
- `club_activities.competition_config` (JSON) – Schema flexible, specific format not enforced
- `club_competition_matches.pool_label`, `bracket_position` – Fields unused in current controller
- No fields for team roster constraints or match venue/time constraints
- Soft deletes not implemented; cascades on delete

---

## 9. CODE QUALITY OBSERVATIONS

**Strengths:**
- Clear transaction management (lockForUpdate for concurrency)
- Comprehensive fillable arrays & proper casting
- Relationship naming conventions (activity(), user(), etc.)
- Validation in controller layer
- Service-layer business logic isolation

**Areas for Improvement:**
- API controllers may need additional validation/error handling
- Match generation algorithms lack inline documentation
- No dedicated event/listener pattern for OPRS updates
- Limited test file discovery (only by file grep)

---

## Unresolved Questions

1. What is the intended flow for OPRS sync after club activity matches?
2. Are team rosters (player assignments to ClubCompetitionTeam) managed elsewhere?
3. How are pool/bracket labels populated in ClubCompetitionMatch?
4. Is there a UI for creating/managing competition team rosters?
5. Should recurring activities have an explicit end date or max occurrence limit?
6. Are post-match notifications/emails implemented in observers or queued jobs?
