<?php

namespace App\Services;

use App\Models\League;
use App\Models\LeagueTeam;
use App\Models\LeagueTeamPlayer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class LeagueService
{
    // Trang thai hop le cho chuyen doi
    private const STATUS_TRANSITIONS = [
        'draft' => ['registration', 'cancelled'],
        'registration' => ['active', 'cancelled'],
        'active' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    // Config mac dinh cho league
    public const DEFAULT_CONFIG = [
        'points_for_win' => 3,
        'points_for_loss' => 0,
        'max_teams' => 16,
        'max_players_per_team' => 10,
        'match_format' => ['WD', 'MD', 'MXD', 'MXD'],
        'scoring_type' => 'best_of',
    ];

    // Preset noi dung thi dau cho MLP
    public const MLP_MATCH_FORMAT = ['MD', 'WD', 'MXD', 'MXD'];

    public function __construct(
        private LeagueScheduleService $scheduleService,
        private LeagueStandingsService $standingsService
    ) {}

    /**
     * Tao league moi
     */
    public function createLeague(User $user, array $data): League
    {
        return DB::transaction(function () use ($user, $data) {
            $slug = Str::slug($data['name']);
            if (League::where('slug', $slug)->exists()) {
                $slug .= '-' . Str::random(5);
            }

            $config = array_merge(self::DEFAULT_CONFIG, $data['config'] ?? []);

            // MLP: luôn dùng preset cố định, bỏ qua match_format từ form
            $competitionFormat = $data['competition_format'] ?? 'traditional';
            if ($competitionFormat === 'mlp') {
                $config['match_format'] = self::MLP_MATCH_FORMAT;
            }

            return League::create([
                'user_id' => $user->id,
                'club_id' => $data['club_id'] ?? null,
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'season_name' => $data['season_name'] ?? null,
                'config' => $config,
                'competition_format' => $data['competition_format'] ?? 'traditional',
                'status' => 'draft',
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'registration_deadline' => $data['registration_deadline'] ?? null,
                'logo' => $data['logo'] ?? null,
                'required_players_per_registration' => $data['required_players_per_registration'] ?? 1,
                'registration_fee' => $data['registration_fee'] ?? null,
            ]);
        });
    }

    /**
     * Cap nhat league
     */
    public function updateLeague(League $league, array $data): League
    {
        if (isset($data['name']) && $data['name'] !== $league->name) {
            $slug = Str::slug($data['name']);
            if (League::where('slug', $slug)->where('id', '!=', $league->id)->exists()) {
                $slug .= '-' . Str::random(5);
            }
            $data['slug'] = $slug;
        }

        $league->update($data);
        return $league->refresh();
    }

    /**
     * Xoa league (chi khi status = draft)
     */
    public function deleteLeague(League $league): bool
    {
        if ($league->status !== 'draft') {
            throw new InvalidArgumentException('Chi co the xoa league o trang thai draft.');
        }

        return (bool) $league->delete();
    }

    /**
     * Chuyen doi trang thai league
     */
    public function updateStatus(League $league, string $newStatus): League
    {
        $allowed = self::STATUS_TRANSITIONS[$league->status] ?? [];

        if (!in_array($newStatus, $allowed)) {
            throw new InvalidArgumentException(
                "Khong the chuyen tu '{$league->status}' sang '{$newStatus}'."
            );
        }

        return DB::transaction(function () use ($league, $newStatus) {
            // Lock league row de tranh race condition
            $league = League::lockForUpdate()->find($league->id);

            // Khi chuyen sang active: tao lich thi dau va khoi tao standings
            if ($newStatus === 'active') {
                if ($league->competition_format === 'mlp') {
                    $this->scheduleService->validateMlpTeams($league);
                }
                if ($league->rounds()->count() === 0) {
                    $this->scheduleService->generateRoundRobin($league);
                }
                $this->standingsService->initializeStandings($league);
            }

            $league->update(['status' => $newStatus]);
            return $league->refresh();
        });
    }

    // === Team Management ===

    /**
     * Them team vao league
     */
    public function addTeam(League $league, array $data): LeagueTeam
    {
        $maxTeams = $league->getConfigValue('max_teams', self::DEFAULT_CONFIG['max_teams']);

        if ($league->teams()->count() >= $maxTeams) {
            throw new InvalidArgumentException("Da dat gioi han {$maxTeams} doi.");
        }

        return $league->teams()->create([
            'name' => $data['name'],
            'logo' => $data['logo'] ?? null,
            'captain_user_id' => $data['captain_user_id'] ?? null,
            'status' => 'active',
        ]);
    }

    /**
     * Cap nhat team
     */
    public function updateTeam(LeagueTeam $team, array $data): LeagueTeam
    {
        $team->update($data);
        return $team->refresh();
    }

    /**
     * Xoa team (chi khi league o draft/registration)
     */
    public function removeTeam(LeagueTeam $team): bool
    {
        $league = $team->league;

        if (!in_array($league->status, ['draft', 'registration'])) {
            throw new InvalidArgumentException('Chi co the xoa doi khi league o trang thai draft hoac registration.');
        }

        return (bool) $team->delete();
    }

    // === Player Management ===

    /**
     * Them player vao team
     */
    public function addPlayer(LeagueTeam $team, array $data): LeagueTeamPlayer
    {
        $league = $team->league;
        $maxPlayers = $league->getConfigValue('max_players_per_team', self::DEFAULT_CONFIG['max_players_per_team']);

        if ($team->players()->count() >= $maxPlayers) {
            throw new InvalidArgumentException("Da dat gioi han {$maxPlayers} nguoi choi moi doi.");
        }

        // Kiem tra user da o trong team chua
        if ($team->players()->where('user_id', $data['user_id'])->exists()) {
            throw new InvalidArgumentException('Nguoi choi da co trong doi.');
        }

        // Kiem tra user chua o doi khac trong cung league
        $league = $team->league;
        $existsInOtherTeam = LeagueTeamPlayer::whereHas('team', fn ($q) => $q->where('league_id', $league->id))
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($existsInOtherTeam) {
            throw new InvalidArgumentException('Nguoi choi da tham gia doi khac trong cung league.');
        }

        return $team->players()->create([
            'user_id' => $data['user_id'],
            'position' => $data['position'] ?? null,
            'gender' => $data['gender'],
            'status' => 'active',
        ]);
    }

    /**
     * Xoa player khoi team
     */
    public function removePlayer(LeagueTeamPlayer $player): bool
    {
        return (bool) $player->delete();
    }

    /**
     * Kiem tra quyen so huu league
     */
    public function validateLeagueOwnership(League $league, User $user): bool
    {
        return $league->user_id === $user->id;
    }
}
