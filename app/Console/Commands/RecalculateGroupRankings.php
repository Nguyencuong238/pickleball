<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupStanding;
use App\Services\Tournament\TournamentStandingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfill: re-sort rank_position của các group legacy theo spec 5-tier mới.
 * Khác với tournament:recalculate-standings ở chỗ KHÔNG đụng tới counters
 * (matches_played, points, sets_won, ...) — chỉ chạy sorter và update
 * rank_position + is_advanced. Nhanh hơn nhiều cho mục đích align rank cũ.
 */
class RecalculateGroupRankings extends Command
{
    protected $signature = 'tournament:recalculate-rankings
        {--tournament= : Chỉ xử lý tournament id này}
        {--group= : Chỉ xử lý group id này}
        {--dry-run : Chỉ log khác biệt, không lưu DB}';

    protected $description = 'Re-sort rank_position theo spec 5-tier mới (không đụng counters). Dùng để fix legacy.';

    public function handle(TournamentStandingService $service): int
    {
        $groupIds = $this->resolveGroupIds();
        if ($groupIds->isEmpty()) {
            $this->warn('Không có group nào để xử lý.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info(sprintf('Re-rank %d group(s)%s', $groupIds->count(), $dryRun ? ' (dry-run)' : ''));

        $processed = 0;
        $changed = 0;
        $errors = 0;

        foreach ($groupIds as $groupId) {
            try {
                $before = $this->snapshot($groupId);
                if ($before === []) {
                    continue;
                }

                if ($dryRun) {
                    DB::beginTransaction();
                    $service->recalculateGroupRankings($groupId);
                    $after = $this->snapshot($groupId);
                    DB::rollBack();
                } else {
                    $service->recalculateGroupRankings($groupId);
                    $after = $this->snapshot($groupId);
                }

                $changed += $this->logDiff($groupId, $before, $after);
                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                $this->error(sprintf('Group %d lỗi: %s', $groupId, $e->getMessage()));
            }
        }

        $this->info(sprintf(
            'Xong. Groups: %d, athletes thay đổi rank: %d, errors: %d%s',
            $processed,
            $changed,
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
            ->get(['id', 'athlete_id', 'rank_position', 'is_advanced'])
            ->keyBy('id')
            ->map(fn ($r) => [
                'athlete_id' => $r->athlete_id,
                'rank_position' => $r->rank_position,
                'is_advanced' => (bool) $r->is_advanced,
            ])
            ->toArray();
    }

    private function logDiff(int $groupId, array $before, array $after): int
    {
        $diff = 0;
        foreach ($after as $id => $row) {
            $prev = $before[$id] ?? null;
            if ($prev === null || $prev !== $row) {
                $diff++;
                $this->line(sprintf(
                    '  group=%d athlete=%s rank:%s→%s adv:%s→%s',
                    $groupId,
                    $row['athlete_id'],
                    $prev['rank_position'] ?? '-',
                    $row['rank_position'],
                    isset($prev['is_advanced']) ? ($prev['is_advanced'] ? 'Y' : 'N') : '-',
                    $row['is_advanced'] ? 'Y' : 'N'
                ));
            }
        }
        return $diff;
    }
}
