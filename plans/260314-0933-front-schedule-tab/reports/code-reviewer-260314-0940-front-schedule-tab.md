# Code Review: Front-end Tournament Schedule Tab

## Scope
- **Files**: 3 changed (HomeController.php, tabs-section.blade.php, _front-bracket-match.blade.php)
- **LOC**: ~570 added
- **Focus**: Recent commit `ca3a201`

## Overall Assessment

Solid implementation. Eager loading is mostly correct, CSS scoping is good, responsive breakpoints are complete. Several issues found ranging from a potential crash for unauthenticated users to missing Vietnamese diacritics and a minor N+1 risk.

---

## Critical Issues

### 1. `auth()->id()` called on public route without auth middleware

**File**: `app/Http/Controllers/Front/HomeController.php` line 645

```php
$registered = DB::table('tournament_athletes')
    ->where('tournament_id', $tournament->id)
    ->where('user_id', auth()->id())  // returns null for guests
    ->exists();
```

The route `GET /tournaments/{tournament}` has **no auth middleware**. For guest users, `auth()->id()` returns `null`, so the query becomes `WHERE user_id = NULL` which always returns false. This works by accident but is fragile and semantically incorrect.

**Fix**:
```php
$registered = auth()->check()
    ? DB::table('tournament_athletes')
        ->where('tournament_id', $tournament->id)
        ->where('user_id', auth()->id())
        ->exists()
    : false;
```

**Severity**: High -- not a crash today but `WHERE user_id = NULL` is semantically wrong (should be `IS NULL`), and future DB strict mode could surface issues.

---

## High Priority

### 2. Missing Vietnamese diacritics in all UI strings

**File**: `tabs-section.blade.php` -- 6 occurrences

Per project feedback rule, all Vietnamese UI text must include diacritics. Current text:

| Line | Current | Should Be |
|------|---------|-----------|
| 1071 | `Vong bang` | `Vong bang` |
| 1104 | `Chua co tran dau` | `Chua co tran dau` |
| 1138 | `Tong ket` | `Tong ket` |
| 1164 | `Vong dau loai truc tiep` | `Vong dau loai truc tiep` |
| 1187 | `Lich thi dau chi tiet` | `Lich thi dau chi tiet` |
| 1204 | `Lich thi dau se duoc cap nhat sau khi dong dang ky` | Full diacritics needed |

**Correct Vietnamese**:
- `Vong bang` -> `Vong bang`
- `Chua co tran dau` -> `Chua co tran dau`
- `Tong ket` -> `Tong ket`
- `Vong dau loai truc tiep` -> `Vong dau loai truc tiep`
- `Lich thi dau chi tiet` -> `Lich thi dau chi tiet`
- `Lich thi dau se duoc cap nhat sau khi dong dang ky` -> `Lich thi dau se duoc cap nhat sau khi dong dang ky`

**Note**: I cannot produce the exact diacritics here; the developer who is Vietnamese should supply the correct accented forms. The key point is that ALL Vietnamese strings are currently stripped of diacritics and must be fixed. Compare with the original code that had `Lich thi dau chi tiet` (with accents).

**Severity**: High -- user-facing text quality issue, violates project convention.

### 3. Fallback regex lost Vietnamese diacritics too

**File**: `tabs-section.blade.php` line ~1190

Original regex was `/^Ngay\s+\d+/i` but the committed version uses `Ngay` without accent. The original code had `Ngay` with proper accent. Also the `Sang/Chieu/Toi` matching was removed entirely from the fallback section.

---

## Medium Priority

### 4. Bracket match scores show `0` for unplayed matches

**File**: `_front-bracket-match.blade.php` line 8

```php
<span class="front-bracket-slot-score">{{ $match->athlete1_score ?? 0 }}</span>
```

When a match is `scheduled` (not yet played), showing `0-0` is misleading. Should show `--` or nothing for unplayed matches.

