# Phase 01: Implement Helpers + Switch Logic

## Overview

- **Priority:** High
- **Status:** pending
- **Description:** Thêm 3 method cyclic pairing vào `BracketSeedingHelper`, sửa `KnockoutBracketService::generateBracket` chọn path cyclic hoặc fallback seed-based.

## Context Links

- Research report: `plans/reports/researcher-260422-1420-vn-tournament-bracket-pairing.md`
- Ảnh user confirm: pattern cyclic với wrap-around (H1 vs A2 cho N=8)
- Root cause fix trước: `hasBracket` getter (commit `521b03c`)

## Key Insights

- **Bit-reverse placement** giữ same-group separation tự động
- Công thức pattern ảnh N=8 khớp `slot_order = bit_reverse([0..7]) = [0,4,2,6,1,5,3,7]`
- Không cần sửa `calculateBracketSize`, `getRoundName`, `KnockoutMatchBuilder` — chỉ đổi cách xây `$slots` array

## Requirements

### Functional
- Nếu tất cả N bảng (N ∈ {2,4,8,16}) có đúng 2 athletes `is_advanced=true` → dùng cyclic
- Ngược lại → fallback seed-based (code hiện tại)
- Output `$slots` array truyền xuống `KnockoutMatchBuilder::createMatches` không đổi interface

### Non-functional
- Deterministic: cùng input phải ra cùng output (stable, không phụ thuộc usort)
- Backward compatible: tournaments đã có bracket không ảnh hưởng
- Log warning khi fallback (level `warning`, context có `tournament_id`, `category_id`, `reason`)

## Architecture

### Flow

```
generateBracket(tournament, categoryId, enableThirdPlace)
 ├─ grouped = collectGroupedAdvancers(tid, cid)  [new]
 ├─ if isCyclicEligible(grouped):                 [new]
 │    slots = arrangeByGroupPairing(grouped)      [new]
 │    bracketSize = count(grouped) * 2
 │ else:
 │    Log::warning(...)
 │    seeded = collectSeededAthletes(tid, cid)    [existing, kept]
 │    bracketSize = calculateBracketSize(count(seeded))
 │    slots = arrangeSeedsIntoBracket(seeded, bracketSize)  [existing, kept]
 │ totalRounds = log2(bracketSize)
 │ clearExistingBracket + createRounds + createMatches (unchanged)
```

### Algorithm chi tiết — `arrangeByGroupPairing`

```
Input: grouped = [[g0_r1, g0_r2], [g1_r1, g1_r2], ..., [gN-1_r1, gN-1_r2]]
       N = count(grouped), N ∈ {2,4,8,16}

Step 1 — Build N pairings (cyclic):
  for i in 0..N-1:
    pairs[i] = (grouped[i][0], grouped[(i+1) % N][1])

Step 2 — Bit-reverse placement:
  bits = log2(N)
  slots = array of length 2N, initialized null
  for i in 0..N-1:
    j = bitReverse(i, bits)
    slots[j*2]     = pairs[i][0]
    slots[j*2 + 1] = pairs[i][1]

Return slots
```

### Bit-reverse helper

```php
private function bitReverse(int $num, int $bits): int
{
    $result = 0;
    for ($i = 0; $i < $bits; $i++) {
        $result = ($result << 1) | (($num >> $i) & 1);
    }
    return $result;
}
```

### Verify algorithm bằng tay (N=4)

```
grouped = [[A1, A2], [B1, B2], [C1, C2], [D1, D2]]

Pairings:
  pairs[0] = (A1, B2)
  pairs[1] = (B1, C2)
  pairs[2] = (C1, D2)
  pairs[3] = (D1, A2)

Bit-reverse 2-bit: 0→0, 1→2, 2→1, 3→3
Slot layout [index = bit_reverse(pairs_idx)]:
  slots[0,1] = pairs[0] = A1, B2  → match pos 4
  slots[4,5] = pairs[1] = B1, C2  → match pos 6
  slots[2,3] = pairs[2] = C1, D2  → match pos 5
  slots[6,7] = pairs[3] = D1, A2  → match pos 7

Final matches (QF 4-7):
  pos 4: A1 vs B2
  pos 5: C1 vs D2
  pos 6: B1 vs C2
  pos 7: D1 vs A2  ✓ khớp convention
```

