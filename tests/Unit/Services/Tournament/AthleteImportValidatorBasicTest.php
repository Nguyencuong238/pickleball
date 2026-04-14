<?php

namespace Tests\Unit\Services\Tournament;

use App\Models\TournamentCategory;
use App\Services\Tournament\AthleteImportValidator;
use Tests\TestCase;

/**
 * Unit tests cơ bản cho AthleteImportValidator:
 * kiểm tra từng trường và trùng lặp trong file.
 */
class AthleteImportValidatorBasicTest extends TestCase
{
    private AthleteImportValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new AthleteImportValidator();
    }

    public function test_ten_rong_tao_ra_loi(): void
    {
        $rows = [$this->makeRow(['athlete_name' => ''])];
        $errors = $this->validator->validate($rows, $this->singleCategoryMap());
        $this->assertFieldError($errors, 'athlete_name', 2);
    }

    public function test_ten_qua_100_ky_tu_tao_ra_loi(): void
    {
        $rows = [$this->makeRow(['athlete_name' => str_repeat('A', 101)])];
        $errors = $this->validator->validate($rows, $this->singleCategoryMap());
        $this->assertFieldError($errors, 'athlete_name', 2);
    }

    public function test_email_khong_hop_le_tao_ra_loi(): void
    {
        $rows = [$this->makeRow(['email' => 'not-an-email'])];
        $errors = $this->validator->validate($rows, $this->singleCategoryMap());
        $this->assertFieldError($errors, 'email', 2);
    }

    public function test_phone_rong_tao_ra_loi(): void
    {
        $rows = [$this->makeRow(['phone' => ''])];
        $errors = $this->validator->validate($rows, $this->singleCategoryMap());
        $this->assertFieldError($errors, 'phone', 2);
    }

    public function test_category_khong_ton_tai_tao_ra_loi(): void
    {
        $rows = [$this->makeRow(['category_name' => 'Hang Muc La'])];
        $errors = $this->validator->validate($rows, $this->singleCategoryMap());
        $this->assertFieldError($errors, 'category_name', 2);
    }

    public function test_doubles_thieu_partner_tao_ra_loi(): void
    {
        $rows = [$this->makeRow(['category_name' => 'Nam Doi', 'partner_name' => ''])];
        $errors = $this->validator->validate($rows, $this->doublesCategoryMap());
        $this->assertFieldError($errors, 'partner_name', 2);
    }

    public function test_trung_phone_trong_file_tao_ra_loi(): void
    {
        $rows = [
            $this->makeRow(['athlete_name' => 'A', 'phone' => '0901111111', '_row_number' => 2]),
            $this->makeRow(['athlete_name' => 'B', 'email' => 'b@b.com', 'phone' => '0901111111', '_row_number' => 3]),
        ];
        $errors = $this->validator->validate($rows, $this->singleCategoryMap());
        $this->assertFieldError($errors, 'phone', 3);
    }

    public function test_trung_email_trong_file_tao_ra_loi(): void
    {
        $rows = [
            $this->makeRow(['athlete_name' => 'A', 'email' => 'same@email.com', '_row_number' => 2]),
            $this->makeRow(['athlete_name' => 'B', 'phone' => '0902222222', 'email' => 'same@email.com', '_row_number' => 3]),
        ];
        $errors = $this->validator->validate($rows, $this->singleCategoryMap());
        $this->assertFieldError($errors, 'email', 3);
    }

    public function test_trung_ten_trong_cung_category_tao_ra_loi(): void
    {
        $rows = [
            $this->makeRow(['athlete_name' => 'Nguyen Van A', '_row_number' => 2]),
            $this->makeRow(['athlete_name' => 'Nguyen Van A', 'phone' => '0902222222', 'email' => 'b@b.com', '_row_number' => 3]),
        ];
        $errors = $this->validator->validate($rows, $this->singleCategoryMap());
        $this->assertFieldError($errors, 'athlete_name', 3);
    }

    public function test_row_hop_le_khong_tao_ra_loi(): void
    {
        $rows = [$this->makeRow()];
        $errors = $this->validator->validate($rows, $this->singleCategoryMap());
        $this->assertEmpty($errors);
    }

    // === Helpers ===

    private function makeRow(array $overrides = []): array
    {
        return array_merge([
            'athlete_name'  => 'Nguyen Van A',
            'email'         => 'a@example.com',
            'phone'         => '0901234567',
            'category_name' => 'Nam Don',
            'partner_name'  => '',
            '_row_number'   => 2,
        ], $overrides);
    }

    /** @return array<string, TournamentCategory> */
    private function singleCategoryMap(): array
    {
        $cat = TournamentCategory::make([
            'id'            => 1,
            'category_name' => 'Nam Don',
            'category_type' => 'single_men',
        ]);
        return ['nam don' => $cat];
    }

    /** @return array<string, TournamentCategory> */
    private function doublesCategoryMap(): array
    {
        $cat = TournamentCategory::make([
            'id'            => 2,
            'category_name' => 'Nam Doi',
            'category_type' => 'double_men',
        ]);
        return ['nam doi' => $cat];
    }

    /**
     * @param array<int, array{row:int, field:string, message:string}> $errors
     */
    private function assertFieldError(array $errors, string $field, int $row): void
    {
        $match = array_filter($errors, fn($e) => $e['field'] === $field && $e['row'] === $row);
        $this->assertNotEmpty($match, "Khong tim thay loi field='{$field}' tai row={$row}. Errors: " . json_encode($errors));
    }
}
