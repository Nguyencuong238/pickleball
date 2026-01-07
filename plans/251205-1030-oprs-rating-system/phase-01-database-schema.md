# Phase 1: Database Schema & Migrations

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: None (first phase)
**Related Docs**: [code-standards.md](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2025-12-05 |
| Description | Create database tables for OPRS components |
| Priority | Critical |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

1. Existing user table already has `elo_rating`, `elo_rank`, OCR stats
2. Need new tables for challenges, activities, OPRS history
3. Challenge types are fixed (4 types) - use enum
4. Activity types are extensible - use string
5. OPRS history needed for tracking/debugging

## Requirements

### Functional
- Store challenge results with scores and points
- Track community activities with references
- Record OPRS history for auditing
- Support match type filtering (official, partner, ocr, ranked)
- Add OPRS fields to users table

### Non-Functional
- Indexed for leaderboard queries
- Foreign key constraints for data integrity
- Timestamps for all records

## Architecture

### New Tables

```
users (extend)
├── challenge_score DECIMAL(10,2) DEFAULT 0
├── community_score DECIMAL(10,2) DEFAULT 0
├── total_oprs DECIMAL(10,2) DEFAULT 700 (0.7 * 1000)
└── opr_level VARCHAR(10) DEFAULT '2.0'

challenge_results
├── id (PK)
├── user_id (FK users)
├── challenge_type ENUM
├── score INT
├── passed BOOLEAN
├── points_earned DECIMAL(10,2)
├── verified_by (FK users, nullable)
├── verified_at TIMESTAMP
└── timestamps

community_activities
├── id (PK)
├── user_id (FK users)
├── activity_type VARCHAR
├── points_earned DECIMAL(10,2)
├── reference_id (nullable)
├── reference_type VARCHAR (nullable)
├── metadata JSON (nullable)
└── timestamps

oprs_histories
├── id (PK)
├── user_id (FK users)
├── elo_score DECIMAL(10,2)
├── challenge_score DECIMAL(10,2)
├── community_score DECIMAL(10,2)
├── total_oprs DECIMAL(10,2)
├── opr_level VARCHAR(10)
├── change_reason VARCHAR
├── metadata JSON (nullable)
└── timestamps
```

### Extend ocr_matches Table
```sql
ALTER TABLE ocr_matches
ADD COLUMN match_category ENUM('official', 'partner', 'ocr', 'ranked_challenge')
DEFAULT 'ocr' AFTER match_type;
```

## Related Code Files

| File | Action | Purpose |
|------|--------|---------|
| `database/migrations/2025_12_05_*_add_oprs_fields_to_users.php` | Create | Add OPRS columns to users |
| `database/migrations/2025_12_05_*_create_challenge_results_table.php` | Create | Challenge results storage |
| `database/migrations/2025_12_05_*_create_community_activities_table.php` | Create | Community activity storage |
| `database/migrations/2025_12_05_*_create_oprs_histories_table.php` | Create | OPRS change history |
| `database/migrations/2025_12_05_*_add_match_category_to_ocr_matches.php` | Create | Match type categorization |

## Implementation Steps

### Step 1: Add OPRS Fields to Users
```php
Schema::table('users', function (Blueprint $table) {
    $table->decimal('challenge_score', 10, 2)->default(0)->after('ocr_losses');
    $table->decimal('community_score', 10, 2)->default(0)->after('challenge_score');
    $table->decimal('total_oprs', 10, 2)->default(700)->after('community_score');
    $table->string('opr_level', 10)->default('2.0')->after('total_oprs');

    $table->index('total_oprs');
    $table->index('opr_level');
});
```

### Step 2: Create Challenge Results Table
```php
Schema::create('challenge_results', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->enum('challenge_type', [
        'dinking_rally',
        'drop_shot',
        'serve_accuracy',
        'monthly_test'
    ]);
    $table->unsignedSmallInteger('score');
    $table->boolean('passed')->default(false);
    $table->decimal('points_earned', 10, 2)->default(0);
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();

    $table->index('user_id');
    $table->index('challenge_type');
    $table->index(['user_id', 'challenge_type']);
});
```

### Step 3: Create Community Activities Table
```php
Schema::create('community_activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('activity_type', 50); // check_in, event, referral, weekly_matches, monthly_challenge
    $table->decimal('points_earned', 10, 2);
    $table->unsignedBigInteger('reference_id')->nullable();
    $table->string('reference_type', 100)->nullable(); // For polymorphic references
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->index('user_id');
    $table->index('activity_type');
    $table->index(['user_id', 'activity_type']);
    $table->index(['user_id', 'created_at']);
});
```

### Step 4: Create OPRS Histories Table
```php
Schema::create('oprs_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->decimal('elo_score', 10, 2);
    $table->decimal('challenge_score', 10, 2);
    $table->decimal('community_score', 10, 2);
    $table->decimal('total_oprs', 10, 2);
    $table->string('opr_level', 10);
    $table->string('change_reason', 100);
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->index('user_id');
    $table->index(['user_id', 'created_at']);
});
```

### Step 5: Add Match Category to OCR Matches
```php
Schema::table('ocr_matches', function (Blueprint $table) {
    $table->enum('match_category', [
        'official',        // OnePickleball official tournaments
        'partner',         // Partner tournaments
        'ocr',             // OCR challenge matches
        'ranked_challenge' // Supervised 1v1/2v2
    ])->default('ocr')->after('match_type');

    $table->index('match_category');
});
```

## Todo List

- [ ] Create migration for OPRS fields on users table
- [ ] Create challenge_results table migration
- [ ] Create community_activities table migration
- [ ] Create oprs_histories table migration
- [ ] Create migration for match_category on ocr_matches
- [ ] Run migrations and verify schema
- [ ] Update existing users with calculated OPRS

## Success Criteria

1. All migrations run without errors
2. Foreign key constraints work correctly
3. Indexes created for performance
4. Default values set appropriately
5. Rollback works cleanly

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Existing user data conflicts | Low | Use defaults for new columns |
| Large table ALTER | Low | Users table is small |
| Index overhead | Low | Minimal indexes added |

## Security Considerations

- No sensitive data exposed
- Foreign keys prevent orphan records
- Verified_by tracks admin accountability

## Next Steps

After migrations complete:
1. Proceed to [Phase 2: Models & Relationships](./phase-02-models-relationships.md)
2. Create model classes with fillable/casts
3. Define relationships
