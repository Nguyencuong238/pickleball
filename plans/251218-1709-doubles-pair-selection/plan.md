# Doubles Pair Selection for Match Creation

**Date**: 2025-12-18
**Status**: Completed
**Priority**: High

## Overview

Fix doubles registration and match creation flow:
1. Registration form (`tournaments_detail.blade.php`) - show partner fields when doubles category selected
2. Match creation (`config.blade.php`) - show pair selection instead of individual athletes

## Problem Statement

- **Registration**: Form only collects 1 athlete info, no partner for doubles
- **Match creation**: Shows individual athlete dropdowns (VDV 1, VDV 2) for all categories
- **Database**: No `partner_id` to link doubles pairs

## Solution Approach

### Registration Flow (New)
When user selects doubles category in registration form:
1. Show additional partner info fields (name, email, phone)
2. Backend creates 2 TournamentAthlete records linked by `partner_id`
3. Both athletes auto-approved as a pair

### Match Creation Flow
1. API returns pairs (athletes with partners) for doubles categories
2. UI shows "Cap 1 / Cap 2" labels with pair names
3. Backend validates pairs don't overlap

## Implementation Phases

| Phase | Description | Status | Progress |
|-------|-------------|--------|----------|
| 1 | [Database Schema](./phase-01-database-schema.md) | Completed | 100% |
| 2 | [Registration Form](./phase-02-registration-form.md) | Completed | 100% |
| 3 | [Backend API](./phase-03-backend-api.md) | Completed | 100% |
| 4 | [Match Creation UI](./phase-04-match-creation-ui.md) | Completed | 100% |

## Key Files to Modify

### Database
- `database/migrations/` - New migration for `partner_id`

### Registration (Phase 2)
- `resources/views/front/tournaments/tournaments_detail.blade.php` - Add partner fields
- `app/Http/Controllers/TournamentRegistrationController.php` - Handle partner creation

### Backend (Phase 3)
- `app/Models/TournamentAthlete.php` - Add partner relationship
- `app/Models/TournamentCategory.php` - Add `isDoubles()` helper
- `app/Http/Controllers/Front/HomeYardTournamentController.php` - Modify APIs

### Match Creation (Phase 4)
- `resources/views/home-yard/config.blade.php` - Update modal and JS

## User Flow

```
[Registration Form]
     │
     ▼
Select Category ───► Singles? ───► Show: Name, Email, Phone
     │                              Create: 1 TournamentAthlete
     │
     └──────────► Doubles? ───► Show: Name, Email, Phone
                                      + Partner Name, Email, Phone
                                Create: 2 TournamentAthletes (linked)

[Match Creation - Admin]
     │
     ▼
Select Category ───► Singles? ───► Show: VDV 1, VDV 2 dropdowns
     │
     └──────────► Doubles? ───► Show: Cap 1, Cap 2 dropdowns
                                (pair names: "A / B")
```

## Dependencies

- None - self-contained feature

## Related Documentation

- [Code Standards](../../docs/code-standards.md)
- [Codebase Summary](../../docs/codebase-summary.md)
