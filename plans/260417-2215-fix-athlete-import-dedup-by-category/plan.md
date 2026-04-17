# Fix Athlete Import Dedup — Scope by Category

**Created:** 2026-04-17 22:15 (Asia/Saigon)
**Scope:** Bug fix — dedup logic trong import Excel VĐV
**Complexity:** Small (1 file chính + 1 file validator + tests)

## Problem

User báo: import 2 file, toast hiển thị "Đã import 32 VĐV. Bỏ qua 32 VĐV đã tồn tại." → bị skip toàn bộ 32 VĐV khi file 2 chứa các VĐV đã đăng ký ở hạng mục khác của file 1.

**Root cause:** `AthleteImportService::detectDuplicates` (`app/Services/Tournament/AthleteImportService.php:87-120`) check trùng phone/email theo scope **tournament** thay vì **(tournament, category)**. Validator `AthleteImportValidator::validate` (`app/Services/Tournament/AthleteImportValidator.php:48-53`) cũng check trùng phone/email trong cùng file globally, không phân biệt category.

**Business rule (user confirmed):** 1 VĐV được đăng ký nhiều hạng mục trong cùng 1 giải (MS + MD + XD …).

**Schema đã đúng:** migration `2025_11_22_092950_modify_unique_constraint_tournament_athletes.php` đã set unique `(tournament_id, athlete_name, category_id)` → DB allow same phone/email cross-category. Chỉ logic service/validator sai.

## Goal

Dedup check chuyển sang scope **(tournament_id, category_id, phone|email|name)**:
- Cùng phone/email/tên + cùng category → skip (giữ nguyên hành vi hiện tại trong phạm vi category)
- Cùng phone/email/tên + khác category → cho phép import

**Thêm `athlete_name` vào dedup vs DB** để đóng gap pre-existing: trước đây chỉ check phone/email, nên row có tên trùng + category trùng (khác phone/email) sẽ lọt qua service và crash DB unique `(tournament_id, athlete_name, category_id)` ở `persistAthletes`, rollback toàn bộ transaction → user thấy 500.

## Non-Goals

- Không thay đổi schema DB (unique key đã đúng).
- Không rework UX modal (chỉ cập nhật message string ở backend).
- Không đổi validator rules khác (tên, email format, phone length, partner symmetry).
- Không đụng tới `TournamentAthleteController::store` (flow tạo manual từ UI, không ảnh hưởng).

## Phases

| # | File | Status | Description |
|---|------|--------|-------------|
| 01 | [phase-01-fix-dedup-scope.md](phase-01-fix-dedup-scope.md) | complete | Scope dedup + in-file dedup by (category, phone/email) |
| 02 | [phase-02-tests.md](phase-02-tests.md) | complete | Update + add tests cho scenario cross-category |

## Key Files

- `app/Services/Tournament/AthleteImportService.php` — `detectDuplicates`
- `app/Services/Tournament/AthleteImportValidator.php` — `validate` (seenPhones/seenEmails)
- `tests/Feature/Tournament/AthleteImportServiceDedupeTest.php`
- `tests/Unit/Services/Tournament/AthleteImportValidatorBasicTest.php`

## Success Criteria

- VĐV cùng phone/email/tên import vào 2 category khác nhau → cả 2 đều tạo thành công, `created=2`, `skipped=[]`.
- VĐV cùng phone/email/tên import cùng category lần 2 → skip với reason `"Đã tồn tại trong hạng mục '{name}'."`.
- Re-import cùng file → tất cả rows skip (giữ nguyên hành vi idempotent), không crash DB unique.
- Row có tên trùng + category trùng (phone/email khác) → skip thay vì 500 error.
- Test suite pass full (`php artisan test --filter=AthleteImport`).

## Dependencies

None. Chạy độc lập.
