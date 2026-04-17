<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Services\Tournament\TournamentStandingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repair stale group_standings:
 *  - Xoa standings co group_id khong khop voi athlete.group_id hien tai (stale sau khi bi re-draw).
 *  - Tao standing empty cho athletes dang o trong group ma thieu standing row.
 *  - Goi recalculateGroupStandings de build lai stats tu matches.
 *
 * Dung sau khi deploy fix ranking mismatch de heal cac tournament data cu.
 */
class RepairTournamentStandings extends Command
{
    protected $signature = 'tournament:repair-standings
        {--tournament= : Chi xu ly tournament id nay}
        {--category= : Chi xu ly category id nay (phai dung kem --tournament)}
        {--dry-run : Chi bao cao, khong ghi DB}';

    protected $description = 'Heal stale/missing group_standings rows cho cac tournament co du lieu bi bug truoc khi deploy fix.';

    public function handle(TournamentStandingService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $tournaments = $this->resolveTournaments();

        if ($tournaments->isEmpty()) {
            $this->warn('Khong co tournament nao de xu ly.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Quet %d tournament(s)%s', $tournaments->count(), $dryRun ? ' (dry-run)' : ''));

        $totalStaleDeleted = 0;
        $totalMissingCreated = 0;
        $totalGroupsRecalc = 0;
        $errors = 0;

        foreach ($tournaments as $tournament) {
            try {
                $result = $this->repairTournament($tournament, $service, $dryRun);
                $totalStaleDeleted += $result['stale'];
                $totalMissingCreated += $result['missing'];
                $totalGroupsRecalc += $result['recalc'];
            } catch (\Throwable $e) {
                $errors++;
                $this->error(sprintf('Tournament %d loi: %s', $tournament->id, $e->getMessage()));
            }
        }

        $this->info(sprintf(
            'Hoan tat. Stale xoa: %d, missing tao moi: %d, groups recalc: %d, errors: %d%s',
            $totalStaleDeleted,
            $totalMissingCreated,
            $totalGroupsRecalc,
            $errors,
            $dryRun ? ' (dry-run, khong luu)' : ''
        ));

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function resolveTournaments(): \Illuminate\Support\Collection
    {
        $query = Tournament::query();
        if ($tid = $this->option('tournament')) {
            $query->where('id', (int) $tid);
        }
        return $query->get(['id', 'slug']);
    }

    private function repairTournament(Tournament $tournament, TournamentStandingService $service, bool $dryRun): array
    {
        $categoryFilter = $this->option('category');
        $groupQuery = Group::where('tournament_id', $tournament->id);
        if ($categoryFilter) {
            $groupQuery->where('category_id', (int) $categoryFilter);
        }
        $groups = $groupQuery->get(['id', 'category_id', 'group_name']);

        if ($groups->isEmpty()) {
            return ['stale' => 0, 'missing' => 0, 'recalc' => 0];
        }

        $groupIds = $groups->pluck('id');
        $groupById = $groups->keyBy('id');

        $stale = $this->findStaleStandings($groupIds);
        $missing = $this->findMissingStandings($groups);

        if ($stale->isEmpty() && $missing->isEmpty()) {
            return ['stale' => 0, 'missing' => 0, 'recalc' => 0];
        }

        $this->line(sprintf('Tournament %d (%s):', $tournament->id, $tournament->slug));
        foreach ($stale as $row) {
            $g = $groupById[$row->group_id] ?? null;
            $this->line(sprintf(
                '  [stale] athlete=%d o standing group=%d (%s) nhung athlete.group_id=%s',
                $row->athlete_id,
                $row->group_id,
                $g?->group_name ?? '?',
                $row->current_group_id === null ? 'null' : (string) $row->current_group_id
            ));
        }
        foreach ($missing as $row) {
            $g = $groupById[$row->group_id] ?? null;
            $this->line(sprintf(
                '  [missing] athlete=%d thieu standing o group=%d (%s)',
                $row->athlete_id,
                $row->group_id,
                $g?->group_name ?? '?'
            ));
        }

        if ($dryRun) {
            return ['stale' => $stale->count(), 'missing' => $missing->count(), 'recalc' => 0];
        }

        $affectedGroupIds = collect();
        DB::transaction(function () use ($stale, $missing, $affectedGroupIds) {
            foreach ($stale as $row) {
                GroupStanding::where('id', $row->id)->delete();
                $affectedGroupIds->push($row->group_id);
            }
            foreach ($missing as $row) {
                $this->createEmptyStanding((int) $row->group_id, (int) $row->athlete_id);
                $affectedGroupIds->push($row->group_id);
            }
        });

        $recalcCount = 0;
        foreach ($affectedGroupIds->unique() as $gid) {
            $service->recalculateGroupStandings((int) $gid);
            $recalcCount++;
        }

        return ['stale' => $stale->count(), 'missing' => $missing->count(), 'recalc' => $recalcCount];
    }

    /**
     * Standings con trong DB nhung athlete.group_id da khac group_id cua standing.
     */
    private function findStaleStandings(\Illuminate\Support\Collection $groupIds): \Illuminate\Support\Collection
    {
        return DB::table('group_standings as gs')
            ->leftJoin('tournament_athletes as ta', 'ta.id', '=', 'gs.athlete_id')
            ->whereIn('gs.group_id', $groupIds)
            ->where(function ($q) {
                $q->whereNull('ta.group_id')
                    ->orWhereColumn('ta.group_id', '!=', 'gs.group_id');
            })
            ->select('gs.id', 'gs.group_id', 'gs.athlete_id', 'ta.group_id as current_group_id')
            ->get();
    }

    /**
     * Athletes dang o trong group nhung chua co row standing tuong ung.
     */
    private function findMissingStandings(\Illuminate\Support\Collection $groups): \Illuminate\Support\Collection
    {
        $groupIds = $groups->pluck('id');

        return DB::table('tournament_athletes as ta')
            ->leftJoin('group_standings as gs', function ($join) {
                $join->on('gs.athlete_id', '=', 'ta.id')
                    ->on('gs.group_id', '=', 'ta.group_id');
            })
            ->whereIn('ta.group_id', $groupIds)
            ->whereNull('gs.id')
            ->select('ta.id as athlete_id', 'ta.group_id')
            ->get();
    }

    private function createEmptyStanding(int $groupId, int $athleteId): void
    {
        GroupStanding::updateOrCreate(
            ['group_id' => $groupId, 'athlete_id' => $athleteId],
            [
                'rank_position' => 0, 'matches_played' => 0, 'matches_won' => 0,
                'matches_lost' => 0, 'matches_drawn' => 0, 'win_rate' => 0, 'points' => 0,
                'sets_won' => 0, 'sets_lost' => 0, 'sets_differential' => 0,
                'games_won' => 0, 'games_lost' => 0, 'games_differential' => 0, 'is_advanced' => false,
            ]
        );
    }
}
