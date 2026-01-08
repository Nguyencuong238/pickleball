# OCR (OnePickleball Championship Ranking) System Implementation Plan

**Created**: 2025-12-02
**Completed**: 2025-12-03
**Status**: Completed
**Priority**: High

## Overview

Implement an open ranking system (OCR) that allows players to self-organize matches and earn Elo points integrated with OPRS (OnePickleball Rating Score).

## Key Features

- Self-organized matches between players (1v1 or 2v2)
- Elo rating system with rank-based tiers
- Match invitation/acceptance workflow
- Result submission with evidence upload
- Badge/achievement system
- Global leaderboard

## Implementation Phases

| Phase | Name | Status | Progress |
|-------|------|--------|----------|
| 1 | [Database Schema](./phase-01-database-schema.md) | Completed | 100% |
| 2 | [Core Models](./phase-02-core-models.md) | Completed | 100% |
| 3 | [Elo Service](./phase-03-elo-service.md) | Completed | 100% |
| 4 | [API Controllers](./phase-04-api-controllers.md) | Completed | 100% |
| 5 | [Match Workflow](./phase-05-match-workflow.md) | Completed | 100% |
| 6 | [Badge System](./phase-06-badge-system.md) | Completed | 100% |
| 7 | [Frontend Views](./phase-07-frontend-views.md) | Completed | 100% |

## Implementation Summary

### Files Created

**Migrations (4):**
- `database/migrations/2025_12_02_170001_add_elo_fields_to_users_table.php`
- `database/migrations/2025_12_02_170002_create_ocr_matches_table.php`
- `database/migrations/2025_12_02_170003_create_elo_histories_table.php`
- `database/migrations/2025_12_02_170004_create_user_badges_table.php`

**Models (3):**
- `app/Models/OcrMatch.php`
- `app/Models/EloHistory.php`
- `app/Models/UserBadge.php`

**Services (2):**
- `app/Services/EloService.php`
- `app/Services/BadgeService.php`

**Controllers (6):**
- `app/Http/Controllers/Api/OcrMatchController.php`
- `app/Http/Controllers/Api/OcrUserController.php`
- `app/Http/Controllers/Api/OcrLeaderboardController.php`
- `app/Http/Controllers/Front/OcrController.php`
- `app/Http/Controllers/Admin/OcrDisputeController.php`
- `app/Http/Controllers/Admin/OcrBadgeController.php`

**Views (10):**
- `resources/views/front/ocr/index.blade.php`
- `resources/views/front/ocr/leaderboard.blade.php`
- `resources/views/front/ocr/profile.blade.php`
- `resources/views/front/ocr/matches/index.blade.php`
- `resources/views/front/ocr/matches/show.blade.php`
- `resources/views/front/ocr/matches/create.blade.php`
- `resources/views/admin/ocr/matches/index.blade.php`
- `resources/views/admin/ocr/badges/index.blade.php`
- `resources/views/admin/ocr/badges/show.blade.php`
- `resources/views/admin/ocr/disputes/index.blade.php`
- `resources/views/admin/ocr/disputes/show.blade.php`
- `resources/views/components/ocr-badge.blade.php`

**Other:**
- `app/Console/Commands/OcrAutoConfirmCommand.php`
- `app/Events/OcrMatch*.php` (4 events)
- `app/Notifications/OcrMatchNotification.php`
- `app/Http/Requests/OcrMatch*.php` (2 requests)

## Dependencies

- Existing User model with Spatie Permissions
- Laravel Sanctum for API auth
- Spatie Media Library for evidence uploads

## Architecture Summary

```
User (1000 base Elo)
  |
  +-- OcrMatch (ranked match)
  |     +-- match_type (singles/doubles)
  |     +-- status (pending/accepted/in_progress/completed/disputed)
  |     +-- Evidence (media collection)
  |
  +-- EloHistory (point changes)
  |
  +-- UserBadge (achievements)
```

## API Endpoints

```
POST   /api/ocr/matches           - Create match invitation
GET    /api/ocr/matches           - List user's matches
GET    /api/ocr/matches/:id       - Get match details
PATCH  /api/ocr/matches/:id/accept   - Accept invitation
POST   /api/ocr/matches/:id/result   - Submit result
POST   /api/ocr/matches/:id/confirm  - Confirm result
POST   /api/ocr/matches/:id/evidence - Upload evidence
GET    /api/users/:id/elo         - Get Elo rating
GET    /api/users/:id/badges      - Get badges
GET    /api/leaderboard           - Get rankings
```

## Risk Assessment

- **Cheating**: Mitigate via evidence upload + confirmation system
- **Elo manipulation**: Limit matches per day, detect suspicious patterns
- **Disputes**: Manual review process by admin

## Related Documentation

- [Project Overview](../../docs/project-overview-pdr.md)
- [Code Standards](../../docs/code-standards.md)
- [System Architecture](../../docs/system-architecture.md)
