<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\OcrMatch;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\EloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OcrController extends Controller
{
    public function __construct(
        private EloService $eloService,
        private BadgeService $badgeService
    ) {
    }

    /**
     * OCR landing page
     */
    public function index(): View
    {
        // Top 10 leaderboard
        $topPlayers = User::where('total_ocr_matches', '>', 0)
            ->orderBy('elo_rating', 'desc')
            ->take(10)
            ->get();

        // Recent matches
        $recentMatches = OcrMatch::where('status', OcrMatch::STATUS_CONFIRMED)
            ->with(['challenger', 'opponent'])
            ->orderBy('confirmed_at', 'desc')
            ->take(5)
            ->get();

        // User's position if logged in
        $userRank = null;
        if (auth()->check() && auth()->user()->total_ocr_matches > 0) {
            $userRank = User::where('elo_rating', '>', auth()->user()->elo_rating)
                ->where('total_ocr_matches', '>', 0)
                ->count() + 1;
        }

        return view('front.ocr.index', compact('topPlayers', 'recentMatches', 'userRank'));
    }

    /**
     * Match list
     */
    public function matchIndex(Request $request): View
    {
        $user = auth()->user();
        $status = $request->query('status');

        $query = OcrMatch::forUser($user->id)
            ->with(['challenger', 'opponent', 'challengerPartner', 'opponentPartner'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $matches = $query->paginate(20);

        return view('front.ocr.matches.index', compact('matches', 'status'));
    }

    /**
     * Match detail
     */
    public function matchShow(OcrMatch $match): View
    {
        $user = auth()->user();

        if (!$match->isParticipant($user->id)) {
            abort(403, 'You are not a participant of this match');
        }

        $match->load(['challenger', 'opponent', 'challengerPartner', 'opponentPartner', 'media']);

        // Win probability
        $winProbability = null;
        if (in_array($match->status, [OcrMatch::STATUS_PENDING, OcrMatch::STATUS_ACCEPTED])) {
            $winProbability = [
                'challenger' => $this->eloService->getWinProbability($match->challenger, $match->opponent),
                'opponent' => $this->eloService->getWinProbability($match->opponent, $match->challenger),
            ];
        }

        return view('front.ocr.matches.show', compact('match', 'winProbability'));
    }

    /**
     * Create match form
     */
    public function matchCreate(): View
    {
        return view('front.ocr.matches.create');
    }

    /**
     * Search users for opponent selection
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->query('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', auth()->id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'name', 'email', 'elo_rating', 'elo_rank']);

        return response()->json($users);
    }

    /**
     * Full leaderboard
     */
    public function leaderboard(Request $request): View
    {
        $rank = $request->query('rank');

        $query = User::where('total_ocr_matches', '>', 0)
            ->orderBy('elo_rating', 'desc');

        if ($rank && in_array(ucfirst($rank), array_keys(User::getEloRanks()))) {
            $query->where('elo_rank', ucfirst($rank));
        }

        $players = $query->paginate(50);

        // User's position
        $userRank = null;
        if (auth()->check() && auth()->user()->total_ocr_matches > 0) {
            $userRank = User::where('elo_rating', '>', auth()->user()->elo_rating)
                ->where('total_ocr_matches', '>', 0)
                ->count() + 1;
        }

        $ranks = array_keys(User::getEloRanks());

        return view('front.ocr.leaderboard', compact('players', 'rank', 'userRank', 'ranks'));
    }

    /**
     * User OCR profile
     */
    public function profile(User $user): View
    {
        $user->load('badges');

        $recentMatches = OcrMatch::forUser($user->id)
            ->where('status', OcrMatch::STATUS_CONFIRMED)
            ->with(['challenger', 'opponent'])
            ->orderBy('confirmed_at', 'desc')
            ->take(10)
            ->get();

        $eloHistory = $user->eloHistories()
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $badgeProgress = $this->badgeService->getBadgeProgress($user);

        // Global rank
        $globalRank = User::where('elo_rating', '>', $user->elo_rating)
            ->where('total_ocr_matches', '>', 0)
            ->count() + 1;

        return view('front.ocr.profile', compact('user', 'recentMatches', 'eloHistory', 'badgeProgress', 'globalRank'));
    }
}
