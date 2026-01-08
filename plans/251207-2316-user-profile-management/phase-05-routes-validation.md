# Phase 05: Routes & Form Requests

## Context Links

- Parent: [plan.md](./plan.md)
- Previous: [phase-04-profile-view.md](./phase-04-profile-view.md)
- Docs: [code-standards.md](../../docs/code-standards.md)

## Overview

**Date**: 2025-12-07
**Description**: Add profile routes and update dashboard links
**Priority**: High
**Implementation Status**: Pending
**Review Status**: Pending

## Key Insights

- Profile routes under authenticated user group
- Follow existing route naming pattern (user.*)
- Update dashboard "Edit Profile" link
- No Form Request classes needed (validation in controller is sufficient)

## Requirements

### Functional
- GET /user/profile/edit - Show edit form
- PUT /user/profile - Update basic info
- PUT /user/profile/avatar - Update avatar
- PUT /user/profile/email - Update email
- PUT /user/profile/password - Update password

### Non-Functional
- Routes protected by auth middleware
- Follow existing route group structure

## Architecture

```
Route Group: /user (auth middleware)
├── GET  /profile/edit     -> ProfileController@edit      [user.profile.edit]
├── PUT  /profile          -> ProfileController@updateProfile  [user.profile.update]
├── PUT  /profile/avatar   -> ProfileController@updateAvatar   [user.profile.avatar]
├── PUT  /profile/email    -> ProfileController@updateEmail    [user.profile.email]
└── PUT  /profile/password -> ProfileController@updatePassword [user.profile.password]
```

## Related Code Files

### Files to Modify
| File | Action | Description |
|------|--------|-------------|
| `routes/web.php` | Modify | Add profile routes |
| `resources/views/user/dashboard.blade.php` | Modify | Update edit profile link |

## Implementation Steps

### Step 1: Add routes to web.php

Add inside existing `Route::middleware('auth')->group(function () {...})`:

```php
// User Profile Routes
Route::prefix('user/profile')->name('user.profile.')->group(function () {
    Route::get('/edit', [App\Http\Controllers\Front\ProfileController::class, 'edit'])->name('edit');
    Route::put('/', [App\Http\Controllers\Front\ProfileController::class, 'updateProfile'])->name('update');
    Route::put('/avatar', [App\Http\Controllers\Front\ProfileController::class, 'updateAvatar'])->name('avatar');
    Route::put('/email', [App\Http\Controllers\Front\ProfileController::class, 'updateEmail'])->name('email');
    Route::put('/password', [App\Http\Controllers\Front\ProfileController::class, 'updatePassword'])->name('password');
});
```

Location: After line 136 (after the existing auth group routes like favorites and media upload)

### Step 2: Update dashboard.blade.php

Find and replace the edit profile link:

**Current (line ~414):**
```blade
<a href="#" class="edit-profile-btn">Chinh Sua Ho So</a>
```

**Replace with:**
```blade
<a href="{{ route('user.profile.edit') }}" class="edit-profile-btn">Chinh Sua Ho So</a>
```

Also update the "Xem Ho So" link (~line 445):
```blade
<a href="{{ route('user.profile.edit') }}" class="menu-btn">Xem Ho So [ARROW]</a>
```

### Step 3: Add import statement

Add ProfileController import at top of routes/web.php:
```php
use App\Http\Controllers\Front\ProfileController;
```

## Todo List

- [ ] Add ProfileController import to web.php
- [ ] Add profile route group to web.php
- [ ] Update dashboard edit profile link
- [ ] Update dashboard view profile link
- [ ] Test routes with `php artisan route:list`

## Success Criteria

- [ ] All profile routes registered
- [ ] Routes protected by auth middleware
- [ ] Route names follow convention (user.profile.*)
- [ ] Dashboard links point to profile edit
- [ ] `php artisan route:list | grep profile` shows all routes

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|------------|
| Route name conflict | Low | Low | Use unique prefix user.profile.* |
| Missing import | Low | Low | PHP error will indicate |

## Security Considerations

- All routes protected by `auth` middleware
- No RBAC needed (user can only edit own profile)

## Testing

```bash
# Verify routes registered
php artisan route:list | grep profile

# Expected output:
# GET|HEAD   user/profile/edit       user.profile.edit
# PUT        user/profile            user.profile.update
# PUT        user/profile/avatar     user.profile.avatar
# PUT        user/profile/email      user.profile.email
# PUT        user/profile/password   user.profile.password
```

## Next Steps

After completing all phases:
1. Run `php artisan migrate` for new column
2. Clear caches: `php artisan cache:clear && php artisan config:clear`
3. Test all functionality manually
4. Check that OCR profile also shows avatar (uses User model)
