<?php

namespace App\Http\Controllers\Verifier;

use App\Http\Controllers\Controller;
use App\Models\OprVerificationRequest;
use App\Models\SkillDomain;
use App\Services\OprVerificationService;
use App\Services\SkillQuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifierDashboardController extends Controller
{
    public function __construct(
        private OprVerificationService $verificationService,
        private SkillQuizService $quizService
    ) {}

    /**
     * Dashboard with stats and pending requests
     */
    public function index(): View
    {
        $stats = $this->verificationService->getStats();
        $pendingRequests = $this->verificationService->getPendingRequests(10);

        return view('verifier.dashboard', [
            'stats' => $stats,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /**
     * List all verification requests
     */
    public function requests(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $requests = $this->verificationService->getRequests($status, 20);

        return view('verifier.requests.index', [
            'requests' => $requests,
            'currentStatus' => $status,
            'stats' => $this->verificationService->getStats(),
        ]);
    }

    /**
     * Show verification request detail
     */
    public function show(OprVerificationRequest $verificationRequest): View
    {
        $verificationRequest->load(['user', 'verifier', 'media']);

        $user = $verificationRequest->user;

        // Get user's latest quiz results
        $latestQuiz = $user->latestSkillQuizAttempt();
        $quizResult = null;

        if ($latestQuiz) {
            try {
                $quizResult = $this->quizService->getResult($latestQuiz->id);
            } catch (\Exception $e) {
                // Quiz result not available
            }
        }

        $quizDomains = SkillDomain::pluck('name_vi', 'key')->toArray();

        return view('verifier.requests.show', [
            'verificationRequest' => $verificationRequest,
            'user' => $user,
            'quizResult' => $quizResult,
            'quizDomains' => $quizDomains
        ]);
    }

    /**
     * Approve verification request
     */
    public function approve(Request $request, OprVerificationRequest $verificationRequest): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->verificationService->approve(
                $verificationRequest,
                auth()->user(),
                $validated['notes'] ?? null
            );

            return redirect()->route('verifier.requests.index')
                ->with('success', 'Yêu cầu xác minh đã được chấp thuận');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject verification request
     */
    public function reject(Request $request, OprVerificationRequest $verificationRequest): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'required|string|min:10|max:1000',
        ], [
            'notes.required' => 'Vui lòng nhập lý do từ chối',
            'notes.min' => 'Lý do từ chối phải có ít nhất 10 ký tự',
        ]);

        try {
            $this->verificationService->reject(
                $verificationRequest,
                auth()->user(),
                $validated['notes']
            );

            return redirect()->route('verifier.requests.index')
                ->with('success', 'Yêu cầu xác minh đã bị từ chối');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
