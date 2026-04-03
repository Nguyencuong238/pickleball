# Tournament Views Structure

**Last Updated**: 2026-04-03
**Focus**: Public and Admin tournament views architecture

## Overview

Tournament views are organized into two main sections: Admin Dashboard (Home Yard) and Public Frontend. The public tournament detail page displays rich schedule, standings, and bracket information through a tabbed interface with advanced match editing capabilities.

## Admin Views (`resources/views/home-yard/tournaments/`)

### Core Admin Pages

| File | Purpose |
|------|---------|
| `dashboard.blade.php` | Main tournament management dashboard with sidebar nav |
| `draw.blade.php` | Draw/seeding management and execution |
| `bracket.blade.php` | Admin single elimination knockout bracket display |

### Admin Partials (20+ files)

**Navigation & Overview:**
- `_sidebar.blade.php` - Dashboard navigation with tournament sections
- `_overview.blade.php` - Tournament summary and stats

**Athlete Management:**
- `_athletes.blade.php` - Athlete listing table
- `_athletes-modal.blade.php` - Add/edit athlete modal
- `_athletes-mobile-cards.blade.php` - Mobile-friendly athlete cards

**Draw & Seeding:**
- `_draw.blade.php` - Draw execution container
- `_draw-seeding.blade.php` - Seeding algorithm selection UI
- `_draw-manual.blade.php` - Manual draw builder with sortable interface
- `_draw-results.blade.php` - Group assignments display

**Match Management:**
- `_matches.blade.php` - Match listing container
- `_matches-row.blade.php` - Individual match row with score entry
- `_matches-empty-generate.blade.php` - Empty state with generate button
- `_bracket-match-editor.blade.php` - Match editor with athlete reassignment and cascade warning
- `_bracket-swap-editor.blade.php` - Bracket slot swap with null athlete/bye support

**Rankings & Results:**
- `_rankings.blade.php` - Rankings container by category
- `_rankings-group-table.blade.php` - Group standings table with cumulative game scores and +/- column
- `_category-editor.blade.php` - Category configuration form

**Bracket Display:**
- `_bracket-tree.blade.php` - Bracket tree structure (16/8/4-player formats)
- `_bracket-match.blade.php` - Admin bracket match card (editable scores)

**Responsive:**
- `_mobile-tabs.blade.php` - Mobile tab navigation

## Public Frontend Views (`resources/views/front/tournaments/`)

### Main Pages

| File | Purpose |
|------|---------|
| `tournaments_detail.blade.php` | Tournament detail with tab navigation |
| `tabs-section.blade.php` | Dynamic tab content (schedule, standings, bracket) |

### Public Partials (New - Mar 2026)

- `_front-bracket-match.blade.php` - Read-only bracket match card for public viewing

**Features:**
- Displays match status (scheduled, in_progress, completed)
- Shows "LIVE" badge for in-progress matches (limited to 2-hour window after match start)
- Displays athlete names and scores
- Highlights winner with visual styling
- No edit controls (read-only for public)
- Includes match date field

## View Component Details

### Tournament Detail Tab Structure

The `tournaments_detail.blade.php` page includes tabs:

1. **Schedule Tab** (`tabs-section.blade.php`)
   - Group stage grid with all groups displayed
   - Standings tables for each group
   - Knockout bracket tree
   - Match cards with live status

2. **Athletes Tab**
   - Registered participant list
   - Skill levels and categories
   - Partner info for doubles

3. **Information Tab**
   - Tournament rules and details
   - Location and dates
   - Registration info

### Eager Loading Optimization

`HomeController::tournamentsDetail()` loads:
- Tournament categories with groups
- All groups with standings
- All matches with bracket data
- Bracket rounds for knockout display

**Query optimization:**
```php
with([
    'categories' => fn($q) => $q->with(['groups' => fn($gq) => $gq->with('standings')]),
    'matches' => fn($q) => $q->with('bracketRound'),
    'bracketRounds' => function($q) { ... }
])
```

## Controller Integration

### `HomeController` Methods

| Method | View | Purpose |
|--------|------|---------|
| `tournamentsDetail()` | `tournaments_detail.blade.php` | Load and display tournament detail with all relationships |

**Key relationships loaded:**
- Tournament categories (skill/age groups)
- Tournament groups (group stage organization)
- Group standings (rankings)
- Tournament matches (all competition matches)
- Bracket rounds (knockout structure)

## Responsive Design

### Desktop Layout
- Horizontal tab navigation at top
- Full-width grid layouts for groups/standings
- Wide bracket tree display

### Mobile Layout
- Vertical scrollable tabs (`_mobile-tabs.blade.php`)
- Stacked group/standings cards
- Bracket in horizontal scroll container

## Data Flow for Schedule Display

```
Public Request to /tournaments/{slug}
        ↓
HomeController::tournamentsDetail()
        ↓
Load Tournament with Eager Load:
  - Categories → Groups → Standings
  - Matches → Bracket Rounds
        ↓
Pass to tournaments_detail.blade.php
        ↓
Render tab navigation + tab-section.blade.php
        ↓
Display Groups/Standings/Bracket based on active tab
        ↓
Render read-only bracket match cards via _front-bracket-match.blade.php
```

## CSS & Styling

### Bracket Match Cards (Public)

Selectors:
- `.front-bracket-match` - Container
- `.front-bracket-match--done` - Completed match styling
- `.front-bracket-match--live` - In-progress match styling
- `.front-bracket-match-live` - "LIVE" badge
- `.front-bracket-slot` - Athlete slot container
- `.front-bracket-slot--winner` - Winner highlight
- `.front-bracket-slot-name` - Athlete name display
- `.front-bracket-slot-score` - Score display (- if not played)

## Match Editor Features (2026-03-22)

### Bracket Match Editor
- Athlete reassignment with dropdown selection
- Cascade warning when changing athletes in elimination bracket
- Match date field for scheduling
- Real-time athlete list filtering by category
- Preselect current athletes with string conversion for x-model binding

### Bracket Slot Swap Editor
- Swap bracket slots including null athletes and bye matches
- Confirmation dialog with descriptive messaging
- Support for flexible slot reordering in brackets

## Athlete Management Enhancements

- User search by email or phone for tournament athlete management
- Category-aware athlete selection and partner management
- First bracket round allows all category athletes for wildcard flexibility
- Uses `group_standings.is_advanced` instead of tournament_athletes for eligible athletes query

## Tournament Form Changes

- Renamed `rules` field to `competition_rules` in tournament forms
- Added `event_timeline` field to tournament configuration
- Enhanced registration logic with category validation

## Related Files

- **Controller**: `app/Http/Controllers/Front/HomeController.php` - tournamentsDetail() method
- **Admin Controller**: `app/Http/Controllers/Admin/TournamentController.php` - tournament management
- **Models**: Tournament, TournamentCategory, Group, GroupStanding, MatchModel
- **Services**: TournamentService, KnockoutBracketService, TournamentCrudService
- **JavaScript**:
  - `bracket-match-editor.js` (v1.2) - match editor with athlete reassignment
  - `bracket-swap-editor.js` (v1.1) - slot swap functionality
- **Alpine.js**: Components for admin dashboard tournament management

## Unresolved Notes

- Mobile bracket horizontal scroll UX could be enhanced with touch swipe indicators
- Consider caching eager-loaded tournament data for public views (tournamentsDetail)
- Wildcard flexibility in first bracket round may need additional validation for tournament integrity
