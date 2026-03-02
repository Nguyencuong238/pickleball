---
title: "Club Activities ReClub-Style Upgrade"
description: "Upgrade ClubActivity to support recurring meets, one-off meets, and competitions with RSVP/waitlist"
status: complete
priority: P1
effort: 20h
branch: main
tags: [club, activities, reclub, rsvp, competition]
created: 2026-02-27
---

# Club Activities ReClub-Style Upgrade

## Summary
Upgrade skeleton `ClubActivity` system to support 3 activity types: Recurring Schedule, One-Off Meet, Competition. Add RSVP/waitlist via `club_activity_participants`. Reuse League services for competition match generation and standings.

## Phases

| # | Phase | Status | Effort |
|---|-------|--------|--------|
| 1 | [Database Migrations](phase-01-database-migrations.md) | complete | 2h |
| 2 | [Models & Services](phase-02-models-and-services.md) | complete | 5h |
| 3 | [Controllers & Routes](phase-03-controllers-and-routes.md) | complete | 4h |
| 4 | [Views & UI](phase-04-views-and-ui.md) | complete | 5h |
| 5 | [Scheduled Command](phase-05-scheduled-command.md) | complete | 2h |
| 6 | [Testing](phase-06-testing.md) | complete | 2h |

## Key Dependencies
- Existing `LeagueScheduleService` + `LeagueStandingsService` for competition format
- Existing `Club` model with members/roles (creator, admin, moderator, member)
- User `opr_level` field for skill level filtering
- `ClubPolicy` for authorization (update = creator only; needs upgrade for management)

## Architecture Decisions
1. Upgrade existing `club_activities` table -- no new activity tables
2. New `club_activity_participants` table for RSVP/waitlist
3. New `club_competition_teams` + `club_competition_matches` + `club_competition_standings` for competition data (lighter than full League system)
4. `ClubActivityService` orchestrates all 3 types
5. `ClubCompetitionService` wraps round-robin logic adapted from `LeagueScheduleService`
6. `php artisan clubs:generate-recurring-meets` scheduled command for auto-creating instances
7. ClubPolicy update: `update`/`delete` should check `isManagement()` not just creator

## Constraints
- Laravel 10, PHP 8.1+, MySQL, Blade, Vietnamese UI
- Files under 200 lines, YAGNI/KISS/DRY
- No emoji in code -- use placeholder icon names

## Validation Log

### Session 1 -- 2026-02-27
**Trigger:** Initial plan creation validation
**Questions asked:** 4

#### Questions & Answers

1. **[Architecture]** Competition tables: The plan creates 3 new tables (club_competition_teams, club_competition_matches, club_competition_standings) that mirror the existing League system. Should we reuse the existing League tables by linking them to club activities, or keep separate tables?
   - Options: Separate tables (Recommended) | Reuse League tables | Defer competition entirely
   - **Answer:** Separate tables (Recommended)
   - **Rationale:** Keeps club competitions decoupled from League system. Simpler queries, independent lifecycle.

2. **[Scope]** Should competitions also have RSVP/registration? The plan only applies RSVP to one-off and recurring meets, but ReClub requires members to RSVP to competitions too before teams are formed.
   - Options: Yes, RSVP for all types (Recommended) | No, competitions use team management only
   - **Answer:** Yes, RSVP for all types
   - **Custom input:** "RSVP for all types is same as ReClub right?" -- Confirmed: yes, matches ReClub flow where members RSVP first, then host assigns RSVPd players to teams.
   - **Rationale:** Consistent UX across all activity types. Enables team formation from participant pool.

3. **[Scope]** For MVP, should we support only Round Robin format for competitions, or also include Pool Play and Single Elimination like ReClub?
   - Options: Round Robin only (Recommended) | Round Robin + Single Elimination | All 3 formats
   - **Answer:** All 3 formats
   - **Rationale:** User wants full ReClub parity. Requires additional effort in ClubCompetitionService for pool play and bracket generation.

4. **[Scope]** Should club activities be visible to non-members for club discovery?
   - Options: Members only for now | Public activities visible (Recommended)
   - **Answer:** Members only for now
   - **Rationale:** Keeps scope small. Discovery can be added later as a separate feature.

#### Confirmed Decisions
- Separate competition tables: independent from League system
- RSVP for all 3 activity types: consistent flow matching ReClub
- All 3 competition formats: Round Robin, Pool Play + Playoff, Single Elimination
- Activities visible to members only: no public discovery for now

#### Action Items
- [x] Update Phase 2: ClubCompetitionService needs pool play and single elimination generation
- [x] Update Phase 2: RSVP applies to competition type too (show page includes both RSVP + competition panel)
- [x] Update Phase 3: Competition controller needs format selection in generateSchedule
- [x] Update Phase 4: Show page for competitions should show RSVP panel + competition panel
- [x] Update Phase 1: Add `format` field to competition_config or as separate column

#### Impact on Phases
- Phase 1: Add competition format tracking (competition_config JSON should include `format` key: round_robin|pool_play|single_elimination)
- Phase 2: ClubCompetitionService needs 3 methods: `generateRoundRobin()`, `generatePoolPlay()`, `generateSingleElimination()`. RSVP panel shown for all types.
- Phase 3: `generateSchedule` endpoint accepts `format` parameter. Show page passes both RSVP + competition data for competition type.
- Phase 4: Competition show page includes both `_rsvp-panel` and `_competition-panel`. Format selector in competition create form.
