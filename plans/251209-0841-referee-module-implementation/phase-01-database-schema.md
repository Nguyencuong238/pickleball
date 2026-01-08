# Phase 1: Database Schema

**Date**: 2025-12-09
**Status**: Completed
**Completion Date**: 2025-12-09
**Priority**: High (Foundation)
**Parent Plan**: [plan.md](./plan.md)

---

## Context

Create database infrastructure for referee system. Shared auth requires no new users table, only role additions. Pivot table tracks tournament-referee assignments with audit trail. Matches table gains referee_id FK and cached referee_name.

---

## Overview

Four migrations:
1. Add referee role to permissions
2. Create tournament_referees pivot with audit fields
3. Add referee_id, referee_name to matches table
4. Add referee profile fields to users table (optional for phase 2)

---

## Key Insights from Research

**Audit Trail Requirements**:
- Track who assigned referee (assigned_by FK to users)
- Track when assigned (assigned_at timestamp)
- Preserve assignment history even if referee removed from tournament

**Referee Assignment Constraints**:
- Referee must be assigned to tournament before match assignment
- One referee per match (not multi-ref like basketball)
- Soft deletes prevent cascade issues

**From Laravel Patterns**:
- Follow TournamentAthlete pivot pattern (tournament_id, user_id, metadata)
- Use foreign key constraints with cascadeOnDelete for data integrity
- Cache referee_name in matches to avoid join overhead on leaderboards

---

## Requirements

### Functional

1. Store referee role in Spatie permissions
2. Track tournament-referee assignments with who/when metadata
3. Link one referee to each match
4. Cache referee name to avoid joins
5. Support soft deletes for compliance

### Non-Functional

1. Index tournament_id, user_id on tournament_referees for fast lookups
2. Index referee_id on matches for referee dashboard queries
3. Foreign key integrity prevents orphaned assignments
4. Nullable referee_id allows unassigned matches

---

## Related Files

### Migrations to Create

| File | Action | Description |
|------|--------|-------------|
| `database/migrations/2025_12_09_000001_add_referee_role_to_permissions.php` | CREATE | Add referee role and permissions |
| `database/migrations/2025_12_09_000002_create_tournament_referees_table.php` | CREATE | Pivot table with audit fields |
| `database/migrations/2025_12_09_000003_add_referee_to_matches_table.php` | CREATE | Add referee_id, referee_name to matches |
| `database/migrations/2025_12_09_000004_add_referee_fields_to_users_table.php` | CREATE | Optional profile fields (bio, status) |

### Seeder Updates

| File | Action | Description |
|------|--------|-------------|
| `database/seeders/PermissionSeeder.php` | MODIFY | Add referee role and permissions |

---

## Implementation Steps

### Step 1: Add Referee Role to Permissions

**Migration**: `2025_12_09_000001_add_referee_role_to_permissions.php`

```php
public function up(): void
{
    // Permissions handled by seeder, just placeholder
}
```

**Seeder Update**: `PermissionSeeder.php`

```php
// Add to existing permissions
Permission::firstOrCreate(['name' => 'manage-matches']);
Permission::firstOrCreate(['name' => 'submit-scores']);

// Create referee role
$refereeRole = Role::firstOrCreate(['name' => 'referee']);

// Assign permissions
$refereeRole->syncPermissions([
    'manage-matches',
    'submit-scores',
]);
```

### Step 2: Create Tournament Referees Pivot Table

**Migration**: `2025_12_09_000002_create_tournament_referees_table.php`

```php
public function up(): void
{
    Schema::create('tournament_referees', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->timestamp('assigned_at')->useCurrent();
        $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
        $table->string('status')->default('active'); // active, inactive
        $table->timestamps();
        $table->softDeletes();

        // Prevent duplicate assignments
        $table->unique(['tournament_id', 'user_id']);

        // Indexes for queries
        $table->index('tournament_id');
        $table->index('user_id');
        $table->index('status');
    });
}

public function down(): void
{
    Schema::dropIfExists('tournament_referees');
}
```

### Step 3: Add Referee to Matches Table

**Migration**: `2025_12_09_000003_add_referee_to_matches_table.php`

```php
public function up(): void
{
    Schema::table('matches', function (Blueprint $table) {
        $table->foreignId('referee_id')->nullable()->constrained('users')->nullOnDelete();
        $table->string('referee_name')->nullable();

        $table->index('referee_id');
    });
}

public function down(): void
{
    Schema::table('matches', function (Blueprint $table) {
        $table->dropForeign(['referee_id']);
        $table->dropColumn(['referee_id', 'referee_name']);
    });
}
```

### Step 4: Add Referee Profile Fields to Users (Optional)

**Migration**: `2025_12_09_000004_add_referee_fields_to_users_table.php`

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->text('referee_bio')->nullable();
        $table->string('referee_status')->default('active'); // active, inactive
        $table->integer('matches_officiated')->default(0);
        $table->decimal('referee_rating', 3, 2)->nullable(); // 0.00-5.00
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['referee_bio', 'referee_status', 'matches_officiated', 'referee_rating']);
    });
}
```

---

## Todo List

- [ ] Create migration: add_referee_role_to_permissions
- [ ] Update PermissionSeeder with referee role and permissions
- [ ] Create migration: create_tournament_referees_table
- [ ] Create migration: add_referee_to_matches_table
- [ ] Create migration: add_referee_fields_to_users_table
- [ ] Run migrations in dev environment
- [ ] Verify foreign key constraints work
- [ ] Test tournament deletion cascades to tournament_referees
- [ ] Seed test referee role to existing users

---

## Success Criteria

- Migration runs without errors
- Foreign key constraints enforce data integrity
- Can assign multiple referees to tournament
- Can assign one referee to match from tournament pool
- Soft delete preserves assignment history
- Indexes improve query performance (verify with EXPLAIN)

---

## Risk Assessment

**Risk**: Migration fails if existing matches have null constraints
**Mitigation**: referee_id is nullable, migration safe on existing data

**Risk**: Cascade deletes remove too much data
**Mitigation**: Use nullOnDelete for assigned_by to preserve audit trail even if assigner deleted

**Risk**: Unique constraint prevents re-assigning referee
**Mitigation**: Soft deletes allow restore; status field allows inactive/active toggle

---

## Security Considerations

- Foreign key constraints prevent invalid user_id/tournament_id
- Soft deletes prevent accidental data loss
- assigned_by tracks accountability
- No sensitive data in tournament_referees table

---

## Next Steps

After migration complete:
1. Phase 2: Create TournamentReferee model with relationships
2. Test data: Seed referee role to 2-3 test users
3. Verify queries perform well with indexes
