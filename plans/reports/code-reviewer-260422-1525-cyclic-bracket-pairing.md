# Code Review: Cyclic Cross-Group Bracket Pairing

**Date:** 2026-04-22 | **Reviewer:** code-reviewer  
**Scope:** CyclicPairingHelper (mới), KnockoutBracketService lines 1–80, BracketSeedingHelper (diff)

---

## Overall Assessment

Logic cyclic pairing đúng với spec (verified E2E). Codebase sạch, comment đủ. Có 2 issues Medium-severity cần fix trước production, 1 High về floating-point safety, còn lại Low/Nit.

---

## HIGH

### H1 — `log()` floating-point: `$bits` có thể sai với N=16
**File:** `CyclicPairingHelper.php:91`

```php
$bits = (int) \log($n, 2);
```

`log(16, 2)` trả `3.9999999...` trên một số máy PHP 8.x (phụ thuộc libm). `(int)` truncate → `$bits = 3` → `bitReverse` chỉ xét 3 bits → slot index tối đa 7, nhưng N=16 cần index 0..15 → `$slotPairIdx * 2 + 1` vượt `$bracketSize = 32`, ghi đè slot sai.

**Fix:**
```php
// CyclicPairingHelper.php:91
$bits = (int) round(log($n, 2));
```
Hoặc dùng bitwise (zero FP risk):
```php
$bits = (int) log($n, 2) + (((1 << (int) log($n, 2)) < $n) ? 1 : 0);
```
→ Khuyến nghị `round()` — đơn giản, đủ.

---

## MEDIUM

### M1 — `empty()` reject athlete_id = 0 hợp lệ (semantic bug)
**File:** `CyclicPairingHelper.php:62`

```php
if (empty($pair[0]) || empty($pair[1])) {
```

`empty(0)` trả `true`. Nếu DB có `athlete_id = 0` (dù hiếm) sẽ bị reject sai. Nhất quán với null-check còn lại trong codebase.

**Fix:**
```php
if ($pair[0] === null || $pair[1] === null) {
    return false;
}
```

### M2 — Double DB query khi fallback
**File:** `KnockoutBracketService.php:50–63`

`buildBracketSlots` gọi `collectGroupedAdvancers` (query Group+standings) rồi khi fallback gọi `collectSeededAthletes` (lại query Group+standings). Cùng dataset, 2x roundtrip.

Không critical vì fallback hiếm, nhưng với giải lớn (16 bảng × nhiều VĐV) gây chậm.

**Fix đơn giản:** Pass `$grouped` xuống `collectSeededAthletes` hoặc derive seeded list từ `$grouped` flat. Hoặc chấp nhận hiện tại và comment lý do.

---

## LOW

### L1 — `Group::standings()` có global `orderBy('rank_position')` — eager-load thêm `.orderBy` redundant
**File:** `CyclicPairingHelper.php:33`, `BracketSeedingHelper.php:21`

`Group::standings()` relation đã có `->orderBy('rank_position')` (Group.php:78). Cả hai eager-load đều thêm `.orderBy('rank_position')` lần nữa → SQL thêm `ORDER BY rank_position, rank_position`. Không sai về kết quả nhưng redundant.

**Fix:** Bỏ `->orderBy('rank_position')` trong closure eager-load ở cả hai file.

### L2 — `collectGroupedAdvancers` khi group có > 2 advancers
**File:** `CyclicPairingHelper.php:39–41`

Lấy `$advanced[0]` và `$advanced[1]` — đúng, chỉ 2 đầu. Nhưng nếu `advancing_count = 3` (3 VĐV advance/bảng), `isEligible` sẽ pass (vì 2 slot đều non-null) → pair[i] chỉ dùng rank1+rank2, rank3 bị bỏ. Đây là behavior chủ động nhưng không được document.

**Fix:** Thêm comment:
```php
// Chỉ lấy rank 1 và rank 2; rank 3+ bỏ qua (cyclic chỉ hỗ trợ top-2 advance).
```

