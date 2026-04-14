---
status: complete
created: 2026-04-14
completed: 2026-04-14
feature: Import Excel athletes cho tournament-manage
---

# Plan: Import Excel VĐV — Tournament Athletes

**Brainstorm:** [../reports/brainstorm-260414-1618-import-excel-athletes.md](../reports/brainstorm-260414-1618-import-excel-athletes.md)

## Goal
Cho phép owner giải đấu import hàng loạt VĐV từ file Excel tại `/tournament-manage/{slug}/athletes`, thay vì thêm từng người qua modal.

## Scope
- Feature thêm (không refactor store hiện tại)
- Support .xlsx/.xls, columns minimal: `athlete_name, email, phone, category_name, partner_name`
- All-or-nothing validation, soft-skip duplicates, status=pending
- Dynamic template download per-tournament

## Key Dependencies
- **Existing `phpoffice/phpspreadsheet ^5.3`** (không install thêm — maatwebsite/excel KHÔNG tương thích với phpspreadsheet 5.x)
- Reference implementation: `app/Http/Controllers/Admin/UserImportController.php` dùng raw `IOFactory::load()`
- Existing: `TournamentAthleteController`, `TournamentAthlete`, `TournamentCategory` models
- Existing: Alpine component `tournamentAthletes()` in `public/assets/js/tournament-athletes.js`
- Existing: Partial `resources/views/home-yard/tournaments/partials/_athletes.blade.php`

## Phases

| # | Phase | Status | File |
|---|---|---|---|
| 01 | Package install + Import/Export scaffold | complete | [phase-01-package-install-scaffold.md](phase-01-package-install-scaffold.md) |
| 02 | AthleteImportService (validate + persist + partner) | complete | [phase-02-athlete-import-service.md](phase-02-athlete-import-service.md) |
| 03 | Controller methods + routes | complete | [phase-03-controller-routes.md](phase-03-controller-routes.md) |
| 04 | UI modal + Alpine methods | complete | [phase-04-ui-modal-alpine.md](phase-04-ui-modal-alpine.md) |
| 05 | Dynamic template export | complete | [phase-05-template-export.md](phase-05-template-export.md) |
| 06 | Manual test + edge cases | complete | [phase-06-test-validate.md](phase-06-test-validate.md) |

## Success Criteria
- Upload file 100 VĐV → import <5s, state correct
- Hard error → abort, error list chỉ rõ row/field
- Duplicates → skip với warning, không abort
- Doubles: 2 row cross-reference → partner_id bidirectional
- Re-import cùng file → idempotent (tất cả skip)
- Download template per-tournament có dropdown category

## Completion Summary

**Files tạo mới (8):** `TournamentAthletesImporter`, `TournamentAthleteTemplateBuilder`, `AthleteImportService`, `AthleteImportValidator`, `AthleteRowNormalizer`, `TournamentAthleteImportTrait`, `_athletes-import-modal.blade.php`, và 7 test files.

**Files sửa (4):** `routes/web.php` (+2 routes), `TournamentAthleteController.php` (trait + store refactor), `_athletes.blade.php` (config URLs + button + include), `tournament-athletes.js` (+4 methods).

**Tests:** 51 passed, 0 failed — 7 test files (4 Unit, 3 Feature).

**Code review:** 8.8/10. Đã fix: `setShowDropDown` bug, phone normalize edge case, `in_array` → hash lookup, `selectedIds` reset.

**Tech debt tạm defer:** DB unique constraint `(tournament_id, phone)` chưa thêm migration — defer đến khi có nhu cầu enforce ở DB level.
