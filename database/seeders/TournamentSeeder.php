<?php

namespace Database\Seeders;

use App\Models\Tournament;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing tournaments (optional)
        // Tournament::truncate();

        $tournaments = [
            [
                'name' => 'HCM Pickleball Open 2025',
                'description' => 'Giải đấu mở rộng quy mô lớn nhất năm với tổng giá trị giải thưởng 5 triệu đồng',
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(12),
                'location' => 'Sân Rạch Chiếc, Q2',
                'max_participants' => 128,
                'price' => 500000.00,
                'prizes' => '5 triệu đồng',
            ],
            [
                'name' => 'Hà Nội Pickleball Masters',
                'description' => 'Giải đấu dành cho các tay vợt chuyên nghiệp hạng Masters trở lên',
                'start_date' => Carbon::now()->addDays(20),
                'end_date' => Carbon::now()->addDays(22),
                'location' => 'Cung TDTT Quốc Gia',
                'max_participants' => 64,
                'price' => 300000.00,
                'prizes' => '3 triệu đồng',
            ],
            [
                'name' => 'Đà Nẵng Beach Pickleball',
                'description' => 'Giải đấu bãi biển độc đáo với không khí sôi động và giải thưởng hấp dẫn',
                'start_date' => Carbon::now()->addDays(35),
                'end_date' => Carbon::now()->addDays(37),
                'location' => 'Bãi Biển Mỹ Khê',
                'max_participants' => 96,
                'price' => 250000.00,
                'prizes' => '2.5 triệu đồng',
            ],
            [
                'name' => 'Cần Thơ Mekong Cup',
                'description' => 'Giải đấu khu vực miền Tây Nam Bộ dành cho mọi trình độ',
                'start_date' => Carbon::now()->addDays(42),
                'end_date' => Carbon::now()->addDays(44),
                'location' => 'Sân TDTT Cần Thơ',
                'max_participants' => 80,
                'price' => 150000.00,
                'prizes' => '1.5 triệu đồng',
            ],
            [
                'name' => 'Vũng Tàu Seaside Open',
                'description' => 'Kết hợp nghỉ dưỡng và thi đấu tại thành phố biển xinh đẹp',
                'start_date' => Carbon::now()->addDays(49),
                'end_date' => Carbon::now()->addDays(51),
                'location' => 'Resort Paradise',
                'max_participants' => 72,
                'price' => 180000.00,
                'prizes' => '1.8 triệu đồng',
            ],
            [
                'name' => 'Vietnam National Championship',
                'description' => 'Giải vô địch quốc gia - Sân chơi lớn nhất trong năm',
                'start_date' => Carbon::now()->addDays(75),
                'end_date' => Carbon::now()->addDays(80),
                'location' => 'Hà Nội (TBD)',
                'max_participants' => 256,
                'price' => 1000000.00,
                'prizes' => '10 triệu đồng',
            ],
        ];

        foreach ($tournaments as $tournament) {
            Tournament::create(array_merge($tournament, [
                'status' => 1,
                'user_id' => 1, // Assumes user ID 1 exists
            ]));
        }

        $this->command->info('Tournament seeder completed! ' . count($tournaments) . ' tournaments created.');
    }
}