### L3 — `Log::warning` không log reason "tại sao fallback"
**File:** `KnockoutBracketService.php:57–61`

Hiện log `group_count` nhưng không biết reason: N không phải power-of-2, hay có group thiếu advancer?

**Fix:**
```php
Log::warning('Bracket fallback to seed-based pairing', [
    'tournament_id' => $tournamentId,
    'category_id'   => $categoryId,
    'group_count'   => count($grouped),
    'reason'        => !in_array(count($grouped), [2,4,8,16], true)
        ? 'group_count_not_power_of_2'
        : 'incomplete_advancers',
]);
```

### L4 — `clearExistingBracket` chạy trong transaction nhưng query trước transaction
**File:** `KnockoutBracketService.php:29, 32–33`

`buildBracketSlots` (line 29) chạy trước `DB::transaction`. Nếu `buildBracketSlots` thành công nhưng transaction fail → bracket cũ đã bị xóa (line 33 trong transaction thực ra vẫn trong transaction). Nhìn lại: `clearExistingBracket` nằm bên trong closure (line 33) → OK, cả xóa lẫn ghi đều trong transaction.

Không phải bug. Ghi nhận để clarify: transaction bao phủ toàn bộ write.

---

## NIT

### N1 — `arrange()` tên không nói rõ ngữ cảnh
`CyclicPairingHelper::arrange()` — nên `arrangeCyclic()` hoặc `buildCyclicSlots()` để phân biệt với `arrangeSeedsIntoBracket` bên BracketSeedingHelper. Khi đọc call-site không cần mở file mới biết đây là cyclic.

### N2 — `isEligible` không nói "eligible for what"
Trong context public API của helper, nên `isEligibleForCyclic()` hoặc thêm `@return bool True nếu đủ điều kiện dùng cyclic cross-group pairing.`

---

## Positive Observations

- `bitReverse` implementation đúng về thuật toán (LSB→MSB shift), verified N=2,4,8 manually.
- Eager-load `with(['standings'])` tránh N+1, đúng.
- Comment giải thích global-limit pitfall (line 25–27) rất tốt — giúp dev sau không tái phạm.
- `isEligible` check `in_array(..., true)` dùng strict comparison.
- BracketSeedingHelper diff clean: chỉ xóa import thừa `TournamentAthlete`, logic không đổi, backward-compat giữ nguyên.
- DI order constructor: Laravel container resolve theo type-hint, thứ tự không ảnh hưởng. OK.
- Fallback silent (Log::warning, không throw) đúng với spec.
- Tournament không có group stage → `collectGroupedAdvancers` trả `[]` → `isEligible` return false (N=0 không in {2,4,8,16}) → fallback. Safe.
- Partial advancement (1 group 1 advancer) → `isEligible` false (`pair[1]` null) → fallback. Safe.

---

## Recommended Actions (Priority Order)

1. **[H1]** `CyclicPairingHelper.php:91` — đổi `(int) \log($n, 2)` → `(int) round(\log($n, 2))`.
2. **[M1]** `CyclicPairingHelper.php:62` — đổi `empty()` → `=== null` check.
3. **[L3]** Thêm `reason` vào Log::warning context.
4. **[L2]** Thêm comment về top-2 only behavior trong `collectGroupedAdvancers`.
5. **[M2]** Chấp nhận double-query hoặc refactor nếu tournament scale lớn.

---

## Unresolved Questions

1. `advancing_count` trên Group model có được validate là luôn = 2 khi cyclic được dùng không? Nếu admin set advancing_count=3 nhưng chỉ 2 VĐV thực tế advance → behavior là gì?
2. N=1 (1 bảng): `isEligible` trả false (1 not in {2,4,8,16}). `collectSeededAthletes` sẽ trả tất cả advancers của bảng đó → bracketSize tính từ count. Đây có phải intended behavior?
3. Nếu cyclic eligible nhưng `arrange()` trả slot array với null (do race condition giữa advance flag và query) — `KnockoutMatchBuilder::createMatches` có handle null slots an toàn không? (out of scope review này nhưng liên quan).
