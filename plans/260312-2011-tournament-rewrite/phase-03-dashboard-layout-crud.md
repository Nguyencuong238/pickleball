# Phase 3: Tournament Dashboard Layout + CRUD

## Context Links
- [Plan Overview](./plan.md)
- [UX Research](../reports/researcher-260312-2011-tournament-ux-research.md)
- Layout: `resources/views/layouts/homeyard.blade.php`
- Current views: `resources/views/home-yard/tournaments/`

## Overview
- **Priority:** P1
- **Status:** Complete
- **Effort:** 4h

Build the tournament dashboard shell (sidebar nav + content area) and rewrite CRUD views. This is the foundation all other phases plug into.

## Key Insights
- Research recommends: sidebar nav (desktop) + bottom tabs (mobile)
- Current views extend `layouts.homeyard` - keep this pattern
- Tournament has a `slug` field for URL routing
- Categories managed inline during tournament create/edit
- Progress indicator shows tournament setup completion status

## Requirements

### Functional
- Dashboard layout with sidebar: Overview | Athletes | Draw | Matches | Rankings
- Sidebar shows active section + completion badges (green check if step done)
- Tournament list page (index) with search/filter
- Create/edit forms with category management
- Overview tab: tournament stats summary + progress indicator
- Mobile: bottom tab bar replacing sidebar

### Non-functional
- Mobile-first CSS
- < 200 lines per view file
- Alpine.js for tab switching, form interactions
- No full page reloads for tab navigation (Alpine manages sections)

## Architecture

```
Dashboard Layout (dashboard.blade.php)
├── @extends('layouts.homeyard')
├── Sidebar (_sidebar.blade.php)
│   ├── Overview (active state)
│   ├── Athletes (badge: 12/16 approved)
│   ├── Draw (badge: completed/pending)
│   ├── Matches (badge: 5/8 played)
│   └── Rankings
├── Content Area
│   └── @yield('tournament-content')
└── Mobile Bottom Tabs (CSS hidden on desktop)
```

## Related Code Files

### Create
- `resources/views/home-yard/tournaments/dashboard.blade.php` - Main layout
- `resources/views/home-yard/tournaments/partials/_sidebar.blade.php`
- `resources/views/home-yard/tournaments/partials/_overview.blade.php`
- `resources/views/home-yard/tournaments/partials/_mobile-tabs.blade.php`
- `public/assets/css/tournament-dashboard.css` - New dashboard styles
- `public/assets/js/tournament-dashboard.js` - Alpine components for dashboard

### Modify
- `resources/views/home-yard/tournaments/index.blade.php` - Rewrite list page
- `resources/views/home-yard/tournaments/create.blade.php` - Rewrite create form
- `resources/views/home-yard/tournaments/edit.blade.php` - Rewrite edit form
- `app/Http/Controllers/Front/Tournament/TournamentController.php` - Implement methods

## Implementation Steps

### 1. Create Dashboard CSS
```css
/* tournament-dashboard.css */
/* Mobile-first approach */

.tournament-dashboard { display: flex; flex-direction: column; }
.tournament-sidebar { display: none; } /* Hidden on mobile */
.tournament-content { width: 100%; padding: 16px; }

/* Sidebar nav items */
.sidebar-nav-item { padding: 12px 16px; border-left: 3px solid transparent; }
.sidebar-nav-item.active { border-left-color: var(--primary-color); background: rgba(0,217,181,0.08); }
.sidebar-nav-item .badge { /* completion badge */ }

/* Mobile bottom tabs */
.mobile-tabs { position: fixed; bottom: 0; display: flex; }

/* Desktop */
@media (min-width: 1024px) {
    .tournament-dashboard { flex-direction: row; }
    .tournament-sidebar { display: block; width: 240px; min-height: calc(100vh - 64px); }
    .tournament-content { flex: 1; }
    .mobile-tabs { display: none; }
}
```

### 2. Create Dashboard Layout (dashboard.blade.php)
- Extends `layouts.homeyard`
- Receives `$tournament` variable
- Sidebar: 5 nav items with dynamic active state and badges
- Content area: `@yield('tournament-content')`
- Mobile bottom tabs: same 5 items as sidebar
- Alpine.js `x-data` for active tab management
- Progress indicator bar at top (% of steps completed)

### 3. Create Sidebar Partial
- Nav items: Overview, Athletes, Draw, Matches, Rankings
- Each item: icon placeholder + label + badge
- Badge logic:
  - Athletes: `{approved_count}/{total_count}`
  - Draw: "Done" / "Pending"
  - Matches: `{completed}/{total}`
  - Rankings: visible only after matches exist
- Active state from current URL or Alpine state

### 4. Create Overview Partial
- Tournament info card (name, dates, location, format, status)
- Stats row: athletes count, matches played, categories count
- Progress checklist:
  - [x] Tournament created
  - [ ] Categories added (count)
  - [ ] Athletes registered (count)
  - [ ] Draw completed
  - [ ] Matches generated
  - [ ] Results finalized
- Quick action buttons: "Add Athletes", "Start Draw", "View Matches"

### 5. Rewrite Tournament Index
- Clean card/list view with search bar
- Filter by status (upcoming/ongoing/completed)
- Each card: banner image, name, dates, athlete count, status badge
- "Create Tournament" CTA button
- Alpine.js for search filtering

### 6. Rewrite Create/Edit Forms
- Clean form layout with sections:
  - Basic info (name, slug, dates, location, description)
  - Format settings (competition_format, rules)
  - Registration (price, max_participants, registration_date)
  - Categories (inline add/remove with Alpine.js)
  - Media (banner upload, gallery)
- Alpine.js for:
  - Dynamic category rows (add/remove)
  - Image preview on upload
  - Form validation feedback

### 7. Implement TournamentController Methods
- `index`: query tournaments with search/filter, return index view
- `create`: return create view
- `store`: validate + create tournament + sync categories + handle media
- `show`: load tournament with relations, return dashboard with overview
- `edit`: return edit view with tournament data
- `update`: validate + update + sync categories + media
- `destroy`: delete tournament (soft or hard based on status)
- `overview`: return overview stats for tournament

## Todo List
- [x] Create tournament-dashboard.css (split into 4 modules under tournament-dashboard/)
- [x] Create dashboard.blade.php layout
- [x] Create _sidebar.blade.php partial
- [x] Create _overview.blade.php partial
- [x] Create _mobile-tabs.blade.php partial
- [x] Create tournament-dashboard.js (Alpine components)
- [x] Rewrite index.blade.php (tournament list)
- [x] Rewrite create.blade.php form
- [x] Rewrite edit.blade.php form
- [x] Implement TournamentController methods
- [x] Wire routes to controller methods (resource route already registered)

## Success Criteria
- Dashboard loads with sidebar on desktop, bottom tabs on mobile
- Sidebar shows correct active state and badges
- Tournament CRUD works (create, edit, delete)
- Category inline management works
- Progress indicator reflects real tournament state
- Clean, responsive design on all screen sizes

## Risk Assessment
- **Layout conflicts with homeyard:** Low - dashboard is a new view within existing layout
- **Category sync complexity:** Medium - inline add/remove needs careful Alpine + server sync

## Security Considerations
- CSRF token on all forms
- Validate all inputs server-side
- Authorize tournament ownership (user can only manage own tournaments)
- Sanitize rich text fields (description, rules)

## Next Steps
- Phases 4-7 build content for each sidebar section
