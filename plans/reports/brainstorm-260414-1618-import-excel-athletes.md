# Brainstorm: Import Excel VĐV cho Tournament Athletes Page

**Date:** 2026-04-14
**Scope:** `/tournament-manage/{slug}/athletes` — thêm feature import athletes hàng loạt từ file Excel
**Status:** Agreed, ready for `/plan`

---

## 1. Problem Statement

Owner giải đấu hiện tại phải thêm từng VĐV qua modal `store` (1 row/lần). Với giải lớn (50-200 VĐV), nhập tay tốn thời gian và dễ sai. Cần feature import hàng loạt từ Excel.

**Current state:**
- Controller: `app/Http/Controllers/Front/Tournament/TournamentAthleteController.php`
- Route: `POST /tournament-manage/{tournament}/athletes` (single-add only)
- View: Alpine component `tournamentAthletes()` tại `public/assets/js/tournament-athletes.js`
- Schema `tournament_athletes`: `athlete_name, email, phone, category_id, partner_id, user_id, status, payment_status`
- Library: `phpoffice/phpspreadsheet ^5.2` đã có. Chưa có `maatwebsite/excel`, chưa có `app/Imports/`

---

## 2. Agreed Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Columns | Minimal: `athlete_name, email, phone, category_name, partner_name` | Schema hiện tại không có gender/dob/DUPR, YAGNI |
| Category mapping | By `category_name` (exact match trong tournament) | User-friendly, không cần nhớ code |
| Error handling | All-or-nothing (hard errors abort full file) | An toàn, state sạch |
| Duplicate handling | Skip + warning (soft — không abort) | Tách biệt hard error vs existing athlete |
| Default status | `pending` | Consistent với `store` flow hiện tại |
| Library | `maatwebsite/excel` | Laravel-friendly, giảm boilerplate |
| Template | Generate động per-tournament | Dropdown category list chính xác |

---

## 3. Architecture

### 3.1 File Structure

```
app/
├── Http/Controllers/Front/Tournament/
│   └── TournamentAthleteController.php     # add: importExcel(), downloadTemplate()
├── Imports/
│   └── TournamentAthletesImport.php        # implements ToCollection, WithHeadingRow, WithValidation
├── Exports/
│   └── TournamentAthleteTemplateExport.php # generate template per-tournament
└── Services/Tournament/
    └── AthleteImportService.php            # parse + validate + persist logic
routes/web.php                              # +2 routes
resources/views/home-yard/tournaments/partials/
└── _athletes.blade.php                     # add import modal
public/assets/js/
└── tournament-athletes.js                  # Alpine methods: openImport, submitImport, downloadTemplate
```

### 3.2 Routes (add under existing group)

```php
Route::get('{tournament}/athletes/import-template', [TAC::class, 'downloadTemplate'])
    ->name('tournament-manage.athletes.import-template');
Route::post('{tournament}/athletes/import', [TAC::class, 'importExcel'])
    ->name('tournament-manage.athletes.import');
```

### 3.3 Import Flow (2-pass)

```
Upload .xlsx
  → TournamentAthletesImport::collection()
  → AthleteImportService::execute($rows, $tournament)
      1. Normalize rows (trim, lowercase email, normalize phone)
      2. Validate schema (required fields, email format, category exists)
      3. HARD ERRORS collection — nếu có → abort, trả JSON { errors: [{row, field, message}] }
      4. Detect SOFT duplicates (phone/email đã có trong tournament_athletes) → skip list
      5. DB::transaction:
         Pass A: Create all non-duplicate athletes (status=pending, link user_id theo phone/email, auto-create user nếu chưa có)
         Pass B: Link partners — với doubles category, match partner_name trong cùng file + cùng category
      6. Return report: { created: N, skipped: M, warnings: [...] }
```

### 3.4 Partner Linking Logic

File có 2 row cho đôi, mỗi row `partner_name` trỏ sang nhau:
- Build map `[category_id → [athlete_name → athlete_id]]` sau Pass A
- Pass B: với mỗi athlete có `partner_name` + category là doubles, lookup id → set `partner_id` bidirectional
- Validation: partner_name phải match 1 row khác trong cùng file + cùng category; nếu không tìm thấy → hard error ở Pass 3

### 3.5 Template Generation (dynamic)

`TournamentAthleteTemplateExport`:
- Sheet 1 "Athletes": header row + 2 example rows
- Sheet 2 "Categories" (hidden): list `category_name` của tournament → reference cho data validation
- Apply `DataValidation` type LIST cho column `category_name` → dropdown trong Excel
- Filename: `template-athletes-{slug}.xlsx`

---

## 4. Validation Rules

### Hard errors (abort file)

