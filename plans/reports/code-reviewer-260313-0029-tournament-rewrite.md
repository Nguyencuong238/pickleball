# Code Review: Tournament Management Rewrite

**Date:** 2026-03-13
**Reviewer:** code-reviewer
**Score: 6.5/10 — Conditionally Pass** (must fix Critical + Major before merge)

---

## Scope

- **Files reviewed:** 9 Controllers/Traits, 7 Services, 12+ Views/Partials, 6 JS files, 1 route group
- **LOC:** ~3,200 (new code)
- **Focus:** Full rewrite — security, Vietnamese diacritics, code quality, correctness

---

## Critical Issues (Must Fix)

### C1. Missing authorization on TournamentRankingController public endpoints

`getCategoryRankings()` and `getCategoryGroups()` have NO ownership check. Any authenticated user can view any tournament's rankings by guessing IDs.

**File:** `app/Http/Controllers/Front/Tournament/TournamentRankingController.php:45-66`

```php
// MISSING: abort_unless($tournament->user_id === auth()->id(), 403);
public function getCategoryRankings(Tournament $tournament, int $categoryId): JsonResponse
```

**Fix:** Add `abort_unless($tournament->user_id === auth()->id(), 403)` to both methods, or extract `authorizeOwner()` like the other controllers.

### C2. TournamentMatchController::store — race condition on match_number

```php
$matchNumber = MatchModel::where('tournament_id', $tournament->id)
    ->where('category_id', $validated['category_id'])
    ->max('match_number') + 1;
```

No DB transaction or lock. Concurrent requests can generate duplicate match numbers.

**Fix:** Wrap in `DB::transaction()` with `lockForUpdate()`, or use DB-level unique constraint.

### C3. Error message exposes internal exception to user

**File:** `TournamentDrawController.php:89`, `TournamentMatchController.php:192`

```php
return response()->json([..., 'message' => 'Loi khi boc tham: ' . $e->getMessage()], 500);
```

Exception messages can leak DB schema, file paths, etc.

**Fix:** Log the full exception, return generic message to user. Keep `$e->getMessage()` in `Log::error()` only.

### C4. TournamentDrawController::reset — delete + reset not in transaction

```php
if ($matchCount > 0) {
    MatchModel::where(...)->delete();  // Outside transaction
}
$this->drawService->resetDraw($tournament, $categoryId);  // Separate operation
```

If `resetDraw()` fails, matches are already deleted with no way to recover.

**Fix:** Wrap both operations in `DB::transaction()`.

---

## Major Issues (Should Fix)

### M1. `autoTab()` function referenced but never defined

**File:** `resources/views/home-yard/tournaments/partials/_matches-row.blade.php:44`

```html
@input="autoTab($event, match.id, idx, 's1')"
```

This function doesn't exist in any JS file. Will cause runtime error when user types in score input.

**Fix:** Either implement `autoTab()` in `tournament-matches.js` or remove the `@input` binding.

### M2. LIKE search without escaping special characters

**File:** `TournamentController.php:25`

```php
$query->where('name', 'like', '%' . $request->search . '%');
```

User input containing `%` or `_` is injected directly into LIKE pattern (not SQL injection, but bypasses intended search behavior).

**Fix:** Use `str_replace(['%', '_'], ['\%', '\_'], $request->search)` or Laravel's `addslashes` approach.

### M3. `DrawAssignmentHelper` uses raw DB queries inconsistently

`drawPairsByRandom()` and `drawPairsBySeeding()` use `DB::table('tournament_athletes')->where(...)->update(...)` while `drawAthletesByRandom()` uses Eloquent `$athlete->update(...)`. The raw DB approach bypasses model events, timestamps, and any observers.

**Fix:** Use consistent approach — prefer Eloquent `TournamentAthlete::where()->update()`.

### M4. `saveManualDraw()` doesn't verify athlete belongs to tournament

**File:** `TournamentDrawService.php:190`

```php
TournamentAthlete::where('id', $athleteData['athlete_id'])
    ->update(['group_id' => (int)$groupId, ...]);
```

No check that `athlete_id` belongs to the current tournament. Attacker could assign athletes from other tournaments to groups.

**Fix:** Add `->where('tournament_id', $tournament->id)` to the update query.

### M5. `recalculateGroupRankings()` sort chain is incorrect

**File:** `TournamentStandingService.php:135-140`

```php
$standings = GroupStanding::where('group_id', $groupId)
    ->get()
    ->sortByDesc('points')
    ->sortByDesc('matches_won')  // This re-sorts, breaking previous sort
    ->sortByDesc(fn($s) => ...)  // This re-sorts again
```

