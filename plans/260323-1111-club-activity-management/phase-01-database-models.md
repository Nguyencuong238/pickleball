# Phase 1: Database Migrations & Model Extensions

**Priority**: P1 | **Effort**: Low | **Status**: Complete

## Context

- Brainstorm Section 4: Database Design
- Existing models: ClubActivity (16 fillable), ClubActivityParticipant, ClubActivityMatch
- Existing migrations in `database/migrations/2026_02_27_*` and `2026_03_03_*`

## Requirements

- Extend 3 existing tables with new columns
- Modify 1 existing pivot table (club_members)
- Create 1 new table (club_member_stats)
- Update 3 existing models with new fillable/casts/relationships

## Migration 1: Extend club_activities, club_activity_participants, club_activity_matches

**File**: `database/migrations/2026_03_23_extend_club_activity_for_open_play.php`

```php
// club_activities - ADD columns
$table->string('qr_code')->nullable()->unique()->after('type');
$table->unsignedInteger('courts_count')->default(1)->after('qr_code');
$table->unsignedInteger('avg_match_duration')->nullable()->after('courts_count');
$table->enum('rotation_mode', ['round_robin', 'oprs_based', 'random'])->default('oprs_based')->after('avg_match_duration');
$table->boolean('gender_preference_enabled')->default(false)->after('rotation_mode');
$table->decimal('oprs_weight', 3, 2)->default(0.50)->after('gender_preference_enabled');
$table->boolean('allow_guests')->default(false)->after('oprs_weight');
$table->dateTime('started_at')->nullable()->after('end_time');
$table->dateTime('ended_at')->nullable()->after('started_at');
// NOTE: type enum needs ALTER to add 'open_play' - use DB::statement

// club_activity_participants - ADD columns
$table->dateTime('checked_in_at')->nullable()->after('status');
$table->string('gender_preference')->nullable()->after('checked_in_at');
$table->enum('current_status', ['idle', 'queued', 'playing', 'left'])->default('idle')->after('gender_preference');
$table->unsignedInteger('queue_position')->nullable()->after('current_status');
$table->unsignedInteger('matches_played_count')->default(0)->after('queue_position');
$table->dateTime('last_match_ended_at')->nullable()->after('matches_played_count');

// club_activity_matches - ADD columns
$table->unsignedInteger('match_number')->default(0)->after('match_type');
$table->unsignedInteger('scheduled_court')->nullable()->after('match_number');
$table->dateTime('started_at')->nullable()->after('scheduled_court');
$table->dateTime('ended_at')->nullable()->after('started_at');
$table->unsignedBigInteger('result_submitted_by')->nullable()->after('ended_at');
$table->boolean('result_confirmed')->default(false)->after('result_submitted_by');
$table->boolean('oprs_processed')->default(false)->after('result_confirmed');
$table->json('set_scores')->nullable()->after('oprs_processed');
$table->foreign('result_submitted_by')->references('id')->on('users')->nullOnDelete();
```

## Migration 2: Create club_member_stats + extend club_members

**File**: `database/migrations/2026_03_23_create_club_member_stats_table.php`

```php
// club_member_stats - NEW table
Schema::create('club_member_stats', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('club_id');
    $table->unsignedBigInteger('user_id');
    $table->unsignedInteger('total_matches')->default(0);
    $table->unsignedInteger('total_wins')->default(0);
    $table->unsignedInteger('total_losses')->default(0);
    $table->unsignedInteger('total_points_scored')->default(0);
    $table->unsignedInteger('total_points_against')->default(0);
    $table->unsignedInteger('activities_participated')->default(0);
    $table->decimal('current_oprs', 5, 2)->nullable();
    $table->dateTime('last_played_at')->nullable();
    $table->timestamps();
    $table->unique(['club_id', 'user_id']);
    $table->foreign('club_id')->references('id')->on('clubs')->cascadeOnDelete();
    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
});

// club_members pivot - ADD columns
Schema::table('club_members', function (Blueprint $table) {
    $table->decimal('initial_oprs', 5, 2)->nullable()->after('role');
    $table->text('notes')->nullable()->after('initial_oprs');
    $table->enum('member_status', ['active', 'inactive', 'suspended'])->default('active')->after('notes');
});
```

