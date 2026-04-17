# Phase 01 — Fix Dedup Scope by Category

**Status:** complete
**Priority:** High
**Effort:** S (≤ 1h)

## Context

- Service: `app/Services/Tournament/AthleteImportService.php`
- Validator: `app/Services/Tournament/AthleteImportValidator.php`
- Current dedup key: `phone` / `email` → change to `"{category_id}|{phone|email|nameLower}"`.
- DB schema unique: `(tournament_id, athlete_name, category_id)` — đã đúng. Fix service/validator để không crash transaction khi re-import.

## Changes

### 1. `AthleteImportService::detectDuplicates` (lines 87-120)

**Current behavior:**
```php
$existing = TournamentAthlete::where('tournament_id', $tournament->id)
    ->get(['phone', 'email']);
// build $existingPhones[$p] and $existingEmails[$e] — GLOBAL scope
```

**Problems:**
- Dedup scope = tournament (sai — phải theo category).
- Không check `athlete_name` vs DB → re-import file cùng tên khác phone/email sẽ làm `persistAthletes` throw DB unique `(tournament_id, athlete_name, category_id)` → rollback toàn bộ transaction → user thấy 500.

**Target signature + logic:**

```php
// Nhận thêm $categoriesByName để resolve row → category_id
private function detectDuplicates(array $rows, Tournament $tournament, array $categoriesByName): array
{
    // Lấy thêm athlete_name + category_id cho 3-dimensional dedup
    $existing = TournamentAthlete::where('tournament_id', $tournament->id)
        ->get(['phone', 'email', 'athlete_name', 'category_id']);

    $existingPhones = []; // key "categoryId|phone"
    $existingEmails = []; // key "categoryId|emailLower"
    $existingNames  = []; // key "categoryId|nameLower"
    foreach ($existing as $e) {
        if ($e->phone) {
            $existingPhones[$e->category_id . '|' . $e->phone] = true;
        }
        if ($e->email) {
            $existingEmails[$e->category_id . '|' . mb_strtolower($e->email)] = true;
        }
        if ($e->athlete_name) {
            $existingNames[$e->category_id . '|' . mb_strtolower($e->athlete_name)] = true;
        }
    }

    $toPersist = [];
    $skipped = [];
    foreach ($rows as $row) {
        $catKey = mb_strtolower($row['category_name']);
        // Defensive: validator đã reject rows có category không tồn tại (service
        // return sớm nếu $errors non-empty), nhưng check lại để tránh warning PHP.
        if (!isset($categoriesByName[$catKey])) {
            continue;
        }
        $category = $categoriesByName[$catKey];
        $phoneKey = $category->id . '|' . $row['phone'];
        $emailKey = $category->id . '|' . $row['email'];
        $nameKey  = $category->id . '|' . mb_strtolower($row['athlete_name']);

        $phoneDup = $row['phone'] !== '' && isset($existingPhones[$phoneKey]);
        $emailDup = $row['email'] !== '' && isset($existingEmails[$emailKey]);
        $nameDup  = $row['athlete_name'] !== '' && isset($existingNames[$nameKey]);

        if ($phoneDup || $emailDup || $nameDup) {
            $skipped[] = [
                'row'          => $row['_row_number'],
                'athlete_name' => $row['athlete_name'],
                'reason'       => "Đã tồn tại trong hạng mục '{$category->category_name}'.",
            ];
            continue;
        }
        $toPersist[] = $row;
    }

    return [$toPersist, $skipped];
}
```

**Caller update** (`execute` method, line 37):
```php
[$toPersist, $skipped] = $this->detectDuplicates($normalized, $tournament, $categoriesByName);
```

### 2. `AthleteImportValidator::validate` (lines 48-65)

In-file dedup của phone/email phải scope theo category (không chặn cùng phone ở 2 category khác nhau trong cùng file — hợp lệ). `seenNames` đã scope đúng (line 55) — giữ nguyên.

**Current:**
```php
if ($row['phone'] !== '' && isset($seenPhones[$row['phone']])) { ... }
if ($row['email'] !== '' && isset($seenEmails[$row['email']])) { ... }
// ...
if ($row['phone'] !== '') { $seenPhones[$row['phone']] = true; }
if ($row['email'] !== '') { $seenEmails[$row['email']] = true; }
```

**Target:** nằm SAU block resolve `$category` (line 43) — tận dụng `$category->id`:

```php
$phoneKey = $category->id . '|' . $row['phone'];
$emailKey = $category->id . '|' . $row['email'];

if ($row['phone'] !== '' && isset($seenPhones[$phoneKey])) {
    $errors[] = ['row' => $rowNum, 'field' => 'phone', 'message' => 'Trùng số điện thoại với dòng khác trong cùng hạng mục.'];
}
if ($row['email'] !== '' && isset($seenEmails[$emailKey])) {
    $errors[] = ['row' => $rowNum, 'field' => 'email', 'message' => 'Trùng email với dòng khác trong cùng hạng mục.'];
}
// ...
if ($row['phone'] !== '') { $seenPhones[$phoneKey] = true; }
if ($row['email'] !== '') { $seenEmails[$emailKey] = true; }
```

### 3. Skip-count message (không đổi)

`TournamentAthleteImportTrait::importExcel` line 53-54: giữ `"Bỏ qua {n} vận động viên đã tồn tại."`. Chi tiết reason mới (có tên category) nằm trong `$result['skipped']` — chưa được frontend hiển thị (out of scope).

## Breaking Changes (Notes)

- Reason message trong `skipped[]`: `"Đã tồn tại trong giải."` → `"Đã tồn tại trong hạng mục '{name}'."`
  - Verify: `grep -r "Đã tồn tại trong giải"` → không thấy ở code khác ngoài service. Frontend không grep theo text này. An toàn.
- In-file validator message: `"… trong file."` → `"… trong cùng hạng mục."`
  - Existing tests chỉ assert `field` + `row`, không assert `message` text → an toàn.

## Todo

- [ ] Sửa `detectDuplicates` signature (+ param `$categoriesByName`), query thêm `athlete_name`, build 3 hash sets
- [ ] Thêm check `$nameDup` song song `$phoneDup`/`$emailDup`
- [ ] Update call site trong `execute`
- [ ] Sửa in-file dedup ở `AthleteImportValidator::validate` — key `categoryId|phone` + `categoryId|email`
- [ ] Update thông điệp lỗi validator: "…trong cùng hạng mục"
- [ ] Run `php -l` trên 2 file để check syntax
- [ ] Manual smoke test: import file có cùng phone+tên ở 2 category → đều tạo thành công

## Risks

- **Defensive check `!isset($categoriesByName[$catKey])`:** không remove — phòng trường hợp validator bypass. Khi hit, skip row silent (không thêm vào `skipped` vì validator đã báo error ở flow trước).
- **`athlete_name` case-sensitivity:** dùng `mb_strtolower` ở cả hai side (DB load + row) để nhất quán với hành vi validator hiện tại.
- **Phone empty + email empty + name khớp:** với logic mới, row có name match DB nhưng không có phone/email sẽ bị skip. Đúng hành vi mong muốn (tránh crash DB unique).

## Success Criteria

- `php -l app/Services/Tournament/AthleteImportService.php` pass
- `php -l app/Services/Tournament/AthleteImportValidator.php` pass
- Manual test: import 2 file, file 2 có cùng phone ở hạng mục khác file 1 → `created > 0`, `skipped` chỉ chứa rows thực sự trùng trong cùng hạng mục.
- Re-import cùng file → `created=0, skipped=N`, không có 500 error.
