<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SkillQuizAnswerRequest;
use App\Models\SkillQuizAttempt;
use App\Services\SkillQuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillQuizController extends Controller
{
    public function __construct(
        private SkillQuizService $quizService
    ) {}

    /**
     * Check if user is eligible to take quiz
     * GET /api/skill-quiz/eligibility
     */
    public function eligibility(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->quizService->canTakeQuiz($user);

        return response()->json([
            'success' => true,
            'data' => [
                'allowed' => $result['allowed'],
                'reason' => $result['reason'] ?? null,
                'next_allowed_at' => $result['next_allowed_at'] ?? null,
                'days_remaining' => $result['days_remaining'] ?? null,
                'quiz_count' => $user->skill_quiz_count,
                'last_quiz_at' => $user->last_skill_quiz_at,
                'current_elo' => $user->elo_rating,
                'is_provisional' => $user->elo_is_provisional,
            ],
        ]);
    }

    /**
     * Start a new quiz attempt
     * POST /api/skill-quiz/start
     */
    public function start(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check eligibility
        $eligibility = $this->quizService->canTakeQuiz($user);
        if (!$eligibility['allowed']) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đủ điều kiện làm quiz',
                'data' => $eligibility,
            ], 403);
        }

        try {
            $attempt = $this->quizService->startQuiz($user);
            $questions = $this->quizService->getQuestions();

            return response()->json([
                'success' => true,
                'data' => [
                    'attempt_id' => $attempt->id,
                    'started_at' => $attempt->started_at->toIso8601String(),
                    'timeout_seconds' => SkillQuizService::TIMEOUT_SECONDS,
                    'min_time_seconds' => SkillQuizService::MIN_TIME_SECONDS,
                    'max_time_seconds' => SkillQuizService::MAX_TIME_SECONDS,
                    'questions' => $questions,
                    'total_questions' => count($questions),
                    'answer_scale' => [
                        ['value' => 0, 'label' => 'Chưa làm được'],
                        ['value' => 1, 'label' => 'Làm được hiếm khi'],
                        ['value' => 2, 'label' => 'Làm được khá thường xuyên'],
                        ['value' => 3, 'label' => 'Làm được ổn định trong thi đấu'],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể bắt đầu quiz: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current attempt status
     * GET /api/skill-quiz/attempt/{id}
     */
    public function attempt(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $attempt = SkillQuizAttempt::where('id', $id)
            ->where('user_id', $user->id)
            ->with('answers')
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiên quiz',
            ], 404);
        }

        $elapsedSeconds = now()->diffInSeconds($attempt->started_at);
        $timeoutSeconds = SkillQuizService::TIMEOUT_SECONDS;
        $remainingSeconds = max(0, $timeoutSeconds - $elapsedSeconds);

        return response()->json([
            'success' => true,
            'data' => [
                'attempt_id' => $attempt->id,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at->toIso8601String(),
                'elapsed_seconds' => $elapsedSeconds,
                'remaining_seconds' => $remainingSeconds,
                'answered_count' => $attempt->answers->count(),
                'answered_question_ids' => $attempt->answers->pluck('question_id'),
            ],
        ]);
    }

    /**
     * Record an answer
     * POST /api/skill-quiz/answer
     */
    public function answer(SkillQuizAnswerRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $attempt = SkillQuizAttempt::where('id', $validated['attempt_id'])
            ->where('user_id', $user->id)
            ->where('status', SkillQuizAttempt::STATUS_IN_PROGRESS)
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên quiz không hợp lệ hoặc đã kết thúc',
            ], 400);
        }

        // Check timeout
        $elapsedSeconds = now()->diffInSeconds($attempt->started_at);
        $timeoutSeconds = SkillQuizService::TIMEOUT_SECONDS;
        if ($elapsedSeconds >= $timeoutSeconds) {
            $this->quizService->submitQuiz($attempt);
            return response()->json([
                'success' => false,
                'message' => 'Quiz đã hết thời gian',
                'data' => ['auto_submitted' => true],
            ], 400);
        }

        try {
            $answer = $this->quizService->recordAnswer(
                $attempt,
                $validated['question_id'],
                $validated['answer_value'],
                $validated['time_spent_seconds'] ?? 0
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'answer_id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'answer_value' => $answer->answer_value,
                    'answered_at' => $answer->answered_at,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Submit completed quiz
     * POST /api/skill-quiz/submit
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'attempt_id' => 'required|uuid',
        ]);

        $attempt = SkillQuizAttempt::where('id', $request->attempt_id)
            ->where('user_id', $user->id)
            ->where('status', SkillQuizAttempt::STATUS_IN_PROGRESS)
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên quiz không hợp lệ hoặc đã nộp',
            ], 400);
        }

        // Check minimum answers (at least 30 of 36)
        $answeredCount = $attempt->answers()->count();
        if ($answeredCount < 30) {
            return response()->json([
                'success' => false,
                'message' => "Cần trả lời ít nhất 30 câu. Hiện tại: {$answeredCount}/36",
            ], 400);
        }

        try {
            $result = $this->quizService->submitQuiz($attempt);

            return response()->json([
                'success' => true,
                'message' => 'Nộp quiz thành công',
                'data' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get quiz result
     * GET /api/skill-quiz/result/{id}
     */
    public function result(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        // Verify ownership
        $attempt = SkillQuizAttempt::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kết quả quiz',
            ], 404);
        }

        if ($attempt->status !== SkillQuizAttempt::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz chưa hoàn thành',
            ], 400);
        }

        $result = $this->quizService->getResult($id);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
