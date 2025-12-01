<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('provinces')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $provinces = [
            'TP. Hà Nội',
            'TP. Hồ Chí Minh',
            'TP. Hải Phòng',
            'TP. Huế',
            'TP. Đà Nẵng',
            'TP. Cần Thơ',
            'An Giang',
            'Bắc Ninh',
            'Cà Mau',
            'Cao Bằng',
            'Đắk Lắk',
            'Điện Biên',
            'Đồng Nai',
            'Đồng Tháp',
            'Gia Lai',
            'Hà Tĩnh',
            'Hưng Yên',
            'Khánh Hoà',
            'Lai Châu',
            'Lâm Đồng',
            'Lạng Sơn',
            'Lào Cai',
            'Nghệ An',
            'Ninh Bình',
            'Phú Thọ',
            'Quảng Ngãi',
            'Quảng Ninh',
            'Quảng Trị',
            'Sơn La',
            'Tây Ninh',
            'Thái Nguyên',
            'Thanh Hóa',
            'Tuyên Quang',
            'Vĩnh Long',
        ];

        foreach ($provinces as $province) {
            DB::table('provinces')->insert([
                'name' => $province,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
