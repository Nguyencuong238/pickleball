# Phase 1: Database Schema

## Context Links

- [Parent Plan](./plan.md)
- [Code Standards](../../docs/code-standards.md)
- [Existing Match Migration](../../database/migrations/2025_11_19_000004_create_matches_table.php)

## Overview

- **Date**: 2025-12-02
- **Priority**: High
- **Implementation Status**: Pending
- **Review Status**: Pending

Create database schema for OCR ranking system including Elo tracking, ranked matches, and badges.

## Key Insights

1. Existing `matches` table is tournament-specific; OCR needs separate `ocr_matches` table
2. User model exists, needs Elo fields added
3. Keep OCR matches separate from tournament matches to avoid complexity
4. Elo history enables rollback and audit trail

## Requirements

### Functional

- Store user Elo ratings (base: 1000)
- Track ranked matches with full workflow states
- Record Elo changes per match
- Store badges and their criteria

### Non-Functional

- Index frequently queried columns (user_id, status, created_at)
- Use foreign key constraints with cascade deletes
- Support both singles (1v1) and doubles (2v2)

## Architecture

### ERD

```
users
  +-- elo_rating (int, default 1000)
  +-- elo_rank (string, e.g., "Bronze", "Silver")
  +-- total_ocr_matches (int)
  +-- ocr_wins (int)
  +-- ocr_losses (int)

ocr_matches
  +-- id
  +-- match_type (singles/doubles)
  +-- challenger_id (FK users)
  +-- opponent_id (FK users)
  +-- challenger_partner_id (nullable, FK users) -- for doubles
  +-- opponent_partner_id (nullable, FK users) -- for doubles
  +-- challenger_score (int)
  +-- opponent_score (int)
  +-- winner_team (challenger/opponent)
  +-- status (pending/accepted/in_progress/result_submitted/confirmed/disputed/cancelled)
  +-- scheduled_date
  +-- scheduled_time
  +-- location
  +-- notes
  +-- result_submitted_by (FK users)
  +-- result_submitted_at
  +-- confirmed_at
  +-- disputed_reason
  +-- elo_challenger_before
  +-- elo_opponent_before
  +-- elo_challenger_after
  +-- elo_opponent_after
  +-- elo_change

elo_histories
  +-- id
  +-- user_id (FK users)
  +-- ocr_match_id (FK ocr_matches, nullable)
  +-- elo_before
  +-- elo_after
  +-- change_amount
  +-- change_reason (match_win/match_loss/admin_adjustment)

user_badges
  +-- id
  +-- user_id (FK users)
  +-- badge_type (first_win/streak_5/rank_gold/etc.)
  +-- earned_at
  +-- metadata (JSON)
```

## Related Code Files

