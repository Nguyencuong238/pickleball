# Phase 01: League Register Page - 3 Tab Layout

**Priority**: High
**Status**: Complete

## Overview

Add tab navigation to the public league register page showing: info+form, schedule, standings.

## Related Code Files

### Modify
- `app/Http/Controllers/Front/LeagueRegistrationController.php` - `showForm()` eager-load relationships
- `resources/views/front/leagues/register.blade.php` - Add tabs UI, move existing content to tab 1, add schedule/standings tabs

### Reference (admin tabs)
- `resources/views/home-yard/leagues/_tab-matches.blade.php` - Schedule display pattern
- `resources/views/home-yard/leagues/_tab-standings.blade.php` - Standings display pattern

## Implementation Steps

### 1. Update Controller `showForm()`
- Eager-load: `rounds.matches.homeTeam`, `rounds.matches.awayTeam`, `rounds.matches.games`, `standings.team`, `teams`
- Pass loaded league to view (relationships already loaded)

### 2. Update View - Add Tab Navigation
- Add tab bar below header with 3 tabs: "Thong tin & Dang ky", "Lich thi dau", "Bang xep hang"
- Style consistent with existing site (gradient active tab indicator)
- Hash-based tab persistence (`#info`, `#schedule`, `#standings`)

### 3. Tab 1 - Info & Registration (existing content)
- Wrap existing content (description card + form + closed message) in tab panel div

### 4. Tab 2 - Schedule (read-only)
- Show rounds with round_number, scheduled_date, scheduled_time, venue
- Show matches per round: homeTeam vs awayTeam with score (if completed)
- No edit buttons, no modals (public view)
- MLP format: show sub-game details if applicable

### 5. Tab 3 - Standings (read-only)
- Standings table: rank, team name, played, wins, losses, GW, GL, GD, points
- Reuse same table structure as admin tab
- Empty state message if no standings yet

### 6. Tab Switching JS
- Vanilla JS tab switching (show/hide panels)
- URL hash persistence on tab click
- Read hash on page load to activate correct tab
- Default to `#info` tab

## Success Criteria
- [x] 3 tabs visible on league register page
- [x] Existing registration form unchanged
- [x] Schedule shows rounds and matches (read-only)
- [x] Standings table displays correctly
- [x] Tab state persists via URL hash
- [x] Mobile responsive
