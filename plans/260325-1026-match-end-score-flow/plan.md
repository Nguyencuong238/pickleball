# Match End + Score Flow Refactor

## Overview
Refactor open play match ending flow with 2 modes:
- **Flow A (Admin)**: Admin clicks "End match" -> Score form -> Submit -> Complete match
- **Flow B (Player)**: Player clicks "End match" -> Score form -> Submit -> Opposing team confirms -> Complete match
- Both flows allow ending WITHOUT score (cancelled/abandoned match with confirm dialog)

## Current State
- `endMatch()` and `submitScore()` are decoupled — admin can end without score, anyone can score independently
- Only admin can end matches (`authorize('manageActivity')`)
- No player-initiated end match
- No peer confirmation — single submit = `result_confirmed = true`
- Score form is standalone page (`score-submit.blade.php`)

## Target State
- Admin: "End match" opens score form first. Submit score = complete match immediately
- Admin: Can skip score (confirm dialog) = complete match without score
- Player: Sees "End match" button when playing. Click -> score form -> submit -> `pending_confirmation`
- Opposing team player: Sees pending score -> confirm or reject
- If rejected: submitter can re-enter, or escalate to admin
- Admin dashboard: Shows `pending_confirmation` matches prominently

## Architecture Decisions
- **Confirmation model**: 1 player from opposing team confirms (KISS)
- **Timeout**: No auto-timeout (admin resolves manually)
- **No new migration columns needed beyond**: `score_status`, `score_confirmed_by`
- **Reuse existing score form** — add `mode` param (admin/player) and `confirm` variant
- **`result_confirmed` kept for backward compat**, `score_status` is source of truth
- **Court freed on player score submit** (ended_at set), players stay 'playing' until confirm

## Phases

| # | Phase | Status | Priority |
|---|-------|--------|----------|
| 1 | [Database + Model](phase-01-database-model.md) | Complete | High |
| 2 | [Backend API](phase-02-backend-api.md) | Complete | High |
| 3 | [Admin Dashboard UI](phase-03-admin-dashboard-ui.md) | Complete | High |
| 4 | [Player Queue + Score UI](phase-04-player-queue-score-ui.md) | Complete | High |
| 5 | [Score Confirmation Flow](phase-05-score-confirmation.md) | Complete | High |

## Dependencies
- Phase 1 -> Phase 2 (model changes needed before API)
- Phase 2 -> Phase 3, 4, 5 (API endpoints before UI)
- Phase 3, 4 can run in parallel
- Phase 5 depends on Phase 4

## Key Files
- `app/Http/Controllers/ClubOpenPlayController.php` — main controller
- `app/Http/Controllers/ClubDashboardController.php` — admin dashboard state
- `app/Services/ClubMatchmakingService.php` — match completion logic
- `resources/views/home-yard/clubs/activity-dashboard.blade.php` — admin dashboard
- `resources/views/front/clubs/queue.blade.php` — player queue page
- `resources/views/front/clubs/score-submit.blade.php` — score form
- `public/assets/js/club-activity-dashboard.js` — admin JS
- `public/assets/js/club-activity-queue.js` — player queue JS
- `public/assets/js/club-activity-score.js` — score form JS
- `routes/web.php` — routes (lines 353-370)
