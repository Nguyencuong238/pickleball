# Plan Report: League Registration Flow

**Plan dir**: `/Users/thaopv/Desktop/php/pickleball/plans/260309-1128-league-registration-flow/`
**Total effort**: ~10h across 5 phases
**Priority**: P1

## Summary

5-phase plan for VDV self-registration, admin approval, and pool-based team assignment.

## Phase Breakdown

| Phase | What | Effort | Depends On |
|-------|------|--------|------------|
| 1 | DB migrations (2 new tables + 2 cols on leagues) + 2 models | 2h | -- |
| 2 | LeagueRegistrationService + Controller + routes | 3h | Phase 1 |
| 3 | Public registration page (Blade, no auth) | 2.5h | Phase 2 |
| 4 | Admin "Dang ky" tab in league show page (AJAX) | 1.5h | Phase 2 |
| 5 | Enhanced "Them VDV" modal with pool tab | 1h | Phase 2 |

Phases 3, 4, 5 can be implemented in parallel after Phase 2.

## Key Decisions
- `registration_deadline` already exists on leagues -- only adding `required_players_per_registration` + `registration_fee`
- New `LeagueRegistrationService` separate from existing `LeagueService` (separation of concerns)
- Public routes outside auth middleware, throttled
- Pool endpoint returns players grouped by registration for "Them ca nhom" support
- AJAX-loaded admin tab to avoid slowing show page

## Files Created/Modified

**New files (8):**
- 3 migrations, 2 models, 1 service, 1 controller, 1 blade view (register page)
- 1 blade partial (_tab-registrations)

**Modified files (4):**
- League model (fillable + relationship)
- HomeYardLeagueController (new fields + registration count)
- show.blade.php (new tab)
- _tab-teams.blade.php (enhanced modal)
- routes/web.php (new routes)

## No Unresolved Questions
