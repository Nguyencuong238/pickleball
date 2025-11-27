<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Instructor;
use App\Models\Province;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = Province::all();
        
        if ($provinces->isEmpty()) {
            return;
        }

        $instructors = [
            [
                'name' => 'Nguyễn Văn A',
                'experience' => 'Có 10 năm kinh nghiệm giảng dạy Pickleball. Đạt chứng chỉ huấn luyện viên quốc tế. Đã giảng dạy cho hơn 500 học viên.',
                'ward' => 'Phường Tây Thạnh',
                'province_id' => $provinces->random()->id,
            ],
            [
                'name' => 'Trần Thị B',
                'experience' => 'Giảng viên Pickleball chuyên nghiệp với 8 năm kinh nghiệm. Chuyên về kỹ thuật vợt và chiến thuật trò chơi.',
                'ward' => 'Phường Bình Thuận',
                'province_id' => $provinces->random()->id,
            ],
            [
                'name' => 'Lê Minh C',
                'experience' => 'Huấn luyện viên hạng A. Từng tham gia các giải đấu quốc tế. Đặc biệt trong huấn luyện đôi nam nữ.',
                'ward' => 'Phường 1',
                'province_id' => $provinces->random()->id,
            ],
            [
                'name' => 'Phạm Hương D',
                'experience' => '12 năm kinh nghiệm với hơn 1000 học viên. Huấn luyện viên chính của Liên đoàn Pickleball. Chuyên về cải thiện kỹ năng.',
                'ward' => 'Phường 2',
                'province_id' => $provinces->random()->id,
            ],
            [
                'name' => 'Vũ Đình E',
                'experience' => 'Giảng viên trẻ tài năng, 5 năm kinh nghiệm. Đạt danh hiệu VĐV xuất sắc. Chuyên dạy các học viên mới bắt đầu.',
                'ward' => 'Phường 3',
                'province_id' => $provinces->random()->id,
            ],
            [
                'name' => 'Đỗ Thị F',
                'experience' => '9 năm kinh nghiệm giảng dạy Pickleball. Được cấp chứng chỉ huấn luyện viên cấp quốc tế. Chuyên dạy kỹ thuật cơ bản.',
                'ward' => 'Phường 4',
                'province_id' => $provinces->random()->id,
            ],
        ];

        foreach ($instructors as $instructor) {
            Instructor::create($instructor);
        }
    }
}
