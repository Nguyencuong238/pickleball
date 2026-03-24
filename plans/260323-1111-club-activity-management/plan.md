# Club Activity Management - Implementation Plan

**Date**: 2026-03-23
**Branch**: `feat/club-activity-management`
**Approach**: Extend existing ClubActivity system + new ClubMatchmakingService (Hybrid A+C from brainstorm)

## References

- Brainstorm: `plans/reports/brainstorm-260323-0951-club-activity-management.md`
- UX/UI Research: `plans/reports/research-260323-1100-club-activity-ux-ui.md`
- Codebase Scout: `plans/reports/Explore-260323-1112-club-activity-system.md`

## Architecture Summary

Extend existing models (ClubActivity, ClubActivityParticipant, ClubActivityMatch) with new fields. Create 2 new services (ClubMatchmakingService, ClubMemberStatsService), 1 new model (ClubMemberStat), 2 new controllers (ClubCheckinController, ClubLeaderboardController). 6 new Blade views + 6 CSS files + 6 Alpine.js files.

**Stack**: Laravel Blade + Alpine.js + Custom CSS (no framework). AJAX polling for real-time.

## Phases Overview

| # | Phase | Priority | Effort | Status |
|---|-------|----------|--------|--------|
| 1 | Database migrations & model extensions | P1 | Low | Complete |
| 2 | QR check-in flow (controller + view) | P1 | Medium | Complete |
| 3 | Queue + matchmaking + match lifecycle | P1 | High | Complete |
| 4 | Score submission + OPRS integration | P1-P2 | Medium | Complete |
| 5 | Admin dashboard + member mgmt + leaderboard | P2-P3 | Medium | Complete |

## Key Dependencies

```
Phase 1 (DB) -> Phase 2 (Check-in) -> Phase 3 (Queue/Match) -> Phase 4 (Score/OPRS)
                                                              -> Phase 5 (Admin/Leaderboard)
```

Phase 4 and 5 can run in parallel after Phase 3.

## File Impact Summary

### Modify
- `app/Models/ClubActivity.php` - new fillable fields, type enum
- `app/Models/ClubActivityParticipant.php` - check-in, queue, status fields
- `app/Models/ClubActivityMatch.php` - lifecycle fields, set_scores
- `app/Services/ClubMatchService.php` - integrate matchmaking
- `app/Services/OprsService.php` - add recalculateAfterClubMatch()
- `app/Services/EloService.php` - add processClubMatchElo()
- `app/Http/Controllers/ClubActivityController.php` - open_play support
- `database/migrations/` - 2 new migrations

### Create
- `app/Models/ClubMemberStat.php`
- `app/Services/ClubMatchmakingService.php`
- `app/Services/ClubMemberStatsService.php`
- `app/Http/Controllers/ClubCheckinController.php`
- `app/Http/Controllers/ClubLeaderboardController.php`
- 6 Blade views (checkin, queue, match, dashboard, members, leaderboard)
- 6 CSS files in `public/assets/css/club-activity-*.css`
- 6 Alpine.js files in `public/assets/js/club-activity-*.js`
- Routes in `routes/web.php`

## Detailed Phase Files

- [Phase 1: Database & Models](./phase-01-database-models.md)
- [Phase 2: QR Check-in Flow](./phase-02-qr-checkin.md)
- [Phase 3: Queue & Matchmaking](./phase-03-queue-matchmaking.md)
- [Phase 4: Score & OPRS Integration](./phase-04-score-oprs.md)
- [Phase 5: Admin Dashboard & Extras](./phase-05-admin-dashboard-extras.md)
