<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OcrMatch;
use App\Services\BadgeService;
use App\Services\EloService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OcrDisputeController extends Controller
{
    public function __construct(
        private EloService $eloService,
        private BadgeService $badgeService
    ) {
    }

    /**
     * List disputed matches
     */
    public function index(): View
    {
        $disputes = OcrMatch::where('status', OcrMatch::STATUS_DISPUTED)
            ->with(['challenger', 'opponent', 'challengerPartner', 'opponentPartner', 'media'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.ocr.disputes.index', compact('disputes'));
    }

    /**
     * Show dispute details
     */
    public function show(OcrMatch $match): View|RedirectResponse
    {
        if ($match->status !== OcrMatch::STATUS_DISPUTED) {
            return redirect()->route('admin.ocr.disputes.index')
                ->with('error', 'Match is not disputed');
        }

        $match->load([
            'challenger',
            'opponent',
            'challengerPartner',
            'opponentPartner',
            'media',
            'eloHistories',
        ]);

        return view('admin.ocr.disputes.show', compact('match'));
    }

    /**
     * Resolve dispute - confirm the submitted result
     */
    public function confirmResult(OcrMatch $match): RedirectResponse
    {
        if ($match->status !== OcrMatch::STATUS_DISPUTED) {
            return back()->with('error', 'Match is not disputed');
        }

        try {
            DB::transaction(function () use ($match) {
                $match->update([
                    'status' => OcrMatch::STATUS_CONFIRMED,
                    'confirmed_at' => now(),
                ]);

                $this->eloService->processMatchResult($match);

                // Load relationships for badge check
                $match->load(['challenger', 'challengerPartner', 'opponent', 'opponentPartner']);

                // Award badges
                $challengerWon = $match->winner_team === 'challenger';
                $participants = [
                    ['user' => $match->challenger, 'won' => $challengerWon],
                    ['user' => $match->challengerPartner, 'won' => $challengerWon],
                    ['user' => $match->opponent, 'won' => !$challengerWon],
                    ['user' => $match->opponentPartner, 'won' => !$challengerWon],
                ];

                foreach ($participants as $p) {
                    if ($p['user']) {
                        $p['user']->refresh();
                        $this->badgeService->checkBadgesAfterMatch($p['user'], $match, $p['won']);
                    }
                }
            });

            return redirect()->route('admin.ocr.disputes.index')
                ->with('success', 'Match confirmed and Elo ratings updated');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to confirm: ' . $e->getMessage());
        }
    }

    /**
     * Resolve dispute - override the result with admin-specified scores
     */
    public function overrideResult(OcrMatch $match, Request $request): RedirectResponse
    {
        if ($match->status !== OcrMatch::STATUS_DISPUTED) {
            return back()->with('error', 'Match is not disputed');
        }

        $validated = $request->validate([
            'challenger_score' => 'required|integer|min:0|max:99',
            'opponent_score' => 'required|integer|min:0|max:99|different:challenger_score',
        ]);

        try {
            DB::transaction(function () use ($match, $validated) {
                $match->update([
                    'challenger_score' => $validated['challenger_score'],
                    'opponent_score' => $validated['opponent_score'],
                    'winner_team' => $validated['challenger_score'] > $validated['opponent_score']
                        ? 'challenger'
                        : 'opponent',
                    'status' => OcrMatch::STATUS_CONFIRMED,
                    'confirmed_at' => now(),
                ]);

                $this->eloService->processMatchResult($match);

                // Load relationships for badge check
                $match->load(['challenger', 'challengerPartner', 'opponent', 'opponentPartner']);

                // Award badges
                $challengerWon = $match->winner_team === 'challenger';
                $participants = [
                    ['user' => $match->challenger, 'won' => $challengerWon],
                    ['user' => $match->challengerPartner, 'won' => $challengerWon],
                    ['user' => $match->opponent, 'won' => !$challengerWon],
                    ['user' => $match->opponentPartner, 'won' => !$challengerWon],
                ];

                foreach ($participants as $p) {
                    if ($p['user']) {
                        $p['user']->refresh();
                        $this->badgeService->checkBadgesAfterMatch($p['user'], $match, $p['won']);
                    }
                }
            });

            return redirect()->route('admin.ocr.disputes.index')
                ->with('success', 'Result overridden and Elo ratings updated');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to override: ' . $e->getMessage());
        }
    }

    /**
     * Resolve dispute - cancel the match entirely
     */
    public function cancelMatch(OcrMatch $match): RedirectResponse
    {
        if ($match->status !== OcrMatch::STATUS_DISPUTED) {
            return back()->with('error', 'Match is not disputed');
        }

        $match->update(['status' => OcrMatch::STATUS_CANCELLED]);

        return redirect()->route('admin.ocr.disputes.index')
            ->with('success', 'Match cancelled');
    }

    /**
     * List all OCR matches (for admin overview)
     */
    public function allMatches(Request $request): View
    {
        $status = $request->query('status');

        $query = OcrMatch::with(['challenger', 'opponent'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $matches = $query->paginate(20);
        $statuses = [
            OcrMatch::STATUS_PENDING,
            OcrMatch::STATUS_ACCEPTED,
            OcrMatch::STATUS_IN_PROGRESS,
            OcrMatch::STATUS_RESULT_SUBMITTED,
            OcrMatch::STATUS_CONFIRMED,
            OcrMatch::STATUS_DISPUTED,
            OcrMatch::STATUS_CANCELLED,
        ];

        return view('admin.ocr.matches.index', compact('matches', 'statuses', 'status'));
    }
}
