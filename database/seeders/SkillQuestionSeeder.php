<?php

namespace Database\Seeders;

use App\Models\SkillDomain;
use App\Models\SkillQuestion;
use Illuminate\Database\Seeder;

class SkillQuestionSeeder extends Seeder
{
    /**
     * Seed 36 skill assessment questions (6 per domain).
     * Questions are in Vietnamese with anchor levels for ELO calculation.
     */
    public function run(): void
    {
        $questions = [
            // DOMAIN 1 - RULES & POSITIONING
            // Anchor: 2.0 -> 4.0
            'rules' => [
                ['question_vi' => 'Tôi hiểu và áp dụng đúng luật double bounce rule trong trận', 'anchor_level' => 2.0],
                ['question_vi' => 'Tôi hiếm khi đứng sai vị trí khi giao bóng / trả giao', 'anchor_level' => 2.5],
                ['question_vi' => 'Tôi biết khi nào được volley, khi nào không trong NVZ', 'anchor_level' => 3.0],
                ['question_vi' => 'Tôi gọi đúng line/out và hiểu luật replay, foot fault', 'anchor_level' => 3.0],
                ['question_vi' => 'Tôi xoay vị trí hợp lý khi đánh đôi (stacking cơ bản)', 'anchor_level' => 3.5],
                ['question_vi' => 'Tôi hiếm khi mất điểm vì lỗi luật', 'anchor_level' => 4.0],
            ],

            // DOMAIN 2 - CONSISTENCY
            // Anchor: 2.5 -> 4.5
            'consistency' => [
                ['question_vi' => 'Tôi có thể đánh rally 6-8 bóng không lỗi', 'anchor_level' => 2.5],
                ['question_vi' => 'Tôi giữ bóng trong sân khi đánh chéo cơ bản', 'anchor_level' => 3.0],
                ['question_vi' => 'Tôi giảm lỗi tự đánh hỏng khi bị ép nhịp', 'anchor_level' => 3.5],
                ['question_vi' => 'Tôi đánh ổn định cả forehand & backhand', 'anchor_level' => 3.5],
                ['question_vi' => 'Tôi giữ được độ ổn định xuyên suốt 1 game', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi hiếm khi đánh hỏng bóng dễ', 'anchor_level' => 4.5],
            ],

            // DOMAIN 3 - SERVE & RETURN
            // Anchor: 3.0 -> 5.0
            'serve_return' => [
                ['question_vi' => 'Tôi giao bóng đúng luật và ổn định', 'anchor_level' => 3.0],
                ['question_vi' => 'Tôi điều hướng được vị trí giao bóng (wide / body)', 'anchor_level' => 3.5],
                ['question_vi' => 'Return của tôi sâu và gây áp lực', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi hạn chế lỗi return trước giao khó', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi tận dụng return để lên net', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi biến serve/return thành lợi thế chiến thuật', 'anchor_level' => 5.0],
            ],

            // DOMAIN 4 - DINK & NET PLAY
            // Anchor: 3.5 -> 5.5
            'dink_net' => [
                ['question_vi' => 'Tôi duy trì được dink rally mà không nóng vội', 'anchor_level' => 3.5],
                ['question_vi' => 'Tôi điều hướng dink sang backhand đối thủ', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi nhận biết thời điểm speed-up hợp lý', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi volley ổn định tại NVZ line', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi tạo áp lực ở khu vực net', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi kiểm soát được nhịp độ ở kitchen', 'anchor_level' => 5.5],
            ],

            // DOMAIN 5 - RESET & DEFENSE
            // Anchor: 4.0 -> 5.5
            'reset_defense' => [
                ['question_vi' => 'Tôi reset được bóng khi bị smash', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi block drive ổn định', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi giữ bóng thấp khi phòng thủ', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi chuyển từ defense sang neutral hiệu quả', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi đọc được hướng smash của đối thủ', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi giữ bình tĩnh khi bị ép nhịp', 'anchor_level' => 5.5],
            ],

            // DOMAIN 6 - TACTICS & PARTNER PLAY
            // Anchor: 4.0 -> 6.0
            'tactics' => [
                ['question_vi' => 'Tôi chủ động phối hợp với partner', 'anchor_level' => 4.0],
                ['question_vi' => 'Tôi chọn mục tiêu tấn công hợp lý', 'anchor_level' => 4.5],
                ['question_vi' => 'Tôi nhận diện điểm yếu đối thủ trong trận', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi điều chỉnh chiến thuật giữa game', 'anchor_level' => 5.0],
                ['question_vi' => 'Tôi bọc lót và cover sân hiệu quả', 'anchor_level' => 5.5],
                ['question_vi' => 'Tôi giữ nhịp đội và tinh thần thi đấu', 'anchor_level' => 6.0],
            ],
        ];

        $totalQuestions = 0;

        foreach ($questions as $domainKey => $domainQuestions) {
            $domain = SkillDomain::where('key', $domainKey)->first();

            if (!$domain) {
                $this->command->warn("Domain not found: {$domainKey}");
                continue;
            }

            foreach ($domainQuestions as $order => $questionData) {
                SkillQuestion::updateOrCreate(
                    [
                        'domain_id' => $domain->id,
                        'order_in_domain' => $order + 1,
                    ],
                    [
                        'question_vi' => $questionData['question_vi'],
                        'question_en' => null,
                        'anchor_level' => $questionData['anchor_level'],
                        'is_active' => true,
                    ]
                );
                $totalQuestions++;
            }
        }

        $this->command->info("[QUIZ] Seeded {$totalQuestions} skill assessment questions");
    }
}
