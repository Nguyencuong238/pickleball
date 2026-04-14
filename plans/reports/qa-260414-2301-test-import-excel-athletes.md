# QA Report — Import Excel Athletes (Chrome DevTools E2E)

**Date:** 2026-04-14
**Plan:** [260414-1618-import-excel-athletes](../260414-1618-import-excel-athletes/plan.md)
**Tester:** Claude Code via chrome-devtools MCP
**Target:** `http://pickleball.test` — tournament `giai-dau-mini-onepickleball-2026` (1 category: "Đôi Nam")
**Account:** alexvu8588@gmail.com (owner)

## Summary
**9/9 test cases passed.** Feature works as spec'd. Hard-errors abort all-or-nothing, duplicates soft-skip idempotently, doubles partner symmetry enforced, dynamic template downloads correctly, MIME/column/data validation all firing.

## Test Matrix

| # | Case | Expected | Actual | Pass |
|---|---|---|---|---|
| 1 | Login + navigate `/tournament-manage/{slug}/athletes` | Page loads with "Import Excel" button | OK (26/26 athletes, category "Đôi Nam") | ✓ |
| 2 | Download dynamic template | 200 + xlsx + filename per-tournament | `template-athletes-giai-dau-mini-onepickleball-2026.xlsx` 8544 bytes, content-type correct | ✓ |
| 3 | Happy path: 4 valid doubles rows (2 pairs) | All 4 created, partners linked bidirectional | `{"created":4}`; UI count 26→30, "Chờ duyệt 0→4"; partner column shows A1↔A2, B1↔B2 | ✓ |
| 4 | Re-import same file (idempotency) | created=0, skipped=4 with reason "Đã tồn tại" | `{"created":0,"skipped":[4 × "Đã tồn tại trong giải."]}` | ✓ |
| 5 | Validation errors (bad.xlsx, 11 rows × 9 err types) | 422 + error list per row/field, no inserts | 13 errors caught, renders "Phát hiện 13 lỗi — file không được import:" in modal | ✓ |
| 6 | Empty data (headers only) | 422 "File không có dữ liệu." | Matches | ✓ |
| 7 | Missing required column (`phone`) | 422 "Thiếu cột bắt buộc: phone" | Matches | ✓ |
| 8 | Wrong MIME (.txt) | Laravel 422 mime rejection | `"The file field must be a file of type: xlsx, xls."` | ✓ |
| 9 | Cleanup | 4 test athletes + users removed, count → 26 | `athletes_deleted=4 users_deleted=4`, UI reload shows 26/26 | ✓ |

## Validation Errors Caught (case 5)

Bad file 11 rows → 13 hard errors returned (no partial inserts):

- Row 2: `athlete_name` empty + `partner_name` not found ('Nobody')
- Row 3: `email` invalid format + partner not found ('Bad Email 2')
- Row 4: `phone` empty + partner not found ('Somebody')
- Row 5: `category_name` 'Ghost Category' không tồn tại
- Row 6: doubles row thiếu partner
- Row 7: partner self-reference ('Self Ref' → 'Self Ref')
- Rows 8,9,10: asymmetric partner chain (A→B, B→C, C→A)
- Row 12: trùng phone với dòng 11 trong file

**Observation:** Rows with missing required fields still get secondary partner-symmetry errors (row 4 "Empty Phone" + partner missing). Behavior correct — all-or-nothing abort means user sees full error list upfront.

## UI/UX Verification

- Modal title "Import vận động viên từ Excel" ✓
- "1. Tải file mẫu" link + "2. Chọn file Excel (.xlsx, .xls — tối đa 2MB, 500 dòng)" copy ✓
- File input `accept=".xlsx,.xls"` attribute present ✓
- Import button disabled until file chosen ✓
- Success toast: "Đã import 4 vận động viên." ✓
- Duplicate toast (not seen inline — response body has skipped list; toast may auto-dismiss before wait_for)
- Error list rendered line-by-line inside modal body ✓
- Athlete list auto-refreshes after success (4 new rows appear on top with "Chờ duyệt" status) ✓

## Backend Checks (code-reviewed)

- `TournamentAthleteImportTrait::importExcel` — auth, mimes validation, MAX_IMPORT_ROWS=500, try/catch split for `InvalidArgumentException` / `PhpSpreadsheetException` / generic `Throwable`
- `AthleteImportService::execute` — normalize → validate → dedupe → DB::transaction persist
- Duplicate detection: phone OR email match against existing `TournamentAthlete` (O(1) hash lookup)
- Partner linking: 2-pass (create all then update partner_id) — bidirectional via iteration
- User resolution: phone-first, email-fallback, create with random bcrypt password

## Unresolved Questions / Tech Debt

- **No DB unique constraint** on `(tournament_id, phone)` — race condition between concurrent imports could insert duplicates. Plan already notes this as deferred tech debt.
- **Duplicate toast UX** — on re-import (case 4) the JSON response contains skipped list but `wait_for` didn't find "Bỏ qua" text in DOM within 15s. Likely auto-dismisses or goes to console — worth verifying in `tournament-athletes.js` toast handler whether skip-count message is rendered long enough.
- **Partner symmetry cascade** — when a row has a hard error (empty name/phone), partner-not-found errors are still generated for it. Not wrong per se, but could cluster error count. Consider suppressing symmetry check for rows already failing basic field validation to reduce noise.
- **No capacity check** — `importExcel` does not verify `tournament.max_athletes`. Tournament at 26/26 accepted 4 more → 30/26. Not tested whether this breaks downstream (draw/bracket). Worth confirming if max is advisory or hard.
