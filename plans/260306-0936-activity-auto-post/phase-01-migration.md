# Phase 1: DB Migration

## Priority: HIGH | Status: TODO | Effort: S

## Overview
Add `club_activity_id` nullable FK to `club_posts` table to link auto-generated posts to their source activity.

## Implementation Steps

1. Create migration: `2026_03_06_add_club_activity_id_to_club_posts_table.php`
2. Add nullable `club_activity_id` FK column after `user_id`
3. Add index on `club_activity_id`
4. Add cascade on delete (when activity deleted, nullify the FK - use `nullOnDelete`)

## Migration Schema
<!-- Updated: Validation Session 1 - Use cascadeOnDelete for hard-delete -->
```php
$table->foreignId('club_activity_id')->nullable()->after('user_id')
    ->constrained('club_activities')->cascadeOnDelete();
```

## Model Changes

### ClubPost model
- Add `club_activity_id` to `$fillable`
- Add `activity()` BelongsTo relationship

### ClubActivity model
- Add `post()` HasOne relationship (via `club_activity_id`)

## Related Files
- `database/migrations/2026_03_06_add_club_activity_id_to_club_posts_table.php` (create)
- `app/Models/ClubPost.php` (edit)
- `app/Models/ClubActivity.php` (edit)

## TODO
- [ ] Create migration file
- [ ] Add `club_activity_id` to ClubPost `$fillable`
- [ ] Add `activity()` relationship to ClubPost
- [ ] Add `post()` relationship to ClubActivity
- [ ] Run migration

## Success Criteria
- Migration runs successfully
- Models have correct relationships
- Existing posts unaffected (nullable FK)
