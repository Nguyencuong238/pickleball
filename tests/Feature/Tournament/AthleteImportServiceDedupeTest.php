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
 * Feature tests cho AthleteImportService: dedupe, user resolution, idempotency.
 */
class AthleteImportServiceDedupeTest extends TestCase
{
    use RefreshDatabase;

    private AthleteImportService $service;
    private Tournament $tournament;
    private TournamentCategory $singleCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AthleteImportService();

        $owner = User::factory()->create();
        $this->tournament = Tournament::create([
            'user_id'          => $owner->id,
            'name'             => 'Giai Test Dedupe',
            'start_date'       => now()->toDateString(),
            'price'            => 0,
            'max_participants' => 100,
        ]);

        $this->singleCategory = TournamentCategory::create([
            'tournament_id' => $this->tournament->id,
            'category_name' => 'Nam Don',
            'category_type' => 'single_men',
        ]);
    }

    public function test_trung_phone_voi_vdv_da_co_trong_giai_thi_skip(): void
    {
        $existingUser = User::factory()->create(['phone' => '0901111111']);
        TournamentAthlete::create([
            'tournament_id'  => $this->tournament->id,
            'category_id'    => $this->singleCategory->id,
            'athlete_name'   => 'Cu Dan',
            'email'          => 'cu@cu.com',
            'phone'          => '0901111111',
            'user_id'        => $existingUser->id,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $rows = [
            $this->makeRow('A', 'a@a.com', '0901111111', 2), // phone trùng → skip
            $this->makeRow('B', 'b@b.com', '0902222222', 3), // phone mới → tạo
        ];
        $result = $this->service->execute($rows, $this->tournament);

        $this->assertSame(1, $result['created']);
        $this->assertCount(1, $result['skipped']);
        $this->assertSame(2, $result['skipped'][0]['row']);
    }

    public function test_tu_dong_tao_user_khi_chua_co_phone_email(): void
    {
        $rows = [$this->makeRow('Moi', 'moi@moi.com', '0909999999', 2)];
        $this->service->execute($rows, $this->tournament);

        $this->assertDatabaseHas('users', ['phone' => '0909999999']);
    }

    public function test_tai_su_dung_user_neu_phone_trung(): void
    {
        $existingUser = User::factory()->create(['phone' => '0908888888']);
        $userCountBefore = User::count();

        $rows = [$this->makeRow('Tai Su Dung', 'taisd@moi.com', '0908888888', 2)];
        $this->service->execute($rows, $this->tournament);

        $this->assertSame($userCountBefore, User::count());

        $athlete = TournamentAthlete::where('athlete_name', 'Tai Su Dung')->first();
        $this->assertSame($existingUser->id, $athlete->user_id);
    }

    public function test_reimport_cung_rows_thi_tat_ca_skip(): void
    {
        $rows = [
            $this->makeRow('VDV 1', 'vdv1@test.com', '0901000001', 2),
            $this->makeRow('VDV 2', 'vdv2@test.com', '0901000002', 3),
            $this->makeRow('VDV 3', 'vdv3@test.com', '0901000003', 4),
        ];

        $result1 = $this->service->execute($rows, $this->tournament);
        $this->assertSame(3, $result1['created']);

        $result2 = $this->service->execute($rows, $this->tournament);
        $this->assertSame(0, $result2['created']);
        $this->assertCount(3, $result2['skipped']);
        $this->assertDatabaseCount('tournament_athletes', 3);
    }

    // === Helper ===

    private function makeRow(string $name, string $email, string $phone, int $rowNumber): array
    {
        return [
            'athlete_name'  => $name,
            'email'         => $email,
            'phone'         => $phone,
            'category_name' => 'Nam Don',
            'partner_name'  => '',
            '_row_number'   => $rowNumber,
        ];
    }
}
