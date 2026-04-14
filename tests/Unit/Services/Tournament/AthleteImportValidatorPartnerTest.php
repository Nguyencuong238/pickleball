<?php

namespace Tests\Unit\Services\Tournament;

use App\Models\TournamentCategory;
use App\Services\Tournament\AthleteImportValidator;
use Tests\TestCase;

/**
 * Unit tests cho validatePartnerSymmetry (gọi qua validate()).
 * Kiểm tra logic đôi: tự trỏ mình, partner không tồn tại, 1 chiều, 2 chiều.
 */
class AthleteImportValidatorPartnerTest extends TestCase
{
    private AthleteImportValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new AthleteImportValidator();
    }

    public function test_partner_tu_chinh_minh_tao_ra_loi(): void
    {
        $map = $this->doublesCategoryMap();
        $rows = [
            $this->makeDoublesRow('A', 'A', '0901111111', 'a@a.com', 2),
        ];
        $errors = $this->validator->validate($rows, $map);
        $partnerErrors = array_filter($errors, fn($e) => $e['field'] === 'partner_name');
        $this->assertNotEmpty($partnerErrors);
    }

    public function test_partner_khong_co_trong_file_tao_ra_loi(): void
    {
        $map = $this->doublesCategoryMap();
        // A trỏ B nhưng B không có trong file
        $rows = [
            $this->makeDoublesRow('A', 'B', '0901111111', 'a@a.com', 2),
        ];
        $errors = $this->validator->validate($rows, $map);
        $partnerErrors = array_filter($errors, fn($e) => $e['field'] === 'partner_name');
        $this->assertNotEmpty($partnerErrors);
    }

    public function test_partner_khong_khop_2_chieu_tao_ra_loi(): void
    {
        // A → B, B → C (không phải B → A)
        $map = $this->doublesCategoryMap();
        $rows = [
            $this->makeDoublesRow('A', 'B', '0901111111', 'a@a.com', 2),
            $this->makeDoublesRow('B', 'C', '0902222222', 'b@b.com', 3),
            $this->makeDoublesRow('C', 'B', '0903333333', 'c@c.com', 4),
        ];
        $errors = $this->validator->validate($rows, $map);
        $partnerErrors = array_filter($errors, fn($e) => $e['field'] === 'partner_name');
        $this->assertNotEmpty($partnerErrors);
    }

    public function test_partner_khop_2_chieu_khong_co_loi(): void
    {
        // A → B và B → A: hợp lệ
        $map = $this->doublesCategoryMap();
        $rows = [
            $this->makeDoublesRow('A', 'B', '0901111111', 'a@a.com', 2),
            $this->makeDoublesRow('B', 'A', '0902222222', 'b@b.com', 3),
        ];
        $errors = $this->validator->validate($rows, $map);
        $partnerErrors = array_filter($errors, fn($e) => $e['field'] === 'partner_name');
        $this->assertEmpty($partnerErrors, 'Cap doi hop le A<->B khong duoc co loi: ' . json_encode($partnerErrors));
    }

    public function test_category_don_bo_qua_kiem_tra_doi(): void
    {
        // Category don không cần partner → không có lỗi partner_name
        $map = $this->singleCategoryMap();
        $rows = [
            $this->makeRow(['athlete_name' => 'A', 'category_name' => 'Nam Don', 'partner_name' => '', '_row_number' => 2]),
        ];
        $errors = $this->validator->validate($rows, $map);
        $partnerErrors = array_filter($errors, fn($e) => $e['field'] === 'partner_name');
        $this->assertEmpty($partnerErrors);
    }

    // === Helpers ===

    private function makeDoublesRow(
        string $name,
        string $partnerName,
        string $phone,
        string $email,
        int $rowNumber
    ): array {
        return [
            'athlete_name'  => $name,
            'email'         => $email,
            'phone'         => $phone,
            'category_name' => 'Nam Doi',
            'partner_name'  => $partnerName,
            '_row_number'   => $rowNumber,
        ];
    }

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
}
