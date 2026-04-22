# Plan: Cyclic Cross-Group Pairing cho Bracket Generation

## Mục tiêu

Thay thuật toán seed-based hiện tại (unstable khi `seed_number=NULL`) bằng **cyclic cross-group pairing** để:
- Guarantee same-group separation (2 VĐV cùng bảng không gặp trước Final)
- Deterministic với data hiện tại (đa số VĐV VN chưa có seed)
- Match convention user đưa qua ảnh:
  ```
  pair[i] = (group[i].R1, group[(i+1) mod N].R2)
  slot_order = bit_reverse(0..N-1)
  ```

## Điều kiện áp dụng

**Happy path (cyclic):**
- `N ∈ {2, 4, 8, 16}` (số bảng là power of 2)
- Mọi bảng advance đúng 2 VĐV (is_advanced=true)

**Fallback (seed-based cũ):**
- Điều kiện happy path không thoả
- Log warning để admin biết

## Phases

| # | Phase | Status |
|---|---|---|
| 01 | Implement helpers + switch logic | pending |
| 02 | Verify E2E trên tournament 246/cat 688 qua Chrome DevTools | pending |

## Files liên quan

| File | Mục đích |
|---|---|
| `app/Services/Tournament/BracketSeedingHelper.php` | Thêm 3 method mới: `collectGroupedAdvancers`, `isCyclicEligible`, `arrangeByGroupPairing` |
| `app/Services/Tournament/KnockoutBracketService.php` | Sửa `generateBracket` (line 22-43) để chọn cyclic vs fallback |

## Out of scope

- UI setting toggle cyclic/seed-based (YAGNI)
- Handle N không power-of-2 (fallback seed-based đã cover)
- Handle advance count ≠ 2 per group (fallback seed-based đã cover)
- Database migration (không cần, chỉ đổi logic service)

## Verification

1. Reset bracket cat 688 (xóa 3 rounds + matches hiện có)
2. Click "Tạo Bracket" lại
3. Expected 4 QF matches theo đúng pattern cyclic:
   - Match 4: A1 vs B2
   - Match 5: C1 vs D2
   - Match 6: B1 vs C2
   - Match 7: D1 vs A2
4. Verify qua Chrome DevTools + MySQL query
