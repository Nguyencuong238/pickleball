# Code Review: Phase 4 - Views & UI (Club Activities ReClub-Style)

**Reviewer:** code-reviewer | **Date:** 2026-02-27 | **Scope:** 11 Blade files + 3 controllers

---

## Scope

- **Files reviewed:** 7 partials, 4 views, 3 controllers
- **Total LOC:** ~1,675 (Blade) + ~415 (controllers)
- **Focus:** Security, correctness, variable availability, AJAX patterns, Vietnamese text, edge cases

## Overall Assessment

Solid implementation. Clean Blade structure, proper CSRF handling, good use of partials. Several issues found ranging from a critical XSS vulnerability to file size violations and operator precedence bugs.

---

## Critical Issues

### 1. XSS via innerHTML in `_competition-panel.blade.php`

**File:** `/Users/thaopv/Desktop/php/pickleball/resources/views/clubs/activities/partials/_competition-panel.blade.php`

Team names and match data from API are injected via `innerHTML` without escaping (lines 338, 365, 393). If a team name contains `<script>alert('xss')</script>`, it executes.

**Affected lines:**
```javascript
html += '<span class="match-teams">' + homeName + ' vs ' + awayName + '</span>';  // L338
teamHtml += '<span class="team-name">' + t.name + '</span>';  // L365
html += '<td><strong>' + teamName + '</strong></td>';  // L396
```

**Fix:** Create an escape helper and use it:
```javascript
function escHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
// Then use: escHtml(homeName) + ' vs ' + escHtml(awayName)
```

### 2. XSS via innerHTML in `_rsvp-panel.blade.php` -- Lower risk

Lines 158-159 inject `$club->slug` and `$activity->id` via Blade `{{ }}` into JS vars. These are escaped by Blade, so **not directly vulnerable**, but the pattern is fragile. If these values ever contain special characters in a JS context, it could break. Current code is acceptable since slugs and IDs are sanitized upstream, but worth noting.

---

## High Priority

### 3. Operator precedence bug in `_rsvp-panel.blade.php` (line 10)

**File:** `/Users/thaopv/Desktop/php/pickleball/resources/views/clubs/activities/partials/_rsvp-panel.blade.php`

```php
@if($activity->waitlisted_participants_count ?? $activity->waitlistedParticipants->count() > 0)
```

The `??` null coalescing has lower precedence than `>`, so `$activity->waitlistedParticipants->count() > 0` evaluates first (returns bool), then `??` picks the count attribute or the bool. This means the badge shows whenever `waitlisted_participants_count` is truthy OR `count() > 0` -- but the value displayed on line 12 also has the same `??` pattern and would display the raw count correctly. The `@if` condition itself works by accident but is logically wrong.

**Fix:**
```php
@if(($activity->waitlisted_participants_count ?? $activity->waitlistedParticipants->count()) > 0)
```

### 4. File size violations -- 200-line limit

Per project rules, files should be under 200 lines.

| File | Lines | Over by |
|------|-------|---------|
| `_competition-panel.blade.php` | 411 | 211 |
| `show.blade.php` | 315 | 115 |
| `index.blade.php` | 268 | 68 |
| `edit.blade.php` | 263 | 63 |
| `create.blade.php` | 235 | 35 |

**Recommendation:**
- `_competition-panel.blade.php`: Split JS into a separate `_competition-panel-scripts.blade.php` partial (~200 lines of JS). Split CSS into `_competition-panel-styles.blade.php`.
- `show.blade.php`, `index.blade.php`: Extract inline `<style>` blocks into shared CSS partial or a Blade component.
- `create.blade.php` / `edit.blade.php`: Extract shared form styles into `_form-styles.blade.php` partial (nearly identical CSS -- DRY violation).

### 5. DRY violation: Duplicated CSS in create.blade.php and edit.blade.php

