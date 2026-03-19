# Phase 5: Draw & Seeding UI

## Context Links
- [Plan Overview](./plan.md)
- [UX Research](../reports/researcher-260312-2011-tournament-ux-research.md)
- Service: `app/Services/Tournament/TournamentDrawService.php` (Phase 2)
- Controller: `app/Http/Controllers/Front/Tournament/TournamentDrawController.php`

## Overview
- **Priority:** P2
- **Status:** Complete
- **Effort:** 4h
- **Depends on:** Phase 2 (service layer)

Rewrite draw/seeding UI as dashboard tab. Visual interface for seeding athletes, executing draws (random/seeded/manual), viewing group assignments, and resetting draws.

## Key Insights
- 3 draw methods: random, seeded (snake draft), manual
- Singles: individual athletes assigned to groups
- Doubles: pairs (via partner_id) assigned together
- Draw results show groups with ordered athletes
- Must check for existing scheduled matches before allowing redraw
- Manual draw: drag-and-drop athletes into groups

## Requirements

### Functional
- Category selector (which category to draw)
- Draw method selector: Random | Seeded | Manual
- Pre-draw: athlete list with drag-to-reorder for seeding
- Execute draw button with confirmation
- Post-draw: group cards showing assigned athletes in order
- Reset draw (with warning if matches exist)
- Manual draw: drag athletes between group slots
- Lock/unlock draw to prevent accidental changes

### Non-functional
- Alpine.js for all interactivity
- Drag-and-drop via HTML5 Drag API + Alpine
- AJAX for draw execution (no page reload)
- Visual feedback: group cards with athlete names, seed numbers

## Architecture

```
_draw.blade.php
├── Category selector (Alpine)
├── Draw controls
│   ├── Method selector (random/seeded/manual)
│   ├── Execute Draw button
│   └── Reset Draw button (with confirmation)
├── Pre-draw view (seeding)
│   ├── Athlete list (draggable for reorder)
│   └── Seed number display
├── Post-draw view (results)
│   ├── Group cards (grid layout)
│   │   ├── Group A: athlete1, athlete2, ...
│   │   └── Group B: athlete1, athlete2, ...
│   └── Manual adjustment (drag between groups)
└── Status indicator (not drawn / drawn / locked)
```

## Related Code Files

### Create
- `resources/views/home-yard/tournaments/partials/_draw.blade.php`
- `public/assets/js/tournament-draw.js` - Alpine component for draw

### Modify
- `app/Http/Controllers/Front/Tournament/TournamentDrawController.php` - Implement methods

### Reference
- `app/Services/Tournament/TournamentDrawService.php` (Phase 2)
- `app/Models/Group.php`, `app/Models/TournamentAthlete.php`

## Implementation Steps

### 1. Implement TournamentDrawController
```php
class TournamentDrawController extends Controller
{
    public function __construct(private TournamentDrawService $drawService) {}

    public function index(Tournament $tournament)
    // Return draw page data: categories, current draw status per category

    public function draw(Request $request, Tournament $tournament)
    // Execute draw: validate category_id + method, call service
    // Return JSON: groups with athletes

    public function getResults(Tournament $tournament, int $categoryId)
    // Return current draw results as JSON

    public function reset(Request $request, Tournament $tournament)
    // Reset draw for category: check matches, call service
    // Return JSON success/error

    public function getManualDraw(Tournament $tournament, int $categoryId)
    // Return athletes + groups data for manual drag UI

    public function saveManualDraw(Request $request, Tournament $tournament)
    // Save manual group assignments from drag-and-drop
}
```

### 2. Create Draw Partial View
- Alpine component `x-data="drawManager()"` manages all state
- Category tabs/selector at top
- Conditional rendering based on draw state:
  - **Not drawn:** Show athlete list + draw controls
  - **Drawn:** Show group cards with results + reset option

### 3. Seeding Interface (Pre-Draw)
<!-- Updated: Validation Session 1 - Use SortableJS instead of HTML5 Drag API -->
- Approved athletes listed with seed number
- Drag handle (6-dot icon) for reorder
- **SortableJS** for drag-and-drop (reliable on mobile + desktop):
  ```javascript
  new Sortable(athleteList, {
      handle: '.drag-handle',
      animation: 150,
      onEnd: (evt) => { /* update seed numbers */ }
  });
  ```
- Seed numbers auto-update on reorder
- "Shuffle" button randomizes order
- Doubles: show pair names together (partner linked)

### 4. Draw Execution
- Method selector: 3 radio buttons (Random / Seeded / Manual)
- "Execute Draw" button:
  - Confirmation dialog: "This will assign athletes to groups. Continue?"
  - AJAX POST to draw endpoint
  - Loading spinner during execution
  - On success: transition to results view
  - On error: Toastr error message

### 5. Draw Results View (Post-Draw)
- Grid of group cards (2-3 columns desktop, 1 column mobile)
- Each card:
  - Group name header (Group A, Group B, ...)
  - Athlete list with seed/draw order numbers
  - For doubles: show "Player A / Player B" pair format
- Color-coded borders per group

### 6. Manual Draw Mode
- Split view: unassigned athletes (left) + group slots (right)
- Drag athletes from unassigned to group slots
- Drop zones highlight on drag-over
- "Save" button to persist assignments
- Validation: all athletes must be assigned before save

### 7. Reset Draw
- "Reset Draw" button (red/warning color)
- Pre-check: AJAX call to `checkScheduledMatches`
- If matches exist: show warning "X matches will be deleted"
- Confirmation dialog with input: type "RESET" to confirm
- On confirm: AJAX call to reset, transition to pre-draw view

## Todo List
- [x] Implement TournamentDrawController methods
- [x] Create _draw.blade.php partial
- [x] Create tournament-draw.js (Alpine component)
- [x] Seeding interface with drag-to-reorder
- [x] Draw execution (random + seeded methods)
- [x] Draw results group cards display
- [x] Manual draw mode (drag between groups)
- [x] Reset draw with safety checks
- [x] Doubles pair handling in all views
- [x] Mobile responsive layout

## Success Criteria
- Category selection shows correct athletes
- Random draw distributes athletes evenly across groups
- Seeded draw follows snake pattern
- Manual draw allows drag-and-drop assignment
- Draw results display correctly with group cards
- Reset clears assignments and warns about matches
- Doubles pairs displayed and handled correctly

## Risk Assessment
- **Drag-and-drop mobile:** Low - SortableJS handles touch events natively, no fallback needed
- **Draw algorithm correctness:** Mitigated by Phase 2 service extraction with same logic
- **Race condition on draw:** Use DB transaction in service (already patterned)

## Security Considerations
- Only tournament owner can execute/reset draws
- Validate category belongs to tournament
- Validate all athletes in request belong to tournament

## Next Steps
- Phase 6: Matches generated from draw results
