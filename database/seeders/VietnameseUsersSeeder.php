<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VietnameseUsersSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            // 25 tên nam
            'Nguyễn Văn An',
            'Trần Minh Đức',
            'Lê Hoàng Nam',
            'Phạm Quốc Huy',
            'Hoàng Văn Tùng',
            'Vũ Đình Khoa',
            'Đặng Hữu Thắng',
            'Bùi Minh Quân',
            'Ngô Thanh Bình',
            'Dương Văn Hải',
            'Lý Quang Vinh',
            'Phan Đức Anh',
            'Trịnh Văn Long',
            'Đinh Công Minh',
            'Võ Hoàng Phúc',
            'Mai Văn Sơn',
            'Hồ Quang Hiếu',
            'Đỗ Mạnh Cường',
            'Nguyễn Thành Đạt',
            'Trần Đình Trọng',
            'Lê Văn Kiên',
            'Phạm Anh Tuấn',
            'Hoàng Minh Tiến',
            'Vũ Ngọc Hùng',
            'Đặng Quốc Bảo',
            // 25 tên nữ
            'Nguyễn Thị Hoa',
            'Trần Thu Hằng',
            'Lê Thị Mai',
            'Phạm Thanh Thảo',
            'Hoàng Thị Lan',
            'Vũ Ngọc Anh',
            'Đặng Thúy Hạnh',
            'Bùi Thu Trang',
            'Ngô Thị Linh',
            'Dương Minh Châu',
            'Lý Thị Hương',
            'Phan Thu Hiền',
            'Trịnh Ngọc Bích',
            'Đinh Thị Ngọc',
            'Võ Thị Diễm',
            'Mai Phương Thúy',
            'Hồ Thị Yến',
            'Đỗ Thị Nhung',
            'Nguyễn Thùy Dung',
            'Trần Khánh Linh',
            'Lê Thu Hà',
            'Phạm Thị Vân',
            'Hoàng Thị Huyền',
            'Vũ Thị Thương',
            'Đặng Ngọc Trâm',
        ];

        foreach ($names as $name) {
            $user = User::create([
                'name' => $name,
                'slug' => Str::slug($name) . '-' . uniqid(),
                'email' => Str::slug($name, '.') . '@example.com',
                'phone' => '09' . rand(10000000, 99999999),
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'approved',
                'role_type' => 'user',
            ]);

            $user->assignRole('athlete');
        }

        $this->command->info('Created 50 Vietnamese users with athlete role.');
    }
}
