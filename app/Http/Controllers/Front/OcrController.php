<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\OcrMatch;
use App\Models\User;
use App\Services\BadgeService;
use App\Services\ChallengeService;
use App\Services\CommunityService;
use App\Services\EloService;
use App\Services\OprsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OcrController extends Controller
{
    public function __construct(
        private EloService $eloService,
        private BadgeService $badgeService,
        private OprsService $oprsService,
        private ChallengeService $challengeService,
        private CommunityService $communityService
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

        // OPRS data
        $oprsBreakdown = $this->oprsService->getOprsBreakdown($user);
        $oprsRank = $this->oprsService->getUserRank($user);

        return view('front.ocr.profile', compact(
            'user',
            'recentMatches',
            'eloHistory',
            'badgeProgress',
            'globalRank',
            'oprsBreakdown',
            'oprsRank'
        ));
    }

    // ==================== Challenge Methods ====================

    /**
     * Challenge center index
     */
    public function challenges(): View
    {
        $user = auth()->user();

        return view('front.ocr.challenges.index', [
            'user' => $user,
            'availableChallenges' => $this->challengeService->getAvailableChallenges($user),
            'stats' => $this->challengeService->getChallengeStats($user),
            'history' => $this->challengeService->getChallengeHistory($user, 10),
        ]);
    }

    /**
     * Challenge submit form
     */
    public function challengeSubmit(string $type): View
    {
        $challengeInfo = \App\Models\ChallengeResult::getChallengeInfo($type);

        if (empty($challengeInfo)) {
            abort(404, 'Challenge type not found');
        }

        return view('front.ocr.challenges.submit', [
            'challengeType' => $type,
            'challengeInfo' => $challengeInfo,
        ]);
    }

    /**
     * Store challenge result
     */
    public function challengeStore(Request $request)
    {
        $request->validate([
            'challenge_type' => 'required|string',
            'score' => 'required|integer|min:0|max:100',
        ]);

        $user = auth()->user();

        try {
            $result = $this->challengeService->submitChallenge(
                $user,
                $request->challenge_type,
                $request->score
            );

            $message = $result->passed
                ? 'Challenge passed! +' . $result->points_earned . ' points awarded.'
                : 'Challenge not passed. Try again!';

            return redirect()
                ->route('ocr.challenges.index')
                ->with($result->passed ? 'success' : 'info', $message);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['score' => $e->getMessage()]);
        }
    }

    // ==================== Community Methods ====================

    /**
     * Community hub index
     */
    public function community(): View
    {
        $user = auth()->user();
        $startOfWeek = \Carbon\Carbon::now()->startOfWeek();

        // Count matches this week
        $weeklyMatchCount = OcrMatch::forUser($user->id)
            ->where('status', OcrMatch::STATUS_CONFIRMED)
            ->where('confirmed_at', '>=', $startOfWeek)
            ->count();

        return view('front.ocr.community.index', [
            'user' => $user,
            'stats' => $this->communityService->getActivityStats($user),
            'history' => $this->communityService->getActivityHistory($user, 10),
            'weeklyMatchCount' => $weeklyMatchCount,
            'availableActivities' => $this->communityService->getAvailableActivities($user),
        ]);
    }

    /**
     * Check-in form
     */
    public function checkin(): View
    {
        $user = auth()->user();
        $stadiums = \App\Models\Stadium::where('status', 'active')->get();

        // Check which stadiums user can check in to today
        $canCheckIn = [];
        foreach ($stadiums as $stadium) {
            $canCheckIn[$stadium->id] = $this->communityService->canCheckInToday($user, $stadium->id);
        }

        // Today's check-ins
        $todayCheckIns = $user->communityActivities()
            ->where('activity_type', \App\Models\CommunityActivity::TYPE_CHECK_IN)
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->get();

        return view('front.ocr.community.checkin', [
            'stadiums' => $stadiums,
            'canCheckIn' => $canCheckIn,
            'todayCheckIns' => $todayCheckIns,
        ]);
    }

    /**
     * Store check-in
     */
    public function checkinStore(Request $request)
    {
        $request->validate([
            'stadium_id' => 'required|exists:stadiums,id',
        ]);

        $user = auth()->user();
        $stadium = \App\Models\Stadium::findOrFail($request->stadium_id);

        try {
            $activity = $this->communityService->checkIn($user, $stadium);

            return redirect()
                ->route('ocr.community.index')
                ->with('success', 'Check-in successful! +' . $activity->points_earned . ' points awarded.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['stadium_id' => $e->getMessage()]);
        }
    }
}
