# Phase 1: Database Migrations

## Context Links
- Current migration: `database/migrations/2025_12_25_085802_create_club_activities_table.php`
- Max participants migration: `database/migrations/2025_12_27_add_max_participants_to_club_activities.php`
- Club members: `database/migrations/2025_12_25_085735_create_club_members_table.php`
- League tables pattern: `database/migrations/*league*`

## Overview
- **Priority:** P1 -- all other phases depend on this
- **Status:** complete
- Add columns to `club_activities` for type, recurrence, skill level, end_time, created_by
- Create `club_activity_participants` for RSVP/waitlist
- Create `club_competition_teams` for competition participant teams
- Create `club_competition_matches` for competition match tracking
- Create `club_competition_standings` for competition standings

## Requirements
- Backward compatible: existing rows get `type = 'one_off'` default
- Support recurring schedule metadata (day_of_week, recurrence parent)
- RSVP with status (confirmed, waitlisted, cancelled)
- Competition match/standings tables adapted from League pattern but lighter

## Migration 1: `2026_02_27_upgrade_club_activities_table.php`

### Columns to ADD to `club_activities`:

```php
Schema::table('club_activities', function (Blueprint $table) {
    // Activity type
    $table->enum('type', ['one_off', 'recurring', 'competition'])->default('one_off')->after('club_id');

    // Created by (user who created the activity)
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('club_id');

    // Time fields (activity_date is start, add end_time)
    $table->time('end_time')->nullable()->after('activity_date');

    // Recurrence fields
    $table->unsignedTinyInteger('recurrence_day')->nullable()->after('end_time'); // 0=Sun..6=Sat
    $table->foreignId('parent_activity_id')->nullable()->constrained('club_activities')->nullOnDelete()->after('recurrence_day');
    $table->boolean('auto_approve')->default(true)->after('parent_activity_id');

    // Skill level filter
    $table->string('min_skill_level', 5)->nullable()->after('auto_approve'); // e.g. "2.0"
    $table->string('max_skill_level', 5)->nullable()->after('min_skill_level'); // e.g. "4.5"

    // Competition config (JSON -- format, match_format, points, etc.)
    // format: round_robin | pool_play | single_elimination
    $table->json('competition_config')->nullable()->after('max_skill_level');
    // Note: competition_config JSON should include 'format' key

    // Index for recurring lookups
    $table->index(['type', 'status']);
    $table->index(['parent_activity_id']);
});
```

### Column details:
| Column | Type | Nullable | Default | Purpose |
|--------|------|----------|---------|---------|
| type | enum | no | 'one_off' | Activity type discriminator |
| created_by | FK users | yes | null | Who created (backfill with club.user_id) |
| end_time | time | yes | null | End time of activity |
| recurrence_day | tinyint(0-6) | yes | null | Day of week for recurring |
| parent_activity_id | FK self | yes | null | Links instance to recurring template |
| auto_approve | boolean | no | true | Auto-approve RSVP or waitlist |
| min_skill_level | varchar(5) | yes | null | Min OPR level filter |
| max_skill_level | varchar(5) | yes | null | Max OPR level filter |
| competition_config | json | yes | null | Format (round_robin/pool_play/single_elimination), points config for competitions |
<!-- Updated: Validation Session 1 - competition_config includes format key for 3 competition formats -->

## Migration 2: `2026_02_27_create_club_activity_participants_table.php`

```php
Schema::create('club_activity_participants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->enum('status', ['confirmed', 'waitlisted', 'cancelled'])->default('confirmed');
    $table->unsignedInteger('waitlist_position')->nullable();
    $table->timestamp('responded_at')->nullable();
    $table->timestamps();

    $table->unique(['club_activity_id', 'user_id']);
    $table->index(['club_activity_id', 'status']);
});
```

## Migration 3: `2026_02_27_create_club_competition_teams_table.php`

```php
Schema::create('club_competition_teams', function (Blueprint $table) {
    $table->id();
    $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
    $table->string('name');
    $table->foreignId('captain_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->enum('status', ['active', 'withdrawn'])->default('active');
    $table->timestamps();

    $table->index('club_activity_id');
});
```

## Migration 4: `2026_02_27_create_club_competition_matches_table.php`

```php
Schema::create('club_competition_matches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
    $table->unsignedInteger('round_number');
    $table->foreignId('home_team_id')->constrained('club_competition_teams')->cascadeOnDelete();
    $table->foreignId('away_team_id')->constrained('club_competition_teams')->cascadeOnDelete();
    $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('scheduled');
    $table->unsignedSmallInteger('home_score')->nullable();
    $table->unsignedSmallInteger('away_score')->nullable();
    $table->foreignId('winner_team_id')->nullable()->constrained('club_competition_teams')->nullOnDelete();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();

    $table->index(['club_activity_id', 'round_number']);
});
```

## Migration 5: `2026_02_27_create_club_competition_standings_table.php`

```php
Schema::create('club_competition_standings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
    $table->foreignId('club_competition_team_id')->constrained('club_competition_teams')->cascadeOnDelete();
    $table->unsignedInteger('played')->default(0);
    $table->unsignedInteger('wins')->default(0);
    $table->unsignedInteger('losses')->default(0);
    $table->unsignedInteger('draws')->default(0);
    $table->integer('points')->default(0);
    $table->unsignedInteger('rank')->default(0);
    $table->timestamps();

    $table->unique(['club_activity_id', 'club_competition_team_id']);
});
```

## Migration 6: `2026_02_27_update_club_policy_management_check.php`

Update `ClubPolicy` -- not a migration but listed here as a DB-adjacent concern. The `update` check currently only allows `$user->id === $club->user_id`. Must change to use `$club->isManagement($user)` for activity management.

## Todo List
- [x] Create migration 1: upgrade club_activities
- [x] Create migration 2: club_activity_participants
- [x] Create migration 3: club_competition_teams
- [x] Create migration 4: club_competition_matches (added pool_label, bracket_position)
- [x] Create migration 5: club_competition_standings (custom short index name)
- [x] Run `php artisan migrate` and verify
- [ ] Backfill existing rows: set `created_by` from `club.user_id`

## Success Criteria
- All migrations run without error
- Existing club_activities data preserved with `type = 'one_off'`
- Foreign key constraints correct
- Indexes in place for query performance

## Risk Assessment
- **Enum changes**: MySQL enum column modification can be tricky; using ADD column avoids this
- **Backfill**: Existing rows need `created_by` populated -- handle in migration or seeder
- **Self-referential FK**: `parent_activity_id` FK on same table -- MySQL handles this fine
