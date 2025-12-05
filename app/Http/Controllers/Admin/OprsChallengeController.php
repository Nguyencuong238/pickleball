<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChallengeResult;
use App\Services\ChallengeService;
use App\Services\OprsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OprsChallengeController extends Controller
{
    public function __construct(
        private ChallengeService $challengeService,
        private OprsService $oprsService
    ) {}

    /**
     * Challenge list
     */
    public function index(Request $request): View
    {
        $query = ChallengeResult::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('verified_at');
            } elseif ($request->status === 'verified') {
                $query->whereNotNull('verified_at');
            } elseif ($request->status === 'passed') {
                $query->where('passed', true);
            } elseif ($request->status === 'failed') {
                $query->where('passed', false);
            }
        }

        if ($request->filled('type')) {
            $query->where('challenge_type', $request->type);
        }

        $challenges = $query->paginate(25);

        return view('admin.oprs.challenges.index', [
            'challenges' => $challenges,
            'types' => ChallengeResult::getAllTypes(),
        ]);
    }

    /**
     * Challenge detail
     */
    public function show(ChallengeResult $challenge): View
    {
        return view('admin.oprs.challenges.detail', [
            'challenge' => $challenge->load('user', 'verifier'),
        ]);
    }

    /**
     * Verify challenge
     */
    public function verify(ChallengeResult $challenge): RedirectResponse
    {
        try {
            $this->challengeService->verifyChallenge($challenge, auth()->user());
            return back()->with('success', 'Challenge verified successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject challenge (remove points if awarded)
     */
    public function reject(Request $request, ChallengeResult $challenge): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        // If points were awarded, reverse them
        if ($challenge->passed && $challenge->points_earned > 0) {
            $user = $challenge->user;
            $user->update([
                'challenge_score' => max(0, $user->challenge_score - $challenge->points_earned),
            ]);

            // Recalculate OPRS
            $this->oprsService->updateUserOprs(
                $user,
                'admin_rejected_challenge',
                [
                    'challenge_id' => $challenge->id,
                    'reason' => $request->reason,
                    'admin_id' => auth()->id(),
                ]
            );
        }

        $challenge->update([
            'notes' => 'Rejected: ' . $request->reason,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.oprs.challenges.index')
            ->with('success', 'Challenge rejected');
    }

    /**
     * Bulk verify selected challenges
     */
    public function bulkVerify(Request $request): RedirectResponse
    {
        $request->validate([
            'challenge_ids' => 'required|array',
            'challenge_ids.*' => 'exists:challenge_results,id',
        ]);

        $count = 0;
        foreach ($request->challenge_ids as $id) {
            $challenge = ChallengeResult::find($id);
            if ($challenge && !$challenge->verified_at) {
                $this->challengeService->verifyChallenge($challenge, auth()->user());
                $count++;
            }
        }

        return back()->with('success', "Verified {$count} challenges");
    }
}