Laravel Collection `sortByDesc` is NOT stable chained sorting. Each call re-sorts the entire collection. The final sort by game differential is the only one that takes effect.

**Fix:** Use a single sort with a composite comparator:

```php
$standings->sort(function ($a, $b) {
    return ($b->points <=> $a->points)
        ?: ($b->matches_won <=> $a->matches_won)
        ?: (($b->games_won - $b->games_lost) <=> ($a->games_won - $a->games_lost));
})->values();
```

Same issue in `RankingQueryHelper.php:28-32`.

### M6. `MatchCreationHelper` swallows exceptions silently

**File:** `MatchCreationHelper.php:44-46`

```php
} catch (\Exception $e) {
    Log::error('Error creating matches for groups: ' . $e->getMessage(), ['exception' => $e]);
}
```

Caller `TournamentMatchService::createMatchesForGroups()` and ultimately the controller think success occurred. Partial match creation with no error reported.

**Fix:** Re-throw the exception so the controller can return an error response.

---

## Minor Issues (Nice to Fix)

### m1. `create.blade.php` has duplicate `@submit.prevent`

```html
<div x-data="tournamentForm([])" @submit.prevent="...">  <!-- line 29 -->
<form method="POST" ... @submit.prevent="...">             <!-- line 30-31 -->
```

The `x-data` div intercepts submit before the form does. Only the form should have `@submit.prevent`.

### m2. `match_number` type mismatch

`TournamentMatchController::store()` sets `match_number` as integer (`$matchCount + 1`), but `MatchCreationHelper` sets it as string (`'M' . $matchCount`). Inconsistent — could cause sorting issues.

### m3. `TournamentRankingController::index()` missing ownership check

The `index()` method doesn't call `authorizeOwner()`. Any authenticated user can access `/tournament-manage/{tournament}/rankings`.

### m4. `MatchScoreTrait::processScoreUpdate` standings update outside transaction

Lines 80-87 update standings outside the DB transaction. If standing update fails, match score is saved but rankings are stale. The `try/catch` silently logs and continues.

### m5. Polling interval hardcoded

Both `tournament-matches.js` and `tournament-rankings.js` hardcode 15-second polling. For production with many concurrent users, this could be excessive.

---

## Vietnamese Diacritics Check — FAIL

Extensive Vietnamese text without diacritics (khong dau) found across the rewrite. This violates project requirements.

### Controllers (PHP — user-facing messages)

| File | Line | Text Without Diacritics |
|------|------|------------------------|
| `TournamentMatchController.php` | 83-84 | `'Tao tran dau that bai'` |
| `TournamentMatchController.php` | 150 | `'Da xoa tran dau'` |
| `TournamentMatchController.php` | 153 | `'Xoa tran dau that bai'` |
| `TournamentMatchController.php` | 175 | `'Khong co bang nao. Hay thuc hien boc tham truoc.'` |
| `TournamentMatchController.php` | 187 | `'Da tao ' . $count . ' tran dau'` |
| `MatchScoreTrait.php` | 93 | `'Cap nhat ti so thanh cong'` |
| `MatchScoreTrait.php` | 106 | `'Cap nhat ti so that bai'` |
| `MatchListFormatterTrait.php` | 39 | `'Khong ro'` |
| `MatchListFormatterTrait.php` | 45 | `'Bang chung'` |

### Views (Blade — visible to users)

| File | Line | Text Without Diacritics |
|------|------|------------------------|
| `athletes.blade.php` | 10 | `Van dong vien` |
| `_matches.blade.php` | 18 | `Tran dau` |
| `_matches.blade.php` | 20 | `Quan ly lich thi dau va cap nhat ti so` |
| `_matches.blade.php` | 34 | `Giai dau chua co noi dung thi dau.` |
| `_matches.blade.php` | 55-63 | `hoan thanh`, `dang dau`, `cho` |
| `_matches.blade.php` | 77 | `Tat ca`, `Chua dau`, `Dang dau`, `Hoan thanh` |
| `_matches.blade.php` | 97 | `Khong co tran dau nao.` |
| `_matches-row.blade.php` | 32 | `Nhap ti so` |
| `_matches-row.blade.php` | 64 | `Dang luu...`, `Luu ti so` |
| `_matches-row.blade.php` | 76 | `Xac nhan xoa?` |
| `_matches-row.blade.php` | 82,87 | `Xoa`, `Khong` |
| `_matches-row.blade.php` | 95 | `Xoa tran dau` |
| `_matches-empty-generate.blade.php` | 3-9 | `Chua co tran dau nao`, `Dang tao...`, `Tao tran dau` |
| `_rankings.blade.php` | 19-20 | `Xep hang`, `Bang xep hang theo noi dung va nhom thi dau` |
| `_rankings.blade.php` | 37 | `Giai dau chua co noi dung thi dau.` |
| `_rankings.blade.php` | 52 | `Dang tai du lieu...` |
| `_rankings.blade.php` | 59 | `Chua co du lieu xep hang cho noi dung nay.` |
| `_rankings.blade.php` | 76-83 | `VDV di tiep vong sau`, `Ranh gioi di tiep`, abbreviation legends |
| `_rankings.blade.php` | 104 | `Diem` |

