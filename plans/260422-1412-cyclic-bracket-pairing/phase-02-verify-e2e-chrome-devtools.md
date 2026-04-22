# Phase 02: Verify E2E qua Chrome DevTools

## Overview

- **Priority:** High
- **Status:** pending (blocked by Phase 01)
- **Description:** Verify thuật toán cyclic cross-group pairing chạy đúng trên tournament 246 (ONEPICKLEBALL CHAMPIONSHIP 2026), category 688 (Đôi Nữ Trình 4.3).

## Preconditions

- Phase 01 đã xong (code deployed local, Laravel autoload clear nếu cần)
- Chrome DevTools đã logged in (từ phiên trước còn session)
- Tournament 246 cat 688 có 4 bảng × 2 advance = 8 VĐV → N=4 eligible cho cyclic

## Test steps

### 1. Reset bracket hiện tại (database)

```sql
DELETE FROM matches WHERE tournament_id=246 AND category_id=688 AND round_id IN (31,32,33);
DELETE FROM rounds WHERE id IN (31,32,33);
```

Hoặc trong UI: navigate tới bracket tab → click "Chỉnh sửa bracket" → xóa → regenerate. Nhưng hiện không có nút reset trong UI → dùng SQL trực tiếp nhanh hơn.

### 2. Navigate UI

- URL: `http://pickleball.test/tournament-manage/onepickleball-championship-2026-san-choi-dang-cap-ket-noi-dam-me-1/bracket`
- Click tab "Đôi Nữ Trình 4.3"
- Expect: Nút "Tạo Bracket" xuất hiện (vì không có round nào)

### 3. Click "Tạo Bracket"

- Expect UI hiển thị 3 vòng: Tứ kết (4 trận), Bán kết (2 trận), Chung kết (1 trận)
- Lấy snapshot xem athlete pairings

### 4. Verify qua DB

```sql
SELECT m.id, m.match_number, m.bracket_position,
       a1.athlete_name a1_name, g1.group_name a1_group, gs1.rank_position a1_rank,
       a2.athlete_name a2_name, g2.group_name a2_group, gs2.rank_position a2_rank
FROM matches m
LEFT JOIN tournament_athletes a1 ON a1.id = m.athlete1_id
LEFT JOIN tournament_athletes a2 ON a2.id = m.athlete2_id
LEFT JOIN group_standings gs1 ON gs1.athlete_id = a1.id
LEFT JOIN `groups` g1 ON g1.id = gs1.group_id
LEFT JOIN group_standings gs2 ON gs2.athlete_id = a2.id
LEFT JOIN `groups` g2 ON g2.id = gs2.group_id
WHERE m.tournament_id=246 AND m.category_id=688 AND m.round_id IS NOT NULL
ORDER BY m.bracket_position;
```

### 5. Expected pattern (N=4 cyclic)

| bracket_position | athlete1 | athlete2 |
|---|---|---|
| 4 (QF) | Bảng A rank 1 | Bảng B rank 2 |
| 5 (QF) | Bảng C rank 1 | Bảng D rank 2 |
| 6 (QF) | Bảng B rank 1 | Bảng C rank 2 |
| 7 (QF) | Bảng D rank 1 | Bảng A rank 2 |
| 2 (SF) | NULL | NULL |
| 3 (SF) | NULL | NULL |
| 1 (Final) | NULL | NULL |

### 6. Success criteria

- Tất cả 4 QF matches athlete1/athlete2 khớp bảng expected table
- Same-group separation: Bảng A R1 (pos 4) và Bảng A R2 (pos 7) ở 2 halves khác nhau (top pos 4-5, bottom pos 6-7) ✓
- Tương tự cho B/C/D
- SF + Final athletes null (chờ QF winners)

### 7. Edge case test (optional, nếu có time)

Test fallback path: thay đổi DB sao cho 1 bảng chỉ có 1 is_advanced → click regenerate → expect:
- `isCyclicEligible` return false
- Log `storage/logs/laravel.log` có dòng `Fallback to seed-based bracket`
- Bracket vẫn tạo được (seed-based), nhưng có thể mất property cyclic

## Tools cần dùng

- `mcp__chrome-devtools__*` (navigate, click, take_snapshot, evaluate_script)
- `Bash` cho MySQL queries
- `Read` cho log file

## Todo

- [ ] Reset cat 688 bracket (SQL DELETE)
- [ ] Navigate + click "Tạo Bracket"
- [ ] Snapshot UI verify matches
- [ ] Query DB verify pairings match expected table
- [ ] Check laravel.log không có error
- [ ] (optional) Test fallback path

## Success Criteria

- 100% khớp expected pattern table
- Zero errors trong log
- Screenshot lưu vào `plans/reports/fix-260422-1XXX-cyclic-bracket-verify.png`

## Unresolved

- Có nên thêm reset button vào UI không? (hiện phải SQL hoặc edit từng trận) — out of scope phase này
