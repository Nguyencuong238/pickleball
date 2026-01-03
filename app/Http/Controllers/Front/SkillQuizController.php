<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\SkillQuizAttempt;
use App\Services\SkillQuizService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class SkillQuizController extends Controller
{
    public function __construct(
        private SkillQuizService $quizService
    ) {}

    /**
     * Quiz landing page - check eligibility
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $eligibility = $this->quizService->canTakeQuiz($user);

        // Check for in-progress attempt
        $inProgress = SkillQuizAttempt::where('user_id', $user->id)
            ->where('status', SkillQuizAttempt::STATUS_IN_PROGRESS)
            ->first();

        // Get quiz history
        $history = $this->quizService->getUserHistory($user, 5);

        return view('front.skill-quiz.index', [
            'eligibility' => $eligibility,
            'inProgress' => $inProgress,
            'user' => $user,
            'history' => $history,
        ]);
    }

    /**
     * Start page with instructions
     */
    public function start(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $eligibility = $this->quizService->canTakeQuiz($user);

        if (!$eligibility['allowed']) {
            return redirect()->route('skill-quiz.index')
                ->with('error', 'Chưa đủ điều kiện làm quiz');
        }

        return view('front.skill-quiz.start', [
            'timeoutSeconds' => SkillQuizService::TIMEOUT_SECONDS,
            'minTimeSeconds' => SkillQuizService::MIN_TIME_SECONDS,
            'maxTimeSeconds' => SkillQuizService::MAX_TIME_SECONDS,
        ]);
    }

    /**
     * Main quiz page
     */
    public function quiz(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        try {
            // Get or check for existing in-progress attempt
            $attempt = SkillQuizAttempt::where('user_id', $user->id)
                ->where('status', SkillQuizAttempt::STATUS_IN_PROGRESS)
                ->first();

            if (!$attempt) {
                // Start new attempt
                $eligibility = $this->quizService->canTakeQuiz($user);
                if (!$eligibility['allowed']) {
                    return redirect()->route('skill-quiz.index')
                        ->with('error', 'Chưa đủ điều kiện làm quiz');
                }

                $attempt = $this->quizService->startQuiz($user);
            }

            // Check for timeout
            $elapsed = now()->diffInSeconds($attempt->started_at);
            if ($elapsed >= SkillQuizService::TIMEOUT_SECONDS) {
                $this->quizService->autoSubmit($attempt);
                return redirect()->route('skill-quiz.result', $attempt->id)
                    ->with('info', 'Quiz đã hết thời gian và được tự động nộp');
            }

            // Get questions
            $questions = $this->quizService->getQuestions();

            // Get already answered questions
            $answeredQuestions = $attempt->answers()->pluck('answer_value', 'question_id')->toArray();

            return view('front.skill-quiz.quiz', [
                'attemptId' => $attempt->id,
                'startedAt' => $attempt->started_at->toIso8601String(),
                'timeoutSeconds' => SkillQuizService::TIMEOUT_SECONDS,
                'minTimeSeconds' => SkillQuizService::MIN_TIME_SECONDS,
                'questions' => $questions,
                'answeredQuestions' => $answeredQuestions,
                'totalQuestions' => count($questions),
            ]);
        } catch (\Exception $e) {
            Log::error('Skill Quiz error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);
            return redirect()->route('skill-quiz.index')
                ->with('error', 'Có lỗi xảy ra. Vui lòng thử lại sau.');
        }
    }

    /**
     * Result page
     */
    public function result(Request $request, string $id): View|RedirectResponse
    {
        $user = $request->user();

        $attempt = SkillQuizAttempt::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attempt) {
            return redirect()->route('skill-quiz.index')
                ->with('error', 'Không tìm thấy kết quả quiz');
        }

        // Handle different statuses
        return match ($attempt->status) {
            SkillQuizAttempt::STATUS_COMPLETED => $this->showCompletedResult($attempt, $user, $id),
            SkillQuizAttempt::STATUS_IN_PROGRESS => redirect()->route('skill-quiz.quiz')
                ->with('info', 'Quiz chưa hoàn thành'),
            SkillQuizAttempt::STATUS_ABANDONED => redirect()->route('skill-quiz.index')
                ->with('warning', 'Quiz đã bị bỏ ngang. Bạn có thể làm lại.'),
            default => redirect()->route('skill-quiz.index')
                ->with('error', 'Trạng thái quiz không hợp lệ'),
        };
    }

    /**
     * Show completed result view
     */
    private function showCompletedResult(SkillQuizAttempt $attempt, $user, string $id): View|RedirectResponse
    {
        try {
            $result = $this->quizService->getResult($id);

            if (!$result) {
                return redirect()->route('skill-quiz.index')
                    ->with('error', 'Không thể tải kết quả quiz');
            }

            return view('front.skill-quiz.result', [
                'result' => $result,
                'user' => $user,
                'attempt' => $attempt,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading quiz result: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'attempt_id' => $id,
                'exception' => $e,
            ]);
            return redirect()->route('skill-quiz.index')
                ->with('error', 'Có lỗi khi tải kết quả. Vui lòng thử lại.');
        }
    }
}
