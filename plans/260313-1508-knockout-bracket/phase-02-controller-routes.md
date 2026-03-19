# Phase 2: Controller + Routes

## Context Links
- [Phase 1 - Backend Service](phase-01-backend-service.md)
- [TournamentMatchController reference](/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/Tournament/TournamentMatchController.php)
- [Routes - tournament-manage group](/Users/thaopv/Desktop/php/pickleball/routes/web.php) (line 571+)

## Overview
- **Priority**: P1
- **Status**: completed
- **Description**: Create TournamentBracketController + register routes under `tournament-manage` prefix

## Requirements
- R1: Generate bracket endpoint (POST) -- calls KnockoutBracketService::generateBracket
- R2: Get bracket data endpoint (GET) -- returns JSON for Alpine.js rendering
- R3: Swap athletes endpoint (POST) -- calls swapAthletes
- R4: Bracket page view (GET) -- Blade view extending dashboard layout
- R5: All endpoints gated by tournament ownership check

## Architecture

### Route Registration
```php
// routes/web.php inside tournament-manage group (line 571+)
// Add after matches routes (line 605)

// Bracket routes
Route::get('{tournament}/bracket', [TournamentBracketController::class, 'index'])->name('bracket.index');
Route::get('{tournament}/bracket/data', [TournamentBracketController::class, 'getData'])->name('bracket.data');
Route::post('{tournament}/bracket/generate', [TournamentBracketController::class, 'generate'])->name('bracket.generate');
Route::post('{tournament}/bracket/swap', [TournamentBracketController::class, 'swap'])->name('bracket.swap');
```

## Related Code Files

### Files to Create
- `app/Http/Controllers/Front/Tournament/TournamentBracketController.php`

### Files to Modify
- `routes/web.php` -- add bracket routes inside tournament-manage group
- `resources/views/home-yard/tournaments/partials/_sidebar.blade.php` -- add Bracket nav item

## Implementation Steps

### Step 1: TournamentBracketController (~120 LOC)
```php
// app/Http/Controllers/Front/Tournament/TournamentBracketController.php
namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Services\Tournament\KnockoutBracketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentBracketController extends Controller
{
    public function __construct(
        private KnockoutBracketService $bracketService
    ) {
        $this->middleware(['auth']);
    }

    public function index(Tournament $tournament)
    {
        $this->authorizeOwner($tournament);
        $tournament->load('categories');
        return view('home-yard.tournaments.bracket', compact('tournament'));
    }

    public function getData(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $categoryId = $request->validate([
            'category_id' => 'required|integer',
        ])['category_id'];

        $data = $this->bracketService->getBracketData($tournament->id, $categoryId);

        return response()->json(['success' => true, 'bracket' => $data]);
    }

    public function generate(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'category_id'       => 'required|integer',
            'enable_third_place'=> 'boolean',
        ]);

        try {
            $this->bracketService->generateBracket(
                $tournament,
                (int) $validated['category_id'],
                (bool) ($validated['enable_third_place'] ?? $tournament->enable_third_place ?? false)
            );

            return response()->json([
                'success' => true,
                'message' => 'Da tao bracket thanh cong',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            \Log::error('Generate bracket failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Tao bracket that bai'], 500);
        }
    }

    public function swap(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'match_id_1' => 'required|integer',
            'slot_1'     => 'required|in:athlete1,athlete2',
            'match_id_2' => 'required|integer',
            'slot_2'     => 'required|in:athlete1,athlete2',
        ]);

        try {
            $this->bracketService->swapAthletes(
                $validated['match_id_1'], $validated['slot_1'],
                $validated['match_id_2'], $validated['slot_2']
            );

            return response()->json(['success' => true, 'message' => 'Doi vi tri thanh cong']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function authorizeOwner(Tournament $tournament): void
    {
        abort_if($tournament->user_id !== auth()->id(), 403);
    }
}
```

### Step 2: Add routes to web.php
Insert after line 605 (after matches routes) inside the `tournament-manage` group:
```php
// Bracket
Route::get('{tournament}/bracket', [\App\Http\Controllers\Front\Tournament\TournamentBracketController::class, 'index'])->name('bracket.index');
Route::get('{tournament}/bracket/data', [\App\Http\Controllers\Front\Tournament\TournamentBracketController::class, 'getData'])->name('bracket.data');
Route::post('{tournament}/bracket/generate', [\App\Http\Controllers\Front\Tournament\TournamentBracketController::class, 'generate'])->name('bracket.generate');
Route::post('{tournament}/bracket/swap', [\App\Http\Controllers\Front\Tournament\TournamentBracketController::class, 'swap'])->name('bracket.swap');
```

### Step 3: Add sidebar navigation
In `_sidebar.blade.php`, add between Matches and Rankings nav items:
```blade
<a href="{{ route('tournament-manage.bracket.index', $tournament) }}"
   class="td-nav-item {{ str_contains($currentRoute, 'bracket') ? 'active' : '' }}">
    <span class="td-nav-icon">&#127960;</span>
    <span class="td-nav-label">Bracket</span>
</a>
```

## Todo List
- [ ] Create TournamentBracketController with index, getData, generate, swap
- [ ] Register 4 bracket routes in web.php tournament-manage group
- [ ] Add Bracket nav item to _sidebar.blade.php
- [ ] Test route registration: `php artisan route:list --name=bracket`

## Success Criteria
1. `php artisan route:list` shows 4 bracket routes
2. GET bracket.index returns Blade view (empty placeholder for now)
3. POST bracket.generate calls service and returns JSON
4. GET bracket.data returns bracket structure as JSON
5. POST bracket.swap validates and swaps athletes

## Risk Assessment
| Risk | Impact | Mitigation |
|------|--------|------------|
| Route name collision | Low | Using unique `bracket.*` names |
| Tournament slug vs ID routing | Medium | tournament-manage group uses slug (Route::resource pattern) -- verify model binding |
