# Phase 7: Rankings & Standings UI

## Context Links
- [Plan Overview](./plan.md)
- Service: `app/Services/Tournament/TournamentStandingService.php` (Phase 2)
- Controller: `app/Http/Controllers/Front/Tournament/TournamentRankingController.php`

## Overview
- **Priority:** P3
- **Status:** Complete
- **Effort:** 2h
- **Depends on:** Phase 2 (service layer), Phase 6 (match data)

Rewrite rankings as dashboard tab. Group standings tables with sortable columns, category switching, advancement indicators. Auto-refreshes as scores come in.

## Key Insights
- Rankings come from `GroupStanding` model (per group)
- Columns: rank, athlete/pair name, matches played, W/L, sets W/L, set differential, points
- Advancement: top N athletes advance (marked `is_advanced`)
- 3 points per win, 1 per draw, 0 per loss
- Tiebreaker order: points → set differential → games differential → head-to-head
- For doubles: show pair names

## Requirements

### Functional
- Category selector at top
- Group standings tables (one per group)
- Columns: Rank | Athlete | P | W | L | SW | SL | SD | Pts
- Sortable columns (click header to sort)
- Advancement line: top N rows highlighted with different background
- Overall tournament rankings view (across all groups)
- Auto-refresh via polling during active tournament

### Non-functional
- Alpine.js for sorting, category switching
- Clean table design, zebra striping
- Mobile: horizontal scroll or condensed columns
- Polling: 15s refresh during active matches

## Architecture

```
_rankings.blade.php
├── Category selector tabs
├── View toggle: By Group | Overall
├── Group standings tables
│   ├── Group A table
│   │   ├── Header row (sortable)
│   │   ├── Athlete rows
│   │   └── Advancement line separator
│   ├── Group B table
│   └── ...
└── Legend (points system explanation)
```

## Related Code Files

### Create
- `resources/views/home-yard/tournaments/partials/_rankings.blade.php`
- `public/assets/js/tournament-rankings.js` - Alpine component

### Modify
- `app/Http/Controllers/Front/Tournament/TournamentRankingController.php` - Implement methods

### Reference
- `app/Services/Tournament/TournamentStandingService.php` (Phase 2)
- `app/Models/GroupStanding.php`

## Implementation Steps

### 1. Implement TournamentRankingController
```php
class TournamentRankingController extends Controller
{
    public function __construct(private TournamentStandingService $standingService) {}

    public function index(Tournament $tournament)
    // Return rankings page data: categories list + default category standings

    public function getCategoryRankings(Tournament $tournament, int $categoryId)
    // Return JSON: groups with standings for category
    // Each group: { group_name, standings: [{ rank, athlete_name, played, won, lost, sets_won, sets_lost, set_diff, points, is_advanced }] }

    public function getCategoryGroups(Tournament $tournament, int $categoryId)
    // Return groups list for category
}
```

### 2. Create Rankings Partial View
- Alpine component `x-data="rankingsManager()"`
- Category tabs at top
- On category change: fetch standings via AJAX, update tables

### 3. Group Standings Table
```html
<table class="standings-table">
  <thead>
    <tr>
      <th @click="sortBy('rank')">#</th>
      <th @click="sortBy('name')">Athlete</th>
      <th @click="sortBy('played')">P</th>
      <th @click="sortBy('won')">W</th>
      <th @click="sortBy('lost')">L</th>
      <th @click="sortBy('sets_won')">SW</th>
      <th @click="sortBy('sets_lost')">SL</th>
      <th @click="sortBy('set_diff')">SD</th>
      <th @click="sortBy('points')">Pts</th>
    </tr>
  </thead>
  <tbody>
    <!-- Rows with advancement highlighting -->
  </tbody>
</table>
```

### 4. Advancement Indicator
- Top N rows (based on `advancing_count` from Group) get highlighted background
- Visual separator line between advancing and non-advancing
- Badge or icon next to advancing athletes

### 5. Overall View
- Aggregate standings across all groups for a category
- Useful for post-tournament final rankings
- Sort by: points → set_diff → games_diff

### 6. Styling
```css
.standings-table { width: 100%; border-collapse: collapse; }
.standings-table th { cursor: pointer; user-select: none; }
.standings-table th:hover { background: rgba(0,0,0,0.05); }
.standings-row.advancing { background: rgba(0,217,181,0.1); }
.standings-row.advancing:hover { background: rgba(0,217,181,0.15); }
.advancement-line { border-bottom: 2px dashed var(--primary-color); }
.sort-indicator { /* arrow up/down icon */ }
```

### 7. Auto-Refresh
- Poll every 15s during active tournament
- Only refresh if tournament status is "ongoing"
- Visual indicator: "Last updated: X seconds ago"

## Todo List
- [x] Implement TournamentRankingController methods
- [x] Create _rankings.blade.php partial
- [x] Create tournament-rankings.js (Alpine component)
- [x] Category selector tabs
- [x] Group standings tables with sortable columns
- [x] Advancement highlighting and separator
- [x] Overall rankings view
- [x] Auto-refresh polling
- [x] Mobile responsive (horizontal scroll or condensed)

## Success Criteria
- Rankings load for each category
- Tables display correct standings data
- Column sorting works
- Advancement rows visually distinct
- Auto-refresh updates during active tournament
- Mobile readable with scroll

## Risk Assessment
- **Stale data:** Mitigated by 15s polling
- **Large groups:** Performance fine for typical tournament sizes (8-16 per group)

## Security Considerations
- Rankings are read-only - no special auth needed beyond tournament access
- Ensure standings data matches actual match results (integrity)

## Next Steps
- After all phases: remove old controller methods, clean up old views
- Future: exports, advanced bracket visualization
