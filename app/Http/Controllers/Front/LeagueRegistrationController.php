<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\LeagueTeam;
use App\Services\LeagueRegistrationService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class LeagueRegistrationController extends Controller
{
    public function __construct(private LeagueRegistrationService $registrationService)
    {
    }

    // === Public routes (no auth) ===

    public function index(Request $request)
    {
        // Only show active leagues (registration, active, completed)
        $query = League::whereIn('status', ['registration', 'active', 'completed']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Status filter
        if ($request->has('statuses') && is_array($request->statuses) && count($request->statuses) > 0) {
            $query->whereIn('status', $request->statuses);
        }

        // Location filter (assuming league has a location or via club, since tournament has location, but league might not have a direct location field. Let's check League model. League has no location field, only club_id. Let's skip location filter for now or filter by club location if needed. Let's just keep search and status for now).

        // Default ordering
        $query->orderBy('start_date', 'asc');

        $leagues = $query->paginate(9);
        
        // Calculate statistics
        $activeLeagues = League::whereIn('status', ['registration', 'active'])->count();
        $totalLeagues = League::whereIn('status', ['registration', 'active', 'completed'])->count();
        
        $statusOpen = League::where('status', 'registration')->count();
        $statusOngoing = League::where('status', 'active')->count();
        $statusCompleted = League::where('status', 'completed')->count();

        // Pass filters back to view
        $filters = $request->only(['search', 'statuses']);

        return view('front.leagues.index', compact(
            'leagues',
            'activeLeagues',
            'totalLeagues',
            'statusOpen',
            'statusOngoing',
            'statusCompleted',
            'filters'
        ));
    }

    public function showForm(League $league)
    {
        $closed = false;
        if ($league->status !== 'registration') {
            $closed = true;
        }
        if ($league->registration_deadline && $league->registration_deadline->isPast()) {
            $closed = true;
        }

        $league->load([
            'rounds.matches.homeTeam',
            'rounds.matches.awayTeam',
            'rounds.matches.games.homePlayer1.user',
            'rounds.matches.games.homePlayer2.user',
            'rounds.matches.games.awayPlayer1.user',
            'rounds.matches.games.awayPlayer2.user',
            'standings.team',
        ]);

        return view('front.leagues.register', compact('league', 'closed'));
    }

    public function store(Request $request, League $league)
    {
        if ($league->status !== 'registration') {
            return redirect()->back()->with('error', 'Đăng ký đã đóng.');
        }
        if ($league->registration_deadline && $league->registration_deadline->isPast()) {
            return redirect()->back()->with('error', 'Đã hết hạn đăng ký.');
        }

        $request->validate([
            'players' => 'required|array|size:' . $league->required_players_per_registration,
            'players.*.phone' => 'required|string|max:20',
            'players.*.name' => 'required|string|max:255',
            'players.*.skill_level' => 'nullable|string|max:50',
            'players.*.province' => 'nullable|string|max:100',
            'players.*.gender' => 'required|in:male,female',
            'players.*.birthday' => 'nullable|date',
            'players.*.photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'players.*.message' => 'nullable|string|max:500',
            'payment_proof' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Chuẩn hóa + kiểm tra trùng phone trong cùng submission
        $normalizedPhones = [];
        foreach ($request->players as $playerData) {
            $phone = LeagueRegistrationService::normalizePhone($playerData['phone']);
            if (in_array($phone, $normalizedPhones)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Số điện thoại {$phone} bị trùng trong cùng đăng ký.");
            }
            $normalizedPhones[] = $phone;
        }

        // Kiểm tra phone chưa đăng ký (pending/approved) trong league này
        foreach ($normalizedPhones as $phone) {
            $exists = $league->registrations()
                ->whereIn('status', ['pending', 'approved'])
                ->whereHas('players', function ($q) use ($phone) {
                    $q->where('phone', $phone);
                })
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Số điện thoại {$phone} đã được đăng ký trong giải đấu này.");
            }
        }

        // Upload payment proof if provided
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')
                ->store('league-registrations', config('filesystems.default'));
        }

        // Xử lý upload ảnh VĐV
        $players = [];
        foreach ($request->players as $i => $playerData) {
            $photoPath = null;
            if ($request->hasFile("players.{$i}.photo")) {
                $photoPath = $request->file("players.{$i}.photo")
                    ->store('league-registrations/players', config('filesystems.default'));
            }
            $players[] = array_merge($playerData, ['photo' => $photoPath]);
        }

        $this->registrationService->register($league, [
            'payment_proof' => $paymentProofPath,
            'players' => $players,
        ]);

        return redirect()
            ->route('leagues.register', $league)
            ->with('success', 'Đăng ký thành công! Vui lòng chờ admin duyệt.');
    }

    // === Admin routes (auth + ownership) ===

    public function listRegistrations(Request $request, League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        $query = $league->registrations()->with('players')->latest();

        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function approve(Request $request, League $league, LeagueRegistration $registration)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($registration->league_id !== $league->id, 404);

        if ($registration->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể duyệt đăng ký đang chờ.'], 422);
        }

        $request->validate(['admin_note' => 'nullable|string|max:500']);

        $this->registrationService->approve($registration, $request->input('admin_note'));

        return response()->json(['success' => true, 'message' => 'Đã duyệt đăng ký.']);
    }

    public function reject(Request $request, League $league, LeagueRegistration $registration)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($registration->league_id !== $league->id, 404);

        if ($registration->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể từ chối đăng ký đang chờ.'], 422);
        }

        $request->validate(['admin_note' => 'required|string|max:500']);

        $this->registrationService->reject($registration, $request->input('admin_note'));

        return response()->json(['success' => true, 'message' => 'Đã từ chối đăng ký.']);
    }

    public function pool(League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        $pool = $this->registrationService->getAvailablePool($league);

        return response()->json($pool);
    }

    public function addGroup(League $league, LeagueTeam $team, LeagueRegistration $registration)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($team->league_id !== $league->id, 404);
        abort_if($registration->league_id !== $league->id, 404);

        try {
            $added = $this->registrationService->addGroupToTeam($registration, $team);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm ' . count($added) . ' VĐV vào đội.',
            'added_count' => count($added),
        ]);
    }
}
