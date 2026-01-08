# Phase 01: Database Schema

**Parent Plan**: [plan.md](./plan.md)
**Date**: 2025-12-18
**Priority**: High
**Implementation Status**: Pending
**Review Status**: Pending

## Overview

Add `partner_id` column to `tournament_athletes` table to support doubles pair registration.

## Key Insights

1. **Current schema**: `tournament_athletes` has no partner/team concept
2. **Matches table**: Uses `athlete1_id`, `athlete2_id` - reuse for doubles (pair1, pair2)
3. **Simple approach**: Partner ID linking simpler than separate teams table
4. **Bidirectional**: When A registers with B, both reference each other
5. **Registration creates pair**: One form submission creates 2 linked athletes

## Requirements

### Functional
- Add `partner_id` nullable foreign key to `tournament_athletes`
- Partner must be from same tournament and category
- Partner relationship should be bidirectional (optional - can enforce in application)

### Non-Functional
- Migration must be reversible
- No data loss for existing athletes

## Architecture

```
tournament_athletes
├── id
├── tournament_id
├── category_id
├── user_id
├── athlete_name
├── partner_id (NEW) ──────► tournament_athletes.id
├── ...
└── timestamps
```

## Related Code Files

### Files to Create
- `/database/migrations/YYYY_MM_DD_HHMMSS_add_partner_id_to_tournament_athletes_table.php`

### Files to Modify
- `/app/Models/TournamentAthlete.php` - Add `partner_id` to fillable, add relationship

## Implementation Steps

### Step 1: Create Migration

```php
// database/migrations/2025_12_18_XXXXXX_add_partner_id_to_tournament_athletes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_athletes', function (Blueprint $table) {
            $table->foreignId('partner_id')
                ->nullable()
                ->after('category_id')
                ->constrained('tournament_athletes')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tournament_athletes', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn('partner_id');
        });
    }
};
```

### Step 2: Update TournamentAthlete Model

```php
// app/Models/TournamentAthlete.php

// Add to $fillable array:
'partner_id',

// Add relationship method:
/**
 * Get the partner athlete (for doubles).
 */
public function partner(): BelongsTo
{
    return $this->belongsTo(TournamentAthlete::class, 'partner_id');
}

/**
 * Check if athlete has a partner (for doubles).
 */
public function hasPartner(): bool
{
    return !is_null($this->partner_id);
}

/**
 * Get pair display name (for doubles).
 */
public function getPairNameAttribute(): string
{
    if (!$this->hasPartner()) {
        return $this->athlete_name;
    }
    return $this->athlete_name . ' / ' . ($this->partner->athlete_name ?? 'Unknown');
}
```

## Todo List

- [ ] Create migration file
- [ ] Run migration
- [ ] Update TournamentAthlete model with partner relationship
- [ ] Add `pair_name` accessor to model
- [ ] Test migration rollback

## Success Criteria

1. Migration runs without errors
2. `partner_id` column exists in `tournament_athletes` table
3. Foreign key constraint works correctly
4. Model relationship `partner()` returns correct athlete
5. `pair_name` accessor returns formatted pair name

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Circular reference (A->B->A) | Low | Application logic prevents, not DB constraint |
| Orphaned partners | Low | `onDelete('set null')` handles deletion |

## Security Considerations

- Partner assignment should only be allowed by tournament owner
- Validation required: partner must be in same tournament/category

## Next Steps

After completing this phase:
1. Proceed to Phase 02: Backend API modifications
2. Update `getCategoryAthletes()` to return pairs for doubles categories
