# Code Review: League Management Blade Views

**Date:** 2026-02-25
**Scope:** 10 Blade views under `resources/views/home-yard/leagues/`
**LOC:** ~700 lines total
**Focus:** Syntax, routes, XSS, CSRF, JS, consistency

---

## Overall Assessment

Views are well-structured, follow tournament view conventions, and use correct Blade syntax throughout. No missing `@csrf`/`@method` directives. No `{!! !!}` unescaped output in HTML context. Three issues need fixing before production: two XSS/injection vectors in JS, one missing eager-load causing N+1 query.

---

## Critical Issues

### 1. XSS / JS Injection - Team Name in onclick String Literal
**Files:** `_tab-teams.blade.php` line 43, 92 | `_tab-matches.blade.php` line 59

Team names are interpolated raw into JS string literals inside HTML event attributes:

```php
// Line 43 _tab-teams:
onclick="return confirm('Xoa doi {{ $team->name }}?')"

// Line 92 _tab-teams:
onclick="openPlayerModal({{ $team->id }}, '{{ $team->name }}')"

// Line 59 _tab-matches:
onclick="openScoreModal({{ $match->id }}, '{{ $match->homeTeam->name ?? 'TBD' }}', ...)"
```

If a team name contains a single quote (e.g. `O'Brien`), it **breaks the JS** and is exploitable as stored XSS.

**Fix:** Use `@json()` which escapes correctly for JS string context:

```php
// _tab-teams line 43:
onclick="return confirm('Xoa doi {{ e($team->name) }}?')"

// _tab-teams line 92 - pass via data attribute instead:
<button onclick="openPlayerModal({{ $team->id }}, @json($team->name))" ...>

// _tab-matches line 59:
onclick="openScoreModal({{ $match->id }}, @json($match->homeTeam->name ?? 'TBD'), @json($match->awayTeam->name ?? 'TBD'), ...)"
```

### 2. XSS - innerHTML with API Response Data
**File:** `_tab-teams.blade.php` lines 242-243

User search results are rendered via `innerHTML` using unescaped server data:
```js
div.innerHTML = '<strong>' + user.name + '</strong> <span ...>' + (user.email || '') + '</span>';
```

If the API returns a user with `name` containing `<script>` or `<img onerror=...>`, it executes.

**Fix:** Use `textContent` or escape:
```js
var nameEl = document.createElement('strong');
nameEl.textContent = user.name;
var emailEl = document.createElement('span');
emailEl.textContent = user.email || '';
emailEl.style.cssText = 'color: #6b7280; font-size: 0.85rem;';
div.appendChild(nameEl);
div.appendChild(document.createTextNode(' '));
div.appendChild(emailEl);
```

---

## High Priority

### 3. N+1 Query - Missing `teams.captain` Eager Load
**File:** `_tab-teams.blade.php` line 26-27 | Controller: `HomeYardLeagueController::show()`

View accesses `$team->captain->name` but `show()` only loads `'teams.players.user'`:
```php
$league->load(['teams.players.user', 'rounds.matches.homeTeam', ...]);
// Missing: 'teams.captain'
```

This triggers one extra DB query per team (N+1). Fix in controller:
```php
$league->load([
    'teams.players.user',
    'teams.captain',  // ADD THIS
    'rounds.matches.homeTeam',
    'rounds.matches.awayTeam',
    'standings.team',
]);
```

### 4. Hardcoded URLs Instead of Named Routes (Fragile)
**File:** `_tab-teams.blade.php` line 197 | `_tab-matches.blade.php` line 134

```js
// _tab-teams line 197:
document.getElementById('playerForm').action = '/homeyard/leagues/{{ $league->slug }}/teams/' + teamId + '/players';

// _tab-matches line 134:
fetch('/homeyard/leagues/{{ $league->slug }}/matches/' + matchId + '/score', {
```

These hardcode the URL prefix. If the route prefix changes, they silently break. The slug portion is dynamic so a pure `route()` call isn't possible, but the static prefix should use a JS variable set from `route()`:

```blade
<script>
var leagueBaseUrl = '{{ url("homeyard/leagues/" . $league->slug) }}';
// Then use: leagueBaseUrl + '/matches/' + matchId + '/score'
// Or:       leagueBaseUrl + '/teams/' + teamId + '/players'
</script>
```

---

## Medium Priority

### 5. `switchTab` Uses Implicit `event` Global (Deprecated)
**File:** `show.blade.php` line 94-99

```js
function switchTab(tabName) {
    // ...
    event.currentTarget.classList.add('active'); // 'event' is implicit global
```

`event` as an implicit global is not available in all environments (strict mode, Firefox). The function does not receive `event` as parameter.

**Fix:**
```html
<button class="league-tab active" onclick="switchTab('overview', this)">

<script>
function switchTab(tabName, btn) {
    document.querySelectorAll('.league-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.league-tab-content').forEach(c => c.classList.remove('active'));
    document.getElementById('tab-' + tabName).classList.add('active');
    btn.classList.add('active');
    window.location.hash = tabName;
}
</script>
```

