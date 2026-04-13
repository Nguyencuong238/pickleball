<?php

namespace Tests\Feature\Tournament;

use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\MatchModel;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\User;
use App\Services\Tournament\TournamentStandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: bug cũ tăng matches_played mỗi lần gọi update (không idempotent).
 * Service mới phải rebuild từ matches → an toàn với double-submit.
 */
class RankingsIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private TournamentStandingService $service;
    private Tournament $tournament;
    private Group $group;
    private TournamentAthlete $a;
    private TournamentAthlete $b;
    private TournamentAthlete $c;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TournamentStandingService::class);

        $user = User::factory()->create();
        $this->tournament = Tournament::create([
            'user_id' => $user->id,
            'name' => 'Test Tournament',
            'start_date' => now()->toDateString(),
            'price' => 0,
            'max_participants' => 32,
        ]);
        $this->group = Group::create([
            'tournament_id' => $this->tournament->id,
            'group_name' => 'Bảng Test',
            'max_participants' => 3,
            'advancing_count' => 2,
        ]);

        $this->a = $this->makeAthlete($user->id, 'Pair A');
        $this->b = $this->makeAthlete($user->id, 'Pair B');
        $this->c = $this->makeAthlete($user->id, 'Pair C');
    }

    private function makeAthlete(int $userId, string $name): TournamentAthlete
    {
        return TournamentAthlete::create([
            'tournament_id' => $this->tournament->id,
            'user_id' => $userId,
            'athlete_name' => $name,
        ]);
    }

    private function makeCompletedMatch(int $a1, int $a2, int $setsA1, int $setsA2): MatchModel
    {
        $scores = [];
        for ($i = 0; $i < max($setsA1, $setsA2); $i++) {
            $scores[] = [
                'set' => $i + 1,
                'athlete1' => $i < $setsA1 ? 11 : 5,
                'athlete2' => $i < $setsA2 ? 11 : 5,
            ];
        }
        return MatchModel::create([
            'tournament_id' => $this->tournament->id,
            'group_id' => $this->group->id,
            'athlete1_id' => $a1,
            'athlete2_id' => $a2,
            'status' => 'completed',
            'match_date' => now()->toDateString(),
            'set_scores' => $scores,
        ]);
    }

    public function test_recalculate_idempotent_multiple_calls(): void
    {
        $this->makeCompletedMatch($this->a->id, $this->b->id, 2, 1);

        $this->service->recalculateGroupStandings($this->group->id);
        $first = GroupStanding::where('group_id', $this->group->id)
            ->where('athlete_id', $this->a->id)->first();

        for ($i = 0; $i < 5; $i++) {
            $this->service->recalculateGroupStandings($this->group->id);
        }
        $after = GroupStanding::where('group_id', $this->group->id)
            ->where('athlete_id', $this->a->id)->first();

        $this->assertSame(1, $after->matches_played);
        $this->assertSame(1, $after->matches_won);
        $this->assertSame(3, $after->points);
        $this->assertSame($first->matches_played, $after->matches_played);
        $this->assertSame($first->points, $after->points);
    }

    public function test_full_round_robin_three_pairs_matches_played_equals_2(): void
    {
        $this->makeCompletedMatch($this->a->id, $this->b->id, 2, 0);
        $this->makeCompletedMatch($this->a->id, $this->c->id, 2, 1);
        $this->makeCompletedMatch($this->b->id, $this->c->id, 1, 2);

        $this->service->recalculateGroupStandings($this->group->id);

        $standings = GroupStanding::where('group_id', $this->group->id)->get();
        foreach ($standings as $s) {
            $this->assertSame(2, $s->matches_played, "athlete {$s->athlete_id}");
        }

        $aRow = $standings->firstWhere('athlete_id', $this->a->id);
        $this->assertSame(2, $aRow->matches_won);
        $this->assertSame(6, $aRow->points);
    }

    public function test_recompute_restores_dirty_inflated_row(): void
    {
        $this->makeCompletedMatch($this->a->id, $this->b->id, 2, 0);

        GroupStanding::create([
            'group_id' => $this->group->id,
            'athlete_id' => $this->a->id,
            'rank_position' => 0,
            'matches_played' => 99,
            'matches_won' => 50,
            'matches_lost' => 49,
            'matches_drawn' => 0,
            'points' => 500,
            'sets_won' => 0, 'sets_lost' => 0, 'sets_differential' => 0,
            'games_won' => 0, 'games_lost' => 0, 'games_differential' => 0,
        ]);

        $this->service->recalculateGroupStandings($this->group->id);

        $row = GroupStanding::where('group_id', $this->group->id)
            ->where('athlete_id', $this->a->id)->first();

        $this->assertSame(1, $row->matches_played);
        $this->assertSame(1, $row->matches_won);
        $this->assertSame(3, $row->points);
    }

    public function test_score_update_replaces_previous_result(): void
    {
        $match = $this->makeCompletedMatch($this->a->id, $this->b->id, 2, 0);
        $this->service->recalculateGroupStandings($this->group->id);

        $match->update([
            'set_scores' => [
                ['set' => 1, 'athlete1' => 5, 'athlete2' => 11],
                ['set' => 2, 'athlete1' => 5, 'athlete2' => 11],
            ],
        ]);
        $this->service->recalculateGroupStandings($this->group->id);

        $aRow = GroupStanding::where('group_id', $this->group->id)
            ->where('athlete_id', $this->a->id)->first();
        $bRow = GroupStanding::where('group_id', $this->group->id)
            ->where('athlete_id', $this->b->id)->first();

        $this->assertSame(1, $aRow->matches_played);
        $this->assertSame(0, $aRow->matches_won);
        $this->assertSame(1, $aRow->matches_lost);
        $this->assertSame(0, $aRow->points);

        $this->assertSame(1, $bRow->matches_played);
        $this->assertSame(1, $bRow->matches_won);
        $this->assertSame(3, $bRow->points);
    }
}
