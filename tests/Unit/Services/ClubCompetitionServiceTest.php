<?php

namespace Tests\Unit\Services;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubCompetitionTeam;
use App\Models\User;
use App\Services\ClubCompetitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ClubCompetitionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClubCompetitionService $service;
    private Club $club;
    private ClubActivity $activity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClubCompetitionService();

        $creator = User::factory()->create();
        $this->club = Club::factory()->create(['user_id' => $creator->id]);
        $this->activity = ClubActivity::factory()->competition()->create([
            'club_id' => $this->club->id,
            'created_by' => $creator->id,
        ]);
    }

    public function test_generate_round_robin_creates_correct_matches(): void
    {
        $this->createTeams(4);

        $this->service->generateRoundRobin($this->activity);

        // 4 teams = 3 rounds, 2 matches per round = 6 total
        $this->assertEquals(6, $this->activity->competitionMatches()->count());
    }

    public function test_generate_round_robin_handles_odd_teams(): void
    {
        $this->createTeams(3);

        $this->service->generateRoundRobin($this->activity);

        // 3 teams with bye: 3 rounds, 1 match per round = 3 matches
        $this->assertEquals(3, $this->activity->competitionMatches()->count());
    }

    public function test_generate_round_robin_requires_minimum_teams(): void
    {
        $this->createTeams(1);

        $this->expectException(InvalidArgumentException::class);
        $this->service->generateRoundRobin($this->activity);
    }

    public function test_save_match_score_updates_standings(): void
    {
        $this->createTeams(2);
        $this->service->generateRoundRobin($this->activity);
        $this->service->initializeStandings($this->activity);

        $match = $this->activity->competitionMatches()->first();
        $this->service->saveMatchScore($match, 3, 1);

        $match->refresh();
        $this->assertEquals('completed', $match->status);
        $this->assertEquals($match->home_team_id, $match->winner_team_id);

        // Check standings updated
        $winnerStanding = $this->activity->competitionStandings()
            ->where('club_competition_team_id', $match->home_team_id)
            ->first();
        $this->assertEquals(1, $winnerStanding->wins);
        $this->assertEquals(3, $winnerStanding->points);
    }

    public function test_recalculate_standings_correct_ranking(): void
    {
        $this->createTeams(3);
        $this->service->generateRoundRobin($this->activity);
        $this->service->initializeStandings($this->activity);

        $matches = $this->activity->competitionMatches()->get();

        // Complete all matches with varying scores
        foreach ($matches as $i => $match) {
            $homeScore = ($i % 2 === 0) ? 3 : 1;
            $awayScore = ($i % 2 === 0) ? 1 : 3;
            $this->service->saveMatchScore($match, $homeScore, $awayScore);
        }

        $standings = $this->activity->competitionStandings()->orderBy('rank')->get();

        // Rank 1 should have highest points
        $this->assertEquals(1, $standings->first()->rank);
        $this->assertGreaterThanOrEqual(
            $standings->last()->points,
            $standings->first()->points
        );
    }

    public function test_draw_match_awards_draw_points(): void
    {
        $this->createTeams(2);
        $this->service->generateRoundRobin($this->activity);
        $this->service->initializeStandings($this->activity);

        $match = $this->activity->competitionMatches()->first();
        $this->service->saveMatchScore($match, 2, 2);

        $match->refresh();
        $this->assertNull($match->winner_team_id);

        // Both teams get 1 draw, 1 point each
        $homeStanding = $this->activity->competitionStandings()
            ->where('club_competition_team_id', $match->home_team_id)->first();
        $awayStanding = $this->activity->competitionStandings()
            ->where('club_competition_team_id', $match->away_team_id)->first();

        $this->assertEquals(1, $homeStanding->draws);
        $this->assertEquals(1, $awayStanding->draws);
        $this->assertEquals(1, $homeStanding->points);
        $this->assertEquals(1, $awayStanding->points);
    }

    public function test_initialize_standings_creates_rows(): void
    {
        $this->createTeams(4);
        $this->service->initializeStandings($this->activity);

        $this->assertEquals(4, $this->activity->competitionStandings()->count());

        $standing = $this->activity->competitionStandings()->first();
        $this->assertEquals(0, $standing->played);
        $this->assertEquals(0, $standing->wins);
        $this->assertEquals(0, $standing->points);
    }

    public function test_save_completed_match_throws(): void
    {
        $this->createTeams(2);
        $this->service->generateRoundRobin($this->activity);
        $this->service->initializeStandings($this->activity);

        $match = $this->activity->competitionMatches()->first();
        $this->service->saveMatchScore($match, 3, 1);

        $this->expectException(InvalidArgumentException::class);
        $this->service->saveMatchScore($match->fresh(), 2, 2);
    }

    private function createTeams(int $count): void
    {
        ClubCompetitionTeam::factory()->count($count)->create([
            'club_activity_id' => $this->activity->id,
        ]);
    }
}