**Fix**:
```php
<span class="front-bracket-slot-score">{{ $match->status === 'completed' || $match->status === 'in_progress' ? ($match->athlete1_score ?? 0) : '-' }}</span>
```

### 5. Group match scores show `0` for unplayed matches (same issue)

**File**: `tabs-section.blade.php` lines ~1096-1100

```php
$s1 = $match->athlete1_score ?? 0;
$s2 = $match->athlete2_score ?? 0;
```

Same problem as above. Both scores default to 0, and the won/lost CSS classes apply even when the match hasn't been played (0 == 0, so no class applied, which is OK, but still semantically wrong).

### 6. Summary standings rank column always shows `--`

**File**: `tabs-section.blade.php` line ~1143

```php
<span class="front-schedule-summary-rank">--</span>
```

The rank is hardcoded to `--`. Could use `$loop->iteration` or the standing's `rank_position`.

### 7. Summary standings sorted only by `matches_won`, not by group rank

The `$allStandings` collection sorts by `matches_won` descending but ignores `rank_position`, `win_rate`, or differential stats. Two athletes from different groups with the same win count have arbitrary order. Consider sorting by `win_rate` descending as secondary, or by `rank_position` ascending.

### 8. Bracket round matches not eager-loading athlete names

**File**: `HomeController.php` line 637

```php
->with(['matches' => fn($q) => $q->orderBy('bracket_position')])
```

The bracket matches reference `$match->athlete1_name` and `$match->athlete2_name` which are stored directly on the match (denormalized columns), so this is **not** an N+1 issue. However, `$match->winner_id` is compared with `$match->athlete1_id` -- both are on the match model, so this is also fine. No N+1 here.

---

## Low Priority

### 9. Connector lines (`::after`) only draw right-side horizontal lines

The bracket uses `::after` to draw horizontal connectors from each match to the right. There are no vertical connectors joining two matches to the next round's single match. This is a cosmetic limitation but acceptable for V1.

### 10. Single-category tournaments still render the content wrapper

When there's only 1 category, the category tabs are hidden but the content div still has a `front-schedule-cat-content` wrapper with `id="schedule-cat-X"`. This works fine, just unnecessary DOM. No action needed.

### 11. CSS class `col-rank`, `col-name`, `col-stat` not scoped

These generic class names could conflict with other CSS. They're used inside `.front-schedule-standings-*` and `.front-schedule-summary-*` contexts, which provides implicit scoping, but if another component uses `.col-rank` at the top level it could clash. Low risk since the page is a single Blade view.

---

## Positive Observations

- Eager loading structure is correct: `categories.groups.matches`, `categories.groups.standings.athlete` -- prevents N+1 for the group stage section
- Bracket rounds query is well-structured: separate query with `groupBy('category_id')` avoids loading bracket data through the nested relationship chain
- CSS scoping with `front-schedule-` and `front-bracket-` prefixes is consistent
- Responsive breakpoints (4->2->1 columns) are practical
- Fallback to text schedule is preserved
- `@forelse` / `@empty` used correctly for empty match lists
- Null-safe operator `$standing->athlete?->athlete_name` used properly

---

## Recommended Actions

1. **[High]** Guard `auth()->id()` with `auth()->check()` -- prevents semantic bug on public route
2. **[High]** Add Vietnamese diacritics to all 6 UI strings -- violates project convention
3. **[Medium]** Show `-` instead of `0` for unplayed match scores in both group and bracket views
4. **[Medium]** Use `$loop->iteration` or actual rank for summary standings rank column
5. **[Medium]** Improve summary standings sort (add `win_rate` or `sets_differential` as tiebreaker)
6. **[Low]** Consider adding vertical bracket connectors in future iteration

## Unresolved Questions

1. Is the `Sang/Chieu/Toi` regex matching intentionally removed from the fallback schedule section, or was it lost during refactoring?
2. Should the bracket connector lines be improved now or deferred to a polish pass?
3. The `group_code` field is used in `$group->group_code` -- is this always populated, or could it be null for some groups?