Both files contain ~120 lines of identical CSS (`.activity-form-container`, `.form-card`, `.form-group`, `.btn-submit`, etc.). Extract into a shared partial:

```blade
{{-- resources/views/clubs/activities/partials/_form-styles.blade.php --}}
<style>
    .activity-form-container { ... }
    ...
</style>
```

### 6. Missing `confirmed_participants_count` in index view

**File:** `/Users/thaopv/Desktop/php/pickleball/resources/views/clubs/activities/index.blade.php` (line 229)

The controller loads `withCount('confirmedParticipants')` which creates `confirmed_participants_count`. The view accesses it:
```php
{{ $activity->confirmed_participants_count ?? 0 }}
```
This works correctly. However, `show.blade.php` line 236 does the same, and the controller uses `loadCount` which also works. **No issue here** -- just confirming consistency.

### 7. RSVP panel: `$isMember` not passed to guests

**File:** `/Users/thaopv/Desktop/php/pickleball/resources/views/clubs/activities/partials/_rsvp-panel.blade.php` (line 27)

The `@auth` directive guards access, but `$isMember` is referenced inside. If a user is authenticated but `$isMember` is not passed (e.g., from a future view inclusion), it would throw an undefined variable error. Currently the `show` controller always passes it -- this is fine but fragile. Add a null fallback:

```php
@if(($isMember ?? false) && $activity->status === 'upcoming')
```

---

## Medium Priority

### 8. `loadTeamsFromDOM()` function in competition panel is dead code

**File:** `/Users/thaopv/Desktop/php/pickleball/resources/views/clubs/activities/partials/_competition-panel.blade.php` (lines 232-236)

`loadTeamsFromDOM()` fetches a URL with `?_teams=1` but the controller's `show` method does not handle this query parameter. The function is called from `loadTeams()` but `loadTeams()` itself is never called. Both are dead code.

**Fix:** Remove `loadTeams()` and `loadTeamsFromDOM()` functions (lines 221-236). Teams are already loaded from the matches endpoint (lines 354-370).

### 9. AJAX error handling inconsistency

RSVP panel uses `alert()` for errors. Competition panel also uses `alert()`. Consider a consistent toast/notification pattern instead of browser alerts for better UX. Low-priority but worth noting for polish.

### 10. `edit.blade.php` recurring/competition fields hidden by default but shown via separate script

**File:** `/Users/thaopv/Desktop/php/pickleball/resources/views/clubs/activities/edit.blade.php` (lines 249-262)

The recurring/competition fields partials have `style="display: none;"` baked in. The edit view then uses a separate DOMContentLoaded script to show them. This causes a flash of hidden content. Better approach: pass a `$show` parameter to the partial or remove `display: none` when including conditionally:

```blade
@if($activity->type === 'recurring')
    @include('clubs.activities.partials._recurring-fields', ['activity' => $activity])
    <script>document.getElementById('recurring-fields').style.display = 'block';</script>
@endif
```

This is what's already done -- the approach works but the flash is unavoidable with this pattern. Could be improved by removing the `style="display: none"` in partials and controlling visibility only from the parent view's JS.

### 11. No AJAX loading state / double-click prevention

RSVP join/cancel buttons and competition buttons have no disabled state during AJAX calls. Users can double-click and trigger duplicate requests.

**Fix example:**
```javascript
function rsvpAction(action) {
    var btn = event.target;
    btn.disabled = true;
    // ... fetch ... .finally(function() { btn.disabled = false; });
}
```

### 12. `competition_config` old() handling may fail for nested arrays

**File:** `/Users/thaopv/Desktop/php/pickleball/resources/views/clubs/activities/partials/_competition-fields.blade.php` (line 3)

```php
$config = old('competition_config', $activity->competition_config ?? []);
```

