# OPRS (OnePickleball Rating Score) Implementation Plan

**Created**: 2025-12-05
**Status**: Completed
**Priority**: High
**Completed**: 2025-12-05

## Pre-Implementation Fix Required

**K-Factor Mismatch in EloService.php** - Current implementation differs from OPRS spec:

| Condition | Spec | Current | Action |
|-----------|------|---------|--------|
| New player (<30 matches) | K=30 | K=40 | Fix |
| Regular player | K=24 | K=24 | OK |
| Pro (Elo >1800) | K=20 | K=16 (100+ matches) | Fix |

See [Spec Comparison Report](./reports/01-spec-comparison.md) for details.

## Overview

Implement comprehensive OPRS (OnePickleball Rating Score) system extending existing OCR (Elo-based ranking) with:
- Three-component scoring: Elo (70%) + Challenge (20%) + Community (10%)
- OPR Level mapping (1.0 to 5.0+)
- Challenge test system for technical skills
- Community activity point system
- Enhanced leaderboard with OPRS rankings

## Current State Analysis

### Existing OCR System (Already Implemented)
- Elo rating system (100-3000+ range)
- K-factor based on experience (40/24/16)
- Singles/doubles match support
- Badge system (streaks, ranks, milestones)
- Leaderboard with rank filtering
- Match workflow (challenge/accept/play/confirm)

### Key Files Already in Place
- `app/Services/EloService.php` - Core Elo calculation
- `app/Services/BadgeService.php` - Badge awarding logic
- `app/Models/User.php` - Has elo_rating, elo_rank fields
- `app/Models/OcrMatch.php` - Match model
- `app/Models/EloHistory.php` - Rating history
- `app/Models/UserBadge.php` - Badge records

### Gap Analysis: What OPRS Adds
1. **Challenge Score** - New component (technical tests)
2. **Community Score** - New component (activities)
3. **OPRS Calculation** - Weighted formula combining 3 scores
4. **OPR Level** - Skill tier mapping (1.0-5.0+)
5. **New Database Tables** - Challenges, activities, OPRS history
6. **New API Endpoints** - Challenge/community operations
7. **UI Components** - Profile cards, leaderboard updates

## Implementation Phases

| Phase | Name | Status | Progress |
|-------|------|--------|----------|
| 1 | [Database Schema & Migrations](./phase-01-database-schema.md) | Completed | 100% |
| 2 | [Models & Relationships](./phase-02-models-relationships.md) | Completed | 100% |
| 3 | [OPRS Service & Core Logic](./phase-03-oprs-service.md) | Completed | 100% |
| 4 | [Challenge System](./phase-04-challenge-system.md) | Completed | 100% |
| 5 | [Community Activity System](./phase-05-community-system.md) | Completed | 100% |
| 6 | [API Endpoints](./phase-06-api-endpoints.md) | Completed | 100% |
| 7 | [Frontend Views](./phase-07-frontend-views.md) | Completed | 100% |
| 8 | [Admin Panel](./phase-08-admin-panel.md) | Completed | 100% |
| 9 | [Testing & Validation](./phase-09-testing.md) | Completed | 100% |

## Architecture Decision

### Option Chosen: Extend Existing OCR System
Rationale:
- Reuse existing Elo infrastructure
- Minimal disruption to current users
- Add OPRS as wrapper around Elo + new components
- Keep backward compatibility with current rank system

### OPRS Formula Implementation
```
OPRS = (0.7 * elo_score) + (0.2 * challenge_score) + (0.1 * community_score)
```

### OPR Level Mapping (adjusted to fit existing Elo range)
| OPR Level | OPRS Range | Description |
|-----------|------------|-------------|
| 1.0 | < 600 | Beginner |
| 2.0 | 600-899 | Novice |
| 3.0 | 900-1099 | Intermediate |
| 3.5 | 1100-1349 | Upper Intermediate |
| 4.0 | 1350-1599 | Advanced |
| 4.5 | 1600-1849 | Pro |
| 5.0+ | >= 1850 | Elite |

## Key Dependencies

- Existing OCR models and services
- Spatie Media Library (evidence uploads)
- Laravel Sanctum (API auth)
- MySQL database

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Elo recalibration needed | Medium | Keep existing Elo as-is; OPRS uses scaled Elo |
| Performance with leaderboard | Medium | Add indexes; consider caching |
| Complex OPRS formula | Low | Centralize in OprsService |
| Challenge verification | Medium | Admin approval workflow |

## Success Criteria

1. OPRS calculated correctly per spec formula
2. All challenge types functional
3. Community activities tracked
4. Leaderboard shows OPRS rankings
5. User profile displays OPRS breakdown
6. Admin can manage challenges/activities
7. Backward compatible with existing OCR

## Related Documentation

- [Phase 1: Database Schema](./phase-01-database-schema.md)
- [Phase 2: Models & Relationships](./phase-02-models-relationships.md)
- [Phase 3: OPRS Service](./phase-03-oprs-service.md)
- [Phase 4: Challenge System](./phase-04-challenge-system.md)
- [Phase 5: Community System](./phase-05-community-system.md)
- [Phase 6: API Endpoints](./phase-06-api-endpoints.md)
- [Phase 7: Frontend Views](./phase-07-frontend-views.md)
- [Phase 8: Admin Panel](./phase-08-admin-panel.md)
- [Phase 9: Testing](./phase-09-testing.md)
