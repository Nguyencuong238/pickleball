<?php

namespace Database\Seeders;

use App\Models\Stadium;
use Illuminate\Database\Seeder;

class StadiumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo một số sân test với các trường mới
        Stadium::create([
            'name' => 'Pickleball Rạch Chiếc Premium',
            'description' => 'Sân pickleball hiện đại với đầy đủ tiện ích',
            'address' => 'Quận 2, TP.HCM',
            'phone' => '0901234567',
            'email' => 'rach-chiec@example.com',
            'opening_hours' => '05:00 - 23:00',
            'amenities' => json_encode(['🚿 Phòng tắm', '🅿️ Bãi đỗ xe', '☕ Canteen', '🏪 Cửa hàng']),
            'status' => 'active',
            'is_featured' => true,
            'is_premium' => true,
        ]);

        Stadium::create([
            'name' => 'Thảo Điền Sports Club',
            'description' => 'Sân pickleball chất lượng cao tại Thủ Đức',
            'address' => 'Thủ Đức, TP.HCM',
            'phone' => '0987654321',
            'email' => 'thao-dien@example.com',
            'opening_hours' => '06:00 - 22:00',
            'amenities' => json_encode(['🚿 Phòng tắm VIP', '🅿️ Bãi đỗ xe', '🏋️ Gym']),
            'status' => 'active',
            'is_featured' => false,
            'is_premium' => true,
        ]);

        Stadium::create([
            'name' => 'Cầu Giấy Pickleball Arena',
            'description' => 'Sân pickleball nổi bật tại Hà Nội',
            'address' => 'Cầu Giấy, Hà Nội',
            'phone' => '0912345678',
            'email' => 'cau-giay@example.com',
            'opening_hours' => '05:30 - 23:00',
            'amenities' => json_encode(['🚿 Phòng tắm', '🅿️ Bãi đỗ xe', '🏪 Cửa hàng']),
            'status' => 'active',
            'is_featured' => true,
            'is_premium' => false,
        ]);

        Stadium::create([
            'name' => 'Sân Pickleball Đà Nẵng',
            'description' => 'Sân pickleball chất lượng tại Đà Nẵng',
            'address' => 'Hải Châu, Đà Nẵng',
            'phone' => '0934567890',
            'email' => 'da-nang@example.com',
            'opening_hours' => '06:00 - 21:00',
            'amenities' => json_encode(['🚿 Phòng tắm', '☕ Canteen']),
            'status' => 'active',
            'is_featured' => false,
            'is_premium' => false,
        ]);

        Stadium::create([
            'name' => 'Vũng Tàu Sports Complex',
            'description' => 'Sân pickleball ven biển chất lượng cao',
            'address' => 'Vũng Tàu, Bà Rịa - Vũng Tàu',
            'phone' => '0945678901',
            'email' => 'vung-tau@example.com',
            'opening_hours' => '06:00 - 22:00',
            'amenities' => json_encode(['🚿 Phòng tắm', '🅿️ Bãi đỗ xe', '🏊 Hồ bơi']),
            'status' => 'active',
            'is_featured' => true,
            'is_premium' => false,
        ]);
    }
}
