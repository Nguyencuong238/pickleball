---
title: "MLP League Management MVP"
description: "Team-based league system for Home Yard organizers with round-robin scheduling, match scoring, and standings"
status: in_progress
priority: P1
effort: 23h
branch: main
tags: [league, home-yard, mvp, teams]
created: 2026-02-25
---

# MLP League Management MVP

## Overview
Add team-based league management to the Home Yard dashboard. Organizers create leagues, manage teams/rosters, generate round-robin schedules, enter match scores, and view auto-calculated standings. Reuses existing auth, roles, and layout patterns.

## Architecture
- 7 new `league_*` DB tables, 7 Eloquent models
- 3 service classes (LeagueService, LeagueScheduleService, LeagueStandingsService)
- 3 Front controllers + 1 Admin controller
- 6 Blade views under `resources/views/home-yard/leagues/`
- Routes under `homeyard.leagues.*` prefix

## Phases

| # | Phase | Priority | Effort | Status |
|---|-------|----------|--------|--------|
| 1 | [Database & Models](./phase-01-database-and-models.md) | P1 | 4h | completed |
| 2 | [Service Layer](./phase-02-service-layer.md) | P1 | 4h | completed |
| 3 | [Controllers & Routes](./phase-03-controllers-and-routes.md) | P1 | 4h | completed |
| 4 | [Blade Views](./phase-04-blade-views.md) | P2 | 6h | completed |
| 5 | [Testing & Integration](./phase-05-testing-and-integration.md) | P2 | 3h | pending |

## Key Dependencies
- Phase 2 depends on Phase 1 (models)
- Phase 3 depends on Phase 2 (services)
- Phase 4 depends on Phase 3 (controllers/routes)
- Phase 5 depends on all phases

## Out of Scope
Draft/bidding, trade windows, DreamBreaker format, live scoreboard, public-facing league pages, DUPR integration

## Risk Summary
- Schema migration conflicts if other features in parallel -- mitigate with unique date prefix
- Large round-robin schedules (16+ teams) -- limit max_teams in config
- No existing league patterns to follow -- tournament system provides close reference

## Key Decisions
- Use `$fillable` (not `$guarded`) for new models -- explicit safety
- League `config` stored as JSON -- flexible format without schema changes
- Standings recalculated on each score save -- simple, no caching needed for MVP
- Slug-based routing for leagues -- consistent with Tournament model
- Config change triggers full standings recalculation with confirm dialog
- Read-only API endpoints (mixed auth: standings/schedule public, league list authenticated)
- Round-robin only for MVP; manual schedule deferred to v2
- Player join: organizer/captain only (no self-registration)

## Validation Log

### Session 1 -- 2026-02-25
**Trigger:** Initial plan validation before implementation
**Questions asked:** 6

#### Questions & Answers

1. **[Architecture]** League config dung JSON column. Khi organizer thay doi config giua mua giai, standings co nen recalculate theo config moi khong?
   - Options: Recalculate all | Lock config khi active | Config versioning
   - **Answer:** Recalculate all
   - **Rationale:** Simplest approach, keeps standings consistent with current config. No snapshot complexity for MVP.

2. **[Scope]** Mobile API: Hien plan chi co web views. Co can API endpoints cho mobile khong?
   - Options: Web only | Add read-only API | Full API
   - **Answer:** Add read-only API
   - **Rationale:** Players need to view standings/schedule on mobile app. Read-only sufficient for MVP.

3. **[Architecture]** Round-robin la format duy nhat. Chap nhan khong?
   - Options: Round-robin only | Add manual schedule | Round-robin + manual
   - **Answer:** Round-robin only (MVP)
   - **Rationale:** Covers majority of VN league use cases. Manual/custom formats deferred to v2.

4. **[Scope]** Player co the tu dang ky vao team khong?
   - Options: Organizer/captain only | Player self-register | Invite link
   - **Answer:** Organizer/captain only
   - **Rationale:** Simplest, most controlled. Self-registration adds notification/approval UI complexity.

5. **[Architecture]** Read-only API: authentication va endpoint scope?
   - Options: Authenticated Sanctum | Public (no auth) | Mixed
   - **Answer:** Mixed (standings/schedule public, league list authenticated)
   - **Rationale:** Public standings lets spectators view without login. League list needs auth for organizer context.

6. **[UX]** Config change recalculate: can warning cho organizer?
   - Options: Confirm dialog | Auto-save no warning | Preview diff
   - **Answer:** Confirm dialog
   - **Rationale:** Prevents accidental standings reset. Simple to implement with JS confirm.

#### Confirmed Decisions
- Config change: recalculate all standings, with confirm dialog
- API: mixed auth read-only endpoints (+2h effort)
- Schedule: round-robin only for MVP
- Player join: organizer/captain only
- No self-registration, no invite links

#### Action Items
- [ ] Add read-only API controller + routes to Phase 3
- [ ] Add confirm dialog for config change in Phase 4
- [ ] Update effort estimate: 21h -> 23h (+2h for API)

#### Impact on Phases
- Phase 3: Add Api/LeagueApiController with 4 GET endpoints (mixed auth)
- Phase 4: Add confirm dialog on league edit form when config changes
- Phase 5: Add API endpoint tests
