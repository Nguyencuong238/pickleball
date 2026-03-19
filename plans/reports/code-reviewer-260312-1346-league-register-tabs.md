# Code Review: League Register Page 3-Tab Layout

## Scope
- Files: 5 (1 controller, 4 blade views)
- LOC: ~250 new/changed
- Focus: recent changes for tab navigation feature

## Overall Assessment

**PASS with minor issues.** Clean implementation. Tabs extracted correctly, no admin elements leaked into public views, Vietnamese diacritics used consistently, XSS protection via `{{ }}` escaping throughout. Tab JS is correct and concise.

---

## High Priority

### 1. Unnecessary eager load: `teams`
**File:** `app/Http/Controllers/Front/LeagueRegistrationController.php` line 82

`'teams'` is eager loaded but never referenced in any of the 3 tab partials. This loads all league teams + their columns for every page view unnecessarily.

**Fix:** Remove `'teams'` from the `load()` call.

```php
$league->load([
    'rounds.matches.homeTeam',
    'rounds.matches.awayTeam',
    'rounds.matches.games.homePlayer1.user',
    'rounds.matches.games.homePlayer2.user',
    'rounds.matches.games.awayPlayer1.user',
    'rounds.matches.games.awayPlayer2.user',
    'standings.team',
    // 'teams', -- not used in any public tab
]);
```

### 2. Heavy eager loading on every page load (performance)
**File:** `app/Http/Controllers/Front/LeagueRegistrationController.php`

All rounds/matches/games/standings are loaded even when the user only views the registration tab (default). For leagues with many rounds/matches, this is wasteful.

**Recommendation (optional, lower priority):** Consider lazy-loading schedule/standings tabs via AJAX, or at minimum defer loading until needed. For now acceptable if league data volume is small, but worth noting for scale.

### 3. `toggleMlpPublicDetails` assumes element exists
**File:** `resources/views/front/leagues/register.blade.php` line 194-203

If `matchId` doesn't correspond to a rendered element (e.g., DOM manipulation or stale hash), `el` and `icon` would be null, causing JS error.

**Fix:** Add null guard:
```js
function toggleMlpPublicDetails(matchId) {
    var el = document.getElementById('mlp-pub-details-' + matchId);
    var icon = document.getElementById('mlp-pub-icon-' + matchId);
    if (!el || !icon) return;
    // ...rest
}
```

---

## Medium Priority

### 4. `optional()` chains in sub-game display are verbose
**File:** `_tab-schedule.blade.php` lines 91, 92, 97, 98

`optional(optional($game->homePlayer1)->user)->name ?? '?'` is repeated 4 times. Consider a small Blade helper or inline `@php` function for readability. Not blocking.

### 5. Hash validation could use `includes()` (minor JS compat)
**File:** `register.blade.php` line 165

`['info', 'schedule', 'standings'].indexOf(hash) !== -1` works but `includes()` is more readable. However, `indexOf` has wider browser support, so this is fine as-is.

### 6. `reg-container` max-width (700px) may be narrow for standings table
**File:** `_tab-standings.blade.php`

The standings table has `min-width: 600px` and is wrapped in `overflow-x: auto`, which is correct. But inside a 700px container with 40px padding, horizontal scroll triggers on many screens. Consider widening the container or reducing table `min-width` to ~550px.

---

## Low Priority

### 7. Inline styles are extensive
All views use heavy inline styles. Consistent with existing codebase patterns, so no action needed, but noted for future refactoring.

### 8. Consider `@once` directive for MLP toggle function
The `toggleMlpPublicDetails` function is defined in the parent template (not in partial), so no duplication risk. Fine as-is.

---

## Security Checklist

| Check | Status |
|-------|--------|
| No admin-only elements in public view | PASS - no edit/delete/modal/admin actions found |
| XSS protection (all output escaped) | PASS - all uses `{{ }}`, no `{!! !!}` |
| CSRF on form | PASS - `@csrf` present |
| No sensitive data exposed | PASS - only public match/standings data shown |
| No raw SQL | PASS |
| File upload validation | Existing (unchanged) |

## Vietnamese Diacritics Check
All Vietnamese text uses proper diacritics: "Thong tin & Dang ky" -> "Thong tin & Dang ky" -- PASS. Verified: "Lich thi dau", "Bang xep hang", "Chua co lich thi dau", "Chua co bang xep hang", status labels all correct.

## Tab Switching JS Correctness
- Hash-based persistence: PASS
- Default tab (info): PASS
- `history.replaceState` (no back-button pollution): PASS
- Whitelist validation on hash: PASS
- IIFE scoping: PASS

## Positive Observations
- Clean extraction of tab content into partials follows SRP
- Empty states with icons for schedule/standings -- good UX
- `overflow-x: auto` on standings table -- mobile-friendly
- `type="button"` on tab buttons prevents accidental form submission
- Double-submit prevention on registration form retained
- MLP sub-game collapsible is read-only with no edit affordances

## Recommended Actions (prioritized)
1. Remove unused `'teams'` eager load
2. Add null guard in `toggleMlpPublicDetails`
3. (Optional) Consider narrower standings table or wider container

## Unresolved Questions
- Is `$league->competition_format === 'mlp'` the only format that has sub-games, or could other formats gain this in the future? If so, the toggle logic may need generalization.
- What is the expected max number of rounds/matches per league? If large (50+ matches), AJAX tab loading should be prioritized.
