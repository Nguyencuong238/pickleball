<?php

namespace Database\Seeders;

use App\Models\PointTask;
use Illuminate\Database\Seeder;

class PointTaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            // User tasks (11)
            [
                'code' => PointTask::CODE_REFERRAL,
                'name' => 'Giới thiệu bạn bè',
                'points' => 10,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_DAILY,
                'frequency' => PointTask::FREQ_UNLIMITED,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_NONE,
                'description' => 'Nhận điểm khi bạn giới thiệu hoàn thành Skill Quiz',
            ],
            [
                'code' => PointTask::CODE_CHECK_IN_STADIUM,
                'name' => 'Check-in sân tập',
                'points' => 1,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_DAILY,
                'frequency' => PointTask::FREQ_DAILY,
                'requires_approval' => true,
                'proof_type' => PointTask::PROOF_IMAGE,
                'description' => 'Chụp ảnh check-in tại sân tập',
            ],
            [
                'code' => PointTask::CODE_WEEKLY_5_MATCHES,
                'name' => 'Thử thách 5 trận/tuần',
                'points' => 5,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_DAILY,
                'frequency' => PointTask::FREQ_WEEKLY,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_NONE,
                'description' => 'Hoàn thành 5 trận OCR trong tuần',
            ],
            [
                'code' => PointTask::CODE_JOIN_EVENT,
                'name' => 'Tham gia sự kiện',
                'points' => 5,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_EVENT,
                'frequency' => PointTask::FREQ_UNLIMITED,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_QR_CODE,
                'description' => 'Check-in sự kiện bằng QR code',
            ],
            [
                'code' => PointTask::CODE_SPECIAL_CHALLENGE,
                'name' => 'Thử thách đặc biệt',
                'points' => 15,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_EVENT,
                'frequency' => PointTask::FREQ_UNLIMITED,
                'requires_approval' => true,
                'proof_type' => PointTask::PROOF_IMAGE,
                'description' => 'Hoàn thành thử thách đặc biệt từ Admin',
            ],
            [
                'code' => PointTask::CODE_JOIN_FB_GROUP,
                'name' => 'Tham gia Group Facebook',
                'points' => 1,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_SOCIAL,
                'frequency' => PointTask::FREQ_ONCE,
                'requires_approval' => true,
                'proof_type' => PointTask::PROOF_LINK,
                'description' => 'Tham gia Group Facebook OnePickleball',
            ],
            [
                'code' => PointTask::CODE_FOLLOW_FB_PAGE,
                'name' => 'Follow Fanpage Facebook',
                'points' => 1,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_SOCIAL,
                'frequency' => PointTask::FREQ_ONCE,
                'requires_approval' => true,
                'proof_type' => PointTask::PROOF_LINK,
                'description' => 'Follow Fanpage Facebook OnePickleball',
            ],
            [
                'code' => PointTask::CODE_SUBSCRIBE_YOUTUBE,
                'name' => 'Đăng ký kênh Youtube',
                'points' => 1,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_SOCIAL,
                'frequency' => PointTask::FREQ_ONCE,
                'requires_approval' => true,
                'proof_type' => PointTask::PROOF_LINK,
                'description' => 'Đăng ký kênh Youtube OnePickleball',
            ],
            [
                'code' => PointTask::CODE_FOLLOW_TIKTOK,
                'name' => 'Follow TikTok',
                'points' => 1,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_SOCIAL,
                'frequency' => PointTask::FREQ_ONCE,
                'requires_approval' => true,
                'proof_type' => PointTask::PROOF_LINK,
                'description' => 'Follow kênh TikTok OnePickleball',
            ],
            [
                'code' => PointTask::CODE_JOIN_CLUB,
                'name' => 'Tham gia CLB/Nhóm',
                'points' => 5,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_TOURNAMENT,
                'frequency' => PointTask::FREQ_ONCE,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_NONE,
                'description' => 'Tham gia một CLB trên hệ thống',
            ],
            [
                'code' => PointTask::CODE_CREATE_OCR_MATCH,
                'name' => 'Tạo trận OCR',
                'points' => 2,
                'role' => PointTask::ROLE_USER,
                'category' => PointTask::CATEGORY_TOURNAMENT,
                'frequency' => PointTask::FREQ_UNLIMITED,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_NONE,
                'description' => 'Tạo và hoàn thành trận đấu OCR',
            ],

            // Home Yard tasks (3) - once per stadium
            [
                'code' => PointTask::CODE_UPDATE_STADIUM_INFO,
                'name' => 'Cập nhật thông tin cụm sân',
                'points' => 10,
                'role' => PointTask::ROLE_HOME_YARD,
                'category' => PointTask::CATEGORY_TOURNAMENT,
                'frequency' => PointTask::FREQ_ONCE,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_NONE,
                'description' => 'Cập nhật đầy đủ thông tin cụm sân',
            ],
            [
                'code' => PointTask::CODE_CREATE_SOCIAL_SCHEDULE,
                'name' => 'Tạo lịch đấu Social',
                'points' => 5,
                'role' => PointTask::ROLE_HOME_YARD,
                'category' => PointTask::CATEGORY_TOURNAMENT,
                'frequency' => PointTask::FREQ_ONCE,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_NONE,
                'description' => 'Tạo lịch đấu Social cho sân',
            ],
            [
                'code' => PointTask::CODE_CREATE_TOURNAMENT,
                'name' => 'Tạo giải đấu',
                'points' => 20,
                'role' => PointTask::ROLE_HOME_YARD,
                'category' => PointTask::CATEGORY_TOURNAMENT,
                'frequency' => PointTask::FREQ_ONCE,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_NONE,
                'description' => 'Tạo giải đấu mới cho sân',
            ],

            // Referee task (1)
            [
                'code' => PointTask::CODE_REFEREE_SCORE_MATCH,
                'name' => 'Chấm điểm trận đấu',
                'points' => 10,
                'role' => PointTask::ROLE_REFEREE,
                'category' => PointTask::CATEGORY_TOURNAMENT,
                'frequency' => PointTask::FREQ_UNLIMITED,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_NONE,
                'description' => 'Chấm điểm cho trận đấu giải đấu',
            ],

            // Expert Host task (1)
            [
                'code' => PointTask::CODE_EXPERT_VERIFY_ELO,
                'name' => 'Chấm trình VĐV',
                'points' => 15,
                'role' => PointTask::ROLE_EXPERT_HOST,
                'category' => PointTask::CATEGORY_TOURNAMENT,
                'frequency' => PointTask::FREQ_UNLIMITED,
                'requires_approval' => false,
                'proof_type' => PointTask::PROOF_NONE,
                'description' => 'Xác nhận trình độ ELO cho VĐV',
            ],
        ];

        foreach ($tasks as $task) {
            PointTask::updateOrCreate(['code' => $task['code']], $task);
        }
    }
}
