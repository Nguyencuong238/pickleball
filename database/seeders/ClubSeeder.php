<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Club;
use App\Models\User;
use App\Models\Province;
use App\Models\ClubActivity;
use Illuminate\Support\Str;

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các user hiện có hoặc tạo thêm
        $users = User::limit(5)->get();
        
        if ($users->count() < 5) {
            // Tạo thêm user nếu chưa đủ
            for ($i = 1; $i <= 5; $i++) {
                User::create([
                    'name' => 'Người Dùng ' . $i,
                    'email' => 'user' . $i . '@pickleball.vn',
                    'password' => bcrypt('password'),
                    'phone' => '090' . str_pad($i, 7, '0', STR_PAD_LEFT),
                    'email_verified_at' => now(),
                ]);
            }
            $users = User::limit(5)->get();
        }

        $provinces = Province::all();
        if ($provinces->count() === 0) {
            // Tạo một số tỉnh mẫu nếu chưa có
            $provinceNames = [
                'TP. Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng', 'Cần Thơ', 'Hải Phòng',
                'Bình Dương', 'Đồng Nai', 'Long An', 'Khánh Hòa', 'Quảng Ngãi'
            ];
            foreach ($provinceNames as $name) {
                Province::create(['name' => $name]);
            }
            $provinces = Province::all();
        }

        // Dữ liệu CLB và Nhóm
        $clubsData = [
            [
                'name' => 'Pickleball Thảo Điền Club',
                'type' => 'club',
                'description' => 'Câu lạc bộ Pickleball chuyên nghiệp tại Quận 2, TP.HCM. Tập luyện hàng ngày, tổ chức giải đấu định kỳ và huấn luyện các VĐV trẻ.',
                'objectives' => 'Nâng cao trình độ Pickleball, tổ chức các giải đấu, tuyển chọn VĐV trẻ tài năng, và quảng bá Pickleball tại TP.HCM.',
                'founded_date' => '2023-01-15',
            ],
            [
                'name' => 'Nhóm Tập Pickleball Tây Hồ',
                'type' => 'group',
                'description' => 'Nhóm tập Pickleball thân thiết tại Tây Hồ, Hà Nội. Chủ yếu là những người yêu thích Pickleball, từ sơ cấp đến nâng cao.',
                'objectives' => 'Giúp bạn yêu thích Pickleball trong một môi trường vui vẻ và thân thiện.',
                'founded_date' => '2023-06-20',
            ],
            [
                'name' => 'Đội Pickleball Đà Nẵng Elite',
                'type' => 'club',
                'description' => 'Đội Pickleball hàng đầu tại Đà Nẵng, tuyên bố tiêu chuẩn chuyên nghiệp với các HLV có kinh nghiệm quốc tế.',
                'objectives' => 'Đoạt giải vô địch quốc gia, phát triển tài năng Pickleball tại Đà Nẵng, và góp phần nâng cao ranking quốc tế.',
                'founded_date' => '2022-03-10',
            ],
            [
                'name' => 'Nhóm Pickleball Mẹ Bầu & Mẹ Trẻ',
                'type' => 'group',
                'description' => 'Nhóm vui vẻ dành cho các bà mẹ. Kết hợp Pickleball với sức khỏe, thư giãn và xã hội.',
                'objectives' => 'Tạo cộng đồng hỗ trợ lẫn nhau, rèn luyện sức khỏe thông qua Pickleball và xây dựng tình bạn.',
                'founded_date' => '2023-09-05',
            ],
            [
                'name' => 'Cần Thơ Pickleball Association',
                'type' => 'club',
                'description' => 'Hiệp hội Pickleball Cần Thơ - Đơn vị chính thức liên kết các CLB và nhóm Pickleball tại khu vực ĐBSH.',
                'objectives' => 'Quản lý, phát triển Pickleball ở vùng ĐBSH, tổ chức giải đấu toàn vùng, và phổ biến môn Pickleball.',
                'founded_date' => '2022-11-01',
            ],
            [
                'name' => 'Nhóm Pickleball Startup',
                'type' => 'group',
                'description' => 'Nhóm Pickleball của các startup tech ở TP.HCM. Nơi giao lưu, thể thao và networking giữa các founder & developer.',
                'objectives' => 'Tạo không gian networking, giảm stress qua thể thao, và xây dựng cộng đồng startup yêu Pickleball.',
                'founded_date' => '2024-01-15',
            ],
            [
                'name' => 'Pickleball Bình Dương Pro',
                'type' => 'club',
                'description' => 'CLB Pickleball chuyên nghiệp Bình Dương. Có sân thi đấu tiêu chuẩn quốc tế và đội HLV chuyên môn.',
                'objectives' => 'Phát triển Pickleball chuyên nghiệp tại Bình Dương, cung cấp huấn luyện chất lượng cao, tổ chức giải đấu.',
                'founded_date' => '2023-04-20',
            ],
            [
                'name' => 'Nhóm Pickleball Siêu Tốc Đồng Nai',
                'type' => 'group',
                'description' => 'Nhóm tập Pickleball nhanh chóng với lịch tập linh hoạt. Phù hợp cho những người bận rộn.',
                'objectives' => 'Cung cấp lịch tập hợp lý, giúp mọi người tập Pickleball mà không ảnh hưởng công việc.',
                'founded_date' => '2023-07-10',
            ],
            [
                'name' => 'Long An Pickleball Champions',
                'type' => 'club',
                'description' => 'Clb Pickleball giành nhiều giải thưởng ở Long An. Đội ngũ VĐV mạnh và liên tục cải tiến kỹ năng.',
                'objectives' => 'Tiếp tục giành các giải vô địch, phát triển cầu thủ trẻ tài năng, quảng bá Pickleball tại Long An.',
                'founded_date' => '2022-08-12',
            ],
            [
                'name' => 'Nhóm Pickleball Các Ông Bà Khỏe',
                'type' => 'group',
                'description' => 'Nhóm Pickleball cho những người từ 60+ tuổi. Tập luyện để giữ sức khỏe, vận động và có khoảng thời gian vui vẻ với bạn bè.',
                'objectives' => 'Giúp các ông bà khỏe mạnh qua Pickleball, xây dựng cộng đồng người cao tuổi yêu thích thể thao.',
                'founded_date' => '2023-05-18',
            ],
            [
                'name' => 'Khánh Hòa Pickleball Club',
                'type' => 'club',
                'description' => 'CLB Pickleball hàng đầu tại thành phố biển Khánh Hòa. Sân thi đấu đẹp với view biển, môi trường tập luyện tuyệt vời.',
                'objectives' => 'Phát triển Pickleball tại Khánh Hòa, tổ chức giải đấu du lịch, cung cấp dịch vụ training chuyên nghiệp.',
                'founded_date' => '2023-02-28',
            ],
            [
                'name' => 'Nhóm Pickleball Quảng Ngãi Trẻ',
                'type' => 'group',
                'description' => 'Nhóm Pickleball dành cho thanh niên 18-30 tuổi tại Quảng Ngãi. Năng lượng cao, tổ chức hoạt động thường xuyên.',
                'objectives' => 'Tạo sân chơi cho giới trẻ, phát triển Pickleball ở Quảng Ngãi, tổ chức giải đấu giao lưu.',
                'founded_date' => '2023-10-05',
            ],
            [
                'name' => 'Hải Phòng Pickleball Sports',
                'type' => 'club',
                'description' => 'CLB Pickleball Hải Phòng với cơ sở vật chất hiện đại, đội HLV quốc tế, tuyển dụng VĐV trẻ tài năng.',
                'objectives' => 'Phát triển Pickleball chuyên nghiệp, tổ chức giải đấu quốc tế, đứng trong top các CLB hàng đầu Việt Nam.',
                'founded_date' => '2022-12-01',
            ],
            [
                'name' => 'Nhóm Pickleball Gia Đình TP.HCM',
                'type' => 'group',
                'description' => 'Nhóm Pickleball gia đình - nơi những gia đình tập luyện Pickleball cùng nhau. Bố mẹ, con em đều tham gia.',
                'objectives' => 'Tạo hoạt động gia đình lành mạnh, giáo dục thể thao cho trẻ em, xây dựng cộng đồng gia đình yêu thích Pickleball.',
                'founded_date' => '2023-08-15',
            ],
            [
                'name' => 'Pickleball Hà Nội Legends',
                'type' => 'club',
                'description' => 'CLB Pickleball Hà Nội gồm những VĐV huyền thoại của môn Pickleball. Kinh nghiệm lâu năm, kỹ thuật cao.',
                'objectives' => 'Giữ gìn truyền thống Pickleball, huấn luyện thế hệ mới, duy trì đẳng cấp hàng đầu của Pickleball Hà Nội.',
                'founded_date' => '2021-06-10',
            ],
        ];

        $userIndex = 0;
        foreach ($clubsData as $data) {
            // Gán người tạo theo vòng
            $creator = $users[$userIndex % $users->count()];
            $userIndex++;

            // Tạo club
            $club = Club::create([
                'user_id' => $creator->id,
                'name' => $data['name'],
                'type' => $data['type'],
                'description' => $data['description'],
                'objectives' => $data['objectives'],
                'founded_date' => $data['founded_date'],
                'status' => 'active',
            ]);

            // Thêm creator làm member
            $club->members()->attach($creator->id, ['role' => 'creator']);

            // Thêm các thành viên khác (random)
            $memberCount = rand(3, 5);
            $memberIds = $users->random(min($memberCount, $users->count()))
                ->pluck('id')
                ->toArray();
            
            foreach ($memberIds as $memberId) {
                if ($memberId !== $creator->id) {
                    $club->members()->attach($memberId, ['role' => 'member']);
                }
            }

            // Gán tỉnh (1-3 tỉnh ngẫu nhiên)
            $provinceCount = rand(1, 3);
            $selectedProvinces = $provinces->random(min($provinceCount, $provinces->count()))
                ->pluck('id')
                ->toArray();
            $club->provinces()->attach($selectedProvinces);

            // Tạo hoạt động (1-4 hoạt động)
            $activityCount = rand(1, 4);
            $activities = [
                [
                    'title' => 'Tập luyện thường xuyên',
                    'description' => 'Tập luyện kỹ thuật cơ bản và nâng cao',
                    'location' => 'Sân tập chính',
                ],
                [
                    'title' => 'Thi đấu giao hữu',
                    'description' => 'Trận đấu tập luyện giữa các thành viên',
                    'location' => 'Sân thi đấu',
                ],
                [
                    'title' => 'Đầu tiên vào mùa giải',
                    'description' => 'Chính thức khởi động mùa giải năm 2025',
                    'location' => 'Sân chính',
                ],
                [
                    'title' => 'Lớp huấn luyện cho người mới',
                    'description' => 'Dạy những cơ bản của Pickleball cho người mới bắt đầu',
                    'location' => 'Sân 1',
                ],
            ];

            for ($i = 0; $i < $activityCount; $i++) {
                $activity = $activities[$i % count($activities)];
                ClubActivity::create([
                    'club_id' => $club->id,
                    'title' => $activity['title'],
                    'description' => $activity['description'],
                    'location' => $activity['location'],
                    'activity_date' => now()->addDays(rand(1, 30)),
                    'max_participants' => rand(10, 30),
                ]);
            }
        }

        $this->command->info('✅ CLB & Nhóm Pickleball seeder hoàn tất!');
        $this->command->newLine();
        $this->command->info('📊 Thống kê:');
        $this->command->info('   - Tổng CLB & Nhóm: ' . Club::count());
        $this->command->info('   - Loại Clubs: ' . Club::where('type', 'club')->count());
        $this->command->info('   - Loại Groups: ' . Club::where('type', 'group')->count());
        $this->command->info('   - Tổng hoạt động: ' . ClubActivity::count());
        $this->command->newLine();
        $this->command->info('🔗 Xem danh sách: /clubs');
    }
}
