<?php

namespace Tests\Unit\Imports;

use App\Imports\TournamentAthletesImporter;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Unit tests cho TournamentAthletesImporter::parse().
 * Tạo file xlsx tạm trong setUp, xoá sau mỗi test.
 */
class TournamentAthletesImporterTest extends TestCase
{
    private TournamentAthletesImporter $importer;
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importer = new TournamentAthletesImporter();
        $this->tempFile = sys_get_temp_dir() . '/athlete_import_test_' . uniqid() . '.xlsx';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
        parent::tearDown();
    }

    public function test_parse_file_hop_le_tra_ve_dung_so_row(): void
    {
        $this->createXlsx([
            ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'],
            ['Nguyen Van A', 'a@a.com', '0901111111', 'Nam Don', ''],
            ['Nguyen Van B', 'b@b.com', '0902222222', 'Nu Don', ''],
            ['Nguyen Van C', 'c@c.com', '0903333333', 'Nam Don', ''],
        ]);

        $rows = $this->importer->parse($this->tempFile);

        $this->assertCount(3, $rows);
    }

    public function test_parse_tra_ve_dung_gia_tri_cac_truong(): void
    {
        $this->createXlsx([
            ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'],
            ['Nguyen Van A', 'a@a.com', '0901111111', 'Nam Don', 'partner x'],
        ]);

        $rows = $this->importer->parse($this->tempFile);

        $this->assertSame('Nguyen Van A', $rows[0]['athlete_name']);
        $this->assertSame('a@a.com', $rows[0]['email']);
        $this->assertSame('0901111111', $rows[0]['phone']);
        $this->assertSame('Nam Don', $rows[0]['category_name']);
        $this->assertSame('partner x', $rows[0]['partner_name']);
    }

    public function test_parse_dinh_dang_row_number_chinh_xac(): void
    {
        // Dòng 1 = header, dòng 2 = data đầu tiên → _row_number phải là 2
        $this->createXlsx([
            ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'],
            ['A', 'a@a.com', '0901111111', 'Nam Don', ''],
            ['B', 'b@b.com', '0902222222', 'Nam Don', ''],
        ]);

        $rows = $this->importer->parse($this->tempFile);

        $this->assertSame(2, $rows[0]['_row_number']);
        $this->assertSame(3, $rows[1]['_row_number']);
    }

    public function test_parse_chi_co_header_tra_ve_mang_rong(): void
    {
        $this->createXlsx([
            ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'],
        ]);

        $rows = $this->importer->parse($this->tempFile);

        $this->assertEmpty($rows);
    }

    public function test_parse_bo_qua_row_rong_o_giua_file(): void
    {
        $this->createXlsx([
            ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'],
            ['A', 'a@a.com', '0901111111', 'Nam Don', ''],
            [null, null, null, null, null], // row rỗng hoàn toàn
            ['B', 'b@b.com', '0902222222', 'Nam Don', ''],
        ]);

        $rows = $this->importer->parse($this->tempFile);

        $this->assertCount(2, $rows);
        $this->assertSame('A', $rows[0]['athlete_name']);
        $this->assertSame('B', $rows[1]['athlete_name']);
    }

    public function test_parse_thieu_cot_bat_buoc_throw_exception(): void
    {
        // Thiếu cột 'phone'
        $this->createXlsx([
            ['athlete_name', 'email', 'category_name', 'partner_name'],
            ['A', 'a@a.com', 'Nam Don', ''],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/phone/');

        $this->importer->parse($this->tempFile);
    }

    public function test_parse_header_case_insensitive(): void
    {
        // Header viết hoa vẫn map được
        $this->createXlsx([
            ['Athlete_Name', 'EMAIL', 'Phone', 'Category_Name', 'Partner_Name'],
            ['A', 'a@a.com', '0901111111', 'Nam Don', ''],
        ]);

        $rows = $this->importer->parse($this->tempFile);

        $this->assertCount(1, $rows);
        $this->assertSame('A', $rows[0]['athlete_name']);
    }

    // === Helper ===

    /**
     * Tạo file xlsx từ mảng 2 chiều (hàng đầu là header).
     *
     * @param array<int, array<int, string|null>> $data
     */
    private function createXlsx(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($col . ($rowIndex + 1), $value);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->tempFile);
    }
}
