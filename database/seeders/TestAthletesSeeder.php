<?php

namespace Database\Seeders;

use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\TournamentAthlete;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestAthletesSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a homeyard user
        $user = User::where('email', 'homeyard@test.com')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test HomeYard',
                'email' => 'homeyard@test.com',
                'password' => bcrypt('password'),
            ]);
            $user->assignRole('home_yard');
        }

        // Get first tournament or create one
        $tournament = Tournament::where('user_id', $user->id)->first();
        if (!$tournament) {
            $tournament = Tournament::create([
                'user_id' => $user->id,
                'name' => 'Test Tournament',
                'description' => 'Test',
                'start_date' => now(),
                'end_date' => now()->addDays(7),
                'location' => 'Test Location',
                'status' => true,
            ]);
        }

        // Get or create categories
        $categories = TournamentCategory::where('tournament_id', $tournament->id)->get();
        if ($categories->isEmpty()) {
            $categories = [
                TournamentCategory::create([
                    'tournament_id' => $tournament->id,
                    'category_name' => 'Đơn Nam',
                    'category_type' => 'single_men',
                    'max_participants' => 16,
                    'current_participants' => 0,
                ]),
                TournamentCategory::create([
                    'tournament_id' => $tournament->id,
                    'category_name' => 'Đơn Nữ',
                    'category_type' => 'single_women',
                    'max_participants' => 16,
                    'current_participants' => 0,
                ]),
            ];
        }

        // Create test athletes if not exists
        $athleteCount = TournamentAthlete::where('tournament_id', $tournament->id)->count();
        if ($athleteCount == 0) {
            $athletes = [
                ['name' => 'Nguyễn Văn An', 'email' => 'an@test.com', 'phone' => '0901234567', 'category' => 0],
                ['name' => 'Trần Thu Linh', 'email' => 'linh@test.com', 'phone' => '0912345678', 'category' => 1],
                ['name' => 'Lê Minh Hoàng', 'email' => 'hoang@test.com', 'phone' => '0923456789', 'category' => 0],
                ['name' => 'Phạm Thu Hà', 'email' => 'ha@test.com', 'phone' => '0934567890', 'category' => 1],
                ['name' => 'Đỗ Văn Toàn', 'email' => 'toan@test.com', 'phone' => '0945678901', 'category' => 0],
                ['name' => 'Vũ Thu Lan', 'email' => 'lan@test.com', 'phone' => '0956789012', 'category' => 1],
            ];

            foreach ($athletes as $idx => $athleteData) {
                TournamentAthlete::create([
                    'tournament_id' => $tournament->id,
                    'category_id' => $categories[$athleteData['category']]->id,
                    'user_id' => $user->id,
                    'athlete_name' => $athleteData['name'],
                    'email' => $athleteData['email'],
                    'phone' => $athleteData['phone'],
                    'status' => $idx % 3 == 0 ? 'pending' : 'approved',
                    'position' => $idx % 3 == 0 ? null : $idx + 1,
                    'matches_played' => rand(0, 10),
                    'matches_won' => rand(0, 8),
                    'matches_lost' => rand(0, 8),
                    'total_points' => rand(0, 100),
                    'sets_won' => rand(0, 20),
                    'sets_lost' => rand(0, 20),
                ]);
            }

            $this->command->info('Test athletes created successfully!');
        }
    }
}
