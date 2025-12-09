<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefereeController extends Controller
{
    /**
     * Dashboard with stats and upcoming matches
     */
    public function dashboard(): View
    {
        $referee = auth()->user();

        $stats = [
            'total_matches' => $referee->refereeMatches()->count(),
            'completed_matches' => $referee->refereeMatches()->where('status', 'completed')->count(),
            'upcoming_matches' => $referee->refereeMatches()
                ->where('status', 'scheduled')
                ->where('match_date', '>=', now()->toDateString())
                ->count(),
            'tournaments' => $referee->refereeTournaments()->count(),
        ];

        $upcomingMatches = $referee->refereeMatches()
            ->with(['tournament', 'athlete1', 'athlete2', 'category', 'court'])
            ->where('status', 'scheduled')
            ->where('match_date', '>=', now()->toDateString())
            ->orderBy('match_date')
            ->orderBy('match_time')
            ->limit(5)
            ->get();

        return view('referee.dashboard', compact('stats', 'upcomingMatches'));
    }

    /**
     * List all assigned matches with filters
     */
    public function matches(Request $request): View
    {
        $referee = auth()->user();

        $query = $referee->refereeMatches()
            ->with(['tournament', 'athlete1', 'athlete2', 'category', 'court']);

        // Filter by tournament
        if ($request->filled('tournament_id')) {
            $query->where('tournament_id', $request->tournament_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('match_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('match_date', '<=', $request->date_to);
        }

        $matches = $query->orderBy('match_date', 'desc')
            ->orderBy('match_time', 'desc')
            ->paginate(20);

        $tournaments = $referee->refereeTournaments;

        return view('referee.matches.index', compact('matches', 'tournaments'));
    }

    /**
     * Show match detail with score entry form
     */
    public function show(MatchModel $match): View
    {
        $referee = auth()->user();

        // Authorization check
        if (!$match->isAssignedToReferee($referee)) {
            abort(403, 'You are not assigned to this match');
        }

        $match->load([
            'tournament',
            'category',
            'round',
            'court',
            'athlete1',
            'athlete2',
            'winner',
        ]);

        return view('referee.matches.show', compact('match'));
    }

    /**
     * Start a match
     */
    public function startMatch(MatchModel $match): RedirectResponse
    {
        $referee = auth()->user();

        if (!$match->isAssignedToReferee($referee)) {
            return back()->with('error', 'You are not assigned to this match');
        }

        if ($match->status !== 'scheduled') {
            return back()->with('error', 'Match cannot be started');
        }

        $match->update([
            'status' => 'in_progress',
            'actual_start_time' => now(),
        ]);

        ActivityLog::log("Tran dau #{$match->id} bat dau boi trong tai", 'Match', $match->id);

        return back()->with('success', 'Match started');
    }

    /**
     * Update match scores
     */
    public function updateScore(Request $request, MatchModel $match): RedirectResponse
    {
        $referee = auth()->user();

        // Authorization
        if (!$match->isAssignedToReferee($referee)) {
            return back()->with('error', 'You are not assigned to this match');
        }

        if ($match->isCompleted()) {
            return back()->with('error', 'Cannot edit completed match');
        }

        $validated = $request->validate([
            'set_scores' => 'required|array|min:1',
            'set_scores.*.set' => 'required|integer|min:1',
            'set_scores.*.athlete1' => 'required|integer|min:0',
            'set_scores.*.athlete2' => 'required|integer|min:0',
            'status' => 'required|in:in_progress,completed',
        ]);

        try {
            DB::beginTransaction();

            // Calculate winner from set scores
            $winnerId = $this->calculateWinner($validated['set_scores'], $match);
            $finalScore = $this->formatFinalScore($validated['set_scores']);

            $match->update([
                'set_scores' => $validated['set_scores'],
                'final_score' => $finalScore,
                'winner_id' => $validated['status'] === 'completed' ? $winnerId : null,
                'status' => $validated['status'],
                'actual_end_time' => $validated['status'] === 'completed' ? now() : null,
            ]);

            DB::commit();

            ActivityLog::log("Ti so tran dau #{$match->id} duoc cap nhat: {$finalScore}", 'Match', $match->id);

            return redirect()->route('referee.matches.show', $match)
                ->with('success', 'Ti so da duoc cap nhat');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Score update failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Loi cap nhat ti so')->withInput();
        }
    }

    /**
     * Calculate winner from set scores
     */
    private function calculateWinner(array $setScores, MatchModel $match): ?int
    {
        $athlete1Sets = 0;
        $athlete2Sets = 0;

        foreach ($setScores as $set) {
            if ($set['athlete1'] > $set['athlete2']) {
                $athlete1Sets++;
            } elseif ($set['athlete2'] > $set['athlete1']) {
                $athlete2Sets++;
            }
        }

        if ($athlete1Sets > $athlete2Sets) {
            return $match->athlete1_id;
        } elseif ($athlete2Sets > $athlete1Sets) {
            return $match->athlete2_id;
        }

        return null; // Draw (unlikely in pickleball)
    }

    /**
     * Format final score string
     */
    private function formatFinalScore(array $setScores): string
    {
        $scores = [];
        foreach ($setScores as $set) {
            $scores[] = $set['athlete1'] . '-' . $set['athlete2'];
        }
        return implode(', ', $scores);
    }
}
