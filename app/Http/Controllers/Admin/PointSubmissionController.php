<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Services\PointSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointSubmissionController extends Controller
{
    public function __construct(
        private PointSubmissionService $submissionService
    ) {}

    /**
     * List pending submissions
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PointSubmission::class);

        $filters = $request->only(['task_code', 'status', 'user_id']);
        $submissions = $this->submissionService->getSubmissions($filters, 20);
        $stats = $this->submissionService->getStats();
        $tasks = PointTask::where('requires_approval', true)->get();

        return view('admin.point-submissions.index', compact('submissions', 'stats', 'tasks'));
    }

    /**
     * View submission detail
     */
    public function show(PointSubmission $submission): View
    {
        $this->authorize('view', $submission);

        $submission->load(['user', 'pointTask', 'admin']);

        return view('admin.point-submissions.show', compact('submission'));
    }

    /**
     * Approve submission
     */
    public function approve(Request $request, PointSubmission $submission): RedirectResponse
    {
        $this->authorize('approve', $submission);

        try {
            $this->submissionService->approve(
                $submission,
                auth()->user(),
                $request->input('notes')
            );

            return redirect()
                ->route('admin.point-submissions.index')
                ->with('success', 'Submission approved. ' . $submission->pointTask->points . ' points awarded.');
        } catch (\Exception $e) {
            return back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject submission
     */
    public function reject(Request $request, PointSubmission $submission): RedirectResponse
    {
        $this->authorize('reject', $submission);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->submissionService->reject(
                $submission,
                auth()->user(),
                $request->input('reason')
            );

            return redirect()
                ->route('admin.point-submissions.index')
                ->with('success', 'Submission rejected.');
        } catch (\Exception $e) {
            return back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve submissions
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $this->authorize('bulkApprove', PointSubmission::class);

        $request->validate([
            'submission_ids' => 'required|array',
            'submission_ids.*' => 'exists:point_submissions,id',
        ]);

        $count = 0;
        $failed = 0;
        $errors = [];

        foreach ($request->input('submission_ids') as $id) {
            $submission = PointSubmission::find($id);
            if ($submission && $submission->isPending()) {
                try {
                    $this->submissionService->approve($submission, auth()->user());
                    $count++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Submission #{$id}: {$e->getMessage()}";
                    \Illuminate\Support\Facades\Log::warning("Bulk approve failed for submission {$id}", [
                        'error' => $e->getMessage(),
                        'admin_id' => auth()->id(),
                    ]);
                }
            }
        }

        $message = "Approved {$count} submissions.";
        if ($failed > 0) {
            $message .= " {$failed} failed.";
            if (count($errors) <= 5) {
                $message .= ' Errors: ' . implode('; ', $errors);
            }
        }

        return back()->with('success', $message);
    }
}
