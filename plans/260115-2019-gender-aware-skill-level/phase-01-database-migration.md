# Phase 01: Database Migration

## Context

- **Parent Plan**: [plan.md](./plan.md)
- **Dependencies**: None
- **Docs**: [Code Standards](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2026-01-15 |
| Description | Add gender field to users table |
| Priority | High |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

- User model lacks gender field
- Need enum: male, female
- Nullable for backward compatibility
- No foreign key needed

## Requirements

1. Create migration adding `gender` column
2. Update User model fillable and casts
3. Column should be nullable (existing users have no gender)

## Architecture

```
users table
├── ... existing columns ...
└── gender (enum: 'male', 'female', nullable)
```

## Related Code Files

| File | Action |
|------|--------|
| `database/migrations/2026_01_15_xxx_add_gender_to_users_table.php` | Create |
| `app/Models/User.php` | Update fillable, casts |

## Implementation Steps

### Step 1: Create Migration

```php
// database/migrations/2026_01_15_201900_add_gender_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};
```

### Step 2: Update User Model

Add to `app/Models/User.php`:

```php
// In $fillable array, add:
'gender',

// In $casts array, add:
'gender' => 'string',
```

### Step 3: Run Migration

```bash
php artisan migrate
```

## Todo List

- [ ] Create migration file
- [ ] Update User model $fillable
- [ ] Update User model $casts
- [ ] Run migration
- [ ] Verify column exists

## Success Criteria

- [ ] Migration runs without errors
- [ ] `gender` column exists in users table
- [ ] Column accepts 'male', 'female', or null
- [ ] User model can mass-assign gender

## Risk Assessment

| Risk | Level | Mitigation |
|------|-------|------------|
| Migration failure | Low | Nullable column, no FK |
| Data loss | None | Adding column only |

## Security Considerations

- Gender is PII but not sensitive
- No special encryption needed
- Standard Laravel validation applies

## Next Steps

After completion, proceed to [Phase 02: Service Update](./phase-02-service-update.md)
