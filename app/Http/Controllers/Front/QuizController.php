<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    /**
     * Hiển thị trang quiz chính
     */
    public function index()
    {
        $categories = Quiz::where('is_active', true)
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');

        return view('front.quiz.index', compact('categories'));
    }

    /**
     * Lấy danh sách câu hỏi theo category hoặc tất cả câu hỏi
     */
    public function getQuestions(Request $request)
    {
        $query = Quiz::where('is_active', true);

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        $limit = $request->input('limit', 10);
        $quizzes = $query->inRandomOrder()->limit($limit)->get();

        // Trả về quiz mà không có correct_answer
        return response()->json([
            'success' => true,
            'data' => $quizzes->map(function ($quiz) {
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'question' => $quiz->question,
                    'options' => $quiz->options,
                    'difficulty' => $quiz->difficulty,
                    'category' => $quiz->category,
                ];
            })
        ]);
    }

    /**
     * Submit bài quiz và tính điểm
     * Nếu user được giới thiệu (referral), người giới thiệu sẽ nhận điểm thưởng
     */
    public function submitQuiz(Request $request)
    {
        // Xác thực user phải đăng nhập
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để tham gia quiz'
            ], 401);
        }

        $answers = $request->input('answers', []);

        if (empty($answers)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng trả lời ít nhất một câu hỏi'
            ], 400);
        }

        $results = [];
        $correctCount = 0;
        $totalCount = 0;

        foreach ($answers as $quizId => $userAnswer) {
            $quiz = Quiz::find($quizId);

            if (!$quiz) {
                continue;
            }

            $totalCount++;
            $isCorrect = $quiz->correct_answer === $userAnswer;

            if ($isCorrect) {
                $correctCount++;
            }

            $results[] = [
                'id' => $quiz->id,
                'question' => $quiz->question,
                'userAnswer' => $userAnswer,
                'correctAnswer' => $quiz->correct_answer,
                'isCorrect' => $isCorrect,
                'explanation' => $quiz->explanation,
                'options' => $quiz->options,
            ];
        }

        $score = $totalCount > 0 ? round(($correctCount / $totalCount) * 100, 2) : 0;

        // Nếu user được giới thiệu (có referred_by) và đạt điểm yêu cầu lần đầu
        // Thì cộng điểm cho người giới thiệu (chỉ cộng 1 lần)
        $referrerRewardPoints = 0;
        $user = auth()->user();
        
        // DEBUG: Log thông tin user
        Log::info('Quiz Submit Debug', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'referred_by' => $user->referred_by,
            'score' => $score,
        ]);
        
        // Cộng điểm chỉ khi: 
        // 1. User được giới thiệu (referred_by không null)
        // 2. Score đạt mức yêu cầu (>= 50%)
        // 3. Referral status = pending (chưa được cộng)
        if ($user->referred_by && $score >= 50) {
            Log::info('Condition 1 passed: user has referrer and score >= 50');
            
            // Check xem có referral pending chưa
            $referral = \App\Models\Referral::where('referred_user_id', $user->id)
                ->where('status', 'pending')
                ->first();
            
            Log::info('Referral Check', [
                'referral_found' => $referral ? 'Yes' : 'No',
                'referred_user_id' => $user->id,
            ]);
            
            if ($referral) {
                Log::info('Referral found, checking referrer');
                
                $referrer = \App\Models\User::find($user->referred_by);
                
                Log::info('Referrer Check', [
                    'referrer_id' => $user->referred_by,
                    'referrer_found' => $referrer ? 'Yes' : 'No',
                    'referrer_name' => $referrer?->name,
                ]);
                
                if ($referrer) {
                    // Cộng 10 điểm cho người giới thiệu (lần đầu và duy nhất)
                    $referrerRewardPoints = 10;
                    
                    Log::info('Adding points to referrer', [
                        'referrer_id' => $referrer->id,
                        'points' => $referrerRewardPoints,
                    ]);
                    
                    $referrer->addPoints(
                        $referrerRewardPoints,
                        'quiz_completion', 
                        "User {$user->name} hoàn thành quiz với điểm {$score}%",
                        [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                            'quiz_score' => $score,
                            'quiz_count' => $totalCount,
                        ]
                    );
                    
                    Log::info('Points added successfully');
                    
                    // Update referral status thành completed (đánh dấu đã cộng)
                    $referral->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                    
                    Log::info('Referral status updated to completed');
                } else {
                    Log::warning('Referrer not found', ['referrer_id' => $user->referred_by]);
                }
            } else {
                Log::info('No pending referral found for this user');
            }
        } else {
            if (!$user->referred_by) {
                Log::info('User has no referrer (referred_by is null)');
            }
            if ($score < 50) {
                Log::info('Score is below 50%', ['score' => $score]);
            }
        }

        return response()->json([
            'success' => true,
            'score' => $score,
            'correctCount' => $correctCount,
            'totalCount' => $totalCount,
            'results' => $results,
            'referrerRewardPoints' => $referrerRewardPoints,
            'message' => "Bạn trả lời đúng {$correctCount}/{$totalCount} câu hỏi. Điểm: {$score}%" 
                . ($referrerRewardPoints > 0 ? "\n🎉 Người giới thiệu của bạn được cộng thêm {$referrerRewardPoints} điểm!" : '')
        ]);
    }

    /**
     * Hiển thị trang chi tiết quiz
     */
    public function show($id)
    {
        $quiz = Quiz::where('is_active', true)->find($id);

        if (!$quiz) {
            return abort(404);
        }

        return view('front.quiz.show', compact('quiz'));
    }
}
