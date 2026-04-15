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
 * Cover toàn bộ 5 tier tiebreaker spec:
 * tier 1 points, tier 2 games_differential, tier 3 H2H (chỉ 2 đội),
 * tier 4 games_lost ASC, tier 5 manual_rank_override ASC NULLS LAST.
 */
class TiebreakerRankingTest extends TestCase
{
    use RefreshDatabase;

    private TournamentStandingService $service;
    private User $user;
    private Tournament $tournament;
    private Group $group;
    private TournamentAthlete $a;
    private TournamentAthlete $b;
    private TournamentAthlete $c;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TournamentStandingService::class);

        $this->user = User::factory()->create();
        $this->tournament = Tournament::create([
            'user_id' => $this->user->id,
            'name' => 'Tiebreaker Tournament',
            'start_date' => now()->toDateString(),
            'price' => 0,
            'max_participants' => 32,
        ]);
        $this->group = Group::create([
            'tournament_id' => $this->tournament->id,
            'group_name' => 'Bảng Tiebreaker',
            'max_participants' => 4,
            'advancing_count' => 2,
        ]);

        $this->a = $this->makeAthlete('Team A');
        $this->b = $this->makeAthlete('Team B');
        $this->c = $this->makeAthlete('Team C');
    }

    private function makeAthlete(string $name): TournamentAthlete
    {
        return TournamentAthlete::create([
            'tournament_id' => $this->tournament->id,
            'user_id' => $this->user->id,
            'athlete_name' => $name,
        ]);
    }

    /**
     * Tạo match completed với set scores tùy ý.
     * @param array<int,array{0:int,1:int}> $sets each [a1Score, a2Score]
     */
    private function makeMatch(int $a1, int $a2, array $sets, bool $useScoreSuffix = false): MatchModel
    {
        $payload = [];
        foreach ($sets as $i => $pair) {
            if ($useScoreSuffix) {
                $payload[] = [
                    'set' => $i + 1,
                    'athlete1_score' => $pair[0],
                    'athlete2_score' => $pair[1],
                ];
            } else {
                $payload[] = [
                    'set' => $i + 1,
                    'athlete1' => $pair[0],
                    'athlete2' => $pair[1],
                ];
            }
        }
        return MatchModel::create([
            'tournament_id' => $this->tournament->id,
            'group_id' => $this->group->id,
            'athlete1_id' => $a1,
            'athlete2_id' => $a2,
            'status' => 'completed',
            'match_date' => now()->toDateString(),
            'set_scores' => $payload,
        ]);
    }

    private function rankOf(int $athleteId): int
    {
        return (int) GroupStanding::where('group_id', $this->group->id)
            ->where('athlete_id', $athleteId)
            ->value('rank_position');
    }

    /** Test A — games_differential as tier 2 */
    public function test_tier2_games_differential_breaks_points_tie(): void
    {
        // A và B đều thắng 1 trận vs C nhưng chênh lệch điểm thô khác nhau.
        $this->makeMatch($this->a->id, $this->c->id, [[11, 5], [11, 5]]);
        $this->makeMatch($this->b->id, $this->c->id, [[11, 9], [11, 9]]);

        $this->service->recalculateGroupStandings($this->group->id);

        // A: +12 games_diff; B: +4 games_diff; C: -16
        $this->assertSame(1, $this->rankOf($this->a->id), 'A phải đứng nhất do games_diff cao hơn');
        $this->assertSame(2, $this->rankOf($this->b->id));
        $this->assertSame(3, $this->rankOf($this->c->id));

        $aRow = GroupStanding::where('group_id', $this->group->id)->where('athlete_id', $this->a->id)->first();
        $this->assertSame(22, $aRow->games_won);
        $this->assertSame(10, $aRow->games_lost);
        $this->assertSame(12, $aRow->games_differential);
    }

    /** Test B — H2H 2-team tier 3 */
    public function test_tier3_h2h_two_team_resolves_tie(): void
    {
        $d = $this->makeAthlete('Team D');

        // A vs B: A thắng 11-9, 11-9 → A:+4 games_diff
        $this->makeMatch($this->a->id, $this->b->id, [[11, 9], [11, 9]]);
        // A vs D: A thua 9-11, 9-11 → A:-4
        $this->makeMatch($this->a->id, $d->id, [[9, 11], [9, 11]]);
        // B vs C: B thắng 11-9, 11-9 → B:+4
        $this->makeMatch($this->b->id, $this->c->id, [[11, 9], [11, 9]]);

        $this->service->recalculateGroupStandings($this->group->id);

        // A: 1W 1L 3pts games_diff=0; B: 1W 1L 3pts games_diff=0 → tied tier 1+2
        // H2H: A đã thắng B trong trận đối đầu → A rank trước B
        $aRank = $this->rankOf($this->a->id);
        $bRank = $this->rankOf($this->b->id);
        $this->assertLessThan($bRank, $aRank, 'A phải xếp trên B nhờ thắng H2H');
    }

    /** Test C — H2H bị skip cho bucket 3+ team, fallback tier 4 games_lost */
    public function test_tier4_games_lost_when_three_team_bucket_skips_h2h(): void
    {
        $d = $this->makeAthlete('Team D');

        // Match dữ liệu chính xác từ plan phase-05 Group C
        $this->makeMatch($this->a->id, $this->b->id, [[11, 5], [11, 5]]);
        $this->makeMatch($this->b->id, $this->c->id, [[11, 3], [11, 2]]);
        $this->makeMatch($this->c->id, $this->a->id, [[11, 8], [11, 7]]);
        $this->makeMatch($this->a->id, $d->id, [[11, 8], [11, 7]]);
        $this->makeMatch($this->b->id, $d->id, [[11, 8], [11, 7]]);
        $this->makeMatch($this->c->id, $d->id, [[11, 0], [11, 0]]);

        $this->service->recalculateGroupStandings($this->group->id);

        // A=6/+12/games_lost=47, B=6/+12/games_lost=42, C=6/+12/games_lost=37
        // 3-team bucket → H2H bỏ qua → games_lost ASC: C(37) < B(42) < A(47)
        $this->assertSame(1, $this->rankOf($this->c->id), 'C phải nhất nhờ games_lost thấp nhất');
        $this->assertSame(2, $this->rankOf($this->b->id));
        $this->assertSame(3, $this->rankOf($this->a->id));
        $this->assertSame(4, $this->rankOf($d->id));

        // Regression guard: nếu code áp H2H sai thì A sẽ leo lên trước B (A đã thắng B)
        $this->assertNotSame(1, $this->rankOf($this->a->id), 'H2H không được áp cho 3-team bucket');
    }

    /** Test D — manual_rank_override tier 5 */
    public function test_tier5_manual_override_ranks_first(): void
    {
        // 1 trận hòa giữa A và B (1 set mỗi bên) → mọi field tied
        $this->makeMatch($this->a->id, $this->b->id, [[11, 5], [5, 11]]);
        $this->service->recalculateGroupStandings($this->group->id);

        $aRow = GroupStanding::where('group_id', $this->group->id)->where('athlete_id', $this->a->id)->first();
        $bRow = GroupStanding::where('group_id', $this->group->id)->where('athlete_id', $this->b->id)->first();
        $this->assertSame($aRow->points, $bRow->points);
        $this->assertSame($aRow->games_differential, $bRow->games_differential);
        $this->assertSame($aRow->games_lost, $bRow->games_lost);

        // BTC bốc thăm: B = 1, A = 2
        $aRow->update(['manual_rank_override' => 2]);
        $bRow->update(['manual_rank_override' => 1]);
        $this->service->recalculateGroupRankings($this->group->id);

        $this->assertSame(1, $this->rankOf($this->b->id), 'B có override=1 phải đứng nhất');
        $this->assertSame(2, $this->rankOf($this->a->id));
    }

    /** Test E — manual_rank_override partial: NULLS LAST */
    public function test_tier5_partial_override_nulls_last(): void
    {
        // 3 trận hòa → A, B, C tied trên mọi tier
        $this->makeMatch($this->a->id, $this->b->id, [[11, 5], [5, 11]]);
        $this->makeMatch($this->a->id, $this->c->id, [[11, 5], [5, 11]]);
        $this->makeMatch($this->b->id, $this->c->id, [[11, 5], [5, 11]]);

        $this->service->recalculateGroupStandings($this->group->id);

        // Chỉ A có override = 1
        GroupStanding::where('group_id', $this->group->id)
            ->where('athlete_id', $this->a->id)
            ->update(['manual_rank_override' => 1]);

        $this->service->recalculateGroupRankings($this->group->id);

        $this->assertSame(1, $this->rankOf($this->a->id), 'A có override=1 đứng đầu');
        $this->assertGreaterThan(1, $this->rankOf($this->b->id), 'B null override → sau A');
        $this->assertGreaterThan(1, $this->rankOf($this->c->id), 'C null override → sau A');
    }

    /** Test F — dual key shape athlete1_score */
    public function test_count_games_handles_athlete1_score_key_shape(): void
    {
        $this->makeMatch(
            $this->a->id,
            $this->b->id,
            [[11, 5], [11, 5]],
            useScoreSuffix: true
        );

        $this->service->recalculateGroupStandings($this->group->id);

        $aRow = GroupStanding::where('group_id', $this->group->id)
            ->where('athlete_id', $this->a->id)->first();
        $bRow = GroupStanding::where('group_id', $this->group->id)
            ->where('athlete_id', $this->b->id)->first();

        $this->assertSame(22, $aRow->games_won);
        $this->assertSame(10, $aRow->games_lost);
        $this->assertSame(12, $aRow->games_differential);

        $this->assertSame(10, $bRow->games_won);
        $this->assertSame(22, $bRow->games_lost);
        $this->assertSame(-12, $bRow->games_differential);
    }

    /** Test G — regression guard: dùng games_differential KHÔNG dùng sets_differential */
    public function test_uses_games_differential_not_sets_differential(): void
    {
        // A thắng C 2-0 sít sao: sets_diff=+2, games_diff=+4
        $this->makeMatch($this->a->id, $this->c->id, [[11, 9], [11, 9]]);
        // B thắng C 2-1 với chênh lệch lớn: sets_diff=+1, games_diff=+9
        $this->makeMatch($this->b->id, $this->c->id, [[11, 2], [2, 11], [11, 2]]);

        $this->service->recalculateGroupStandings($this->group->id);

        $aRow = GroupStanding::where('group_id', $this->group->id)->where('athlete_id', $this->a->id)->first();
        $bRow = GroupStanding::where('group_id', $this->group->id)->where('athlete_id', $this->b->id)->first();

        $this->assertSame(2, $aRow->sets_differential);
        $this->assertSame(4, $aRow->games_differential);
        $this->assertSame(1, $bRow->sets_differential);
        $this->assertSame(9, $bRow->games_differential);

        // Cả 2 cùng 3 pts. games_diff B=9 > A=4 → B rank trước A.
        // Nếu code dùng sets_diff thì sẽ flip sang A=1, B=2 → fail.
        $this->assertSame(1, $this->rankOf($this->b->id), 'B phải nhất nhờ games_diff cao hơn');
        $this->assertSame(2, $this->rankOf($this->a->id));
    }
}
