<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateRecurringMeetsTest extends TestCase
{
    use RefreshDatabase;

    private Club $club;
    private User $creator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creator = User::factory()->create();
        $this->club = Club::factory()->create(['user_id' => $this->creator->id]);
    }

    public function test_command_creates_instances_for_correct_day(): void
    {
        // Find next Monday from today
        $nextMonday = Carbon::today()->next(Carbon::MONDAY);

        $template = ClubActivity::factory()->recurring(Carbon::MONDAY)->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
            'activity_date' => $nextMonday->copy()->setTime(18, 0),
            'status' => 'upcoming',
        ]);

        $this->artisan('clubs:generate-recurring-meets', ['--days' => 14])
            ->assertSuccessful();

        // Should have created instances for Mondays within 14 days
        $instances = $template->children()->count();
        $this->assertGreaterThanOrEqual(1, $instances);

        // All instances should be on Monday
        $template->children->each(function ($child) {
            $this->assertEquals(Carbon::MONDAY, $child->activity_date->dayOfWeek);
        });
    }

    public function test_command_skips_existing_instances(): void
    {
        $nextMonday = Carbon::today()->next(Carbon::MONDAY);

        $template = ClubActivity::factory()->recurring(Carbon::MONDAY)->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
            'activity_date' => $nextMonday->copy()->setTime(18, 0),
            'status' => 'upcoming',
        ]);

        // Run twice
        $this->artisan('clubs:generate-recurring-meets', ['--days' => 14])
            ->assertSuccessful();
        $countAfterFirst = $template->children()->count();

        $this->artisan('clubs:generate-recurring-meets', ['--days' => 14])
            ->assertSuccessful();
        $countAfterSecond = $template->children()->count();

        // Idempotent: same count
        $this->assertEquals($countAfterFirst, $countAfterSecond);
    }

    public function test_command_ignores_cancelled_templates(): void
    {
        ClubActivity::factory()->recurring(Carbon::MONDAY)->cancelled()->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
            'activity_date' => Carbon::today()->next(Carbon::MONDAY)->setTime(18, 0),
        ]);

        $this->artisan('clubs:generate-recurring-meets', ['--days' => 14])
            ->assertSuccessful()
            ->expectsOutput('Khong co lich co dinh nao can xu ly.');
    }
}
