<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

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
     */
    public function submitQuiz(Request $request)
    {
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

        return response()->json([
            'success' => true,
            'score' => $score,
            'correctCount' => $correctCount,
            'totalCount' => $totalCount,
            'results' => $results,
            'message' => "Bạn trả lời đúng {$correctCount}/{$totalCount} câu hỏi. Điểm: {$score}%"
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
