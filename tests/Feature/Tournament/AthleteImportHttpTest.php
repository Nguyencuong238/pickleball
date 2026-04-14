<?php

namespace Tests\Feature\Tournament;

use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Feature tests HTTP cho TournamentAthleteImportTrait:
 * authorization, mime validation, empty file, oversized file, hard errors, happy path, download template.
 */
class AthleteImportHttpTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $stranger;
    private Tournament $tournament;
    private TournamentCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner    = User::factory()->create();
        $this->stranger = User::factory()->create();

        $this->tournament = Tournament::create([
            'user_id'          => $this->owner->id,
            'name'             => 'Giai HTTP Test',
            'start_date'       => now()->toDateString(),
            'price'            => 0,
            'max_participants' => 100,
        ]);

        $this->category = TournamentCategory::create([
            'tournament_id' => $this->tournament->id,
            'category_name' => 'Nam Don',
            'category_type' => 'single_men',
        ]);
    }

    // === POST athletes/import ===

    public function test_import_unauthorized_user_tra_ve_403(): void
    {
        $file = $this->makeXlsxUpload([
            ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'],
            ['A', 'a@a.com', '0901111111', 'Nam Don', ''],
        ]);

        $this->actingAs($this->stranger)
            ->postJson(
                route('tournament-manage.athletes.import', $this->tournament),
                ['file' => $file]
            )
            ->assertStatus(403);
    }

    public function test_import_file_khong_phai_xlsx_tra_ve_422(): void
    {
        $file = UploadedFile::fake()->create('athletes.csv', 10, 'text/csv');

        $this->actingAs($this->owner)
            ->postJson(
                route('tournament-manage.athletes.import', $this->tournament),
                ['file' => $file]
            )
            ->assertStatus(422);
    }

    public function test_import_file_chi_co_header_tra_ve_422(): void
    {
        $file = $this->makeXlsxUpload([
            ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'],
        ]);

        $this->actingAs($this->owner)
            ->postJson(
                route('tournament-manage.athletes.import', $this->tournament),
                ['file' => $file]
            )
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'File không có dữ liệu.']);
    }

    public function test_import_file_co_hard_error_tra_ve_422_voi_errors(): void
    {
        $file = $this->makeXlsxUpload([
            ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'],
            ['', 'not-email', '', 'Nam Don', ''], // nhiều lỗi
        ]);

        $this->actingAs($this->owner)
            ->postJson(
                route('tournament-manage.athletes.import', $this->tournament),
                ['file' => $file]
            )
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_import_file_hop_le_tra_ve_200_voi_created_count(): void
    {
        $file = $this->makeXlsxUpload([
            ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'],
            ['Nguyen Van A', 'a@a.com', '0901111111', 'Nam Don', ''],
            ['Nguyen Van B', 'b@b.com', '0902222222', 'Nam Don', ''],
        ]);

        $this->actingAs($this->owner)
            ->postJson(
                route('tournament-manage.athletes.import', $this->tournament),
                ['file' => $file]
            )
            ->assertStatus(200)
            ->assertJsonFragment(['created' => 2]);
    }

    // === GET athletes/import-template ===

    public function test_template_unauthorized_tra_ve_403(): void
    {
        $this->actingAs($this->stranger)
            ->get(route('tournament-manage.athletes.importTemplate', $this->tournament))
            ->assertStatus(403);
    }

    public function test_template_khong_co_category_tra_ve_400(): void
    {
        // Xoá category để giải không có hạng mục nào
        $this->category->delete();

        $this->actingAs($this->owner)
            ->getJson(route('tournament-manage.athletes.importTemplate', $this->tournament))
            ->assertStatus(400)
            ->assertJsonFragment(['message' => 'Giải đấu chưa có hạng mục nào.']);
    }

    public function test_template_owner_co_category_tra_ve_xlsx(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('tournament-manage.athletes.importTemplate', $this->tournament));

        $response->assertStatus(200);
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    // === Helper ===

    /**
     * Tạo UploadedFile xlsx từ mảng 2 chiều dữ liệu.
     *
     * @param array<int, array<int, string|null>> $data
     */
    private function makeXlsxUpload(array $data): UploadedFile
    {
        $path = sys_get_temp_dir() . '/test_import_' . uniqid() . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($col . ($rowIndex + 1), $value);
            }
        }
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'athletes.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true // test mode
        );
    }
}
