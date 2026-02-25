# Phase 4: Blade Views

## Context Links
- [Plan Overview](./plan.md)
- [Phase 3: Controllers & Routes](./phase-03-controllers-and-routes.md)
- [Code Standards - Blade](../../docs/code-standards.md#blade-conventions)
- Reference views: `resources/views/home-yard/tournaments/index.blade.php`, `resources/views/home-yard/tournaments/show.blade.php`

## Overview
- **Priority**: P2
- **Status**: completed
- **Completion Date**: 2026-02-25
- **Description**: Create 6 Blade views for league management in the Home Yard dashboard, plus sidebar link. Follow existing tournament view patterns.

### Completion Summary
All 12 tasks completed successfully:
1. League index view with stats cards and league table
2. Create/edit forms with shared _form partial
3. League show dashboard with 4 tabs (overview, teams, schedule, standings)
4. Tab components for overview, teams, matches, and standings
5. Standalone matches page for full-screen viewing
6. Sidebar navigation link with active state
7. AJAX handlers for score entry and team/player management
8. XSS security fixes using @json and textContent
9. Controller eager loading for teams.captain relationship
10. Vietnamese language support with proper diacritics (Tiếng Việt có dấu)
11. Mobile-responsive design tested
12. Comprehensive modals for add team, add player, and score entry

## Key Insights
- Home Yard views extend `layouts.front` layout
- Existing tournament views use Tailwind CSS classes
- Tournament index shows stats cards + table with pagination
- Tournament show has tabbed interface (overview, athletes, matches, rankings)
- Modals used for create/edit forms (Bootstrap or Alpine.js)
- User search likely uses AJAX with existing user search patterns

## Requirements

### Functional
- League list with stats cards (total, active, completed) and search/filter
- Create/edit form for league details and config
- League dashboard with tabs: Overview, Teams, Schedule, Standings
- Team management with player roster (add/remove via modal)
- Match list grouped by round with score entry modal
- Standings table with rank, team, W/L/D, games, points

### Non-functional
- Responsive design (mobile-friendly)
- Consistent with existing home-yard visual style
- AJAX for score entry and team/player management
- Loading states for async operations

## Related Code Files

| File | Action |
|------|--------|
| `resources/views/home-yard/leagues/index.blade.php` | create |
| `resources/views/home-yard/leagues/create.blade.php` | create |
| `resources/views/home-yard/leagues/edit.blade.php` | create |
| `resources/views/home-yard/leagues/show.blade.php` | create |
| `resources/views/home-yard/leagues/teams.blade.php` | create |
| `resources/views/home-yard/leagues/matches.blade.php` | create |
| `resources/views/layouts/front.blade.php` | modify (add leagues link to sidebar/nav) |

## Implementation Steps

### 1. League Index (~150 lines)

Create `resources/views/home-yard/leagues/index.blade.php`:
- Extend `layouts.front`
- Stats cards row: Total Leagues, Active, Completed (same pattern as tournament index)
- Action button: "Create League" linking to `homeyard.leagues.create`
- Table columns: Name, Season, Status (badge), Teams count, Start Date, Actions (View/Edit/Delete)
- Status badges: draft=gray, registration=blue, active=green, completed=purple, cancelled=red
- Pagination at bottom
- Empty state message if no leagues

### 2. League Create (~120 lines)

Create `resources/views/home-yard/leagues/create.blade.php`:
- Extend `layouts.front`
- Form posting to `homeyard.leagues.store`
- Fields:
  - Name (text, required)
  - Description (textarea)
  - Season Name (text)
  - Start Date, End Date (date pickers)
  - Registration Deadline (datetime)
  - Logo (file upload with preview)
- Config section (collapsible or separate card):
  - Match Format: dynamic list of game types (WD, MD, MXD) -- add/remove inputs
  - Max Teams (number, default 16)
  - Max Players Per Team (number, default 10)
  - Points for Win (number, default 3)
  - Points for Loss (number, default 0)
- Validation error display using `@error` directive

### 3. League Edit (~130 lines)

Create `resources/views/home-yard/leagues/edit.blade.php`:
- Same structure as create, pre-filled with `$league` data
- Form uses PUT method
- Config fields populated from `$league->config` array
- Additional: status display (read-only) with status change button
<!-- Updated: Validation Session 1 - Confirm dialog for config changes -->
- **Config change warning**: When league status=active and config fields modified, show JS confirm dialog: "Changing config will recalculate all standings. Continue?" before form submit. Track original config values with hidden inputs or data attributes for comparison.

### 4. League Show/Dashboard (~200 lines, largest view)

Create `resources/views/home-yard/leagues/show.blade.php`:
- Extend `layouts.front`
- Header: league name, status badge, season, date range, edit button
- Tab navigation: Overview | Teams | Schedule | Standings
- Use Alpine.js or URL fragments for tab switching

**Overview Tab:**
- League details card (description, config summary)
- Quick stats: total teams, total matches, completed matches, next scheduled match
- Status action buttons: "Open Registration", "Start League", "Complete League" (context-dependent)
- Generate Schedule button (if status=registration and 2+ teams, no existing schedule)

**Teams Tab** (content from teams.blade.php or inline):
- Add Team button (opens modal)
- Team cards/list: team name, logo, captain name, player count, status
- Expand team to see roster (player name, gender, position)
- Add Player button per team (opens modal with user search)
- Remove player/team buttons with confirmation

**Schedule Tab** (content from matches.blade.php or inline):
- Rounds accordion/cards
- Each round: list of matches (Home Team vs Away Team, score, status)
- Click match to open score entry modal
- Score entry modal: either direct match score OR game-by-game scores
- Game-by-game: list game_type with score inputs per game

**Standings Tab:**
- Table: Rank, Team, Played, W, L, D, GW, GL, GD, Points
- Sorted by rank (pre-calculated)
- Highlight top teams (top 2 or configurable)

### 5. Team Management Partial

Create `resources/views/home-yard/leagues/teams.blade.php`:
- Can be `@include`d in show.blade.php or standalone
- Add Team modal: team name, captain (user search), logo upload
- Team list with expandable roster
- Add Player modal: user search (AJAX to existing user search endpoint), gender select, position input
- AJAX handlers for add/remove operations with DOM updates

### 6. Match & Score Entry

Create `resources/views/home-yard/leagues/matches.blade.php`:
- Can be `@include`d in show.blade.php or standalone
- Rounds grouped in cards/accordion
- Match row: round label, home team, score display, away team, status badge, edit button
- Score entry modal:
  - Match-level: home_score / away_score inputs
  - Game-level: list each game (game_type label), home_score / away_score per game
  - Submit button posts to appropriate endpoint
  - On success: update DOM, refresh standings section

### 7. Sidebar Integration

Modify `resources/views/layouts/front.blade.php` (or relevant sidebar partial):
- Add "Leagues" navigation link after "Tournaments" in Home Yard section
- Icon: use placeholder `[LEAGUE]` per project convention (no emoji)
- Route: `route('homeyard.leagues.index')`
- Active state: `request()->routeIs('homeyard.leagues.*')`

### JavaScript (~50 lines inline or in @push('scripts'))

8. Score entry AJAX:
   ```javascript
   // Submit score via fetch/axios
   // PUT to leagues/{league}/matches/{match}/score
   // Update DOM on success (score display, match status badge)
   // Refresh standings table via partial reload or full page reload
   ```

9. Team/player AJAX:
   ```javascript
   // POST to leagues/{league}/teams
   // POST to leagues/{league}/teams/{team}/players
   // DELETE operations with confirmation dialog
   // Update team list DOM on success
   ```

10. User search for player/captain assignment:
    - Reuse existing user search pattern if available
    - Debounced input, fetch matching users, display dropdown

## Todo List
- [x] Create leagues/index.blade.php with stats and table
- [x] Create leagues/create.blade.php with config form
- [x] Create leagues/edit.blade.php (shared _form.blade.php partial)
- [x] Create leagues/show.blade.php with tabbed dashboard
- [x] Create leagues/_tab-teams.blade.php partial (teams + player modals)
- [x] Create leagues/_tab-matches.blade.php partial with score entry modal
- [x] Create leagues/_tab-overview.blade.php partial (stats + status actions)
- [x] Create leagues/_tab-standings.blade.php partial
- [x] Create leagues/matches.blade.php standalone page
- [x] Add Leagues link to sidebar navigation + overview quick actions
- [x] Implement AJAX handlers for score entry (fetch API)
- [x] Implement AJAX handlers for team/player management (user search)
- [x] Test responsive layout on mobile viewport (manual)
- [x] Fix XSS vulnerabilities (using @json, textContent)
- [x] Add teams.captain eager load in controller
- [x] Vietnamese text with proper diacritics (Tiếng Việt có dấu)

## Success Criteria
- All views render without errors
- Forms submit and validate correctly
- Score entry updates match and standings
- Team/player management works via modals
- Sidebar link active state works
- Views consistent with existing tournament views visually
- No broken links or missing routes

## Risk Assessment
- **View complexity**: show.blade.php may exceed 200 lines. Mitigate by extracting tabs into `@include` partials.
- **User search**: Need existing user search endpoint. If none exists, create simple one or use select dropdown with pre-loaded users.
- **JavaScript**: Inline JS may grow. Keep minimal for MVP; extract to separate file if exceeding 100 lines.

## Security Considerations
- All forms include `@csrf` directive
- PUT/DELETE forms use `@method` directive
- User-facing data escaped with `{{ }}` (not `{!! !!}`)
- File uploads validated server-side (controller handles, view just provides input)
