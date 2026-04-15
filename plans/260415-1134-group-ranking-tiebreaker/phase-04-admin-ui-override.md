# Phase 04 — Admin UI Manual Rank Override (BTC Bốc Thăm)

## Context Links
- Controller: `app/Http/Controllers/Front/Tournament/TournamentRankingController.php`
- View: `resources/views/home-yard/tournaments/tournament-rankings.blade.php`
- Partial: `resources/views/home-yard/tournaments/partials/_rankings.blade.php` (check exists)
- JS: `public/assets/js/tournament-rankings.js`
- Routes: `routes/web.php` lines 620-673 (`tournament-manage` prefix)
- Service: `app/Services/Tournament/TournamentStandingService.php`
- Blocked by: Phase 01 (migration), Phase 02 (sort uses `manual_rank_override`)

## Overview
- **Priority:** P2
- **Status:** pending
- **Description:** Detect standings still tied after tiers 1-4, highlight them in UI, allow BTC to input `manual_rank_override` per athlete. POST endpoint saves overrides, triggers recalculate, audit-logs action.

## Key Insights
- "Tied after tier 4" detection: standings where `(points, games_differential, games_lost)` are all equal AND `manual_rank_override IS NULL`. Backend flags in response as `is_tied: true`. Source of truth: stored DB fields `$standing->points`, `$standing->games_differential`, `$standing->games_lost` (available after Phase 01 — do NOT recompute from match JSON).
- UI pattern: existing rankings table in `tournament-rankings.blade.php` uses Alpine.js + vanilla JS (`tournament-rankings.js`, verified 180 lines). Adding override UI (~50 lines) would push over 200 — **commit to extracting a mixin file** `tournament-rankings-override-mixin.js` following pattern of `tournament-draw-manual-sortable-mixin.js`.
- Controller file `TournamentRankingController` verified 178 lines. Adding `updateRankOverrides` + tied detection helper (~35 lines) → ~213 > 200 → **commit to extracting `buildTiedFlags()` private method + move audit log into a small `RankOverrideLogger` helper** if still over. Or alternatively, extract whole endpoint to new `TournamentRankOverrideController` (preferred for cleaner separation — one controller, one concern).
- Audit log: Laravel built-in `Log::info()` is sufficient (no external package needed — YAGNI). Log channel `daily`, message includes `user_id`, `tournament_id`, `group_id`, overrides array, timestamp.
- Partial override scenario (some tied teams have override, some don't): sort by `manual_rank_override ASC NULLS LAST` — teams without override sort to end of tied group. No warning needed (KISS).
- API endpoint: `POST /tournament-manage/{tournament}/groups/{group}/rank-overrides`
- Only tournament owner (`tournament->user_id === auth()->id()`) can set overrides — reuse existing auth pattern.
- Transaction scope: wrap override update + `recalculateGroupRankings()` call in `DB::transaction()` — already shown in Step 2 pseudo-code.

## Architecture Decision: Controller extraction (committed)
**Create new `app/Http/Controllers/Front/Tournament/TournamentRankOverrideController.php`** (single-action controller, ~80 lines). Keeps `TournamentRankingController` untouched for read endpoints. Single Responsibility Principle.

## Requirements

**Backend:**
- `TournamentRankingController` — new method `updateRankOverrides(Request $request, Tournament $tournament, int $groupId): JsonResponse`
- Validate: `overrides` array of `{athlete_id: int, rank: int|null}`
- Update `manual_rank_override` on matching `GroupStanding` rows
- Call `recalculateGroupRankings($groupId)` after update
- Log via `Log::info('rank_override', [...])`
- Return updated standings JSON

**Frontend:**
- In standings response, include `manual_rank_override` and `is_tied` flag per row
- Tied rows: show orange highlight + number input for override
- Non-tied rows: no input shown
- Submit button per group to POST overrides
- On success: reload standings for that group

## Architecture

```
POST /tournament-manage/{tournament}/groups/{group}/rank-overrides
  Body: { overrides: [{athlete_id: 5, rank: 1}, {athlete_id: 7, rank: 2}] }
  Auth: tournament owner only
  → validate → DB::transaction(update standings + recalculate) → log → return JSON
```

**Tied detection logic (in `buildCategoryRankingsResponse` or separate):**
```php
// After loading standings sorted by rank_position, group by (points, games_differential, games_lost)
// Flag groups with count > 1 as tied
$tieKeys = [];
foreach ($standingsArray as $s) {
    $key = "{$s['points']}|{$s['games_differential']}|{$s['games_lost']}";
    $tieKeys[$key][] = $s['athlete_id'];
}
// $s['is_tied'] = count($tieKeys[$key]) > 1
```

## Related Code Files

**Create:**
- `app/Http/Controllers/Front/Tournament/TournamentRankOverrideController.php` — new single-action controller (~80 lines)
- `public/assets/js/tournament-rankings-override-mixin.js` — new Alpine mixin (~60 lines)

**Modify:**
- `app/Http/Controllers/Front/Tournament/TournamentRankingController.php` — ONLY add `is_tied` + `manual_rank_override` to response map (`buildCategoryRankingsResponse`); add private `buildTiedFlags()` helper (~10 lines net)
- `resources/views/home-yard/tournaments/tournament-rankings.blade.php` — include new mixin script, add override input markup
- `public/assets/js/tournament-rankings.js` — register/use the new mixin
- `routes/web.php` — add POST route

## Implementation Steps

1. **Add route** in `routes/web.php` inside `tournament-manage` group (after line 671):
   ```php
   Route::post('{tournament}/groups/{group}/rank-overrides', [TournamentRankOverrideController::class, 'update'])
       ->name('rankings.rankOverrides');
   ```
   Also add import at top of `routes/web.php`:
   ```php
   use App\Http\Controllers\Front\Tournament\TournamentRankOverrideController;
   ```

2. **Create `TournamentRankOverrideController.php`** (new single-action controller):
   ```php
   <?php

   namespace App\Http\Controllers\Front\Tournament;

   use App\Http\Controllers\Controller;
   use App\Models\Group;
   use App\Models\GroupStanding;
   use App\Models\Tournament;
   use App\Services\Tournament\TournamentStandingService;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Auth;
   use Illuminate\Support\Facades\DB;
   use Illuminate\Support\Facades\Log;

   class TournamentRankOverrideController extends Controller
   {
       public function __construct(
           private TournamentStandingService $standingService,
           private TournamentRankingController $rankingController,
       ) {}

       public function update(Request $request, Tournament $tournament, int $group): JsonResponse
       {
           abort_unless($tournament->user_id === Auth::id(), 403);

           $groupModel = Group::where('id', $group)
               ->whereHas('category', fn($q) => $q->where('tournament_id', $tournament->id))
               ->firstOrFail();

           $data = $request->validate([
               'overrides'              => ['required', 'array', 'min:1'],
               'overrides.*.athlete_id' => ['required', 'integer'],
               'overrides.*.rank'       => ['nullable', 'integer', 'min:1'],
           ]);

           DB::transaction(function () use ($data, $group) {
               foreach ($data['overrides'] as $override) {
                   GroupStanding::where('group_id', $group)
                       ->where('athlete_id', $override['athlete_id'])
                       ->update(['manual_rank_override' => $override['rank']]);
               }
               $this->standingService->recalculateGroupRankings($group);
           });

           Log::info('rank_override', [
               'user_id'       => Auth::id(),
               'tournament_id' => $tournament->id,
               'group_id'      => $group,
               'overrides'     => $data['overrides'],
           ]);

           // Delegate back to ranking controller to rebuild response for the category
           return $this->rankingController->getCategoryRankings($tournament, $groupModel->category_id);
       }
   }
   ```
   Note: `getCategoryRankings(Tournament, int $categoryId): JsonResponse` may need a small wrapper on `TournamentRankingController` if not already public — add one if missing. Keep it <10 lines.

3. **Extend standings response in `TournamentRankingController::buildCategoryRankingsResponse()`** — add to each standing row map:
   ```php
   'manual_rank_override' => $standing->manual_rank_override,
   'games_differential'   => $standing->games_differential,
   'games_lost'           => $standing->games_lost,
   'is_tied'              => false, // populated in step 4
   ```

4. **Add `buildTiedFlags()` private helper** in `TournamentRankingController` (after `buildCategoryRankingsResponse`):
   ```php
   private function buildTiedFlags(array $standingsArray): array
   {
       $tieGroups = [];
       foreach ($standingsArray as $s) {
           $key = $s['points'] . '|' . $s['games_differential'] . '|' . $s['games_lost'];
           $tieGroups[$key][] = $s['athlete_id'];
       }
       foreach ($standingsArray as &$s) {
           $key = $s['points'] . '|' . $s['games_differential'] . '|' . $s['games_lost'];
           $s['is_tied'] = count($tieGroups[$key] ?? []) > 1;
       }
       unset($s);
       return $standingsArray;
   }
   ```
   Call it in `buildCategoryRankingsResponse()` just before returning the response:
   ```php
   $standingsArray = $this->buildTiedFlags($standingsArray);
   ```

5. **Create `public/assets/js/tournament-rankings-override-mixin.js`** (new Alpine mixin, ~60 lines):
   - Expose `rankOverrideMixin()` returning `{ saveOverrides(groupId), overrideInputs: {} }`
   - `saveOverrides(groupId)`: collect inputs via `document.querySelectorAll('[data-group-id="'+groupId+'"] .rank-override-input')`, build `overrides: [{athlete_id, rank}]` array, POST to `/tournament-manage/{tournament}/groups/{group}/rank-overrides` with CSRF token from `<meta name="csrf-token">`, on success call existing `loadRankings()` / `refreshGroup(groupId)` function
   - Include at top of `tournament-rankings.blade.php` via `<script src="{{ asset('assets/js/tournament-rankings-override-mixin.js') }}"></script>`

6. **Update `tournament-rankings.blade.php` markup** — inside the group standings table row template:
   ```blade
   @if($row['is_tied'])
       <td class="tied-row" style="background:#fff7ed;border:1px solid #f97316">
           {{ $row['rank_position'] }}
           <input type="number" min="1" class="rank-override-input"
                  data-group-id="{{ $group['id'] }}" data-athlete-id="{{ $row['athlete_id'] }}"
                  value="{{ $row['manual_rank_override'] }}" style="width:50px;margin-left:4px;">
       </td>
   @else
       <td>{{ $row['rank_position'] }}</td>
   @endif
   ```
   Add a "Lưu thứ tự bốc thăm" button per group container, only visible when `$group['standings']` contains any `is_tied === true`, wired to `saveOverrides({{ $group['id'] }})`.

7. **Update `public/assets/js/tournament-rankings.js`** — import/use mixin:
   ```js
   // At top:
   // (mixin is loaded as a global function via separate script tag)
   // Inside main Alpine component:
   ...rankOverrideMixin(),
   ```
   No other changes to existing file — net addition ~3 lines. Keeps `tournament-rankings.js` at ~183 lines (still < 200).

8. **CSRF handling**: mixin reads `<meta name="csrf-token">` value and sends via `X-CSRF-TOKEN` header (standard Laravel pattern — check `tournament-matches-api.js` for reference implementation).

## Todo List
- [ ] Add route `POST .../groups/{group}/rank-overrides` + import
- [ ] Create `TournamentRankOverrideController` (new file)
- [ ] Add public `getCategoryRankings()` wrapper on `TournamentRankingController` if missing
- [ ] Extend `buildCategoryRankingsResponse` row map with `manual_rank_override`, `games_differential`, `games_lost`, `is_tied`
- [ ] Add `buildTiedFlags()` private helper + call before return
- [ ] Create `tournament-rankings-override-mixin.js` (new file)
- [ ] Update `tournament-rankings.blade.php` markup (tied highlight + input + save button)
- [ ] Wire mixin into `tournament-rankings.js`
- [ ] Verify all touched files < 200 lines
- [ ] Manual test: create 2 teams with identical points/games_differential/games_lost, confirm UI highlights and override saves correctly
- [ ] Manual test: tournament owner auth check returns 403 for non-owner
- [ ] Manual test: audit log entry appears in `storage/logs/laravel-*.log`

## Success Criteria
- Tied rows visually distinct (orange highlight) in admin rankings view
- BTC can input numbers, save, and rankings immediately update
- Non-tied rows show no input
- Audit log entry appears in `storage/logs/laravel-*.log` with correct fields
- Clearing override (set to null) works — team falls back to tier 4 natural sort

## Risk Assessment
- **Controller file size**: new endpoint in dedicated `TournamentRankOverrideController` (~80 lines). `TournamentRankingController` gains ~15 lines (`buildTiedFlags` + response fields) → ~193 lines, under 200.
- **JS file size**: mixin extraction committed upfront. `tournament-rankings.js` gains ~3 lines for mixin usage → ~183 lines. New mixin file ~60 lines.
- **Partial override**: documented behavior (NULLS LAST) — no edge case crash
- **Transaction atomicity**: `DB::transaction()` wraps override update + `recalculateGroupRankings()` — all-or-nothing
- **Circular dependency**: `TournamentRankOverrideController` injects `TournamentRankingController` — Laravel resolves fine since neither has construction-time side effects, but consider extracting response builder to a service if this becomes awkward (deferred)

## Security Considerations
- Auth check: `abort_unless($tournament->user_id === auth()->id(), 403)` — only tournament owner
- Input validation: `rank` is nullable int with `min:1`
- No direct SQL — Eloquent update scoped to `group_id + athlete_id`
- Audit log does not contain sensitive data (no PII beyond user_id)
