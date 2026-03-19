# Phase 1: Setup Alpine.js + Routes + Controller Stubs

## Context Links
- [Plan Overview](./plan.md)
- [UX Research](../reports/researcher-260312-2011-tournament-ux-research.md)
- Current routes: `routes/web.php`
- Current controller: `app/Http/Controllers/Front/HomeYardTournamentController.php`
- Layout: `resources/views/layouts/homeyard.blade.php`

## Overview
- **Priority:** P1 (blocks all other phases)
- **Status:** Complete
- **Effort:** 3h

Add Alpine.js to project, create 5 new controllers with stubs, define new route group, keep old controller functional during transition.

## Key Insights
- Alpine.js NOT in project yet - add via CDN in homeyard layout
- jQuery/Select2/Toastr already loaded - Alpine coexists fine
- Existing `HomeYardTournamentController` has 60+ methods - keep it until all routes migrated
- Service directory `app/Services/` exists with 20+ services - follow same pattern

## Requirements

### Functional
- Alpine.js available in all homeyard views
- 5 new controller files with method stubs
- New route group registered (old routes still work)
- No breaking changes to existing functionality

### Non-functional
- Alpine.js loaded after jQuery (no conflicts)
- Controllers follow existing DI pattern (constructor injection)

## Architecture

```
routes/web.php
  └── Route::prefix('tournaments')->group(...)
        ├── TournamentController          → CRUD + overview
        ├── TournamentAthleteController   → athletes management
        ├── TournamentDrawController      → draw/seeding
        ├── TournamentMatchController     → matches/scoring
        └── TournamentRankingController   → rankings/standings
```

## Related Code Files

### Modify
- `resources/views/layouts/homeyard.blade.php` - Add Alpine.js CDN
- `routes/web.php` - Add new route group (keep old routes)

### Create
- `app/Http/Controllers/Front/Tournament/TournamentController.php`
- `app/Http/Controllers/Front/Tournament/TournamentAthleteController.php`
- `app/Http/Controllers/Front/Tournament/TournamentDrawController.php`
- `app/Http/Controllers/Front/Tournament/TournamentMatchController.php`
- `app/Http/Controllers/Front/Tournament/TournamentRankingController.php`

## Implementation Steps

### 1. Add Alpine.js + SortableJS + CSRF Meta Tag to Layout
<!-- Updated: Validation Session 1 - Add SortableJS CDN + CSRF meta tag -->
- Add CSRF meta tag in `<head>`: `<meta name="csrf-token" content="{{ csrf_token() }}">`
- Add `<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>` to homeyard layout
- Add `<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>` (~8KB, for drag-and-drop)
- Place BEFORE closing `</body>` tag, AFTER jQuery
- Test: verify `Alpine` and `Sortable` objects available in browser console

### 2. Create Controller Directory
- Create `app/Http/Controllers/Front/Tournament/` directory

### 3. Create TournamentController
```php
namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function __construct() {
        $this->middleware(['auth']);
    }

    public function index(Request $request) {} // List tournaments
    public function create() {}                 // Create form
    public function store(Request $request) {}  // Save new tournament
    public function show(Tournament $tournament) {} // Dashboard view
    public function edit(Tournament $tournament) {} // Edit form
    public function update(Request $request, Tournament $tournament) {} // Update
    public function destroy(Tournament $tournament) {} // Delete
    public function overview() {}               // Overview/stats page
}
```

### 4. Create TournamentAthleteController
```php
// Methods: index, store, update, destroy, updateStatus, approve, reject, listByStatus
```

### 5. Create TournamentDrawController
```php
// Methods: index (draw page), draw (execute), getResults, reset, getManualDraw, saveManualDraw
```

### 6. Create TournamentMatchController
```php
// Methods: index, store, show, updateScore, destroy, createForGroups
```

### 7. Create TournamentRankingController
```php
// Methods: index, getCategoryRankings, getCategoryGroups
```

### 8. Register New Routes
- Add new route group in `routes/web.php` under authenticated middleware
- Use `Route::prefix('tournament-manage')` to avoid conflict with existing `/tournaments`
- Pattern: `/tournament-manage/{tournament}/athletes`, `/tournament-manage/{tournament}/draw`, etc.
- Keep old routes active - no breaking changes

### 9. Verify No Conflicts
- Visit existing tournament pages - should still work
- Visit new route stubs - should return empty response or placeholder

## Todo List
- [ ] Add Alpine.js CDN to homeyard layout
- [ ] Create `Front/Tournament/` controller directory
- [ ] Create TournamentController with stubs
- [ ] Create TournamentAthleteController with stubs
- [ ] Create TournamentDrawController with stubs
- [ ] Create TournamentMatchController with stubs
- [ ] Create TournamentRankingController with stubs
- [ ] Register new route group in web.php
- [ ] Verify existing routes still work

## Success Criteria
- Alpine.js loads without errors on homeyard pages
- All 5 controllers exist with proper namespace
- New routes registered, respond to requests
- Old tournament routes unchanged

## Risk Assessment
- **Alpine + jQuery conflict:** Low risk - Alpine designed to coexist. Use `x-data` only on new views.
- **Route collision:** Mitigated by using `/tournament-manage` prefix (separate from `/tournaments`)

## Security Considerations
- All new routes behind `auth` middleware
- Alpine.js from official CDN (integrity hash recommended)

## Next Steps
- Phase 2: Extract business logic into services
- Phase 3: Build dashboard layout using new controllers
