# Club Activities E2E Browser Test Report

**Date:** 2026-02-27 | **Tester:** Chrome DevTools MCP | **Environment:** localhost:8000

## Test Summary

| # | Test | Status | Notes |
|---|------|--------|-------|
| 1 | Activity Index Page | PASS | Lists all activities with type badges, edit/delete buttons |
| 2 | Create One-Off Activity | PASS | Pre-existing "Giao luu Social" confirmed working |
| 3 | Create Recurring Activity | PASS | Shows recurrence day selector, auto-approve checkbox |
| 4 | Create Competition Activity | PASS | Shows format selector, win/loss points fields |
| 5 | Activity Show Page | PASS | Details, type badge, status badge, club info card |
| 6 | RSVP Join | PASS | Count updates, user shown in confirmed list |
| 7 | RSVP Cancel | PASS | Count decreases, join button reappears |
| 8 | Competition: Add Teams | PASS | Teams created via AJAX, shown after schedule generation |
| 9 | Competition: Round Robin Schedule | PASS | 4 teams -> 3 rounds x 2 matches = 6 matches |
| 10 | Competition: Save Match Scores | PASS | Scores saved, completed matches show final score |
| 11 | Competition: Standings | PASS | Points calculated correctly (3pts/win), rankings updated |
| 12 | Edit Activity | PASS | Pre-populated form, updates saved, success message |
| 13 | Delete Activity | PASS | Confirmation dialog, activity removed, success message |

**Result: 13/13 tests PASS**

## Bugs Found & Fixed

### Bug 1: `end_time` validation mismatch (FIXED)
- **Severity:** Medium
- **Location:** `ClubActivityController.php` lines 59, 137
- **Issue:** Validation rule `date_format:H:i` rejects browser-submitted `H:i:s` format (e.g. `17:00:00`)
- **Fix:** Changed to `date_format:H:i,H:i:s` to accept both formats

### Bug 2: No duplicate team name validation (FIXED)
- **Severity:** Low
- **Location:** `ClubCompetitionController.php` `addTeam()` method
- **Issue:** Same team name can be added multiple times, causing "Team A vs Team A" matches in schedule
- **Fix:** Added existence check before creating team, returns 422 with error message

## Observations

- Type selector UI (cards with icons) works well for 3 types
- Competition panel uses AJAX throughout -- no full page reloads for team/match operations
- Team list only populated from match data, not standalone API -- teams show after schedule generation
- Vietnamese UI labels consistent throughout
- Format selector (Round Robin / Pool Play / Single Elimination) present in both create form and competition panel
