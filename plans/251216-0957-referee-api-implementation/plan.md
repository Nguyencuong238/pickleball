# Referee API Implementation Plan

**Created**: 2025-12-16
**Status**: Completed
**Priority**: High

## Overview

Create API endpoints for referee functionality, mirroring existing frontend controllers:
- `RefereeController` - Referee dashboard, match management, score entry
- `RefereeProfileController` - Public referee directory and profiles

## Context

Frontend controllers exist in `app/Http/Controllers/Front/`:
- `RefereeController.php` - Authenticated referee operations (dashboard, matches, scores)
- `RefereeProfileController.php` - Public referee browsing

API follows existing patterns in `app/Http/Controllers/Api/` with:
- Sanctum authentication (`auth:api`)
- Consistent JSON response format
- Service layer integration where needed

## Implementation Phases

| Phase | Name | Status | Files |
|-------|------|--------|-------|
| 01 | API Controllers | Completed | [phase-01-api-controllers.md](./phase-01-api-controllers.md) |
| 02 | API Routes | Completed | [phase-02-api-routes.md](./phase-02-api-routes.md) |

## Key Decisions

1. **Controller Structure**: Two API controllers mirroring frontend structure
   - `Api/RefereeController` - Dashboard + match operations
   - `Api/RefereeProfileController` - Public referee directory

2. **Authentication**:
   - Referee operations: `auth:api` + referee role check
   - Public profiles: No auth required

3. **Response Format**: Follow existing API pattern
   ```json
   {
     "success": true,
     "data": { ... },
     "message": "..."
   }
   ```

## Dependencies

- Existing `MatchModel` with `isAssignedToReferee()` method
- Existing `User` model with referee fields and relationships
- Existing `GroupStanding` and `TournamentAthlete` models

## Related Files

- `app/Http/Controllers/Front/RefereeController.php`
- `app/Http/Controllers/Front/RefereeProfileController.php`
- `routes/api.php`
- `app/Models/User.php`
- `app/Models/MatchModel.php`

## Success Criteria

- [x] All referee dashboard data accessible via API
- [x] Match operations (list, view, start, update score) functional
- [x] Public referee directory accessible without auth
- [x] Consistent JSON response format
- [x] Proper authorization checks
