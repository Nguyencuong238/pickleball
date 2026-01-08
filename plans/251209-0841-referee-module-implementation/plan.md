# Referee Module Implementation Plan

**Date**: 2025-12-09
**Status**: Completed
**Completion Date**: 2025-12-09
**Priority**: High
**Project**: Pickleball Platform Referee System

---

## Overview

Implement full referee management system for tournaments with role-based access, match assignment, score management, and public profiles. Shared auth with multi-role support (user can be home_yard + referee).

---

## Implementation Phases

### Phase 1: Database Schema ✓
**File**: [phase-01-database-schema.md](./phase-01-database-schema.md)
**Status**: Completed
**Completion Date**: 2025-12-09
**Lines**: 4 migrations (tournament_referees table, matches referee columns, users referee fields, permissions). Updated PermissionSeeder with referee role.

### Phase 2: Models & Relationships ✓
**File**: [phase-02-models-relationships.md](./phase-02-models-relationships.md)
**Status**: Completed
**Completion Date**: 2025-12-09
**Lines**: Created TournamentReferee model, updated Tournament/User/MatchModel with referee relationships

### Phase 3: Referee Dashboard ✓
**File**: [phase-03-referee-dashboard.md](./phase-03-referee-dashboard.md)
**Status**: Completed
**Completion Date**: 2025-12-09
**Lines**: Created RefereeController with dashboard/matches views, referee layout, routes

### Phase 4: Tournament Referee Assignment ✓
**File**: [phase-04-tournament-referee-assignment.md](./phase-04-tournament-referee-assignment.md)
**Status**: Completed
**Completion Date**: 2025-12-09
**Lines**: Updated HomeYardTournamentController with referee assignment methods, updated tournament form

### Phase 5: Match Management ✓
**File**: [phase-05-match-management.md](./phase-05-match-management.md)
**Status**: Completed
**Completion Date**: 2025-12-09
**Implementation Details**:
- Created MatchObserver to sync referee_name cache on referee_id assignment
- Updated HomeYardTournamentController::configTournament() to pass referees data to config view
- Added referee dropdown to create match modal in config.blade.php
- Added referee dropdown to edit match modal in config.blade.php
- Updated storeMatch() method with referee_id parameter and validation (referee must be in tournament pool)
- Updated updateMatch() method with referee_id parameter and validation (referee must be in tournament pool)
- Added getMatch() method returning match with referee relationship
- Added referee display in match details modal in matches.blade.php
- Integrated activity logging for referee assignments and score updates

**Notes**: JavaScript lint errors in blade files are false positives - Blade syntax like @json and {{ }} is correctly used but not understood by JavaScript linters.

### Phase 6: Public Profiles ✓
**File**: [phase-06-public-profiles.md](./phase-06-public-profiles.md)
**Status**: Completed
**Completion Date**: 2025-12-09
**Lines**: Created RefereeProfileController, academy/referees routes, index/show views

---

## Architecture Decisions

- **Auth Strategy**: Shared user accounts with Spatie Permission multi-role support
- **Pivot Pattern**: tournament_referees table with assigned_at, assigned_by audit fields
- **Referee Assignment**: One referee per match, selected from tournament referee pool
- **Score Entry**: Mirror existing HomeYardTournamentController match result pattern
- **Public Profile**: Follow instructor detail pattern for consistency

---

## Success Criteria

- Referees assigned to tournaments via HomeYard dashboard
- One referee per match with validation against tournament pool
- Referee dashboard shows only assigned matches with score entry
- Public referee profiles accessible via /academy/referees
- Multi-role users see both HomeYard and Referee menu items
- Activity logging tracks all referee assignments and changes

---

## Dependencies

- Existing: Spatie Permission, User model, Tournament system, MatchModel
- New: RefereeController, TournamentReferee pivot model, referee views

---

## Rollout Strategy

1. Phase 1-2: Database + Models (no user impact)
2. Phase 3-4: Referee dashboard + assignment (limited rollout to test users)
3. Phase 5: Match management (enable score entry for test matches)
4. Phase 6: Public profiles (full launch)

---

## Links

- [Research: Referee Systems](./research/researcher-01-referee-systems.md)
- [Research: Laravel Patterns](./research/researcher-02-laravel-patterns.md)
- [Codebase Summary](/Users/thaopv/Desktop/php/pickleball/docs/codebase-summary.md)
- [Code Standards](/Users/thaopv/Desktop/php/pickleball/docs/code-standards.md)
- [System Architecture](/Users/thaopv/Desktop/php/pickleball/docs/system-architecture.md)
