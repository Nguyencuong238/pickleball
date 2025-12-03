<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OcrMatchStoreRequest;
use App\Http\Requests\OcrMatchResultRequest;
use App\Models\OcrMatch;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\EloService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OcrMatchController extends Controller
{
    public function __construct(
        private EloService $eloService,
        private BadgeService $badgeService
    ) {}

    /**
     * List user's matches
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = OcrMatch::forUser($user->id)
            ->with(['challenger', 'opponent', 'challengerPartner', 'opponentPartner'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $matches = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $matches,
        ]);
    }

    /**
     * Create match invitation
     */
    public function store(OcrMatchStoreRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Validate opponent exists
        $opponent = User::find($validated['opponent_id']);
        if (!$opponent) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Opponent not found');
            }
            return response()->json([
                'success' => false,
                'error' => 'Opponent not found',
            ], 404);
        }

        // Prevent self-challenge
        if ($opponent->id === $user->id) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Cannot challenge yourself');
            }
            return response()->json([
                'success' => false,
                'error' => 'Cannot challenge yourself',
            ], 422);
        }

        // For doubles, validate partners
        if (($validated['match_type'] ?? 'singles') === 'doubles') {
            $partnerIds = array_filter([
                $validated['challenger_partner_id'] ?? null,
                $validated['opponent_partner_id'] ?? null,
            ]);

            // Check for duplicate players
            $allPlayerIds = array_filter([
                $user->id,
                $validated['opponent_id'],
                ...$partnerIds,
            ]);

            if (count($allPlayerIds) !== count(array_unique($allPlayerIds))) {
                if (!$request->expectsJson()) {
                    return redirect()->back()->with('error', 'Duplicate players in match');
                }
                return response()->json([
                    'success' => false,
                    'error' => 'Duplicate players in match',
                ], 422);
            }
        }

        // Create match
        $match = OcrMatch::create([
            'match_type' => $validated['match_type'] ?? 'singles',
            'challenger_id' => $user->id,
            'challenger_partner_id' => $validated['challenger_partner_id'] ?? null,
            'opponent_id' => $validated['opponent_id'],
            'opponent_partner_id' => $validated['opponent_partner_id'] ?? null,
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'scheduled_time' => $validated['scheduled_time'] ?? null,
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => OcrMatch::STATUS_PENDING,
        ]);

        $match->load(['challenger', 'opponent']);

        if (!$request->expectsJson()) {
            return redirect()->route('ocr.matches.show', $match)
                ->with('success', 'Match invitation sent!');
        }

        return response()->json([
            'success' => true,
            'message' => 'Match invitation sent',
            'data' => $match,
        ], 201);
    }

    /**
     * Get match details
     */
    public function show(OcrMatch $match, Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is participant or admin
        if (!$match->isParticipant($user->id) && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $match->load([
            'challenger',
            'opponent',
            'challengerPartner',
            'opponentPartner',
            'media',
        ]);

        // Add win probability
        $winProbability = null;
        if ($match->isPending() || $match->isAccepted()) {
            $winProbability = [
                'challenger' => $this->eloService->getWinProbability(
                    $match->challenger,
                    $match->opponent
                ),
                'opponent' => $this->eloService->getWinProbability(
                    $match->opponent,
                    $match->challenger
                ),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $match,
            'meta' => [
                'win_probability' => $winProbability,
            ],
        ]);
    }

    /**
     * Accept match invitation
     */
    public function accept(OcrMatch $match, Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (!$match->isOpponentTeam($user->id)) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Only opponent can accept');
            }
            return response()->json([
                'success' => false,
                'error' => 'Only opponent can accept',
            ], 403);
        }

        if (!$match->isPending()) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Match not pending');
            }
            return response()->json([
                'success' => false,
                'error' => 'Match not pending',
            ], 422);
        }

        $match->accept();

        if (!$request->expectsJson()) {
            return redirect()->route('ocr.matches.show', $match)
                ->with('success', 'Match accepted!');
        }

        return response()->json([
            'success' => true,
            'message' => 'Match accepted',
            'data' => $match->fresh(),
        ]);
    }

    /**
     * Reject match invitation
     */
    public function reject(OcrMatch $match, Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (!$match->isOpponentTeam($user->id)) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Only opponent can reject');
            }
            return response()->json([
                'success' => false,
                'error' => 'Only opponent can reject',
            ], 403);
        }

        if (!$match->isPending()) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Match not pending');
            }
            return response()->json([
                'success' => false,
                'error' => 'Match not pending',
            ], 422);
        }

        $match->cancel();

        if (!$request->expectsJson()) {
            return redirect()->route('ocr.matches.index')
                ->with('success', 'Match rejected');
        }

        return response()->json([
            'success' => true,
            'message' => 'Match rejected',
        ]);
    }

    /**
     * Start match (transition from accepted to in_progress)
     */
    public function start(OcrMatch $match, Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (!$match->isParticipant($user->id)) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Only participants can start the match');
            }
            return response()->json([
                'success' => false,
                'error' => 'Only participants can start the match',
            ], 403);
        }

        if (!$match->isAccepted()) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Match must be accepted first');
            }
            return response()->json([
                'success' => false,
                'error' => 'Match must be accepted first',
            ], 422);
        }

        $match->update(['status' => OcrMatch::STATUS_IN_PROGRESS]);

        // For web requests, redirect back
        if (!$request->expectsJson()) {
            return redirect()->route('ocr.matches.show', $match)
                ->with('success', 'Match started!');
        }

        return response()->json([
            'success' => true,
            'message' => 'Match started',
            'data' => $match->fresh(),
        ]);
    }

    /**
     * Submit match result
     */
    public function submitResult(OcrMatch $match, OcrMatchResultRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if (!$match->isParticipant($user->id)) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Only participants can submit result');
            }
            return response()->json([
                'success' => false,
                'error' => 'Only participants can submit result',
            ], 403);
        }

        if (!in_array($match->status, [OcrMatch::STATUS_ACCEPTED, OcrMatch::STATUS_IN_PROGRESS])) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Match not in valid state for result submission');
            }
            return response()->json([
                'success' => false,
                'error' => 'Match not in valid state for result submission',
            ], 422);
        }

        $match->submitResult(
            $user->id,
            $validated['challenger_score'],
            $validated['opponent_score']
        );

        if (!$request->expectsJson()) {
            return redirect()->route('ocr.matches.show', $match)
                ->with('success', 'Result submitted, waiting for confirmation');
        }

        return response()->json([
            'success' => true,
            'message' => 'Result submitted, waiting for confirmation',
            'data' => $match->fresh(),
        ]);
    }

    /**
     * Confirm match result
     */
    public function confirmResult(OcrMatch $match, Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (!$match->isParticipant($user->id)) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Only participants can confirm');
            }
            return response()->json([
                'success' => false,
                'error' => 'Only participants can confirm',
            ], 403);
        }

        // Submitter cannot confirm their own result
        if ($match->result_submitted_by === $user->id) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Cannot confirm your own submission');
            }
            return response()->json([
                'success' => false,
                'error' => 'Cannot confirm your own submission',
            ], 422);
        }

        if ($match->status !== OcrMatch::STATUS_RESULT_SUBMITTED) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'No result to confirm');
            }
            return response()->json([
                'success' => false,
                'error' => 'No result to confirm',
            ], 422);
        }

        try {
            DB::transaction(function () use ($match) {
                // Confirm the result
                $match->confirmResult();

                // Process Elo changes
                $this->eloService->processMatchResult($match);

                // Reload relationships
                $match->load(['challenger', 'challengerPartner', 'opponent', 'opponentPartner']);

                // Check badges for all participants
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
        } catch (Exception $e) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Failed to process result: ' . $e->getMessage());
            }
            return response()->json([
                'success' => false,
                'error' => 'Failed to process result: ' . $e->getMessage(),
            ], 500);
        }

        if (!$request->expectsJson()) {
            return redirect()->route('ocr.matches.show', $match)
                ->with('success', 'Match confirmed and Elo updated!');
        }

        return response()->json([
            'success' => true,
            'message' => 'Match confirmed and Elo updated',
            'data' => $match->fresh()->load(['challenger', 'opponent']),
        ]);
    }

    /**
     * Dispute match result
     */
    public function dispute(OcrMatch $match, Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (!$match->isParticipant($user->id)) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Only participants can dispute');
            }
            return response()->json([
                'success' => false,
                'error' => 'Only participants can dispute',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $match->dispute($validated['reason']);

        if (!$request->expectsJson()) {
            return redirect()->route('ocr.matches.show', $match)
                ->with('success', 'Dispute submitted for admin review');
        }

        return response()->json([
            'success' => true,
            'message' => 'Dispute submitted for admin review',
        ]);
    }

    /**
     * Upload evidence
     */
    public function uploadEvidence(OcrMatch $match, Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if (!$match->isParticipant($user->id)) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Only participants can upload evidence');
            }
            return response()->json([
                'success' => false,
                'error' => 'Only participants can upload evidence',
            ], 403);
        }

        $request->validate([
            'evidence' => 'required',
            'evidence.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:20480',
        ]);

        // Handle multiple file uploads
        $files = $request->file('evidence');
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $match->addMedia($file)
                ->usingFileName(time() . '_' . $file->getClientOriginalName())
                ->toMediaCollection('evidence');
        }

        if (!$request->expectsJson()) {
            return redirect()->route('ocr.matches.show', $match)
                ->with('success', 'Evidence uploaded successfully');
        }

        return response()->json([
            'success' => true,
            'message' => 'Evidence uploaded',
            'data' => $match->fresh()->getMedia('evidence'),
        ]);
    }
}
