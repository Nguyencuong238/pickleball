<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\MatchModel;
use App\Services\Tournament\TournamentStandingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfill: quét các group và rebuild standings từ matches table.
 * Dùng để fix data bị inflate do bug increment không idempotent trước đây.
 */
class RecalculateTournamentStandings extends Command
{
    protected $signature = 'tournament:recalculate-standings
        {--tournament= : Chỉ xử lý tournament id này}
        {--group= : Chỉ xử lý group id này}
        {--dry-run : Chỉ log khác biệt, không lưu DB}';

    protected $description = 'Rebuild lại group standings + tournament athlete stats từ matches table (idempotent).';

    public function handle(TournamentStandingService $service): int
    {
        $groupIds = $this->resolveGroupIds();
        if ($groupIds->isEmpty()) {
            $this->warn('Không có group nào để xử lý.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info(sprintf('Bắt đầu recompute %d group(s)%s', $groupIds->count(), $dryRun ? ' (dry-run)' : ''));

        $processed = 0;
        $changedRows = 0;
        $errors = 0;

        foreach ($groupIds as $groupId) {
            try {
                $before = $this->snapshot($groupId);

                if ($dryRun) {
                    DB::beginTransaction();
                    $service->recalculateGroupStandings($groupId);
                    $this->recomputeAthletesForGroup($service, $groupId);
                    $after = $this->snapshot($groupId);
                    DB::rollBack();
                } else {
                    $service->recalculateGroupStandings($groupId);
                    $this->recomputeAthletesForGroup($service, $groupId);
                    $after = $this->snapshot($groupId);
                }

                $diffCount = $this->logDiff($groupId, $before, $after);
                $changedRows += $diffCount;
                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                $this->error(sprintf('Group %d lỗi: %s', $groupId, $e->getMessage()));
            }
        }

        $this->info(sprintf(
            'Hoàn tất. Groups: %d, rows thay đổi: %d, errors: %d%s',
            $processed,
            $changedRows,
            $errors,
            $dryRun ? ' (dry-run, không lưu)' : ''
        ));

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function resolveGroupIds(): \Illuminate\Support\Collection
    {
        if ($gid = $this->option('group')) {
            return collect([(int) $gid]);
        }
        $query = Group::query();
        if ($tid = $this->option('tournament')) {
            $query->where('tournament_id', (int) $tid);
        }
        return $query->pluck('id');
    }

    private function snapshot(int $groupId): array
    {
        return GroupStanding::where('group_id', $groupId)
            ->get()
            ->keyBy('id')
            ->map(function ($row) {
                return [
                    'athlete_id' => $row->athlete_id,
                    'matches_played' => $row->matches_played,
                    'matches_won' => $row->matches_won,
                    'matches_lost' => $row->matches_lost,
                    'points' => $row->points,
                    'sets_won' => $row->sets_won,
                    'sets_lost' => $row->sets_lost,
                ];
            })
            ->toArray();
    }

    private function logDiff(int $groupId, array $before, array $after): int
    {
        $changed = 0;
        foreach ($after as $id => $row) {
            $prev = $before[$id] ?? null;
            if ($prev === null || $prev !== $row) {
                $changed++;
                $this->line(sprintf(
                    '  group=%d athlete=%s P:%s→%s W:%s→%s L:%s→%s pts:%s→%s sets:%s/%s→%s/%s',
                    $groupId,
                    $row['athlete_id'],
                    $prev['matches_played'] ?? '-',
                    $row['matches_played'],
                    $prev['matches_won'] ?? '-',
                    $row['matches_won'],
                    $prev['matches_lost'] ?? '-',
                    $row['matches_lost'],
                    $prev['points'] ?? '-',
                    $row['points'],
                    $prev['sets_won'] ?? '-',
                    $prev['sets_lost'] ?? '-',
                    $row['sets_won'],
                    $row['sets_lost']
                ));
            }
        }
        return $changed;
    }

    private function recomputeAthletesForGroup(TournamentStandingService $service, int $groupId): void
    {
        $athleteIds = MatchModel::where('group_id', $groupId)
            ->where('status', 'completed')
            ->whereNotNull('set_scores')
            ->get(['athlete1_id', 'athlete2_id'])
            ->flatMap(fn ($m) => [$m->athlete1_id, $m->athlete2_id])
            ->filter()
            ->unique();

        foreach ($athleteIds as $id) {
            $service->recalculateTournamentAthleteStats((int) $id);
        }
    }
}
