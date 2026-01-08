# Phase 01: Database Migration

## Context Links

- Parent: [plan.md](./plan.md)
- Docs: [codebase-summary.md](../../docs/codebase-summary.md), [code-standards.md](../../docs/code-standards.md)

## Overview

**Date**: 2025-12-07
**Description**: Add `avatar`, `location`, and `province_id` columns to users table
**Priority**: High
**Implementation Status**: Pending
**Review Status**: Pending

## Key Insights

- User model currently has: name, email, phone, password, google_id, facebook_id, role_type, status, elo fields, oprs fields
- No avatar or location fields exist
- Avatar will be stored as file path string (Laravel Storage)
- Location is free text for detailed address/area
- Province_id is foreign key to existing `provinces` table

## Requirements

### Functional
- Add `avatar` nullable string column for avatar file path
- Add `location` nullable string column for detailed address
- Add `province_id` nullable foreign key to `users` table
- Reference existing `provinces` table

### Non-Functional
- Migration must be reversible
- Follow Laravel migration naming conventions

## Architecture

```
users table
├── id (existing)
├── name (existing)
├── email (existing)
├── phone (existing)
├── avatar (NEW - string, file path)
├── location (NEW - string, detailed address)
├── province_id (NEW - FK to provinces)
└── ... other fields
```

## Related Code Files

### Files to Create
| File | Action | Description |
|------|--------|-------------|
| `database/migrations/YYYY_MM_DD_HHMMSS_add_profile_fields_to_users_table.php` | Create | Add avatar and province_id columns |

### Files to Modify
None

## Implementation Steps

1. Create migration file:
```bash
php artisan make:migration add_profile_fields_to_users_table --table=users
```

2. Migration content:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('phone');
            $table->string('location')->nullable()->after('avatar');
            $table->unsignedBigInteger('province_id')->nullable()->after('location');
            $table->foreign('province_id')->references('id')->on('provinces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropColumn(['avatar', 'location', 'province_id']);
        });
    }
};
```

3. Run migration:
```bash
php artisan migrate
```

## Todo List

- [ ] Create migration file
- [ ] Add avatar column (string, nullable)
- [ ] Add location column (string, nullable)
- [ ] Add province_id column with foreign key
- [ ] Run migration
- [ ] Verify columns exist

## Success Criteria

- [ ] Migration runs without errors
- [ ] `avatar` column exists in `users` table (string, nullable)
- [ ] `location` column exists in `users` table (string, nullable)
- [ ] `province_id` column exists in `users` table
- [ ] Foreign key constraint references `provinces.id`
- [ ] Migration is reversible via `php artisan migrate:rollback`

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|------------|
| Migration conflict | Low | Low | Run in development first |
| FK constraint issues | Low | Medium | Use nullOnDelete |

## Security Considerations

- No security concerns for this migration

## Next Steps

- Proceed to Phase 02: User Model Update
