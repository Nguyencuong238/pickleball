# Phase 02 — Tests for Cross-Category Dedup

**Status:** complete
**Priority:** High
**Effort:** S (≤ 1h)
**Depends on:** Phase 01

## Context

- `tests/Feature/Tournament/AthleteImportServiceDedupeTest.php`
- `tests/Unit/Services/Tournament/AthleteImportValidatorBasicTest.php`

## Changes

### 1. `AthleteImportServiceDedupeTest`

**Update `setUp`:** tạo thêm `$this->secondCategory` (e.g., `Nu Don`, type `single_women`) để phục vụ test cross-category.

**Update existing test** `test_reimport_cung_rows_thi_tat_ca_skip` (line 92-107):
- Vẫn pass vì cùng category.
- Thêm assertion: `assertStringContainsString("Nam Don", $result2['skipped'][0]['reason'])` để lock reason format mới.

**Add new tests:**

**`test_cung_phone_khac_category_thi_deu_tao_thanh_cong`**
```php
// Setup: VĐV X đã có ở Nam Don với phone 0901111111.
// Act: import 1 row cùng phone 0901111111, category = Nu Don.
// Expect: created=1, skipped=[].
```

**`test_cung_email_khac_category_thi_deu_tao_thanh_cong`**
```php
// Tương tự nhưng match bằng email.
```

**`test_cung_ten_khac_category_thi_deu_tao_thanh_cong`**
```php
// VĐV "Nguyen Van A" phone 0901111111 ở Nam Don đã tồn tại.
// Import row "Nguyen Van A" phone 0902222222 email khác, ở Nu Don.
// Expect: created=1 (cross-category, khác phone).
```

**`test_cung_ten_cung_category_voi_vdv_da_co_thi_skip_khong_crash`**
```php
// VĐV "Nguyen Van A" phone 0901111111 đã có ở Nam Don.
// Import row "Nguyen Van A" phone 0902222222 email khác (tên trùng, phone/email khác)
// ở CÙNG Nam Don.
// Expect: created=0, skipped=[row đó]. KHÔNG throw exception (không hit DB unique).
// Verify $result['skipped'][0]['reason'] chứa "Nam Don".
```

**`test_file_co_cung_phone_o_2_category_khac_nhau_thi_khong_bao_loi`**
```php
// 1 file: row A phone 0901111111 cat Nam Don, row B phone 0901111111 cat Nu Don.
// Expect: created=2, errors=[], skipped=[].
// Verify in-file validator dedup đã scope theo category.
```

### 2. `AthleteImportValidatorBasicTest`

**Add helper:**
```php
/** @return array<string, TournamentCategory> */
private function multiCategoryMap(): array
{
    $namDon = TournamentCategory::make(['id' => 1, 'category_name' => 'Nam Don', 'category_type' => 'single_men']);
    $nuDon  = TournamentCategory::make(['id' => 2, 'category_name' => 'Nu Don', 'category_type' => 'single_women']);
    return ['nam don' => $namDon, 'nu don' => $nuDon];
}
```

**Add tests:**

**`test_cung_phone_o_2_category_khac_nhau_trong_file_khong_bao_loi`**
```php
$rows = [
    $this->makeRow(['athlete_name' => 'A', 'phone' => '0901111111', 'category_name' => 'Nam Don', '_row_number' => 2]),
    $this->makeRow(['athlete_name' => 'B', 'phone' => '0901111111', 'email' => 'b@b.com', 'category_name' => 'Nu Don', '_row_number' => 3]),
];
$errors = $this->validator->validate($rows, $this->multiCategoryMap());
// Expect: không có error field 'phone' ở row 3.
$phoneErrors = array_filter($errors, fn($e) => $e['field'] === 'phone');
$this->assertEmpty($phoneErrors);
```

**`test_cung_email_o_2_category_khac_nhau_trong_file_khong_bao_loi`**
```php
// Tương tự nhưng cho email.
```

**`test_cung_phone_cung_category_trong_file_bao_loi` (keep existing behavior)**
- Test đã có `test_trung_phone_trong_file_tao_ra_loi` (line 65) — vẫn pass với fix. Có thể bổ sung test assert `message` chứa `"hạng mục"` nếu muốn lock format mới, nhưng không bắt buộc.

## Todo

- [ ] Update `AthleteImportServiceDedupeTest::setUp` thêm `$secondCategory`
- [ ] Add 5 test cases mới trong `AthleteImportServiceDedupeTest`
- [ ] Update assertion trong `test_reimport_cung_rows_thi_tat_ca_skip` (reason contains category name)
- [ ] Add helper `multiCategoryMap()` trong `AthleteImportValidatorBasicTest`
- [ ] Add 2 test cases mới trong `AthleteImportValidatorBasicTest`
- [ ] Run: `php artisan test --filter=AthleteImport`
- [ ] Run full athlete-related suite để check regression: `php artisan test tests/Feature/Tournament tests/Unit/Services/Tournament tests/Unit/Imports`

## Success Criteria

- Toàn bộ new + existing athlete-import tests green.
- Không có test crash vì DB unique throw (phase-01 phải cover).
- Test `test_cung_ten_cung_category_voi_vdv_da_co_thi_skip_khong_crash` là smoke test cho gap pre-existing đã được đóng.

## Notes

- Dùng `RefreshDatabase` (đã setup). Không mock.
- Convention đặt tên test: snake_case tiếng Việt không dấu (theo file hiện tại).
- `TournamentCategory::create` trong test feature cần `tournament_id`, `category_name`, `category_type` — không có field khác bắt buộc (confirmed từ setUp hiện tại line 38-43).