### Files to Create

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/YYYY_MM_DD_HHMMSS_add_elo_fields_to_users_table.php` | Create | Add Elo columns to users |
| `database/migrations/YYYY_MM_DD_HHMMSS_create_ocr_matches_table.php` | Create | OCR matches table |
| `database/migrations/YYYY_MM_DD_HHMMSS_create_elo_histories_table.php` | Create | Elo change history |
| `database/migrations/YYYY_MM_DD_HHMMSS_create_user_badges_table.php` | Create | Badge achievements |

## Implementation Steps

### Step 1: Add Elo Fields to Users Table

```php
// database/migrations/YYYY_MM_DD_HHMMSS_add_elo_fields_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->integer('elo_rating')->default(1000)->after('status');
    $table->string('elo_rank')->default('Bronze')->after('elo_rating');
    $table->unsignedInteger('total_ocr_matches')->default(0)->after('elo_rank');
    $table->unsignedInteger('ocr_wins')->default(0)->after('total_ocr_matches');
    $table->unsignedInteger('ocr_losses')->default(0)->after('ocr_wins');

    $table->index('elo_rating');
    $table->index('elo_rank');
});
```

### Step 2: Create OCR Matches Table

```php
// database/migrations/YYYY_MM_DD_HHMMSS_create_ocr_matches_table.php
Schema::create('ocr_matches', function (Blueprint $table) {
    $table->id();

    // Match type
    $table->enum('match_type', ['singles', 'doubles'])->default('singles');

    // Challenger team
    $table->foreignId('challenger_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('challenger_partner_id')->nullable()->constrained('users')->nullOnDelete();

    // Opponent team
    $table->foreignId('opponent_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('opponent_partner_id')->nullable()->constrained('users')->nullOnDelete();

    // Scores
    $table->unsignedTinyInteger('challenger_score')->default(0);
    $table->unsignedTinyInteger('opponent_score')->default(0);
    $table->enum('winner_team', ['challenger', 'opponent'])->nullable();

    // Status workflow
    $table->enum('status', [
        'pending',         // Waiting for opponent to accept
        'accepted',        // Opponent accepted, match scheduled
        'in_progress',     // Match started
        'result_submitted', // One party submitted result
        'confirmed',       // Both parties confirmed result
        'disputed',        // Result disputed
        'cancelled'        // Match cancelled
    ])->default('pending');

    // Schedule
    $table->date('scheduled_date')->nullable();
    $table->time('scheduled_time')->nullable();
    $table->string('location')->nullable();
    $table->text('notes')->nullable();

    // Result tracking
    $table->foreignId('result_submitted_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('result_submitted_at')->nullable();
    $table->timestamp('confirmed_at')->nullable();
    $table->text('disputed_reason')->nullable();

    // Elo tracking
    $table->integer('elo_challenger_before')->nullable();
    $table->integer('elo_opponent_before')->nullable();
    $table->integer('elo_challenger_after')->nullable();
    $table->integer('elo_opponent_after')->nullable();
    $table->integer('elo_change')->nullable(); // Absolute change amount

    $table->timestamps();

    // Indexes
    $table->index('challenger_id');
    $table->index('opponent_id');
    $table->index('status');
    $table->index('scheduled_date');
    $table->index(['status', 'created_at']);
});
```

### Step 3: Create Elo Histories Table

```php
// database/migrations/YYYY_MM_DD_HHMMSS_create_elo_histories_table.php
Schema::create('elo_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('ocr_match_id')->nullable()->constrained('ocr_matches')->nullOnDelete();
    $table->integer('elo_before');
    $table->integer('elo_after');
    $table->integer('change_amount');
    $table->enum('change_reason', ['match_win', 'match_loss', 'admin_adjustment'])->default('match_win');
    $table->timestamps();

    $table->index('user_id');
    $table->index(['user_id', 'created_at']);
});
```

### Step 4: Create User Badges Table

```php
// database/migrations/YYYY_MM_DD_HHMMSS_create_user_badges_table.php
Schema::create('user_badges', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('badge_type'); // e.g., 'first_win', 'streak_5', 'rank_gold'
    $table->timestamp('earned_at');
    $table->json('metadata')->nullable(); // Extra data like streak count
    $table->timestamps();

    $table->index('user_id');
    $table->unique(['user_id', 'badge_type']); // One badge per type per user
});
```

## Todo List

- [ ] Create migration for users table Elo fields
- [ ] Create migration for ocr_matches table
- [ ] Create migration for elo_histories table
- [ ] Create migration for user_badges table
- [ ] Run migrations
- [ ] Verify indexes created correctly

## Success Criteria

1. All migrations run without errors
2. Foreign key constraints work correctly
3. Indexes created for query optimization
4. Default values set appropriately (Elo = 1000)

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Existing users need Elo initialization | Low | Migration sets default 1000 |
| Index overhead on writes | Low | Minimal indexes, only essential ones |

## Security Considerations

- No sensitive data exposed
- Foreign key cascades prevent orphan records
- Status enum prevents invalid states

## Next Steps

After migrations complete, proceed to [Phase 2: Core Models](./phase-02-core-models.md)
