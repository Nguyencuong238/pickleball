# Phase 02: User Model Update

## Context Links

- Parent: [plan.md](./plan.md)
- Previous: [phase-01-database-migration.md](./phase-01-database-migration.md)
- Docs: [code-standards.md](../../docs/code-standards.md)

## Overview

**Date**: 2025-12-07
**Description**: Update User model with avatar and province_id fillable fields, province relationship, and avatar URL helper
**Priority**: High
**Implementation Status**: Pending
**Review Status**: Pending

## Key Insights

- User model does NOT need HasMedia trait (using simple column storage)
- Need to add `avatar`, `location`, and `province_id` to fillable
- Need province relationship (belongsTo Province)
- Need helper method for avatar URL with fallback to Storage URL

## Requirements

### Functional
- Add `avatar`, `location`, and `province_id` to fillable
- Add `province` relationship
- Add `getAvatarUrl()` helper method

### Non-Functional
- Follow existing model patterns in codebase
- Maintain backward compatibility

## Architecture

```php
User extends Authenticatable implements JWTSubject
├── $fillable includes 'avatar', 'location', 'province_id'
├── province() -> belongsTo(Province)
└── getAvatarUrl() -> returns full avatar URL or null
```

## Related Code Files

### Files to Modify
| File | Action | Description |
|------|--------|-------------|
| `app/Models/User.php` | Modify | Add fillable fields, relationship, helper method |

## Implementation Steps

1. Add `avatar`, `location`, and `province_id` to $fillable array (after 'phone'):
```php
protected $fillable = [
    'name',
    'email',
    'phone',
    'avatar',       // ADD THIS
    'location',     // ADD THIS
    'province_id',  // ADD THIS
    'password',
    'google_id',
    'facebook_id',
    'role_type',
    'status',
    // ... rest of fields
];
```

2. Add province relationship after favoriteStadiums() method:
```php
/**
 * Get the user's location province
 */
public function province()
{
    return $this->belongsTo(Province::class);
}
```

3. Add getAvatarUrl helper method (after getInitials method):
```php
/**
 * Get avatar URL or null
 * Returns full URL to avatar image stored in public disk
 *
 * @return string|null
 */
public function getAvatarUrl(): ?string
{
    if (empty($this->avatar)) {
        return null;
    }

    return asset('storage/' . $this->avatar);
}
```

## Todo List

- [ ] Add `avatar` to $fillable
- [ ] Add `location` to $fillable
- [ ] Add `province_id` to $fillable
- [ ] Add province() relationship
- [ ] Add getAvatarUrl() helper method

## Success Criteria

- [ ] `$user->province` returns Province model or null
- [ ] `$user->getAvatarUrl()` returns full URL string or null
- [ ] User can be created/updated with avatar and province_id
- [ ] No breaking changes to existing functionality

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|------------|
| Missing Province import | Low | Low | Add use statement if not present |
| Storage link not created | Medium | Medium | Run `php artisan storage:link` |

## Security Considerations

- Avatar URL is public (stored in public disk)
- No sensitive data exposed

## Next Steps

- Proceed to Phase 03: Profile Controller & Service
