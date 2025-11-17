#!/usr/bin/env php
<?php
/**
 * Tournament Test Data Seeder Script
 * 
 * Usage: php create-tournament-test-data.php
 * Or: php artisan tinker < create-tournament-test-data.php
 */

// Sample data for tournaments
$tournaments = [
    [
        'name' => 'HCM Pickleball Open 2025',
        'description' => 'Giải đấu mở rộng quy mô lớn nhất năm với tổng giá trị giải thưởng 500 triệu đồng',
        'start_date' => '+10 days',
        'end_date' => '+12 days',
        'location' => 'Sân Rạch Chiếc, Q2',
        'max_participants' => 128,
        'price' => 500000000,
        'prizes' => 500000000,
    ],
    [
        'name' => 'Hà Nội Pickleball Masters',
        'description' => 'Giải đấu dành cho các tay vợt chuyên nghiệp hạng Masters trở lên',
        'start_date' => '+20 days',
        'end_date' => '+22 days',
        'location' => 'Cung TDTT Quốc Gia',
        'max_participants' => 64,
        'price' => 300000000,
        'prizes' => 300000000,
    ],
    [
        'name' => 'Đà Nẵng Beach Pickleball',
        'description' => 'Giải đấu bãi biển độc đáo với không khí sôi động và giải thưởng hấp dẫn',
        'start_date' => '+35 days',
        'end_date' => '+37 days',
        'location' => 'Bãi Biển Mỹ Khê',
        'max_participants' => 96,
        'price' => 200000000,
        'prizes' => 200000000,
    ],
    [
        'name' => 'Cần Thơ Mekong Cup',
        'description' => 'Giải đấu khu vực miền Tây Nam Bộ dành cho mọi trình độ',
        'start_date' => '+42 days',
        'end_date' => '+44 days',
        'location' => 'Sân TDTT Cần Thơ',
        'max_participants' => 80,
        'price' => 150000000,
        'prizes' => 150000000,
    ],
    [
        'name' => 'Vũng Tàu Seaside Open',
        'description' => 'Kết hợp nghỉ dưỡng và thi đấu tại thành phố biển xinh đẹp',
        'start_date' => '+49 days',
        'end_date' => '+51 days',
        'location' => 'Resort Paradise',
        'max_participants' => 72,
        'price' => 180000000,
        'prizes' => 180000000,
    ],
    [
        'name' => 'Vietnam National Championship',
        'description' => 'Giải vô địch quốc gia - Sân chơi lớn nhất trong năm',
        'start_date' => '+75 days',
        'end_date' => '+80 days',
        'location' => 'Hà Nội (TBD)',
        'max_participants' => 256,
        'price' => 1000000000,
        'prizes' => 1000000000,
    ],
];

// For use in artisan tinker, create tournaments
use App\Models\Tournament;
use Carbon\Carbon;

echo "Creating test tournaments...\n";

foreach ($tournaments as $key => $data) {
    try {
        $tournament = Tournament::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'start_date' => Carbon::now()->modify($data['start_date']),
            'end_date' => Carbon::now()->modify($data['end_date']),
            'location' => $data['location'],
            'max_participants' => $data['max_participants'],
            'price' => $data['price'],
            'prizes' => $data['prizes'],
            'status' => 'active',
            'user_id' => 1,
        ]);
        
        echo "[✓] Created: {$tournament->name} (ID: {$tournament->id})\n";
    } catch (\Exception $e) {
        echo "[✗] Failed to create tournament {$key}: {$e->getMessage()}\n";
    }
}

echo "\nTest data creation complete!\n";
echo "You can now view the tournaments on your homepage.\n";