## Model Updates

### ClubActivity.php

```php
// Add to fillable array:
'qr_code', 'courts_count', 'avg_match_duration', 'rotation_mode',
'gender_preference_enabled', 'oprs_weight', 'allow_guests', 'started_at', 'ended_at'

// Add casts:
'courts_count' => 'integer',
'gender_preference_enabled' => 'boolean',
'oprs_weight' => 'decimal:2',
'allow_guests' => 'boolean',
'started_at' => 'datetime',
'ended_at' => 'datetime',

// Add method:
public function isOpenPlay(): bool { return $this->type === 'open_play'; }
public function generateQrCode(): string {
    $this->qr_code = (string) Str::uuid();
    $this->save();
    return $this->qr_code;
}
```

### ClubActivityParticipant.php

```php
// Add to fillable:
'checked_in_at', 'gender_preference', 'current_status', 'queue_position',
'matches_played_count', 'last_match_ended_at'

// Add casts:
'checked_in_at' => 'datetime',
'last_match_ended_at' => 'datetime',
'matches_played_count' => 'integer',
'queue_position' => 'integer',

// Add scopes:
public function scopeQueued($q) { return $q->where('current_status', 'queued'); }
public function scopePlaying($q) { return $q->where('current_status', 'playing'); }
public function scopeCheckedIn($q) { return $q->whereNotNull('checked_in_at'); }
```

### ClubActivityMatch.php

```php
// Add to fillable:
'match_number', 'scheduled_court', 'started_at', 'ended_at',
'result_submitted_by', 'result_confirmed', 'oprs_processed', 'set_scores'

// Add casts:
'started_at' => 'datetime',
'ended_at' => 'datetime',
'result_confirmed' => 'boolean',
'oprs_processed' => 'boolean',
'set_scores' => 'array',
'match_number' => 'integer',
'scheduled_court' => 'integer',

// Add relationship:
public function submittedBy() { return $this->belongsTo(User::class, 'result_submitted_by'); }
```

### ClubMemberStat.php (NEW)

```php
// New model: app/Models/ClubMemberStat.php
// fillable: club_id, user_id, total_matches, total_wins, total_losses,
//           total_points_scored, total_points_against, activities_participated,
//           current_oprs, last_played_at
// Relationships: club(), user()
// Computed: getWinRateAttribute() => total_matches > 0 ? round(total_wins / total_matches * 100, 1) : 0
```

## Implementation Steps

1. Create migration file 1 (extend tables)
2. Create migration file 2 (new table + club_members extend)
3. Update ClubActivity model (fillable, casts, methods)
4. Update ClubActivityParticipant model (fillable, casts, scopes)
5. Update ClubActivityMatch model (fillable, casts, relationships)
6. Create ClubMemberStat model
7. Run `php artisan migrate`
8. Run compile check

## Todo

- [x] Migration 1: extend club_activities, club_activity_participants, club_activity_matches
- [x] Migration 2: create club_member_stats + extend club_members
- [x] Update ClubActivity model
- [x] Update ClubActivityParticipant model
- [x] Update ClubActivityMatch model
- [x] Create ClubMemberStat model
- [x] Run migrations successfully
- [x] Compile check passes

## Success Criteria

- All migrations run without error
- Models have correct fillable/casts
- `php artisan migrate:fresh` passes (if safe) or `php artisan migrate` on existing DB
- No syntax errors in modified models

## Risk

- ALTER ENUM for `type` column may need raw SQL (`DB::statement`) on MySQL
- club_members pivot may not be standard table - verify structure before migration
