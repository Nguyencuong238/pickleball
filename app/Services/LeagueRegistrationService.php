<?php

namespace App\Services;

use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\LeagueRegistrationPlayer;
use App\Models\LeagueTeam;
use App\Models\LeagueTeamPlayer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class LeagueRegistrationService
{
    /**
     * Chuẩn hóa số điện thoại về dạng 0xxx
     */
    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Chuyển +84/84 prefix sang 0
        if (str_starts_with($phone, '84') && strlen($phone) >= 11) {
            $phone = '0' . substr($phone, 2);
        }

        if (!str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }

        // Validate chiều dài hợp lệ (VN: 10 digits)
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return $phone; // Return as-is, validation sẽ reject
        }

        return $phone;
    }

    /**
     * Đăng ký nhóm VĐV vào league
     */
    public function register(League $league, array $data): LeagueRegistration
    {
        return DB::transaction(function () use ($league, $data) {
            $registration = LeagueRegistration::create([
                'league_id' => $league->id,
                'payment_proof' => $data['payment_proof'],
                'status' => 'pending',
            ]);

            foreach ($data['players'] as $playerData) {
                $phone = self::normalizePhone($playerData['phone']);

                // Tìm hoặc tạo user theo phone (race-safe)
                $user = User::firstOrCreate(
                    ['phone' => $phone],
                    [
                        'name' => $playerData['name'],
                        'email' => $phone . '@league-reg.local',
                        'password' => bcrypt(Str::random(16)),
                    ]
                );

                LeagueRegistrationPlayer::create([
                    'league_registration_id' => $registration->id,
                    'user_id' => $user->id,
                    'phone' => $phone,
                    'name' => $playerData['name'],
                    'skill_level' => $playerData['skill_level'] ?? null,
                    'province' => $playerData['province'] ?? null,
                    'gender' => $playerData['gender'],
                    'birthday' => $playerData['birthday'] ?? null,
                    'photo' => $playerData['photo'] ?? null,
                    'message' => $playerData['message'] ?? null,
                ]);
            }

            return $registration->load('players');
        });
    }

    public function approve(LeagueRegistration $registration, ?string $note = null): void
    {
        $registration->update([
            'status' => 'approved',
            'admin_note' => $note,
        ]);
    }

    public function reject(LeagueRegistration $registration, ?string $note = null): void
    {
        $registration->update([
            'status' => 'rejected',
            'admin_note' => $note,
        ]);
    }

    /**
     * Lấy danh sách VĐV đã duyệt nhưng chưa được thêm vào đội nào
     */
    public function getAvailablePool(League $league): Collection
    {
        // Lấy tất cả user_id đã trong đội của league này
        $assignedUserIds = LeagueTeamPlayer::whereHas('team', function ($q) use ($league) {
            $q->where('league_id', $league->id);
        })->pluck('user_id')->toArray();

        // Lấy các registration đã approved
        $registrations = $league->registrations()
            ->where('status', 'approved')
            ->with(['players' => function ($q) use ($assignedUserIds) {
                $q->whereNotIn('user_id', $assignedUserIds);
            }])
            ->get()
            ->filter(function ($reg) {
                return $reg->players->isNotEmpty();
            })
            ->values();

        return $registrations;
    }

    /**
     * Thêm cả nhóm VĐV từ 1 registration vào team
     */
    public function addGroupToTeam(LeagueRegistration $registration, LeagueTeam $team): array
    {
        return DB::transaction(function () use ($registration, $team) {
            $league = $team->league;
            $added = [];

            // Kiểm tra giới hạn VĐV/đội
            $maxPlayers = $league->getConfigValue('max_players_per_team', LeagueService::DEFAULT_CONFIG['max_players_per_team']);
            $currentCount = $team->players()->count();
            $newPlayersCount = $registration->players->count();
            if ($currentCount + $newPlayersCount > $maxPlayers) {
                throw new InvalidArgumentException("Vượt quá giới hạn {$maxPlayers} VĐV/đội.");
            }

            // Lấy user_id đã trong đội của league
            $assignedUserIds = LeagueTeamPlayer::whereHas('team', function ($q) use ($league) {
                $q->where('league_id', $league->id);
            })->pluck('user_id')->toArray();

            $isFirstPlayer = $currentCount === 0;

            foreach ($registration->players as $regPlayer) {
                if (in_array($regPlayer->user_id, $assignedUserIds)) {
                    continue;
                }

                $teamPlayer = $team->players()->create([
                    'user_id' => $regPlayer->user_id,
                    'gender' => $regPlayer->gender,
                    'status' => 'active',
                ]);

                // Đặt đội trưởng là VĐV đầu tiên nếu team chưa có captain
                if ($isFirstPlayer && !$team->captain_user_id) {
                    $team->update(['captain_user_id' => $regPlayer->user_id]);
                    $isFirstPlayer = false;
                }

                $added[] = $teamPlayer;
                $assignedUserIds[] = $regPlayer->user_id;
            }

            return $added;
        });
    }
}
