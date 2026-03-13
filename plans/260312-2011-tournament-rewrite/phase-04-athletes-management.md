# Phase 4: Athletes Management UI

## Context Links
- [Plan Overview](./plan.md)
- [UX Research](../reports/researcher-260312-2011-tournament-ux-research.md)
- Current view: `resources/views/home-yard/tournaments/athletes.blade.php`
- Controller: `app/Http/Controllers/Front/Tournament/TournamentAthleteController.php`

## Overview
- **Priority:** P2
- **Status:** Complete
- **Effort:** 3h

Rewrite athletes management as a dashboard tab. List athletes with status filtering, inline approve/reject, add athlete modal, search. Supports both singles and doubles (partner pairing).

## Key Insights
- Athletes have 3 statuses: pending, approved, rejected
- Doubles: `partner_id` links two TournamentAthlete records
- Payment status tracked separately
- Bulk actions needed: approve all pending, export
- Current view is a monolithic page - split into focused partial

## Requirements

### Functional
- Athletes list with columns: name, email, phone, category, status, payment, partner (if doubles), actions
- Filter tabs: All | Pending | Approved | Rejected
- Search by name/email
- Add athlete modal (name, email, phone, category, partner for doubles)
- Inline approve/reject buttons (no page reload)
- Bulk approve selected athletes
- Edit athlete inline or modal
- Remove athlete with confirmation

### Non-functional
- Alpine.js for filtering, search, inline actions (no page reload)
- AJAX calls for approve/reject/add/remove
- Toastr notifications for actions
- Responsive: table on desktop, cards on mobile

## Architecture

```
_athletes.blade.php
├── Filter tabs (Alpine x-data)
├── Search bar (Alpine x-model)
├── Athletes table/cards
│   ├── Row: name | category | status badge | actions
│   └── Actions: approve/reject/edit/remove (AJAX)
├── Add Athlete Modal (Alpine x-show)
└── Bulk action bar (when checkboxes selected)
```

## Related Code Files

### Create
- `resources/views/home-yard/tournaments/partials/_athletes.blade.php`
- `public/assets/js/tournament-athletes.js` - Alpine component for athletes

### Modify
- `app/Http/Controllers/Front/Tournament/TournamentAthleteController.php` - Implement methods

### Reference
- `app/Models/TournamentAthlete.php` - Model with partner relationship
- `app/Models/TournamentCategory.php` - Category types (singles/doubles)

## Implementation Steps

### 1. Implement TournamentAthleteController
```php
class TournamentAthleteController extends Controller
{
    public function index(Tournament $tournament)
    // Return athletes list as JSON for Alpine consumption
    // Includes: athlete data + categories + partner info

    public function store(Request $request, Tournament $tournament)
    // Validate: name, email, phone, category_id, partner_id (optional)
    // Create TournamentAthlete, link partner if doubles

    public function update(Request $request, Tournament $tournament, int $athleteId)
    // Update athlete info

    public function destroy(Tournament $tournament, int $athleteId)
    // Remove athlete (and unlink partner if doubles)

    public function updateStatus(Request $request, Tournament $tournament, int $athleteId)
    // Change status: pending/approved/rejected

    public function approve(Tournament $tournament, int $athleteId)
    // Quick approve shortcut

    public function reject(Tournament $tournament, int $athleteId)
    // Quick reject shortcut

    public function bulkApprove(Request $request, Tournament $tournament)
    // Approve multiple athletes by IDs array
}
```

### 2. Create Athletes Partial View
- Blade renders initial data, Alpine manages reactivity
- Filter tabs: use Alpine `x-data="{ activeFilter: 'all' }"` to show/hide rows
- Search: `x-model="searchQuery"` filters visible rows client-side
- Status badges: color-coded (pending=yellow, approved=green, rejected=red)
- Actions column: approve/reject buttons with `@click` handlers
- Partner display: show pair name for doubles categories

### 3. Add Athlete Modal
- Alpine `x-show="showAddModal"` triggered by "Add Athlete" button
- Form fields: name, email, phone, category (select), partner (select, shown for doubles only)
- Category select: `@change` toggles partner field visibility
- Partner select: populated from approved athletes in same category
- Submit via `fetch()` to store endpoint, Toastr on success, refresh list

### 4. Bulk Actions
- Checkbox per row + "Select All" header checkbox
- Floating action bar appears when > 0 selected
- "Approve Selected" button → AJAX call with IDs array
- "Remove Selected" with confirmation dialog

### 5. Mobile Responsive
- Desktop: full table with all columns
- Mobile: card layout per athlete
  - Name + category as header
  - Status badge + action buttons below
  - Partner info collapsible

## Todo List
- [x] Implement TournamentAthleteController methods
- [x] Create _athletes.blade.php partial
- [x] Create tournament-athletes.js (Alpine component)
- [x] Add athlete modal with doubles partner support
- [x] Status filter tabs (All/Pending/Approved/Rejected)
- [x] Search functionality
- [x] Inline approve/reject AJAX actions
- [x] Bulk approve functionality
- [x] Mobile card layout
- [x] Wire routes

## Success Criteria
- Athletes list loads with correct data
- Filter tabs work without page reload
- Search filters by name/email instantly
- Add athlete works for both singles and doubles
- Approve/reject updates status immediately (no reload)
- Bulk approve handles multiple athletes
- Mobile layout readable and functional

## Risk Assessment
- **Doubles partner pairing:** Medium - need to handle circular reference (A partners B, B partners A)
- **Concurrent approve/reject:** Low - optimistic UI with server validation

## Security Considerations
- Validate tournament ownership before any athlete operation
- Validate category belongs to tournament
- Validate partner belongs to same tournament and category

## Next Steps
- Phase 5: Draw/seeding uses approved athletes from this phase
