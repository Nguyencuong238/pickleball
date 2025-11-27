<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Stadium;
use App\Models\Court;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\Round;
use App\Models\Group;
use App\Models\TournamentAthlete;
use App\Models\MatchModel;
use App\Models\GroupStanding;
use App\Models\Payment;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This creates a complete tournament scenario:
     * - Giải Pickleball Mở Rộng TP.HCM 2025
     * - 3 categories (Nam đơn 18+, Nữ đơn 35+, Đôi nam nữ)
     * - 64 athletes across categories
     * - Multiple rounds, groups, matches
     * - Realistic Vietnamese names and data
     */
    public function run(): void
    {
        // Call individual seeders
        $this->call([
            ProvinceSeeder::class,
            PermissionSeeder::class,
            InstructorSeeder::class,
            VideoSeeder::class,
        ]);

        // ============================================
        // 1. CREATE ROLES & PERMISSIONS
        // ============================================

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $organizerRole = Role::firstOrCreate(['name' => 'organizer']);
        $stadiumOwnerRole = Role::firstOrCreate(['name' => 'stadium_owner']);
        $athleteRole = Role::firstOrCreate(['name' => 'athlete']);

        // ============================================
        // 2. CREATE USERS
        // ============================================

        // Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@pickleball.vn',
            'password' => Hash::make('password'),
            'phone' => '0901234567',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Tournament organizer
        $organizer = User::create([
            'name' => 'Nguyễn Văn Tổ Chức',
            'email' => 'organizer@pickleball.vn',
            'password' => Hash::make('password'),
            'phone' => '0912345678',
            'email_verified_at' => now(),
        ]);
        $organizer->assignRole('organizer');

        // Stadium owner
        $stadiumOwner = User::create([
            'name' => 'Trần Thị Sân',
            'email' => 'stadium@pickleball.vn',
            'password' => Hash::make('password'),
            'phone' => '0923456789',
            'email_verified_at' => now(),
        ]);
        $stadiumOwner->assignRole('stadium_owner');

        // Athletes (12 users who will register as athletes)
        $athleteNames = [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Văn Cường', 'Phạm Văn Dũng',
            'Hoàng Thị Em', 'Vũ Văn Phong', 'Đặng Thị Giang', 'Bùi Văn Hùng',
            'Ngô Thị Hoa', 'Lý Văn Khánh', 'Phan Thị Lan', 'Trương Văn Minh'
        ];

        $athletes = [];
        foreach ($athleteNames as $index => $name) {
            $email = 'athlete' . ($index + 1) . '@pickleball.vn';
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'phone' => '090' . str_pad($index + 1, 7, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('athlete');
            $athletes[] = $user;
        }

        // ============================================
        // 3. CREATE STADIUM & COURTS
        // ============================================

        $stadium = Stadium::create([
            'user_id' => $stadiumOwner->id,
            'name' => 'Sân Pickleball Thảo Điền',
            'description' => 'Sân Pickleball chuyên nghiệp đầu tiên tại Quận 2, TP.HCM với cơ sở vật chất hiện đại và đội ngũ HLV giàu kinh nghiệm.',
            'address' => '123 Đường Xuân Thủy, Phường Thảo Điền, Quận 2, TP.HCM',
            'phone' => '0287654321',
            'email' => 'contact@thaodien-pickleball.vn',
            'courts_count' => 8,
            'court_surface' => 'Acrylic chuyên dụng',
            // 'latitude' => 10.803570,
            // 'longitude' => 106.738640,
            'opening_hours' => '06:00 - 22:00',
            'amenities' => json_encode([
                'Phòng thay đồ',
                'Căn-tin',
                'Wifi miễn phí',
                'Bãi đỗ xe rộng rãi',
                'Cho thuê vợt và bóng'
            ]),
            'status' => 'active',
            'rating' => 4.8,
            'rating_count' => 156,
            'is_verified' => true,
            'is_featured' => true,
        ]);

        // Create 8 courts
        for ($i = 1; $i <= 8; $i++) {
            Court::create([
                'stadium_id' => $stadium->id,
                'court_name' => 'Sân số ' . $i,
                'court_number' => (string)$i,
                'court_type' => $i <= 4 ? 'indoor' : 'outdoor',
                'surface_type' => 'Acrylic',
                'status' => 'available',
                'description' => 'Sân thi đấu chuẩn quốc tế',
                'is_active' => true,
            ]);
        }

        $courts = Court::all();

        // ============================================
        // 4. CREATE TOURNAMENT
        // ============================================

        $tournament = Tournament::create([
            'user_id' => $organizer->id,
            'name' => 'Giải Pickleball Mở Rộng TP.HCM 2025',
            'tournament_code' => 'PB-HCM-2025',
            'description' => 'Giải đấu Pickleball quy mô lớn nhất TP.HCM năm 2025 với sự tham gia của các VĐV hàng đầu khu vực.',
            'start_date' => '2025-01-20',
            'end_date' => '2025-01-22',
            'registration_deadline' => '2025-01-15 23:59:59',
            'location' => 'Sân Pickleball Thảo Điền',
            'max_participants' => 64,
            'price' => 500000,
            'competition_format' => 'Đơn & Đôi',
            'format_type' => 'group_knockout',
            'seeding_enabled' => true,
            'auto_bracket_generation' => false,
            'balanced_groups' => true,
            'group_count' => 4,
            'players_per_group' => 4,
            'tournament_rank' => 'Advanced',
            'status' => true,
            'tournament_stage' => 'in_progress',
            'prizes' => 50000000,
            'rules' => 'Thi đấu theo luật quốc tế. Best of 3 sets, mỗi set 11 điểm.',
            'total_matches' => 60,
            'completed_matches' => 35,
        ]);

        // ============================================
        // 5. CREATE CATEGORIES
        // ============================================

        $categoryMenSingle = TournamentCategory::create([
            'tournament_id' => $tournament->id,
            'category_name' => 'Nam đơn 18+',
            'category_type' => 'single_men',
            'age_group' => '18+',
            'max_participants' => 32,
            'prize_money' => 20000000,
            'description' => 'Nội dung thi đấu đơn nam cho VĐV từ 18 tuổi trở lên',
            'status' => 'ongoing',
            'current_participants' => 32,
        ]);

        $categoryWomenSingle = TournamentCategory::create([
            'tournament_id' => $tournament->id,
            'category_name' => 'Nữ đơn 35+',
            'category_type' => 'single_women',
            'age_group' => '35+',
            'max_participants' => 16,
            'prize_money' => 15000000,
            'description' => 'Nội dung thi đấu đơn nữ cho VĐV từ 35 tuổi trở lên',
            'status' => 'ongoing',
            'current_participants' => 16,
        ]);

        $categoryMixedDouble = TournamentCategory::create([
            'tournament_id' => $tournament->id,
            'category_name' => 'Đôi nam nữ',
            'category_type' => 'double_mixed',
            'age_group' => 'open',
            'max_participants' => 16,
            'prize_money' => 15000000,
            'description' => 'Nội dung thi đấu đôi nam nữ mở rộng',
            'status' => 'ongoing',
            'current_participants' => 16,
        ]);

        // ============================================
        // 6. CREATE ROUNDS
        // ============================================

        // Rounds for Men's Singles
        $roundMenGroup = Round::create([
            'tournament_id' => $tournament->id,
            'category_id' => $categoryMenSingle->id,
            'round_name' => 'Vòng bảng',
            'round_number' => 1,
            'round_type' => 'group_stage',
            'start_date' => '2025-01-20',
            'end_date' => '2025-01-20',
            'start_time' => '08:00:00',
            'status' => 'completed',
            'total_matches' => 24,
            'completed_matches' => 24,
        ]);

        $roundMenQuarter = Round::create([
            'tournament_id' => $tournament->id,
            'category_id' => $categoryMenSingle->id,
            'round_name' => 'Tứ kết',
            'round_number' => 2,
            'round_type' => 'quarterfinal',
            'start_date' => '2025-01-21',
            'start_time' => '08:00:00',
            'status' => 'in_progress',
            'total_matches' => 8,
            'completed_matches' => 6,
        ]);

        $roundMenSemi = Round::create([
            'tournament_id' => $tournament->id,
            'category_id' => $categoryMenSingle->id,
            'round_name' => 'Bán kết',
            'round_number' => 3,
            'round_type' => 'semifinal',
            'start_date' => '2025-01-22',
            'start_time' => '08:00:00',
            'status' => 'pending',
            'total_matches' => 4,
            'completed_matches' => 0,
        ]);

        // Rounds for Women's Singles
        $roundWomenGroup = Round::create([
            'tournament_id' => $tournament->id,
            'category_id' => $categoryWomenSingle->id,
            'round_name' => 'Vòng bảng',
            'round_number' => 1,
            'round_type' => 'group_stage',
            'start_date' => '2025-01-20',
            'start_time' => '13:00:00',
            'status' => 'completed',
            'total_matches' => 12,
            'completed_matches' => 12,
        ]);

        // ============================================
        // 7. CREATE GROUPS
        // ============================================

        // Groups for Men's Singles (4 groups, 8 athletes each)
        $groupMenA = Group::create([
            'tournament_id' => $tournament->id,
            'category_id' => $categoryMenSingle->id,
            'round_id' => $roundMenGroup->id,
            'group_name' => 'Bảng A',
            'group_code' => 'A',
            'max_participants' => 8,
            'current_participants' => 8,
            'advancing_count' => 2,
            'status' => 'completed',
        ]);

        $groupMenB = Group::create([
            'tournament_id' => $tournament->id,
            'category_id' => $categoryMenSingle->id,
            'round_id' => $roundMenGroup->id,
            'group_name' => 'Bảng B',
            'group_code' => 'B',
            'max_participants' => 8,
            'current_participants' => 8,
            'advancing_count' => 2,
            'status' => 'completed',
        ]);

        $groupMenC = Group::create([
            'tournament_id' => $tournament->id,
            'category_id' => $categoryMenSingle->id,
            'round_id' => $roundMenGroup->id,
            'group_name' => 'Bảng C',
            'group_code' => 'C',
            'max_participants' => 8,
            'current_participants' => 8,
            'advancing_count' => 2,
            'status' => 'completed',
        ]);

        $groupMenD = Group::create([
            'tournament_id' => $tournament->id,
            'category_id' => $categoryMenSingle->id,
            'round_id' => $roundMenGroup->id,
            'group_name' => 'Bảng D',
            'group_code' => 'D',
            'max_participants' => 8,
            'current_participants' => 8,
            'advancing_count' => 2,
            'status' => 'completed',
        ]);

        // ============================================
        // 8. CREATE ATHLETES (Tournament Registrations)
        // ============================================

        // Vietnamese athlete names
        $maleNames = [
            'Nguyễn Văn An', 'Trần Văn Bình', 'Lê Văn Cường', 'Phạm Văn Dũng',
            'Hoàng Văn Em', 'Vũ Văn Phong', 'Đặng Văn Giang', 'Bùi Văn Hùng',
            'Ngô Văn Ích', 'Lý Văn Khánh', 'Phan Văn Lâm', 'Trương Văn Minh',
            'Đỗ Văn Nam', 'Võ Văn Oai', 'Đinh Văn Phúc', 'Hà Văn Quang',
            'Mai Văn Sơn', 'Cao Văn Thắng', 'Tô Văn Út', 'Dương Văn Vũ',
            'Lưu Văn Xuân', 'Bùi Văn Yên', 'Hồ Văn Zung', 'Ngô Văn Ánh',
            'Hà Văn Chiến', 'Bùi Văn Khoa', 'Ngô Văn Sơn', 'Trần Văn Dũng',
            'Lê Văn Hải', 'Phạm Văn Long', 'Võ Văn Tài', 'Đinh Văn Quân'
        ];

        $tournamentAthletes = [];
        $groups = [$groupMenA, $groupMenB, $groupMenC, $groupMenD];

        foreach ($maleNames as $index => $name) {
            $groupIndex = floor($index / 8); // 8 athletes per group
            $group = $groups[$groupIndex];
            $seedInGroup = ($index % 8) + 1;

            $paymentStatuses = ['paid', 'paid', 'paid', 'pending', 'unpaid'];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            $confirmationStatuses = ['approved', 'approved', 'approved', 'registered'];
            $status = $confirmationStatuses[array_rand($confirmationStatuses)];

            $athlete = TournamentAthlete::create([
                'tournament_id' => $tournament->id,
                'category_id' => $categoryMenSingle->id,
                'group_id' => $group->id,
                'user_id' => isset($athletes[$index % count($athletes)]) ? $athletes[$index % count($athletes)]->id : null,
                'athlete_name' => $name,
                'email' => 'athlete' . ($index + 1) . '@gmail.com',
                'phone' => '090' . rand(1000000, 9999999),
                'seed_number' => $index + 1,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'registration_fee' => 500000,
                'amount_paid' => $paymentStatus === 'paid' ? 500000 : 0,
                'registered_at' => now()->subDays(rand(10, 30)),
                'confirmed_at' => $status === 'approved' ? now()->subDays(rand(5, 15)) : null,
                'position' => null,
                'matches_played' => 5,
                'matches_won' => rand(0, 5),
                'matches_lost' => 0,
                'total_points' => 0,
                'sets_won' => rand(0, 10),
                'sets_lost' => rand(0, 10),
            ]);

            // Calculate dependent values
            $athlete->matches_lost = 5 - $athlete->matches_won;
            $athlete->win_rate = $athlete->matches_played > 0
                ? round(($athlete->matches_won / $athlete->matches_played) * 100, 2)
                : 0;
            $athlete->total_points = $athlete->matches_won * 3;
            $athlete->save();

            $tournamentAthletes[] = $athlete;

            // Create payment record
            if ($paymentStatus !== 'unpaid') {
                Payment::create([
                    'user_id' => $athlete->user_id,
                    'tournament_id' => $tournament->id,
                    'tournament_athlete_id' => $athlete->id,
                    'payment_reference' => 'PAY-' . strtoupper(substr(md5($athlete->id . time()), 0, 10)),
                    'amount' => 500000,
                    'currency' => 'VND',
                    'payment_method' => ['bank_transfer', 'momo', 'zalopay', 'vnpay'][array_rand(['bank_transfer', 'momo', 'zalopay', 'vnpay'])],
                    'status' => $paymentStatus === 'paid' ? 'completed' : 'pending',
                    'paid_at' => $paymentStatus === 'paid' ? now()->subDays(rand(5, 20)) : null,
                ]);
            }
        }

        // ============================================
        // 9. CREATE GROUP STANDINGS
        // ============================================

        foreach ($tournamentAthletes as $athlete) {
            GroupStanding::create([
                'group_id' => $athlete->group_id,
                'athlete_id' => $athlete->id,
                'rank_position' => 0, // Will be calculated
                'matches_played' => $athlete->matches_played,
                'matches_won' => $athlete->matches_won,
                'matches_lost' => $athlete->matches_lost,
                'matches_drawn' => 0,
                'win_rate' => $athlete->win_rate,
                'points' => $athlete->total_points,
                'sets_won' => $athlete->sets_won,
                'sets_lost' => $athlete->sets_lost,
                'sets_differential' => $athlete->sets_won - $athlete->sets_lost,
                'games_won' => rand(50, 100),
                'games_lost' => rand(30, 80),
                'games_differential' => 0,
                'is_advanced' => false,
            ]);
        }

        // Calculate rank positions and mark top 2 as advanced
        foreach ($groups as $group) {
            $standings = GroupStanding::where('group_id', $group->id)
                ->orderByDesc('points')
                ->orderByDesc('sets_differential')
                ->get();

            foreach ($standings as $index => $standing) {
                $standing->rank_position = $index + 1;
                $standing->is_advanced = $index < 2; // Top 2 advance
                $standing->games_differential = $standing->games_won - $standing->games_lost;
                $standing->save();
            }
        }

        // ============================================
        // 10. CREATE MATCHES
        // ============================================

        $matchNumber = 1;

        // Group stage matches (Round robin within each group)
        foreach ($groups as $groupIndex => $group) {
            $groupAthletes = TournamentAthlete::where('group_id', $group->id)
                ->orderBy('seed_number')
                ->get();

            // Create round-robin matches (each athlete plays everyone once)
            for ($i = 0; $i < count($groupAthletes); $i++) {
                for ($j = $i + 1; $j < count($groupAthletes); $j++) {
                    $athlete1 = $groupAthletes[$i];
                    $athlete2 = $groupAthletes[$j];

                    // Generate realistic scores
                    $set1_a1 = rand(9, 11);
                    $set1_a2 = $set1_a1 == 11 ? rand(0, 9) : 11;

                    $set2_a1 = rand(9, 11);
                    $set2_a2 = $set2_a1 == 11 ? rand(0, 9) : 11;

                    $athlete1_sets = ($set1_a1 > $set1_a2 ? 1 : 0) + ($set2_a1 > $set2_a2 ? 1 : 0);
                    $athlete2_sets = 2 - $athlete1_sets;
                    $winner = $athlete1_sets > $athlete2_sets ? $athlete1 : $athlete2;

                    MatchModel::create([
                        'tournament_id' => $tournament->id,
                        'category_id' => $categoryMenSingle->id,
                        'round_id' => $roundMenGroup->id,
                        'group_id' => $group->id,
                        'court_id' => $courts[($matchNumber % 8)]->id,
                        'match_number' => 'M' . $matchNumber,
                        'athlete1_id' => $athlete1->id,
                        'athlete1_name' => $athlete1->athlete_name,
                        'athlete1_score' => $athlete1_sets,
                        'athlete2_id' => $athlete2->id,
                        'athlete2_name' => $athlete2->athlete_name,
                        'athlete2_score' => $athlete2_sets,
                        'winner_id' => $winner->id,
                        'match_date' => '2025-01-20',
                        'match_time' => '08:00:00',
                        'actual_start_time' => now()->subDays(2)->addHours(rand(0, 10)),
                        'actual_end_time' => now()->subDays(2)->addHours(rand(0, 10))->addMinutes(rand(30, 60)),
                        'status' => 'completed',
                        'best_of' => 3,
                        'set_scores' => json_encode([
                            ['set' => 1, 'athlete1' => $set1_a1, 'athlete2' => $set1_a2],
                            ['set' => 2, 'athlete1' => $set2_a1, 'athlete2' => $set2_a2],
                        ]),
                        'final_score' => "$set1_a1-$set1_a2, $set2_a1-$set2_a2",
                    ]);

                    $matchNumber++;
                }
            }
        }

        // Quarterfinal matches (top 2 from each group)
        $advancedAthletes = GroupStanding::where('is_advanced', true)
            ->with('athlete')
            ->orderBy('group_id')
            ->orderBy('rank_position')
            ->get()
            ->pluck('athlete');

        for ($i = 0; $i < min(count($advancedAthletes), 8); $i += 2) {
            if (!isset($advancedAthletes[$i + 1])) break;

            $athlete1 = $advancedAthletes[$i];
            $athlete2 = $advancedAthletes[$i + 1];

            $status = $i < 6 ? 'completed' : 'in_progress';

            if ($status === 'completed') {
                $set1_a1 = 11;
                $set1_a2 = rand(7, 9);
                $set2_a1 = 11;
                $set2_a2 = rand(5, 9);
                $winner = $athlete1;
            } else {
                $set1_a1 = rand(0, 11);
                $set1_a2 = rand(0, 11);
                $set2_a1 = 0;
                $set2_a2 = 0;
                $winner = null;
            }

            MatchModel::create([
                'tournament_id' => $tournament->id,
                'category_id' => $categoryMenSingle->id,
                'round_id' => $roundMenQuarter->id,
                'court_id' => $courts[($matchNumber % 8)]->id,
                'match_number' => 'QF' . ($i / 2 + 1),
                'athlete1_id' => $athlete1->id,
                'athlete1_name' => $athlete1->athlete_name,
                'athlete1_score' => $status === 'completed' ? 2 : 0,
                'athlete2_id' => $athlete2->id,
                'athlete2_name' => $athlete2->athlete_name,
                'athlete2_score' => 0,
                'winner_id' => $winner?->id,
                'match_date' => '2025-01-21',
                'match_time' => '08:00:00',
                'status' => $status,
                'best_of' => 3,
                'set_scores' => $status === 'completed' ? json_encode([
                    ['set' => 1, 'athlete1' => $set1_a1, 'athlete2' => $set1_a2],
                    ['set' => 2, 'athlete1' => $set2_a1, 'athlete2' => $set2_a2],
                ]) : null,
                'final_score' => $status === 'completed' ? "$set1_a1-$set1_a2, $set2_a1-$set2_a2" : null,
            ]);

            $matchNumber++;
        }

        // ============================================
        // 11. SUMMARY OUTPUT
        // ============================================

        $this->command->info('✅ Database seeded successfully!');
        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->info('   - Users: ' . User::count());
        $this->command->info('   - Stadiums: ' . Stadium::count());
        $this->command->info('   - Courts: ' . Court::count());
        $this->command->info('   - Tournaments: ' . Tournament::count());
        $this->command->info('   - Categories: ' . TournamentCategory::count());
        $this->command->info('   - Rounds: ' . Round::count());
        $this->command->info('   - Groups: ' . Group::count());
        $this->command->info('   - Athletes: ' . TournamentAthlete::count());
        $this->command->info('   - Matches: ' . MatchModel::count());
        $this->command->info('   - Standings: ' . GroupStanding::count());
        $this->command->info('   - Payments: ' . Payment::count());
        $this->command->newLine();
        $this->command->info('🎾 Tournament: Giải Pickleball Mở Rộng TP.HCM 2025');
        $this->command->info('📍 Venue: Sân Pickleball Thảo Điền');
        $this->command->info('📧 Test accounts:');
        $this->command->info('   - Admin: admin@pickleball.vn / password');
        $this->command->info('   - Organizer: organizer@pickleball.vn / password');
        $this->command->info('   - Athletes: athlete1@pickleball.vn / password');
    }
}
