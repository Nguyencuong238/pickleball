<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Models\SpecialChallenge;
use App\Services\PointEarningService;
use App\Services\PointSubmissionService;
use App\Services\SocialVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserPointController extends Controller
{
    public function __construct(
        private PointEarningService $pointEarningService,
        private PointSubmissionService $submissionService,
        private SocialVerificationService $socialVerificationService
    ) {}

    /**
     * Earn points page - show available tasks
     */
    public function index(): View
    {
        $user = auth()->user();

        // Get available tasks with eligibility (for all user roles)
        $tasks = $this->pointEarningService->getAllAvailableTasks($user);

        // Get ongoing special challenges
        $challenges = SpecialChallenge::ongoing()->get();

        // Pre-fetch special challenge task to avoid N+1 in view
        $specialChallengeTask = PointTask::findByCode(PointTask::CODE_SPECIAL_CHALLENGE);

        // Get user's wallet balance (ensure wallet exists)
        $wallet = $user->wallet ?? $user->wallet()->firstOrCreate(['points' => 0]);
        $balance = $wallet->points;

        // Get user's social verification status
        $socialStatus = $this->socialVerificationService->getVerificationStatus($user);

        // Get pending submissions count
        $pendingCount = PointSubmission::where('user_id', $user->id)
            ->where('status', PointSubmission::STATUS_PENDING)
            ->count();

        return view('front.points.index', compact(
            'tasks', 'challenges', 'balance', 'socialStatus', 'pendingCount', 'specialChallengeTask'
        ));
    }

    /**
     * Show submission form for a task
     */
    public function showSubmitForm(PointTask $task): View
    {
        // Authorization check
        $this->authorize('showSubmitForm', $task);

        $user = auth()->user();

        // Check if already has pending submission
        $pendingSubmission = PointSubmission::where('user_id', $user->id)
            ->where('point_task_id', $task->id)
            ->where('status', PointSubmission::STATUS_PENDING)
            ->first();

        // For special_challenge, get active challenges
        $challenges = collect();
        if ($task->code === PointTask::CODE_SPECIAL_CHALLENGE) {
            $challenges = SpecialChallenge::ongoing()->get();
        }

        return view('front.points.submit', compact('task', 'pendingSubmission', 'challenges'));
    }

    /**
     * Submit proof for approval
     */
    public function submit(Request $request, PointTask $task): RedirectResponse
    {
        // Authorization check - verifies task is active, requires approval, and user is eligible
        $this->authorize('submit', $task);

        $user = auth()->user();

        // Validate based on proof type
        $rules = $this->getValidationRules($task);
        $request->validate($rules);

        try {
            return DB::transaction(function () use ($request, $task, $user) {
                // Build proof data with path validation
                $proofData = $this->buildProofData($request, $task);

                // Create submission
                $this->submissionService->submit($user, $task->code, $proofData);

                return redirect()
                    ->route('user.points.submissions')
                    ->with('success', 'Yeu cau da duoc gui. Ban se nhan diem khi duoc phe duyet.');
            });

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Point transaction history
     */
    public function history(): View
    {
        $user = auth()->user();

        $transactions = $user->pointTransactions()
            ->orderByDesc('created_at')
            ->paginate(20);

        $wallet = $user->wallet;
        $balance = $wallet ? $wallet->points : 0;

        return view('front.points.history', compact('transactions', 'balance'));
    }

    /**
     * User's submission history
     */
    public function submissions(): View
    {
        $user = auth()->user();

        // Use pagination directly for better UX
        $submissions = PointSubmission::with('pointTask')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('front.points.submissions', compact('submissions'));
    }

    /**
     * Get validation rules based on proof type
     *
     * @return array<string, string>
     */
    private function getValidationRules(PointTask $task): array
    {
        return match ($task->proof_type) {
            PointTask::PROOF_IMAGE => [
                'images' => 'required|array|min:1|max:5',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ],
            PointTask::PROOF_LINK => [
                'url' => 'required|url|max:500',
            ],
            PointTask::PROOF_QR_CODE => [
                'qr_data' => 'required|string|max:255',
            ],
            default => [],
        };
    }

    /**
     * Build proof data from request
     *
     * @return array<string, mixed>
     * @throws \RuntimeException
     */
    private function buildProofData(Request $request, PointTask $task): array
    {
        $proofData = ['type' => $task->proof_type];
        $expectedPrefix = 'point-submissions/' . auth()->id() . '/';

        switch ($task->proof_type) {
            case PointTask::PROOF_IMAGE:
                $paths = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('point-submissions/' . auth()->id(), 'public');

                    // Validate path stays in intended directory (security check)
                    if (!str_starts_with($path, $expectedPrefix)) {
                        throw new \RuntimeException('Invalid file path detected');
                    }

                    $paths[] = $path;
                }
                $proofData['paths'] = $paths;
                break;

            case PointTask::PROOF_LINK:
                $proofData['url'] = filter_var($request->input('url'), FILTER_SANITIZE_URL);
                break;

            case PointTask::PROOF_QR_CODE:
                $proofData['qr_data'] = $request->input('qr_data');
                break;
        }

        // Special challenge ID
        if ($task->code === PointTask::CODE_SPECIAL_CHALLENGE && $request->filled('challenge_id')) {
            $proofData['challenge_id'] = (int) $request->input('challenge_id');
        }

        return $proofData;
    }
}
