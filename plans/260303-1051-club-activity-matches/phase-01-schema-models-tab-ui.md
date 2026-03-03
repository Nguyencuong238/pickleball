# Phase 01 - Schema, Models, Tab UI

## Context Links
- Plan overview: `plans/260303-1051-club-activity-matches/plan.md`
- Tab nav: `resources/views/clubs/activities/partials/_tab-navigation.blade.php`
- Show view: `resources/views/clubs/activities/show.blade.php`
- ClubActivity model: `app/Models/ClubActivity.php`
- Competition panel (reference): `resources/views/clubs/activities/partials/_competition-panel.blade.php`

## Overview
- **Priority:** P1 (blocks all other phases)
- **Status:** complete
- **Goal:** Create DB schema, Eloquent models, add "Tran dau" tab for non-competition activities, render empty state

## Requirements
- 3 new tables: `club_activity_match_rounds`, `club_activity_matches`, `club_activity_match_standings`
- "Trận đấu" tab shown when `$activity->type !== 'competition'` AND not a recurring template (exclude parent activities with `type=recurring` and no `parent_activity_id`)
<!-- Updated: Validation Session 1 - Hide tab on recurring templates -->
- Empty state: "Generate Matches" button + "Create Custom Match" link (management only)
- Non-management sees message: "Chưa có trận đấu nào"

## Architecture

### Schema
```sql
club_activity_match_rounds
  id, club_activity_id (FK), round_number (tinyint),
  status ENUM(pending, in_progress, completed),
  timestamps

club_activity_matches
  id, round_id (FK club_activity_match_rounds), court_number (tinyint nullable),
  match_type ENUM(singles, doubles),
  player1_id (FK users), player2_id (FK users nullable),   -- team 1
  player3_id (FK users), player4_id (FK users nullable),   -- team 2
  team1_score (tinyint nullable), team2_score (tinyint nullable),
  status ENUM(scheduled, in_progress, completed),
  completed_at (nullable timestamp), timestamps

club_activity_match_standings
  id, club_activity_id (FK), user_id (FK users),
  matches_played, wins, losses, points_scored, points_against (all tinyint default 0),
  timestamps
  UNIQUE(club_activity_id, user_id)
```

### Model Relationships
- `ClubActivity` hasMany `ClubActivityMatchRound`
- `ClubActivityMatchRound` hasMany `ClubActivityMatch` + belongsTo `ClubActivity`
- `ClubActivityMatch` belongsTo `ClubActivityMatchRound` + belongsTo User x4 (player1..4)
- `ClubActivityMatchStanding` belongsTo `ClubActivity` + belongsTo `User`

## Related Code Files

### Create
- `database/migrations/2026_03_03_create_club_activity_match_rounds_table.php`
- `database/migrations/2026_03_03_create_club_activity_matches_table.php`
- `database/migrations/2026_03_03_create_club_activity_match_standings_table.php`
- `app/Models/ClubActivityMatchRound.php`
- `app/Models/ClubActivityMatch.php`
- `app/Models/ClubActivityMatchStanding.php`
- `resources/views/clubs/activities/partials/_matches-panel.blade.php`
- `resources/views/clubs/activities/partials/_matches-styles.blade.php`

### Modify
- `app/Models/ClubActivity.php` — add 3 hasMany relationships
- `resources/views/clubs/activities/partials/_tab-navigation.blade.php` — add matches tab button
- `resources/views/clubs/activities/show.blade.php` — add matches tab panel

## Implementation Steps

1. **Migrations** — create 3 migration files in order (rounds → matches → standings). Use `unsignedBigInteger` for FK columns. Add `onDelete('cascade')` for `round_id → club_activity_match_rounds`. Index `club_activity_id` on all 3 tables.

2. **Models**
   - `ClubActivityMatchRound`: `$fillable = ['club_activity_id','round_number','status']`, status default `pending`, hasMany matches, belongsTo ClubActivity
   - `ClubActivityMatch`: `$fillable` all columns, `$casts = ['completed_at' => 'datetime']`, belongsTo round + 4 player BelongsTo relationships named `player1`, `player2`, `player3`, `player4`
   - `ClubActivityMatchStanding`: `$fillable` all columns, belongsTo activity + user. Add helper `getPointDifferentialAttribute(): int` = `points_scored - points_against`

3. **ClubActivity model** — append at bottom of relationships section:
   ```php
   public function matchRounds(): HasMany
   {
       return $this->hasMany(ClubActivityMatchRound::class);
   }
   public function matchStandings(): HasMany
   {
       return $this->hasMany(ClubActivityMatchStanding::class);
   }
   ```

4. **Tab navigation** — add button after participants button, before `@endif`:
   ```blade
   @if($activity->type !== 'competition' && !($activity->type === 'recurring' && !$activity->parent_activity_id))
   <button class="tab-btn" data-tab="matches" role="tab" aria-selected="false">
       [TROPHY] Trận đấu
   </button>
   @endif
   {{-- Hide on recurring templates (parent activities); show on instances and one_off --}}
   ```
   Use same SVG trophy icon as competition tab (copy existing SVG).

5. **show.blade.php** — add panel block after competition block:
   ```blade
   @if($activity->type !== 'competition')
   <div class="tab-content" id="tab-matches" role="tabpanel">
       @include('clubs.activities.partials._matches-panel')
   </div>
   @endif
   ```

6. **_matches-panel.blade.php** — empty state with:
   - Container `id="matches-panel"`
   - `id="matches-rounds-container"` — empty by default, JS populates
   - Empty state div `id="matches-empty-state"` visible by default
   - If `$isManagement`: "Tạo trận đấu" button (`onclick="openGenerateModal()"`) + "Thêm trận tùy chỉnh" link
   - If not management: "Chưa có trận đấu nào. Liên hệ ban tổ chức."
   - Include `_matches-styles.blade.php` and `_matches-scripts.blade.php` (stubs for now)

7. **_matches-styles.blade.php** — minimal scoped CSS for `.matches-panel`, `.matches-empty`, `.round-section`, `.match-card`. Mirror style patterns from `_competition-styles.blade.php`.

## Todo List
- [ ] Migration: `club_activity_match_rounds`
- [ ] Migration: `club_activity_matches`
- [ ] Migration: `club_activity_match_standings`
- [ ] Model: `ClubActivityMatchRound`
- [ ] Model: `ClubActivityMatch`
- [ ] Model: `ClubActivityMatchStanding`
- [ ] Update `ClubActivity` model with relationships
- [ ] Update `_tab-navigation.blade.php`
- [ ] Update `show.blade.php`
- [ ] Create `_matches-panel.blade.php`
- [ ] Create `_matches-styles.blade.php`
- [ ] Run `php artisan migrate` and verify tables

## Success Criteria
- `php artisan migrate` runs without errors
- "Trận đấu" tab visible on one_off/recurring activities
- "Trận đấu" tab NOT visible on competition activities (competition tab still works)
- Empty state renders correctly for management vs non-management
- Tab hash `#matches` works via URL

## Risk Assessment
- **Migration order matters:** rounds must exist before matches (FK). Create in correct sequence.
- **player1_id nullable concern:** singles only uses player1/player3, doubles uses all 4. Nullable player2/player4 is correct.
- **Tab collision:** existing `competition` tab uses `data-tab="competition"`, new tab uses `data-tab="matches"` — no collision.
