# User Profile Management Implementation Plan

**Created**: 2025-12-07
**Status**: Completed
**Priority**: High
**Last Reviewed**: 2025-12-07
**Completed**: 2025-12-07

## Overview

Implement user self-service profile management feature allowing users to update their personal information including name, avatar, email, location (province), and password.

## Feature Requirements

| Field | Type | Validation | Notes |
|-------|------|------------|-------|
| Name | Text | Required, min:3, max:255 | Update existing field |
| Avatar | Image | max:2MB, jpg/png/webp | New column - Laravel Storage |
| Email | Email | Required, unique | Current password required |
| Location | Text | Optional, max:255 | Detailed address/area |
| Province | Select | Province from `provinces` table | New FK field |
| Password | Password | min:6, confirmed | Current password required |

## Implementation Phases

| Phase | Description | Status | File |
|-------|-------------|--------|------|
| 01 | Database Migration - Add profile fields | COMPLETED | [phase-01-database-migration.md](./phase-01-database-migration.md) |
| 02 | User Model - Add HasMedia, fillable, relationships | COMPLETED | [phase-02-user-model-update.md](./phase-02-user-model-update.md) |
| 03 | Profile Controller & Service | COMPLETED | [phase-03-profile-controller.md](./phase-03-profile-controller.md) |
| 04 | Profile Edit View | COMPLETED | [phase-04-profile-view.md](./phase-04-profile-view.md) |
| 05 | Routes & Form Requests | COMPLETED | [phase-05-routes-validation.md](./phase-05-routes-validation.md) |

## Key Architecture Decisions

1. **Avatar Storage**: Simple `avatar` column with Laravel Storage facade (public disk)
2. **Security**: Require current password for email/password changes
3. **Location**: Reuse existing `provinces` table for location selection
4. **Controller Location**: `App\Http\Controllers\Front\ProfileController` (user-facing)

## Dependencies

- Laravel Storage facade (built-in)
- Existing `provinces` table (has data)
- Laravel Hash facade for password verification

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Email uniqueness conflict | Medium | Validate excluding current user |
| OAuth users changing password | Low | Check if user has password set |
| Large avatar uploads | Low | Validate file size, use conversions |

## Success Criteria

- [x] User can update name
- [x] User can upload/change avatar image
- [x] User can change email (with password confirmation)
- [x] User can select location (province)
- [x] User can change password (with current password verification)
- [x] Form validation with Vietnamese error messages
- [x] Profile changes reflected in dashboard and OCR profile

## Code Review Findings

**Review Date**: 2025-12-07
**Report**: [reports/251207-code-review-report.md](./reports/251207-code-review-report.md)

### Critical Issues (Must Fix)
1. **XSS Vulnerability** - Line 389 in edit.blade.php uses innerHTML with user input
2. **Password Null Check** - ProfileService::verifyPassword() needs null password handling
3. **Hashing Inconsistency** - Mix of bcrypt() and Hash::make()

### High Priority
4. Storage symlink verification needed
5. Add image dimension validation
6. Add try-catch for file operations

### Implementation Status
- [x] All 5 phases completed
- [x] Critical security fixes applied
- [x] High priority improvements completed
- [x] Tests written and passing

### Critical Fixes Applied
1. XSS vulnerability fixed - avatar preview now uses textContent
2. Password null check added to ProfileService::verifyPassword()
3. Password hashing standardized to Hash::make()
4. Image dimension validation added (min 100x100, max 2000x2000)
5. Storage symlink verification included

### Next Steps
1. Deploy to production
2. Monitor user adoption and feedback
3. Consider email verification after email change (future enhancement)

## Unresolved Questions

1. Has `php artisan storage:link` been run in deployment?
2. Should we add avatar image processing (resize, optimize)?
3. Should we track profile update history for audit?
4. Do we need email verification after email change?
5. Should we add profile update notifications?
