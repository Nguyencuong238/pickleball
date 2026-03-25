# Phase 1: Database Migration

**Priority:** High | **Status:** Pending | **Effort:** S

## Overview

Add `best_of` and `points_per_set` columns to `club_activities` table.

## Implementation Steps

1. Create migration: `2026_03_25_add_score_config_to_club_activities_table.php`

```php
Schema::table('club_activities', function (Blueprint $table) {
    $table->unsignedTinyInteger('best_of')->default(1)->after('oprs_weight');
    $table->unsignedTinyInteger('points_per_set')->default(21)->after('best_of');
});
```

## Notes

- `best_of`: 1 or 3 (number of sets). Default 1 per user request.
- `points_per_set`: max points per set (e.g. 11, 15, 21). Default 21.
- Both columns nullable-safe via defaults, no data migration needed for existing rows.

## Related Files

- `database/migrations/2026_03_25_add_score_config_to_club_activities_table.php` (create)

## Todo

- [ ] Create migration file
- [ ] Run migration
