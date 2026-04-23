---
plan: fix-bracket-paired-group-pairing
date: 260423-1549
status: draft
mode: fast
---

# Plan: Bracket paired-group pairing (A↔B, C↔D)

## Mục tiêu

User yêu cầu bắt buộc bracket knockout phải ghép theo **paired-group pattern**, không dùng cyclic nữa:

- N=4 bảng: A1-B2, B1-A2, C1-D2, D1-C2
- Tổng quát: `pair[i] = (group[i].R1, group[i XOR 1].R2)` — tức các bảng ghép theo từng cặp (0↔1, 2↔3, 4↔5, …).

## Scope

Thay công thức trong `CyclicPairingHelper::arrange()` từ cyclic `(i+1) mod N` → paired `i XOR 1`. Không đổi logic bit-reverse slot placement (vẫn cần để đảm bảo same-group separation đến Final).

## Đổi tên class/ngữ cảnh?

**Không đổi tên class** `CyclicPairingHelper` — tránh rename cascade lan ra `KnockoutBracketService`, tests, references. Chỉ cập nhật PHPDoc mô tả thuật toán. (YAGNI)

## Phân tích correctness

### N=4 (bits=2)

Pairs mới:
| i | pair (R1, R2) | slotPairIdx = bitReverse(i) | slots |
|---|---|---|---|
| 0 | (A1, B2) | bitReverse(0,2)=0 | 0,1 |
| 1 | (B1, A2) | bitReverse(1,2)=2 | 4,5 |
| 2 | (C1, D2) | bitReverse(2,2)=1 | 2,3 |
| 3 | (D1, C2) | bitReverse(3,2)=3 | 6,7 |

Bracket order (top→bottom):
```
QF1: A1 vs B2   (slots 0,1) ─┐
                              ├─ SF1 ─┐
QF2: C1 vs D2   (slots 2,3) ─┘        │
                                      ├─ Final
QF3: B1 vs A2   (slots 4,5) ─┐        │
                              ├─ SF2 ─┘
QF4: D1 vs C2   (slots 6,7) ─┘
```

Same-group separation:
- A1 (slot 0, half-top) vs A2 (slot 5, half-bottom) → chỉ gặp ở **Final** ✓
- B2 (slot 1, top) vs B1 (slot 4, bottom) → **Final** ✓
- C1 (slot 2, top) vs C2 (slot 7, bottom) → **Final** ✓
- D2 (slot 3, top) vs D1 (slot 6, bottom) → **Final** ✓

### N=2 (bits=1)

- pair[0] = (A1, B2) → slot 0,1
- pair[1] = (B1, A2) → slot 2,3

Final:
```
SF1: A1 vs B2
SF2: B1 vs A2
Final: winner vs winner
```
Same-group separation: A1 vs A2 chỉ gặp ở Final (nếu A1 thắng B2 và A2 thắng B1). ✓

### N=8 (bits=3)

Pairs:
| i | pair | bitReverse | slots |
|---|---|---|---|
| 0 | A1-B2 | 0 | 0,1 |
| 1 | B1-A2 | 4 | 8,9 |
| 2 | C1-D2 | 2 | 4,5 |
| 3 | D1-C2 | 6 | 12,13 |
| 4 | E1-F2 | 1 | 2,3 |
| 5 | F1-E2 | 5 | 10,11 |
| 6 | G1-H2 | 3 | 6,7 |
| 7 | H1-G2 | 7 | 14,15 |

Top half (slots 0-7): A1,B2, E1,F2, C1,D2, G1,H2
Bottom half (slots 8-15): B1,A2, F1,E2, D1,C2, H1,G2

Same-group separation: mỗi cặp Rk1/Rk2 nằm khác half → gặp Final ✓

### N=16

Cùng pattern. Verification: pair[i=2k] ở `bitReverse(2k)` đảm bảo R1 nằm ở slot pair chẵn trong top half; pair[i=2k+1] là swap đối tác → nằm ở `bitReverse(2k+1) = bitReverse(2k) + N/2` → bottom half. Luôn split đúng.

## Files thay đổi

**Modify:**
- `app/Services/Tournament/CyclicPairingHelper.php` (~5 dòng: formula + PHPDoc)

**Create:**
- `tests/Unit/Services/Tournament/CyclicPairingHelperTest.php` — unit tests cho N=2,4,8 + isEligible edge cases

**Không đổi:**
- `app/Services/Tournament/KnockoutBracketService.php` — call site không đổi
- Tests hiện có — không có test nào assert pairing cụ thể

## Implementation

### 1. `CyclicPairingHelper::arrange()`

```php
// BEFORE
$rank2 = $grouped[($i + 1) % $n][1] ?? null;

// AFTER
$rank2 = $grouped[$i ^ 1][1] ?? null;
```

### 2. PHPDoc update

Sửa class-level comment và arrange() docblock để phản ánh paired-group pattern:
- Thay "cyclic cross-group pairing" → "paired cross-group pairing (A↔B, C↔D, ...)"
- Thay ví dụ N=4: `slot[0,1]=A1-B2, slot[2,3]=C1-D2, slot[4,5]=B1-A2, slot[6,7]=D1-C2`
- Thay mô tả N=8

### 3. Unit tests

File: `tests/Unit/Services/Tournament/CyclicPairingHelperTest.php`

Test cases:
- `arrange_with_2_groups_returns_paired_slots` — input `[[a1,a2],[b1,b2]]` → output `[a1,b2,b1,a2]`
- `arrange_with_4_groups_returns_paired_slots` — input 4 bảng → expect slot order A1,B2,C1,D2,B1,A2,D1,C2
- `arrange_with_8_groups_returns_correct_pairing` — assert separation invariant (R1 và R2 cùng bảng ở khác half)
- `isEligible_returns_false_when_group_count_not_power_of_2`
- `isEligible_returns_false_when_any_group_has_null_advancer`

## Risks

- **Legacy data**: Tournaments đã generate bracket bằng cyclic trước đây → bracket đã persist, không bị ảnh hưởng (chỉ áp dụng khi regenerate).
- **E2E verification**: Repo không có automated E2E cho bracket. Plan trước (`260422-1412-cyclic-bracket-pairing/phase-02`) verify thủ công qua Chrome DevTools. Nếu cần verify visual, user chạy lại tournament test sau deploy.

## Success Criteria

1. `php artisan test --filter=CyclicPairingHelperTest` pass toàn bộ.
2. `php artisan test` (full suite) không regression.
3. Manual: generate bracket 4 bảng, visual bracket hiển thị đúng thứ tự QF: A1-B2, C1-D2, B1-A2, D1-C2 (top-bottom).

## Todo

- [ ] Sửa formula trong `CyclicPairingHelper::arrange()` line 98
- [ ] Cập nhật PHPDoc class + arrange()
- [ ] Tạo `tests/Unit/Services/Tournament/CyclicPairingHelperTest.php`
- [ ] Chạy `php artisan test --filter=CyclicPairingHelperTest`
- [ ] Chạy full suite `php artisan test`
- [ ] User verify E2E trên tournament thật

## Unresolved Questions

1. User có muốn pattern này cho **cả N=2, N=8, N=16** hay chỉ N=4? Plan assume: tổng quát hóa bằng `i XOR 1` (adjacent pair swap) — phù hợp mọi N power-of-2.
2. Có cần giữ fallback về cyclic khi nào không? Plan assume: **không** — thay thẳng paired là default mới, giữ nguyên fallback seed-based như commit `a42951e` khi điều kiện `isEligible` không thoả.
