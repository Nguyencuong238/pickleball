# Phase 02: API Routes

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 01: API Controllers](./phase-01-api-controllers.md)
**Date**: 2025-12-16
**Priority**: High
**Status**: Completed
**Review Status**: Complete

## Context Links

- Routes file: `routes/api.php`
- Existing referee web routes: `routes/web.php` (lines 298-305)
- API pattern reference: OCR/OPRS routes in `routes/api.php`

## Overview

Add API routes for referee functionality following existing patterns.

## Key Insights

- Existing web routes use `role:referee` middleware
- API routes use `auth:api` middleware for Sanctum
- Public routes (referee directory) need no auth
- Pattern follows `/api/referee/...` prefix

## Requirements

### Functional
- RF1: Protected routes for referee operations
- RF2: Public routes for referee directory
- RF3: Consistent URL structure with existing APIs

### Non-Functional
- NF1: Route naming convention `api.referee.*`
- NF2: Proper middleware grouping

## Architecture

### Route Structure

```
/api/referee/
├── dashboard                    GET   [auth:api + referee]
├── matches                      GET   [auth:api + referee]
├── matches/{match}              GET   [auth:api + referee]
├── matches/{match}/start        POST  [auth:api + referee]
├── matches/{match}/score        PUT   [auth:api + referee]
└── (public)
    ├── referees                 GET   [no auth]
    └── referees/{referee}       GET   [no auth]
```

## Related Code Files

### Files to Modify
| Path | Action | Description |
|------|--------|-------------|
| `routes/api.php` | Modify | Add referee API routes |

## Implementation Steps

### Step 1: Add Routes to api.php

Add after existing route groups (around line 200):

```php
/*
|--------------------------------------------------------------------------
| Referee API Routes
|--------------------------------------------------------------------------
*/

// Protected Referee endpoints (auth + referee role required)
Route::prefix('referee')->middleware('auth:api')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Api\RefereeController::class, 'dashboard']);
    Route::get('matches', [App\Http\Controllers\Api\RefereeController::class, 'matches']);
    Route::get('matches/{match}', [App\Http\Controllers\Api\RefereeController::class, 'showMatch']);
    Route::post('matches/{match}/start', [App\Http\Controllers\Api\RefereeController::class, 'startMatch']);
    Route::put('matches/{match}/score', [App\Http\Controllers\Api\RefereeController::class, 'updateScore']);
});

// Public Referee endpoints (no auth required)
Route::prefix('referees')->group(function () {
    Route::get('', [App\Http\Controllers\Api\RefereeProfileController::class, 'index']);
    Route::get('{referee}', [App\Http\Controllers\Api\RefereeProfileController::class, 'show']);
});
```

### Step 2: Add Import Statements

At top of `routes/api.php`, add:

```php
use App\Http\Controllers\Api\RefereeController;
use App\Http\Controllers\Api\RefereeProfileController;
```

Then update routes to use imported classes:

```php
// Protected Referee endpoints
Route::prefix('referee')->middleware('auth:api')->group(function () {
    Route::get('dashboard', [RefereeController::class, 'dashboard']);
    Route::get('matches', [RefereeController::class, 'matches']);
    Route::get('matches/{match}', [RefereeController::class, 'showMatch']);
    Route::post('matches/{match}/start', [RefereeController::class, 'startMatch']);
    Route::put('matches/{match}/score', [RefereeController::class, 'updateScore']);
});

// Public Referee endpoints
Route::prefix('referees')->group(function () {
    Route::get('', [RefereeProfileController::class, 'index']);
    Route::get('{referee}', [RefereeProfileController::class, 'show']);
});
```

## API Endpoints Summary

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/referee/dashboard` | Dashboard stats + upcoming | Yes (referee) |
| GET | `/api/referee/matches` | List assigned matches | Yes (referee) |
| GET | `/api/referee/matches/{id}` | Match detail | Yes (referee) |
| POST | `/api/referee/matches/{id}/start` | Start match | Yes (referee) |
| PUT | `/api/referee/matches/{id}/score` | Update score | Yes (referee) |
| GET | `/api/referees` | List all referees | No |
| GET | `/api/referees/{id}` | Referee profile | No |

## Todo List

- [x] Add import statements to `routes/api.php`
- [x] Add protected referee route group
- [x] Add public referee route group
- [x] Verify routes registered with `php artisan route:list`

## Success Criteria

- [x] All routes registered correctly
- [x] Protected routes require authentication
- [x] Public routes accessible without auth
- [x] Route naming follows convention

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Route conflicts | Low | Using unique prefixes (`referee/` vs `referees/`) |
| Middleware issues | Medium | Test auth flow explicitly |

## Security Considerations

- Protected routes require Sanctum token
- Role check done in controller (not middleware to allow more granular control)
- Public routes expose only safe referee profile data

## Next Steps

After completion:
1. Run `php artisan route:list --path=api/referee` to verify
2. Test endpoints with Postman/curl
3. Update API documentation if exists
