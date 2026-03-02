<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubCompetitionMatch;
use App\Models\ClubCompetitionTeam;
use App\Models\User;
use App\Services\ClubCompetitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubCompetitionTest extends TestCase
{
    use RefreshDatabase;

    private Club $club;
    private User $admin;
    private User $member;
    private ClubActivity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->club = Club::factory()->create(['user_id' => $this->admin->id]);
        $this->member = User::factory()->create();

        $this->club->members()->attach($this->admin->id, ['role' => 'creator']);
        $this->club->members()->attach($this->member->id, ['role' => 'member']);

        $this->activity = ClubActivity::factory()->competition()->create([
            'club_id' => $this->club->id,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_management_can_add_team(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(
                route('clubs.activities.competition.add-team', [$this->club, $this->activity]),
                ['name' => 'Team Alpha']
            );

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('club_competition_teams', [
            'club_activity_id' => $this->activity->id,
            'name' => 'Team Alpha',
            'status' => 'active',
        ]);
    }

    public function test_member_cannot_add_team(): void
    {
        $response = $this->actingAs($this->member)
            ->postJson(
                route('clubs.activities.competition.add-team', [$this->club, $this->activity]),
                ['name' => 'Team Beta']
            );

        $response->assertForbidden();
    }

    public function test_management_can_generate_schedule(): void
    {
        ClubCompetitionTeam::factory()->count(4)->create([
            'club_activity_id' => $this->activity->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(
                route('clubs.activities.competition.generate-schedule', [$this->club, $this->activity]),
                ['format' => 'round_robin']
            );

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertGreaterThan(0, $this->activity->competitionMatches()->count());
    }

    public function test_management_can_save_score(): void
    {
        ClubCompetitionTeam::factory()->count(2)->create([
            'club_activity_id' => $this->activity->id,
        ]);

        $service = app(ClubCompetitionService::class);
        $service->generateRoundRobin($this->activity);
        $service->initializeStandings($this->activity);

        $match = $this->activity->competitionMatches()->first();

        $response = $this->actingAs($this->admin)
            ->putJson(
                route('clubs.activities.competition.save-score', [$this->club, $this->activity, $match]),
                ['home_score' => 11, 'away_score' => 7]
            );

        $response->assertOk()
            ->assertJson(['success' => true]);

        $match->refresh();
        $this->assertEquals('completed', $match->status);
        $this->assertEquals(11, $match->home_score);
    }

    public function test_standings_endpoint_returns_ranked_data(): void
    {
        ClubCompetitionTeam::factory()->count(3)->create([
            'club_activity_id' => $this->activity->id,
        ]);

        $service = app(ClubCompetitionService::class);
        $service->generateRoundRobin($this->activity);
        $service->initializeStandings($this->activity);

        $response = $this->actingAs($this->member)
            ->getJson(route('clubs.activities.competition.standings', [$this->club, $this->activity]));

        $response->assertOk()
            ->assertJsonCount(3, 'standings')
            ->assertJsonStructure(['standings' => [['played', 'wins', 'points', 'rank']]]);
    }
}
