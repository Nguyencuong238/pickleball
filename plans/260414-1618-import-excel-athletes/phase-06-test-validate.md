# Phase 06: Manual Test + Edge Cases

## Context Links
- Brainstorm: `../reports/brainstorm-260414-1618-import-excel-athletes.md` (Section 8 Success Criteria)

## Overview
- **Priority:** High
- **Status:** complete
- **Description:** End-to-end manual test full flow import, verify all edge cases, fix bugs phát sinh.

## Requirements
- Chạy dev server, login owner tournament, test UI flow
- Verify tất cả success criteria trong brainstorm section 8
- Test đủ hard errors và soft warnings cases

## Test Cases

### TC1 — Happy path single
File 5 rows đơn nam, category hợp lệ, không duplicate.
**Expected:** 5 VĐV created, pending status, toast success

### TC2 — Happy path doubles
2 rows cross-reference partner cùng category đôi.
**Expected:** 2 VĐV created + partner_id bidirectional

### TC3 — Mixed single + doubles
3 rows single + 4 rows doubles (2 cặp).
**Expected:** 7 created, 2 partner links correct

### TC4 — Invalid email
1 row email sai format.
**Expected:** 422, error list chỉ row số, không import gì

### TC5 — Category not found
1 row `category_name` không tồn tại trong tournament.
**Expected:** 422 với message "Hạng mục 'X' không tồn tại"

### TC6 — Doubles thiếu partner
1 row category đôi nhưng `partner_name` empty.
**Expected:** 422 "Đôi phải có partner"

### TC7 — Partner không khớp
Row A partner=B, row B partner=C (asymmetric).
**Expected:** 422 "Partner không khớp 2 chiều"

### TC8 — Duplicate phone trong file
2 row cùng phone number.
**Expected:** 422 "Trùng số điện thoại với row khác"

### TC9 — Existing athlete (soft skip)
Row có phone trùng VĐV đã trong tournament.
**Expected:** 200, `skipped` list chứa row đó, `created` = other rows

### TC10 — Re-import same file
Import xong, import lại file đó.
**Expected:** 200, tất cả skip, created = 0

### TC11 — File > 500 rows
File 501 rows.
**Expected:** 422 "File quá lớn"

### TC12 — File > 2MB
**Expected:** 422 "file too large" (Laravel validator)

### TC13 — File format sai (.pdf, .txt)
**Expected:** 422 mime validation fail

### TC14 — Empty file
File chỉ có header.
**Expected:** 422 "File không có dữ liệu"

### TC15 — Vietnamese chars + accents
Tên VĐV có dấu (Nguyễn Văn Ánh).
**Expected:** Import thành công, DB lưu đúng UTF-8

### TC16 — Unauthorized
User khác không phải owner gọi endpoint.
**Expected:** 403

### TC17 — Template download
Owner download template, mở trong Excel.
**Expected:** File có header, 2 example rows, dropdown category hoạt động

### TC18 — Auto user creation
Import row với email/phone chưa có User.
**Expected:** User mới tạo (random password), VĐV link `user_id`

### TC19 — Existing user linking
Import row với phone trùng User đã đăng ký.
**Expected:** VĐV link `user_id` của user đó, không tạo user mới

## Implementation Steps

### 1. Start dev server
```bash
php artisan serve &
npm run dev &  # if Vite
```

### 2. Prepare test tournament
```bash
# Login as owner, navigate to /tournament-manage/{slug}/athletes
# Ensure tournament có cả single + doubles categories
```

### 3. Create test .xlsx files
Tạo 5-10 file Excel test cases ở `/tmp/` theo matrix trên.

### 4. Run test cases theo thứ tự
Ghi PASS/FAIL vào checklist bên dưới.

### 5. Fix bugs phát sinh
Sau mỗi fail, go back phase liên quan, fix, retry.

### 6. Check logs
```bash
tail -f storage/logs/laravel.log
```
Không được có stack trace runtime nào (trừ intentional exceptions).

### 7. DB sanity check
```bash
psql ... -c "SELECT id, athlete_name, phone, partner_id, user_id, status FROM tournament_athletes WHERE tournament_id = X ORDER BY id DESC LIMIT 20;"
```
Verify `partner_id` bidirectional, `user_id` linked correctly.

## Todo List
- [x] Dev server + test tournament setup
- [x] Tạo test .xlsx files
- [x] TC1-TC19 chạy đầy đủ
- [x] Fix bugs phát sinh (loop back phase 02-05)
- [x] DB verify partner_id + user_id
- [x] Log check clean
- [x] Documentation impact check — nếu changes schema/API → update `docs/`

## Success Criteria
- Tất cả 19 test cases PASS
- Không có uncaught exception trong logs
- DB state đúng sau mỗi test
- UX smooth: loading, error display, success toast clear

## Risk Assessment
- **Risk:** Test data contaminate DB. **Mitigation:** Dùng tournament riêng cho test, hoặc reset bằng DELETE sau mỗi run
- **Risk:** Session expired giữa test. **Mitigation:** Re-login khi cần

## Security Considerations
- Verify owner check không bypass được
- Verify CSRF required

## Next Steps
- Depends on: Phase 01-05 complete
- Blocks: none (final phase)
- After complete: update docs/project-changelog.md nếu feature launched
