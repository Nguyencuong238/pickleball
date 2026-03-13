# Phase 6: Matches & Scoring UI

## Context Links
- [Plan Overview](./plan.md)
- [UX Research](../reports/researcher-260312-2011-tournament-ux-research.md)
- Service: `app/Services/Tournament/TournamentMatchService.php` (Phase 2)
- Controller: `app/Http/Controllers/Front/Tournament/TournamentMatchController.php`

## Overview
- **Priority:** P2
- **Status:** Complete
- **Effort:** 3h
- **Depends on:** Phase 2 (service layer)

Rewrite matches management as dashboard tab. Match list by group, status filtering, inline score entry for tournament owners (simple set scores). Auto-updates standings after score submission.

## Key Insights
- Matches auto-generated from round-robin algorithm (groups)
- Score entry: tournament owner enters set scores (e.g., Set 1: 11-9, Set 2: 11-7)
- Referee has separate live scoring screen - NOT in scope
- Standings auto-update after each score submission
- Match statuses: scheduled, in_progress, completed, cancelled
- `set_scores` stored as JSON array in MatchModel
- `best_of` field determines number of sets (typically 3)

## Requirements

### Functional
- Match list grouped by category and group
- Filter: All | Scheduled | In Progress | Completed
- "Generate Matches" button (creates round-robin from draw results)
- Each match row: match#, athlete1 vs athlete2, score, status, court, actions
- Click match → inline expand with score entry form
- Score entry: set-by-set inputs (e.g., Set 1: [__]-[__], Set 2: [__]-[__])
- Submit score → auto-calculate winner → update standings
- Delete/cancel match option

### Non-functional
- Alpine.js for inline score expansion and filtering
- AJAX for score submission (no page reload)
- Toastr for success/error feedback
- Large touch targets (48px+) for score inputs
- Polling: refresh match statuses every 15s during tournament

## Architecture

```
_matches.blade.php
├── Category/Group selector
├── Filter tabs (All/Scheduled/InProgress/Completed)
├── "Generate Matches" button (if no matches yet)
├── Match list
│   ├── Match row (collapsed)
│   │   ├── Match# | Athlete1 vs Athlete2 | Score | Status badge
│   │   └── Click to expand
│   └── Match row (expanded)
│       ├── Score entry form (per set)
│       ├── Submit / Cancel buttons
│       └── Match details (court, time, referee)
└── Match stats summary (total/completed/remaining)
```

## Related Code Files

### Create
- `resources/views/home-yard/tournaments/partials/_matches.blade.php`
- `public/assets/js/tournament-matches.js` - Alpine component

### Modify
- `app/Http/Controllers/Front/Tournament/TournamentMatchController.php` - Implement methods

### Reference
- `app/Services/Tournament/TournamentMatchService.php` (Phase 2)
- `app/Services/Tournament/TournamentStandingService.php` (Phase 2)
- `app/Models/MatchModel.php`

## Implementation Steps

### 1. Implement TournamentMatchController
```php
class TournamentMatchController extends Controller
{
    public function __construct(
        private TournamentMatchService $matchService,
        private TournamentStandingService $standingService
    ) {}

    public function index(Tournament $tournament)
    // Return matches grouped by category/group as JSON
    // Include: match data, athlete names, scores, statuses

    public function createForGroups(Request $request, Tournament $tournament)
    // Generate round-robin matches for category/round
    // Call matchService->createMatchesForGroups()
    // Return new matches JSON

    public function store(Request $request, Tournament $tournament)
    // Create single manual match

    public function show(Tournament $tournament, int $matchId)
    // Return match details JSON

    public function updateScore(Request $request, Tournament $tournament, int $matchId)
    // Validate set scores, call matchService->updateScore()
    // Auto-update standings via standingService
    // Return updated match + standings JSON

    public function destroy(Tournament $tournament, int $matchId)
    // Delete match, recalculate standings
}
```

### 2. Create Matches Partial View
- Alpine component `x-data="matchManager()"` manages state
- Top: category selector + group filter
- "Generate Matches" shown when no matches exist for selected category
- Match list: each row is collapsible

### 3. Match Row Component
- Collapsed state (default):
  ```
  #1 | Nguyen Van A  vs  Tran Van B | 11-9, 11-7 | [COMPLETED]
  ```
- Status badges with colors:
  - Scheduled: gray
  - In Progress: blue
  - Completed: green
  - Cancelled: red

### 4. Inline Score Entry (Expanded State)
- Click match row → Alpine toggles `expandedMatch` state
- Score form appears below the row:
  ```
  Set 1:  [  ] - [  ]
  Set 2:  [  ] - [  ]
  Set 3:  [  ] - [  ]  (shown based on best_of)

  [Submit Score]  [Cancel]
  ```
- Input fields: `type="number"`, `min="0"`, `max="15"`, large font (24px), large padding
- Auto-tab to next field on valid entry
- Submit: AJAX POST with set scores array
- On success: collapse row, update score display, Toastr, update match count

### 5. Generate Matches
- "Generate Matches" button per category
- Confirmation: "Generate round-robin matches for [Category Name]?"
- AJAX call to `createForGroups`
- On success: match list populates, button hides

### 6. Match Stats Summary
- Top bar: "Matches: 5 completed / 8 total | 3 remaining"
- Progress bar visual
- Updates after each score submission

### 7. Polling for Live Updates
```javascript
// tournament-matches.js
setInterval(async () => {
    const res = await fetch(`/tournament-manage/${tournamentId}/matches?category=${categoryId}`);
    const data = await res.json();
    // Alpine reactivity updates the match list
}, 15000);
```

## Todo List
- [x] Implement TournamentMatchController methods
- [x] Create _matches.blade.php partial
- [x] Create tournament-matches.js (Alpine component)
- [x] Match list with category/group filtering
- [x] Inline score entry (expand on click)
- [x] Score submission with auto-standings update
- [x] Generate matches button
- [x] Match status badges
- [x] Stats summary bar
- [x] Polling for live match status
- [x] Mobile responsive (card layout)

## Success Criteria
- Matches list loads grouped by category/group
- Filter tabs work correctly
- Generate matches creates correct round-robin schedule
- Inline score entry expands/collapses smoothly
- Score submission updates match and standings
- Stats summary reflects current state
- Mobile usable with large touch targets

## Risk Assessment
- **Score validation:** Ensure invalid scores rejected (negative, exceeding max)
- **Standings desync:** After score update, always recalculate full group standings
- **Concurrent scoring:** Low risk for tournament owner (single user), but use DB transaction

## Security Considerations
- Only tournament owner can enter/modify scores
- Validate match belongs to tournament
- Validate score ranges (non-negative, within game rules)

## Next Steps
- Phase 7: Rankings display fed by standings data