### JavaScript (user-facing toasts/labels)

| File | Line | Text Without Diacritics |
|------|------|------------------------|
| `tournament-matches.js` | 54 | `'Khong the tai danh sach tran dau'` |
| `tournament-matches.js` | 100 | `'Vui long nhap it nhat 1 set'` |
| `tournament-matches.js` | 108,112 | `'Cap nhat ti so thanh cong'`, `'Cap nhat that bai'` |
| `tournament-matches.js` | 115,141,163 | `'Loi ket noi may chu'` |
| `tournament-matches.js` | 135 | `'Da xoa tran dau'` |
| `tournament-matches.js` | 148 | `'Chon noi dung thi dau truoc'` |
| `tournament-matches.js` | 149 | `'Tao tran dau vong bang cho noi dung nay?'` |
| `tournament-matches.js` | 183 | `'Chua dau'`, `'Dang dau'`, `'Hoan thanh'`, `'Huy'` |
| `tournament-rankings.js` | 146-147 | `'Cap nhat Xs truoc'`, `'Cap nhat Xph truoc'` |

**Total: ~50+ instances of missing diacritics.** All user-visible text must use proper Vietnamese (co dau).

---

## Security Audit

| Check | Result |
|-------|--------|
| CSRF in fetch headers | PASS - All JS fetch calls include `X-CSRF-TOKEN` |
| XSS via unescaped Blade | PASS - Uses `{{ }}` (escaped) throughout; `{!! !!}` only for `json_encode()` in overview.blade.php (safe) |
| Authorization on CRUD | PARTIAL FAIL - Missing on ranking endpoints (C1, m3) |
| SQL Injection | PASS - Uses Eloquent/Query Builder, no raw SQL |
| Mass assignment | PASS - Uses explicit field arrays |
| File upload validation | PASS - `'banner' => 'nullable|image|max:5120'` |
| Input validation | PASS - All store/update endpoints validate input |
| DB transactions for multi-model | PARTIAL FAIL - Missing in draw reset (C4) and match creation (C2) |
| Error info leakage | FAIL - Exception messages exposed to client (C3) |

---

## Positive Observations

1. **Clean controller structure** — Good use of traits to separate concerns (MatchScoreTrait, DrawAuthorizationTrait, MatchListFormatterTrait)
2. **Service layer extraction** — Business logic properly in services, not controllers
3. **Snake draft algorithm** — Correct implementation for fair seeded distribution
4. **Alpine.js patterns** — Config injection from Blade is clean; state management is well-organized
5. **File sizes** — All files under 200 lines (compliant)
6. **CSRF handling** — Consistent across all JS fetch calls
7. **Doubles support** — Pair handling logic covers edge cases well (orphaned partners, bidirectional linking)
8. **Bulk operations** — Bulk approve properly scopes to tournament

---

## Recommended Actions (Priority Order)

1. **Fix C1-C4** (Critical) — Authorization, race condition, error leakage, transaction safety
2. **Fix M1** (Major) — Remove or implement `autoTab()` — runtime error
3. **Fix M4** (Major) — Add tournament scope to manual draw save
4. **Fix M5** (Major) — Fix sort chain in rankings recalculation
5. **Fix M6** (Major) — Re-throw exception in MatchCreationHelper
6. **Fix ALL Vietnamese diacritics** — ~50 instances across PHP, Blade, JS
7. **Fix m2** — Standardize `match_number` type (string or int, not both)

---

## Unresolved Questions

1. Is `TournamentRankingController` intentionally public (no ownership check)? If rankings should be viewable by all, it should still be documented and the `auth` middleware may be removable.
2. `match_number` — should it be integer (auto-increment style) or string (`M1`, `M2`)? Currently inconsistent between manual creation and group generation.
3. `overview.blade.php` uses `{!! json_encode() !!}` — while safe, should these be behind `@json()` Blade directive for consistency?