### 6. `@switch` Blocks Missing `@default` Case
**Files:** `index.blade.php`, `edit.blade.php`, `show.blade.php`, `_tab-overview.blade.php`, `_tab-matches.blade.php`

All `@switch($league->status)` / `@switch($match->status)` blocks have no `@default`. If an unknown status is stored, nothing renders (silent failure).

**Fix:** Add `@default` to each:
```php
@default
    <span style="background-color: #f3f4f6; color: #4b5563; ...">{{ $league->status }}</span>
```

### 7. `_tab-overview.blade.php` - PHP Method Call in `onclick` Attribute
**File:** `_tab-overview.blade.php` line 65

```php
onclick="return confirm('Tao lich thi dau round-robin cho {{ $league->teams->where('status', 'active')->count() }} doi?')"
```

The `$league->teams->where(...)` collection query executes inside an HTML attribute rendering. The teams collection is already loaded, so no extra query, but calling `->where()` on a collection vs. `->active()` scope mismatch: `teams` is the full collection (all statuses), so this is correct behavior. However, using `$league->teams->where('status','active')->count()` inside onclick is fragile. Move to `@php` block:

```php
@php $activeTeamCount = $league->teams->where('status', 'active')->count(); @endphp
onclick="return confirm('Tao lich thi dau round-robin cho {{ $activeTeamCount }} doi?')"
```

---

## Low Priority

### 8. `search-users` Route - Data Format Assumption
**File:** `_tab-teams.blade.php` line 235

```js
var users = data.data || data;
```

`OcrController::searchUsers()` returns a flat JSON array (not `{data: [...]}` pagination). The `data.data || data` fallback works correctly today, but is fragile if the endpoint is changed to paginated. Document the expected format or use a dedicated league search route.

### 9. `@media` Style Block Inside `@section('content')` (Minor)
**Files:** `index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`

Each page puts `<style>` with `@media` directly inside `@section('content')`. This is consistent with existing tournament views (project convention), so acceptable.

Note: In `index.blade.php` there is a `@media` directive inside `<style>` block. Blade processes `@` directives, so `@media` would normally need escaping to `@@media`. This is already working in tournament views implying the layout handles it, but worth confirming it renders correctly (not stripped by Blade parser). The existing tournament views use the same pattern so it's confirmed working.

### 10. Vietnamese Text Inconsistency
Some labels mix diacritic-free Vietnamese (`Hoat dong`, `Tong So League`) with diacritics in confirm dialogs (`Hanh dong nay khong the hoan tac`). Project convention from tournament views omits diacritics. Consistent - already done mostly, confirm strings can be standardized.

---

## Positive Observations

- All forms use `@csrf` and `@method('PUT'/'DELETE'/'PATCH')` correctly.
- No `{!! !!}` in HTML rendering - all data output uses `{{ }}` (Blade auto-escapes).
- Route model binding with slug via `getRouteKeyName()` is correct.
- All route names (`homeyard.leagues.*`) match definitions in `routes/web.php`.
- Modal backdrop-click-to-close is implemented correctly.
- `@json($config)` in `_form.blade.php` script block is safe for JS serialization.
- `LeagueService::DEFAULT_CONFIG` as `public const` makes form defaults clean.
- Pagination in `index.blade.php` uses `$leagues->links()` correctly.
- Authorization (`abort_if`) is in controllers, not views - correct pattern.

---

## Recommended Actions (Priority Order)

1. **[Critical]** Fix team name JS injection in `_tab-teams.blade.php` lines 43, 92 - use `e()` or `@json()`.
2. **[Critical]** Fix team name JS injection in `_tab-matches.blade.php` line 59 - use `@json()`.
3. **[Critical]** Fix `innerHTML` XSS in `_tab-teams.blade.php` line 242 - use DOM methods.
4. **[High]** Add `'teams.captain'` to eager load in `HomeYardLeagueController::show()`.
5. **[High]** Replace hardcoded URL prefixes with JS variable from `url()` helper.
6. **[Medium]** Fix `switchTab()` to pass `this` instead of relying on implicit `event`.
7. **[Medium]** Add `@default` to all `@switch` blocks.
8. **[Medium]** Move `$league->teams->where(...)` in onclick to `@php` block.

---

## Metrics

- Type Coverage: N/A (Blade/PHP views)
- Test Coverage: N/A (no view tests)
- XSS Vectors Found: 3 (items 1, 2)
- N+1 Queries Found: 1 (item 3)
- Missing Route Definitions: 0 (all routes exist)
- Unclosed HTML Tags: 0
- Missing @csrf/@method: 0

---

## Unresolved Questions

- Is `search-users` (`OcrController::searchUsers`) the intended endpoint for player search in league context, or should a dedicated league player search route be created? The current endpoint searches all users, which is probably correct.
- Should the `matches.blade.php` standalone page be removed since it duplicates `_tab-matches` inside `show.blade.php`? Currently both exist and work.
