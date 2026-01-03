<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkillQuizAttempt;
use App\Models\User;
use App\Services\OprsService;
use App\Services\SkillQuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillQuizController extends Controller
{
    public function __construct(
        private SkillQuizService $quizService,
        private OprsService $oprsService
    ) {}

    /**
     * Dashboard with statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_attempts' => SkillQuizAttempt::count(),
            'completed' => SkillQuizAttempt::where('status', SkillQuizAttempt::STATUS_COMPLETED)->count(),
            'in_progress' => SkillQuizAttempt::where('status', SkillQuizAttempt::STATUS_IN_PROGRESS)->count(),
            'abandoned' => SkillQuizAttempt::where('status', SkillQuizAttempt::STATUS_ABANDONED)->count(),
            'flagged' => SkillQuizAttempt::whereNotNull('flags')
                ->whereRaw('JSON_LENGTH(flags) > 0')
                ->count(),
        ];

        // ELO distribution
        $eloDistribution = SkillQuizAttempt::where('status', SkillQuizAttempt::STATUS_COMPLETED)
            ->whereNotNull('final_elo')
            ->selectRaw('
                CASE
                    WHEN final_elo < 900 THEN "< 900"
                    WHEN final_elo < 1000 THEN "900-999"
                    WHEN final_elo < 1100 THEN "1000-1099"
                    WHEN final_elo < 1200 THEN "1100-1199"
                    WHEN final_elo < 1300 THEN "1200-1299"
                    ELSE "1300+"
                END as elo_range,
                COUNT(*) as count
            ')
            ->groupBy('elo_range')
            ->orderByRaw('MIN(final_elo)')
            ->pluck('count', 'elo_range');

        // Recent attempts
        $recentAttempts = SkillQuizAttempt::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Flagged attempts needing review
        $flaggedAttempts = SkillQuizAttempt::with('user')
            ->whereNotNull('flags')
            ->whereRaw('JSON_LENGTH(flags) > 0')
            ->where('status', SkillQuizAttempt::STATUS_COMPLETED)
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.skill-quiz.dashboard', compact(
            'stats',
            'eloDistribution',
            'recentAttempts',
            'flaggedAttempts'
        ));
    }

    /**
     * List all attempts
     */
    public function index(Request $request)
    {
        $query = SkillQuizAttempt::with('user');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('flagged') && $request->flagged === '1') {
            $query->whereNotNull('flags')
                ->whereRaw('JSON_LENGTH(flags) > 0');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $attempts = $query->latest()->paginate(20);

        return view('admin.skill-quiz.index', compact('attempts'));
    }

    /**
     * Show attempt details
     */
    public function show(SkillQuizAttempt $attempt)
    {
        $attempt->load(['user', 'answers.question.domain']);

        // Group answers by domain
        $answersByDomain = $attempt->answers
            ->groupBy(fn ($a) => $a->question->domain->key);

        return view('admin.skill-quiz.show', compact('attempt', 'answersByDomain'));
    }

    /**
     * Adjust user ELO
     */
    public function adjustElo(Request $request, SkillQuizAttempt $attempt)
    {
        $request->validate([
            'new_elo' => 'required|integer|min:100|max:2000',
            'reason' => 'required|string|max:255',
        ]);

        $user = $attempt->user;
        $oldElo = $user->elo_rating;
        $newElo = (int) $request->new_elo;

        DB::transaction(function () use ($user, $attempt, $newElo, $oldElo, $request) {
            // Update user ELO
            $user->update(['elo_rating' => $newElo]);
            $user->updateEloRank();

            // Update attempt record
            $flags = $attempt->flags ?? [];
            $flags[] = [
                'type' => 'ADMIN_ADJUSTMENT',
                'message' => $request->reason,
                'adjustment' => $newElo - $oldElo,
                'admin_id' => auth()->id(),
                'adjusted_at' => now()->toIso8601String(),
            ];

            $attempt->update([
                'final_elo' => $newElo,
                'flags' => $flags,
            ]);

            // Recalculate OPRS
            $this->oprsService->recalculateAfterMatch($user);
        });

        return redirect()->route('admin.skill-quiz.show', $attempt)
            ->with('success', "ELO đã cập nhật: {$oldElo} -> {$newElo}");
    }

    /**
     * Mark flags as reviewed
     */
    public function markReviewed(SkillQuizAttempt $attempt)
    {
        $flags = $attempt->flags ?? [];

        foreach ($flags as &$flag) {
            $flag['reviewed'] = true;
            $flag['reviewed_at'] = now()->toIso8601String();
            $flag['reviewed_by'] = auth()->id();
        }

        $attempt->update(['flags' => $flags]);

        return redirect()->route('admin.skill-quiz.show', $attempt)
            ->with('success', 'Đã đánh dấu đã xem xét');
    }
}
