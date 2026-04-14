<?php

namespace Tests\Unit\Services\Tournament;

use App\Services\Tournament\AthleteRowNormalizer;
use Tests\TestCase;

/**
 * Unit tests cho AthleteRowNormalizer
 * Kiểm tra normalize phone và normalize toàn bộ row.
 */
class AthleteRowNormalizerTest extends TestCase
{
    private AthleteRowNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new AthleteRowNormalizer();
    }

    // === normalizePhone() ===

    public function test_normalizePhone_giu_nguyen_dinh_dang_0(): void
    {
        $this->assertSame('0901234567', $this->normalizer->normalizePhone('0901234567'));
    }

    public function test_normalizePhone_chuyen_prefix_84_viet_nam(): void
    {
        $this->assertSame('0901234567', $this->normalizer->normalizePhone('84901234567'));
    }

    public function test_normalizePhone_chuyen_prefix_cong_84(): void
    {
        $this->assertSame('0901234567', $this->normalizer->normalizePhone('+84901234567'));
    }

    public function test_normalizePhone_strip_ky_tu_la(): void
    {
        // Dấu gạch ngang, dấu cách, ngoặc đơn phải bị xoá
        $this->assertSame('0901234567', $this->normalizer->normalizePhone('090-123-4567'));
    }

    public function test_normalizePhone_strip_ky_tu_la_phan_tach_phuc_tap(): void
    {
        $this->assertSame('0901234567', $this->normalizer->normalizePhone('(090) 123 4567'));
    }

    public function test_normalizePhone_so_ngan_84_khong_du_chieu_dai(): void
    {
        // '841234' chỉ có 6 ký tự → không đủ 10 → giữ nguyên (không chuyển prefix)
        $this->assertSame('841234', $this->normalizer->normalizePhone('841234'));
    }

    public function test_normalizePhone_chuoi_rong(): void
    {
        $this->assertSame('', $this->normalizer->normalizePhone(''));
    }

    // === normalize() ===

    public function test_normalize_trim_ten_vdv(): void
    {
        $row = $this->makeRow(['athlete_name' => '  Nguyen Van A  ']);
        $result = $this->normalizer->normalize($row);
        $this->assertSame('Nguyen Van A', $result['athlete_name']);
    }

    public function test_normalize_lowercase_email(): void
    {
        $row = $this->makeRow(['email' => '  Test@EMAIL.com  ']);
        $result = $this->normalizer->normalize($row);
        $this->assertSame('test@email.com', $result['email']);
    }

    public function test_normalize_phone_qua_normalizePhone(): void
    {
        $row = $this->makeRow(['phone' => '+84901234567']);
        $result = $this->normalizer->normalize($row);
        $this->assertSame('0901234567', $result['phone']);
    }

    public function test_normalize_giu_nguyen_row_number(): void
    {
        $row = $this->makeRow(['_row_number' => 42]);
        $result = $this->normalizer->normalize($row);
        $this->assertSame(42, $result['_row_number']);
    }

    public function test_normalize_row_number_mac_dinh_la_0_khi_thieu(): void
    {
        $row = [
            'athlete_name'  => 'A',
            'email'         => 'a@a.com',
            'phone'         => '0900000000',
            'category_name' => 'Cat',
            'partner_name'  => '',
            // không có _row_number
        ];
        $result = $this->normalizer->normalize($row);
        $this->assertSame(0, $result['_row_number']);
    }

    public function test_normalize_tra_ve_day_du_cac_key(): void
    {
        $row = $this->makeRow();
        $result = $this->normalizer->normalize($row);
        $this->assertArrayHasKey('athlete_name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('phone', $result);
        $this->assertArrayHasKey('category_name', $result);
        $this->assertArrayHasKey('partner_name', $result);
        $this->assertArrayHasKey('_row_number', $result);
    }

    // === helpers ===

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
}