When form validation fails, `old('competition_config')` returns an array from request, which works. But if `$activity->competition_config` is a JSON string (shouldn't be given the cast), it could break. The model has `'competition_config' => 'array'` cast so this is fine for the model. The `old()` value from request will also be an array due to the `name="competition_config[format]"` syntax. **No issue** -- just confirmed.

---

## Low Priority

### 13. Vietnamese text consistency

All Vietnamese text uses proper diacritics -- good. Minor inconsistency:
- `create.blade.php` line 191: "Đã hủy" (status option)
- `show.blade.php` line 201: "Đã hủy" (status display)
- Consistent throughout. No issues found.

### 14. Inline styles

Several files use inline `style` attributes (e.g., `style="color: red;"` for required asterisks, `style="margin-top: 12px;"` in competition panel). Consider CSS classes for consistency but not critical.

### 15. `meta[name="csrf-token"]` dependency

Both `_rsvp-panel.blade.php` and `_competition-panel.blade.php` depend on `<meta name="csrf-token">` existing in the layout. If the layout doesn't include this, AJAX calls will fail with null reference error. Verify `layouts.front` includes it.

---

## Edge Cases Found

1. **Null `$activity->end_time` in show view (line 215):** Handled with `@if($activity->end_time)` -- correct.
2. **Null `$activity->parent` in show view (line 249):** Guarded by `$activity->type === 'recurring' && $activity->parent` -- correct.
3. **Empty participant collections:** `_participant-list.blade.php` checks `$confirmed->count() > 0` before rendering -- correct.
4. **Null user on participant:** `$p->user->name ?? 'Unknown'` and `$p->user->avatar ?? null` handle deleted users -- correct.
5. **`activity_date` format in edit view (line 189):** Uses `->format('Y-m-d\TH:i')` for `datetime-local` input -- correct.
6. **`max_participants` null in RSVP panel (line 8):** Displays `'--'` when null -- good.

---

## Positive Observations

1. Proper CSRF protection on all forms and AJAX calls
2. Authorization checks use `$isManagement` consistently (not `Auth::id() === $club->user_id`)
3. Type immutability enforced in edit view (read-only badge, `unset($validated['type'])` in controller)
4. Good empty states for activities list and competition data
5. Responsive design with media queries on all views
6. Proper use of `e()` via `{{ }}` Blade syntax for output escaping in server-rendered content
7. Clean separation of concerns with partials
8. Pagination support in index view

---

## Recommended Actions (Priority Order)

1. **[CRITICAL]** Fix XSS in `_competition-panel.blade.php` -- escape all `innerHTML` injections from API data
2. **[HIGH]** Fix operator precedence bug in `_rsvp-panel.blade.php` line 10
3. **[HIGH]** Extract duplicated CSS from create/edit into shared partial to fix DRY violation and reduce file sizes
4. **[HIGH]** Split `_competition-panel.blade.php` (411 lines) into panel + scripts + styles partials
5. **[MEDIUM]** Remove dead code (`loadTeams`, `loadTeamsFromDOM`) from competition panel
6. **[MEDIUM]** Add double-click prevention on AJAX buttons
7. **[LOW]** Verify `layouts.front` has `<meta name="csrf-token">`
8. **[LOW]** Replace inline styles with CSS classes

---

## Metrics

| Metric | Value |
|--------|-------|
| Files reviewed | 14 |
| Critical issues | 1 (XSS) |
| High issues | 4 |
| Medium issues | 4 |
| Low issues | 3 |
| Files over 200 lines | 5 of 11 |
| Vietnamese diacritics | Correct throughout |
| CSRF protection | Present on all forms/AJAX |
| Authorization | Consistent `$isManagement` usage |

---

## Unresolved Questions

1. Does `layouts.front` include `<meta name="csrf-token">`? AJAX calls depend on it.
2. Is `Club::isManagement()` inclusive of admin/moderator roles, or only club owner? Affects who sees management UI.
3. Should the `view` policy on ClubPolicy allow unauthenticated users? Currently `view()` requires a User parameter but `index` route may be hit by guests -- could cause 403.
