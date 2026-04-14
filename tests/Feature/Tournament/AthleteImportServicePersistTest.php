<?php

namespace Tests\Feature\Tournament;

use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\TournamentCategory;
use App\Models\User;
use App\Services\Tournament\AthleteImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests cho AthleteImportService: happy path, hard error rollback, doubles partner linking.
 */
class AthleteImportServicePersistTest extends TestCase
{
    use RefreshDatabase;

    private AthleteImportService $service;
    private Tournament $tournament;
    private TournamentCategory $singleCategory;
    private TournamentCategory $doublesCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AthleteImportService();

        $owner = User::factory()->create();
        $this->tournament = Tournament::create([
            'user_id'          => $owner->id,
            'name'             => 'Giai Test Import',
            'start_date'       => now()->toDateString(),
            'price'            => 0,
            'max_participants' => 100,
        ]);

        $this->singleCategory = TournamentCategory::create([
            'tournament_id' => $this->tournament->id,
            'category_name' => 'Nam Don',
            'category_type' => 'single_men',
        ]);

        $this->doublesCategory = TournamentCategory::create([
            'tournament_id' => $this->tournament->id,
            'category_name' => 'Nam Doi',
            'category_type' => 'double_men',
        ]);
    }

    public function test_5_row_don_hop_le_tao_5_vdv(): void
    {
        $rows = $this->makeSingleRows(5);
        $result = $this->service->execute($rows, $this->tournament);

        $this->assertSame(5, $result['created']);
        $this->assertEmpty($result['skipped']);
        $this->assertEmpty($result['errors']);
        $this->assertDatabaseCount('tournament_athletes', 5);
    }

    public function test_cap_doi_tao_partner_id_bidirectional(): void
    {
        $rows = [
            $this->makeRow('A', 'a@a.com', '0901111111', 'Nam Doi', 'B', 2),
            $this->makeRow('B', 'b@b.com', '0902222222', 'Nam Doi', 'A', 3),
        ];
        $result = $this->service->execute($rows, $this->tournament);

        $this->assertSame(2, $result['created']);
        $this->assertEmpty($result['errors']);

        $athleteA = TournamentAthlete::where('athlete_name', 'A')->first();
        $athleteB = TournamentAthlete::where('athlete_name', 'B')->first();

        $this->assertNotNull($athleteA);
        $this->assertNotNull($athleteB);
        $this->assertSame($athleteB->id, $athleteA->partner_id);
        $this->assertSame($athleteA->id, $athleteB->partner_id);
    }

    public function test_hon_hop_don_va_doi_tao_dung_so_luong(): void
    {
        $rows = array_merge(
            $this->makeSingleRows(3),
            [
                $this->makeRow('D1', 'd1@d.com', '0911111111', 'Nam Doi', 'D2', 10),
                $this->makeRow('D2', 'd2@d.com', '0922222222', 'Nam Doi', 'D1', 11),
            ]
        );
        $result = $this->service->execute($rows, $this->tournament);

        $this->assertSame(5, $result['created']);
        $this->assertEmpty($result['errors']);

        $d1 = TournamentAthlete::where('athlete_name', 'D1')->first();
        $d2 = TournamentAthlete::where('athlete_name', 'D2')->first();
        $this->assertSame($d2->id, $d1->partner_id);
        $this->assertSame($d1->id, $d2->partner_id);
    }

    public function test_hard_error_rollback_khong_tao_bat_ky_row_nao(): void
    {
        $rows = [
            $this->makeRow('A', 'a@a.com', '0901111111', 'Nam Don', '', 2),
            $this->makeRow('B', 'not-email', '0902222222', 'Nam Don', '', 3),
        ];
        $result = $this->service->execute($rows, $this->tournament);

        $this->assertSame(0, $result['created']);
        $this->assertNotEmpty($result['errors']);
        $this->assertDatabaseCount('tournament_athletes', 0);
    }

    // === Helpers ===

    private function makeRow(
        string $name,
        string $email,
        string $phone,
        string $categoryName,
        string $partnerName,
        int $rowNumber
    ): array {
        return [
            'athlete_name'  => $name,
            'email'         => $email,
            'phone'         => $phone,
            'category_name' => $categoryName,
            'partner_name'  => $partnerName,
            '_row_number'   => $rowNumber,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function makeSingleRows(int $count): array
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = $this->makeRow(
                "VDV {$i}",
                "vdv{$i}@test.com",
                '090' . str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                'Nam Don',
                '',
                $i + 1
            );
        }
        return $rows;
    }
}
