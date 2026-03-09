<?php

namespace App\Services;

use App\Models\League;
use App\Models\LeagueRegistrationPlayer;
use App\Models\LeagueTeamPlayer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeagueAutoTeamService
{
    /**
     * Tự động tạo đội từ pool VĐV đã duyệt nhưng chưa xếp đội
     *
     * @param string $mode 'skill_ranked' | 'random'
     * @return array Danh sách đội đã tạo
     */
    public function autoGenerateTeams(League $league, string $mode = 'random', int $playersPerTeam = 2): array
    {
        if (!in_array($league->status, ['draft', 'registration'])) {
            throw new InvalidArgumentException('Chỉ có thể tạo đội khi league ở trạng thái nháp hoặc đăng ký.');
        }

        return DB::transaction(function () use ($league, $mode, $playersPerTeam) {
            // Lock league row để tránh race condition
            $league = League::lockForUpdate()->find($league->id);

            // Lấy tất cả user_id đã trong đội
            $assignedUserIds = LeagueTeamPlayer::whereHas('team', fn ($q) => $q->where('league_id', $league->id))
                ->pluck('user_id')
                ->toArray();

            // Lấy VĐV từ registrations đã duyệt, chưa xếp đội (unique theo user_id)
            $players = LeagueRegistrationPlayer::whereHas('registration', function ($q) use ($league) {
                $q->where('league_id', $league->id)->where('status', 'approved');
            })
                ->whereNotIn('user_id', $assignedUserIds)
                ->get()
                ->unique('user_id')
                ->values();

            if ($players->count() < $playersPerTeam) {
                throw new InvalidArgumentException(
                    "Cần ít nhất {$playersPerTeam} VĐV chưa xếp đội để tạo đội. Hiện có {$players->count()} VĐV."
                );
            }

            // Sắp xếp theo mode
            if ($mode === 'skill_ranked') {
                $players = $players->sortByDesc(function ($p) {
                    return (float) ($p->skill_level ?? 0);
                })->values();
                $players = $this->snakeDraftPairing($players, $playersPerTeam);
            } else {
                $players = $players->shuffle()->values();
            }

            // Chia nhóm theo số VĐV/đội
            $chunks = $players->chunk($playersPerTeam);
            $maxTeams = $league->getConfigValue('max_teams', LeagueService::DEFAULT_CONFIG['max_teams']);
            $currentTeamCount = $league->teams()->count();
            $createdTeams = [];

            foreach ($chunks as $group) {
                // Bỏ qua nhóm thiếu người
                if ($group->count() < $playersPerTeam) {
                    break;
                }

                if ($currentTeamCount + count($createdTeams) >= $maxTeams) {
                    break;
                }

                $teamNumber = $currentTeamCount + count($createdTeams) + 1;
                $team = $league->teams()->create([
                    'name' => "Đội {$teamNumber}",
                    'status' => 'active',
                    'captain_user_id' => $group->first()->user_id,
                ]);

                foreach ($group as $player) {
                    $team->players()->create([
                        'user_id' => $player->user_id,
                        'gender' => $player->gender,
                        'status' => 'active',
                    ]);
                }

                $createdTeams[] = $team;
            }

            return $createdTeams;
        });
    }

    /**
     * Snake-draft: ghép VĐV trình cao với trình thấp
     * Sorted DESC: #0 với #N-1, #1 với #N-2, ...
     * Cho 4 người/đội: snake pattern tương tự
     */
    private function snakeDraftPairing(Collection $sortedPlayers, int $perTeam): Collection
    {
        $count = $sortedPlayers->count();
        $usableCount = $count - ($count % $perTeam);
        $numTeams = intdiv($usableCount, $perTeam);
        $teams = array_fill(0, $numTeams, []);
        $forward = true;

        // Snake draft: round 1 forward, round 2 reverse, ...
        $idx = 0;
        for ($round = 0; $round < $perTeam; $round++) {
            if ($forward) {
                for ($t = 0; $t < $numTeams; $t++) {
                    $teams[$t][] = $sortedPlayers[$idx++];
                }
            } else {
                for ($t = $numTeams - 1; $t >= 0; $t--) {
                    $teams[$t][] = $sortedPlayers[$idx++];
                }
            }
            $forward = !$forward;
        }

        // Flatten các đội thành danh sách liên tiếp (chunk sẽ tách lại)
        $result = collect();
        foreach ($teams as $teamPlayers) {
            foreach ($teamPlayers as $p) {
                $result->push($p);
            }
        }

        // Thêm lại VĐV thừa (sẽ bị bỏ qua khi chunk)
        for ($i = $usableCount; $i < $count; $i++) {
            $result->push($sortedPlayers[$i]);
        }

        return $result;
    }
}