| Rule | Message |
|---|---|
| Thiếu file / file rỗng | "File không có dữ liệu" |
| Missing column header | "Thiếu cột: {col}" |
| `athlete_name` required, max 100 | "Row {n}: Tên không hợp lệ" |
| `email` required, valid format | "Row {n}: Email không hợp lệ" |
| `phone` required, max 20 | "Row {n}: Số điện thoại không hợp lệ" |
| `category_name` not found trong tournament | "Row {n}: Hạng mục '{x}' không tồn tại" |
| Doubles category nhưng `partner_name` rỗng | "Row {n}: Đôi phải có partner" |
| `partner_name` không tồn tại trong file cùng category | "Row {n}: Không tìm thấy partner '{x}'" |
| Partner không đối xứng (A→B nhưng B→C) | "Row {n}: Partner không khớp 2 chiều" |
| Trùng phone/email giữa các row trong file | "Row {n}: Trùng {field} với row {m}" |

### Soft warnings (skip row, không abort)

| Condition | Behavior |
|---|---|
| Phone/email đã tồn tại trong `tournament_athletes` cùng tournament | Skip row, thêm vào `skipped` list |

---

## 5. UI Changes

### Modal "Import Excel"

```
[Nút "Import Excel"]  ← thêm cạnh nút "Thêm VĐV"
  ↓ click
┌─────────────────────────────────┐
│ Import VĐV từ Excel             │
├─────────────────────────────────┤
│ 1. Tải file mẫu [Download btn]  │
│ 2. Chọn file:  [file input]     │
│ 3. [Preview errors nếu có]      │
│                                 │
│ [Hủy]              [Import]     │
└─────────────────────────────────┘
```

**Error display** (sau khi submit fail):
- Scrollable list, mỗi item: `Row {n}: {message}` đỏ
- Nút "Đóng" để fix file, upload lại

**Success display:**
- Toast: "Đã import {N} VĐV. Bỏ qua {M} VĐV (đã tồn tại)"
- Reload athletes list

---

## 6. Implementation Considerations

### Security
- `authorizeOwner($tournament)` ở cả 2 route mới (consistent với existing)
- Max upload size: 2MB trong validation (`file|mimes:xlsx,xls|max:2048`)
- Rate limit: Laravel default đủ dùng
- Auto-create user: reuse logic line 94-105 của `store()` — bcrypt random password

### Performance
- Giới hạn 500 row/file (hard limit trong validation)
- DB::transaction wrap toàn bộ persist để rollback nếu hard error phát sinh runtime
- Avoid N+1: preload tournament categories 1 lần, preload existing athletes (phone + email) 1 query

### Maintainability
- Tách logic parse vào `TournamentAthletesImport`, persist vào `AthleteImportService` → controller mỏng, test dễ
- Reuse `formatAthlete()` trong response
- Không duplicate logic user lookup — extract thành method `resolveOrCreateUser(email, phone, name)` dùng chung với `store()`

---

## 7. Risks & Mitigations

| Risk | Mitigation |
|---|---|
| maatwebsite/excel bloat (~5MB) | Acceptable — dùng cho cả future exports |
| User upload file sai format → server crash | `try/catch` PhpSpreadsheetException, trả 422 JSON |
| Partner linking race condition nếu 2 import song song | Transaction + unique check trên `(tournament_id, phone)` (check in-transaction) |
| Vietnamese name encoding (UTF-8) | maatwebsite/excel handles mặc định, test với tên có dấu |
| Category name có khoảng trắng/case sensitive | Normalize trim + case-insensitive match |
| File .xls (old format) vs .xlsx | Accept cả 2 trong validation |

---

## 8. Success Criteria

- Owner upload file 100 VĐV → import < 5s, toast success
- File có 1 row sai category → abort, error list chỉ rõ row số
- File có 5 VĐV đã tồn tại → import 95, skip 5, hiển thị warning
- Download template → file .xlsx có dropdown category với đúng list của giải đó
- Đôi: 2 row cross-reference partner → tạo 2 athletes với partner_id bidirectional
- Re-import cùng file → tất cả skip (idempotent cho duplicates)

---

## 9. Next Steps

1. Install: `composer require maatwebsite/excel`
2. Run `/plan` để tạo detailed implementation plan với phases:
   - Phase 1: Package install + Import/Export classes scaffold
   - Phase 2: AthleteImportService (validate + persist + partner linking)
   - Phase 3: Controller methods + routes
   - Phase 4: UI modal + Alpine methods
   - Phase 5: Dynamic template export với dropdown
   - Phase 6: Manual test + edge cases

---

## 10. Unresolved Questions

1. Có cần log audit khi import (ai import, khi nào, bao nhiêu VĐV)? — Giả định: reuse Laravel log mặc định, không cần audit table riêng.
2. Có nên cho phép import qua CSV luôn không (free cho user)? — Giả định: chỉ xlsx/xls cho v1, CSV để sau nếu cần.
3. `payment_status` import có nên cho user set không, hay luôn `unpaid`? — Giả định: luôn `unpaid`, owner update sau.
4. Max row limit 500 có đủ không, hay cần chunking queue job cho file lớn? — Giả định: 500 đủ cho giải thông thường, YAGNI.