## Related Code Files

### Modify
- `app/Services/Tournament/BracketSeedingHelper.php` (~80 lines thêm)
- `app/Services/Tournament/KnockoutBracketService.php` (lines 22-43)

### Keep unchanged
- `app/Services/Tournament/KnockoutMatchBuilder.php`
- `app/Services/Tournament/KnockoutBracketQuery.php`
- `app/Http/Controllers/Front/Tournament/TournamentBracketController.php`
- Frontend (`bracket-manager.js` đã fix ở commit trước)

## Implementation Steps

1. **`BracketSeedingHelper::collectGroupedAdvancers`**
   - Query `Group` where `tournament_id` & `category_id`, `orderBy('id')`
   - Eager load `standings` where `is_advanced=true`, `orderBy('rank_position')` limit 2
   - Return array `[[r1_athlete_id, r2_athlete_id|null], ...]` theo thứ tự group.id

2. **`BracketSeedingHelper::isCyclicEligible`**
   - Input: `$grouped` array từ step 1
   - Check: `count($grouped) ∈ {2,4,8,16}` AND mọi entry có 2 phần tử không null
   - Return bool

3. **`BracketSeedingHelper::arrangeByGroupPairing`**
   - Build cyclic pairs với wrap-around
   - Dùng private `bitReverse($num, $bits)` để tính slot index
   - Return `array<int, int|null>` length `2N`

4. **`KnockoutBracketService::generateBracket` refactor**
   - Early: gọi `collectGroupedAdvancers`
   - If `isCyclicEligible`: dùng cyclic path
   - Else: `Log::warning('Fallback to seed-based bracket', [...context])` + seed path (giữ nguyên)
   - Giữ check `count < 2` throw InvalidArgumentException cho cả 2 path

5. **Compile check**
   - `php -l app/Services/Tournament/BracketSeedingHelper.php`
   - `php -l app/Services/Tournament/KnockoutBracketService.php`
   - Chạy `php artisan route:list --path=tournament-manage/.*bracket` xem routes còn OK

## Todo

- [ ] Implement `collectGroupedAdvancers`
- [ ] Implement `isCyclicEligible`
- [ ] Implement `arrangeByGroupPairing` + `bitReverse`
- [ ] Refactor `generateBracket` switch logic
- [ ] Compile check (`php -l`)
- [ ] Manual verify qua Chrome DevTools (Phase 02)

## Success Criteria

- Regenerate bracket cat 688 ra đúng pattern:
  - Pos 4: A1 vs B2
  - Pos 5: C1 vs D2
  - Pos 6: B1 vs C2
  - Pos 7: D1 vs A2
- Không có PHP error trong `storage/logs/laravel.log`
- Fallback path (test bằng xoá is_advanced 1 athlete) log warning đúng, không crash

## Risk Assessment

| Risk | Mitigation |
|---|---|
| Break bracket generation cho tournaments hiện có | Giữ seed-based path + Log warning, không xoá code cũ |
| group query ordering không deterministic | Explicit `orderBy('id')` |
| Off-by-one trong bit-reverse | Manual verify N=4 trước khi test N=8 |
| Data fallback path chưa test kỹ | Có thể test bằng unit test hoặc manual toggle is_advanced |

## Security Considerations

- Không đổi authorization (vẫn qua `authorizeOwner` ở controller)
- Không có user input mới → không cần validate thêm
- Không expose data mới qua API

## Next Steps

- Phase 02: Verify E2E qua Chrome DevTools trên giải 246/cat 688
