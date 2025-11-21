<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\Court;
use App\Models\Stadium;
use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeYardTournamentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:home_yard']);
    }

    public function index()
    {
        $tournaments = Tournament::where('user_id', auth()->id())->latest()->paginate(10);
        return view('home-yard.tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        return view('home-yard.tournaments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
            'status' => 'nullable|in:upcoming,ongoing,completed,cancelled',
        ]);

        $data = $request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'location',
            'max_participants',
            'price',
            'rules',
            'status',
        ]);

        $data['user_id'] = auth()->id();
        // Convert status string to boolean: upcoming/ongoing = true, completed/cancelled = false
        if (isset($data['status'])) {
            $data['status'] = in_array($data['status'], ['upcoming', 'ongoing']) ? true : false;
        } else {
            $data['status'] = true; // Default to upcoming
        }

        $tournament = Tournament::create($data);

        return redirect()->back()->with('success', 'Giải đấu đã được tạo thành công. Bạn có thể tiếp tục thêm nội dung thi đấu.');
    }

    public function show(Tournament $tournament)
    {
        $this->authorize('view', $tournament);

        // Return JSON for AJAX requests
        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            $imageUrl = $tournament->image ? Storage::disk('public')->url($tournament->image) : null;
            $bannerUrl = $tournament->banner ? Storage::disk('public')->url($tournament->banner) : null;

            return response()->json([
                'id' => $tournament->id,
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => $tournament->start_date,
                'end_date' => $tournament->end_date,
                'location' => $tournament->location,
                'max_participants' => $tournament->max_participants,
                'price' => $tournament->price,
                'prizes' => $tournament->prizes,
                'competition_format' => $tournament->competition_format,
                'competition_rules' => $tournament->competition_rules,
                'registration_benefits' => $tournament->registration_benefits,
                'status' => $tournament->status,
                'image' => $imageUrl,
                'banner' => $bannerUrl,
            ]);
        }

        // Return view for regular requests
        $athletes = $tournament->athletes()->paginate(15);
        return view('home-yard.tournaments.show', compact('tournament', 'athletes'));
    }

    public function edit(Tournament $tournament)
    {
        $this->authorize('update', $tournament);
        return view('home-yard.tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date_format:Y-m-d\TH:i',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
            'prizes' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'competition_format' => 'nullable|string|in:single,double,mixed',
            'tournament_rank' => 'nullable|string|in:beginner,intermediate,advanced,professional',
            'registration_benefits' => 'nullable|string',
            'competition_rules' => 'nullable|string',
            'event_timeline' => 'nullable|string',
            'social_information' => 'nullable|string',
            'organizer_email' => 'nullable|email',
            'organizer_hotline' => 'nullable|string|max:20',
            'competition_schedule' => 'nullable|string',
            'results' => 'nullable|string',
            'gallery_json' => 'nullable|string',
            'gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $data = $request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'registration_deadline',
            'location',
            'max_participants',
            'price',
            'rules',
            'prizes',
            'competition_format',
            'tournament_rank',
            'registration_benefits',
            'competition_rules',
            'event_timeline',
            'social_information',
            'organizer_email',
            'organizer_hotline',
            'competition_schedule',
            'results',
        ]);

        if ($request->hasFile('image')) {
            if ($tournament->image && Storage::disk('public')->exists($tournament->image)) {
                Storage::disk('public')->delete($tournament->image);
            }
            $data['image'] = $request->file('image')->store('tournament_images', 'public');
        }

        // Handle gallery JSON (from form)
        if ($request->has('gallery_json') && !empty($request->gallery_json)) {
            try {
                $data['gallery'] = json_decode($request->gallery_json, true) ?? [];
            } catch (\Exception $e) {
                $data['gallery'] = $tournament->gallery ?? [];
            }
        }
        // Handle gallery file uploads (legacy support)
        elseif ($request->hasFile('gallery')) {
            $gallery = is_array($tournament->gallery) ? $tournament->gallery : (is_string($tournament->gallery) ? json_decode($tournament->gallery, true) ?? [] : []);
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $file->store('tournament_gallery', 'public');
            }
            $data['gallery'] = $gallery;
        }

        $tournament->update($data);

        // Handle AJAX requests
        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'message' => 'Tournament updated successfully']);
        }

        return redirect()->route('homeyard.tournaments.index')->with('success', 'Tournament updated successfully.');
    }

    public function destroy(Tournament $tournament)
    {
        $this->authorize('delete', $tournament);

        if ($tournament->image && Storage::disk('public')->exists($tournament->image)) {
            Storage::disk('public')->delete($tournament->image);
        }

        $tournament->delete();

        return redirect()->route('homeyard.tournaments.index')->with('success', 'Tournament deleted successfully.');
    }

    public function addAthlete(Request $request, Tournament $tournament)
    {
        \Log::info('addAthlete called', [
            'tournament_id' => $tournament->id,
            'all_inputs' => $request->all(),
            'is_json' => $request->wantsJson(),
            'header' => $request->header('X-Requested-With'),
        ]);

        try {
            $this->authorize('update', $tournament);
        } catch (\Exception $e) {
            \Log::error('Authorization failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'athlete_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'category_id' => 'required|exists:tournament_categories,id',
            ]);

            \Log::info('Validation passed', $validated);

            $athlete = TournamentAthlete::create([
                'tournament_id' => $tournament->id,
                'category_id' => $validated['category_id'],
                'user_id' => auth()->id(),  // Set the current user as the creator
                'athlete_name' => $validated['athlete_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => 'approved',  // Home yard organizer adds athletes as approved
                'payment_status' => 'pending',
            ]);

            \Log::info('Athlete created', ['id' => $athlete->id]);

            // Always return JSON for this endpoint
            return response()->json([
                'success' => true,
                'message' => 'Thêm vận động viên thành công',
                'athlete' => $athlete
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Lỗi validate: ' . collect($e->errors())->flatten()->join(', '),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Create error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateAthlete(Request $request, Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);

        try {
            $validated = $request->validate([
                'athlete_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'category_id' => 'required|exists:tournament_categories,id',
            ]);

            $athlete->update($validated);

            \Log::info('Athlete updated', ['id' => $athlete->id]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật vận động viên thành công',
                'athlete' => $athlete
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Lỗi validate: ' . collect($e->errors())->flatten()->join(', '),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Update error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeAthlete(Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);
        $athlete->delete();
        
        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Vận động viên đã được xóa'
            ]);
        }
        
        return redirect()->back()->with('success', 'Athlete removed successfully.');
    }

    public function updateAthleteStatus(Request $request, Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        $athlete->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Athlete status updated successfully.');
    }

    public function approveAthlete(Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);

        $athlete->update(['status' => 'approved']);

        return redirect()->back()->with('success', "Vận động viên {$athlete->athlete_name} đã được duyệt.");
    }

    public function rejectAthlete(Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);

        $athlete->update(['status' => 'rejected']);

        return redirect()->back()->with('success', "Vận động viên {$athlete->athlete_name} đã bị từ chối.");
    }

    public function listAthletes(Request $request)
    {
        $status = $request->query('status', 'pending');

        $athletes = TournamentAthlete::whereHas('tournament', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->where('status', $status)
            ->with('tournament')
            ->latest()
            ->paginate(15);

        $stats = [
            'pending' => TournamentAthlete::whereHas('tournament', function ($q) {
                $q->where('user_id', auth()->id());
            })->where('status', 'pending')->count(),
            'approved' => TournamentAthlete::whereHas('tournament', function ($q) {
                $q->where('user_id', auth()->id());
            })->where('status', 'approved')->count(),
            'rejected' => TournamentAthlete::whereHas('tournament', function ($q) {
                $q->where('user_id', auth()->id());
            })->where('status', 'rejected')->count(),
        ];

        return view('home-yard.athletes.index', compact('athletes', 'status', 'stats'));
    }

    public function updateTournament(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'rules' => 'nullable|string',
            'status' => 'nullable|in:upcoming,ongoing,completed,cancelled',
        ]);

        $data = $request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'location',
            'max_participants',
            'price',
            'rules',
            'status',
        ]);

        // Convert status string to boolean: upcoming/ongoing = true, completed/cancelled = false
        if (isset($data['status'])) {
            $data['status'] = in_array($data['status'], ['upcoming', 'ongoing']) ? true : false;
        }

        $tournament->update($data);

        return redirect()->back()->with('success', 'Thông tin giải đấu đã được cập nhật thành công.');
    }

    public function overview()
    {
        return view('home-yard.tournaments.overview');
    }

    public function tournaments()
    {
        $tournaments = Tournament::where('user_id', auth()->id())
            ->with(['athletes', 'categories'])
            ->latest()
            ->paginate(12);

        // Calculate stats
        $now = now();
        $stats = [
            'total' => Tournament::where('user_id', auth()->id())->count(),
            'ongoing' => Tournament::where('user_id', auth()->id())
                ->where('start_date', '<=', $now)
                ->where(function ($q) {
                    $q->where('end_date', '>=', now())
                        ->orWhereNull('end_date');
                })
                ->count(),
            'upcoming' => Tournament::where('user_id', auth()->id())
                ->where('start_date', '>', $now)
                ->count(),
            'completed' => Tournament::where('user_id', auth()->id())
                ->where(function ($q) use ($now) {
                    $q->where('end_date', '<', $now)
                        ->orWhere('status', 0);
                })
                ->count(),
        ];

        return view('home-yard.tournaments.tournaments', compact('tournaments', 'stats'));
    }

    public function matches()
    {
        return view('home-yard.tournaments.matches');
    }

    public function athletes()
    {
        return view('home-yard.tournaments.athletes');
    }

    public function rankings()
    {
        return view('home-yard.tournaments.rankings');
    }

    public function courts()
    {
        $stadiums = Stadium::where('user_id', auth()->id())->get();
        $courts = Court::whereIn('stadium_id', $stadiums->pluck('id'))->paginate(9);
        return view('home-yard.tournaments.courts', compact('stadiums', 'courts'));
    }

    public function storeCourt(Request $request)
    {
        try {
            $validated = $request->validate([
                'court_name' => 'required|string|max:255',
                'court_number' => 'nullable|string|max:100',
                'court_type' => 'required|string|in:indoor,outdoor',
                'surface_type' => 'required|string|in:acrylic,polyurethane,concrete,sport-court',
                'stadium_id' => 'required|exists:stadiums,id',
                'capacity' => 'required|integer|min:1',
                'description' => 'nullable|string',
            ]);

            Court::create([
                'stadium_id' => $validated['stadium_id'],
                'court_name' => $validated['court_name'],
                'court_number' => $validated['court_number'] ?? null,
                'court_type' => $validated['court_type'],
                'size' => $request->size,
                'surface_type' => $validated['surface_type'],
                'capacity' => $validated['capacity'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => 'available',
                'is_active' => true,
            ]);

            return response()->json(['success' => true, 'message' => 'Sân được thêm thành công']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ]);
        } catch (\Exception $e) {
            Log::error('Court creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo sân. Vui lòng thử lại sau.'
            ]);
        }
    }

    public function saveCourts(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'court_ids' => 'required|array|min:1',
            'court_ids.*' => 'required|integer|exists:courts,id',
        ]);

        // Save selected court IDs as JSON in tournament_courts field
        $tournament->update([
            'tournament_courts' => json_encode($request->court_ids)
        ]);

        return redirect()->back()->with('success', 'Sân thi đấu đã được lưu thành công.')->with('step', 3);
    }

    public function editCourt(Court $court)
    {
        try {
            return response()->json([
                'success' => true,
                'court' => [
                    'id' => $court->id,
                    'court_name' => $court->court_name,
                    'court_number' => $court->court_number,
                    'court_type' => $court->court_type,
                    'surface_type' => $court->surface_type,
                    'stadium_id' => $court->stadium_id,
                    'capacity' => $court->capacity,
                    'description' => $court->description,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Court fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu sân'
            ], 500);
        }
    }

    public function updateCourt(Request $request, Court $court)
    {
        try {
            $validated = $request->validate([
                'court_name' => 'required|string|max:255',
                'court_number' => 'nullable|string|max:100',
                'court_type' => 'required|string|in:indoor,outdoor',
                'surface_type' => 'required|string|in:acrylic,polyurethane,concrete,sport-court',
                'stadium_id' => 'required|exists:stadiums,id',
                'capacity' => 'required|integer|min:1',
                'description' => 'nullable|string',
            ]);

            $court->update([
                'stadium_id' => $validated['stadium_id'],
                'court_name' => $validated['court_name'],
                'court_number' => $validated['court_number'] ?? null,
                'court_type' => $validated['court_type'],
                'surface_type' => $validated['surface_type'],
                'size' => $request->size,
                'capacity' => $validated['capacity'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'Sân được cập nhật thành công']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ]);
        } catch (\Exception $e) {
            Log::error('Court update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật sân. Vui lòng thử lại sau.'
            ]);
        }
    }

    public function bookings(Request $request)
    {
        $stadiums = Stadium::where('user_id', auth()->id())->get();
        $courts = Court::whereIn('stadium_id', $stadiums->pluck('id'))->where('is_active', 1)->get();
        $date = $request->date ?? now()->format('Y-m-d');

        return view('home-yard.tournaments.bookings', compact('courts', 'date'));
    }

    /**
     * Bốc thăm chia bảng cho một nội dung thi đấu
     */
    public function drawAthletes(Request $request, Tournament $tournament)
    {
        try {
            $this->authorize('update', $tournament);

            $categoryId = $request->input('category_id');
            $numberOfGroups = $request->input('number_of_groups');
            $drawMethod = $request->input('draw_method');

            // Validate manually
            if (!$categoryId || !$numberOfGroups || !$drawMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'category_id, number_of_groups, draw_method are required'
                ], 422);
            }

            if (!in_array($drawMethod, ['auto', 'seeded'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'draw_method must be auto or seeded'
                ], 422);
            }

            Log::info('Draw attempt', [
                'tournament_id' => $tournament->id,
                'category_id' => $categoryId,
                'number_of_groups' => $numberOfGroups,
                'draw_method' => $drawMethod,
                'is_numeric' => is_numeric($numberOfGroups),
                'type' => gettype($numberOfGroups)
            ]);

            if (!is_numeric($numberOfGroups) || $numberOfGroups < 1 || $numberOfGroups > 16) {
                return response()->json([
                    'success' => false,
                    'message' => 'number_of_groups must be between 1 and 16',
                    'debug' => [
                        'is_numeric' => is_numeric($numberOfGroups),
                        'value' => $numberOfGroups,
                        'type' => gettype($numberOfGroups)
                    ]
                ], 422);
            }

            // Lấy danh sách VĐV đã duyệt cho nội dung này
            $approvedAthletes = TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->where('status', 'approved')
                ->get();

            if ($approvedAthletes->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có VĐV nào đã duyệt cho nội dung này'
                ], 422);
            }

            // Kiểm tra xem các bảng có tồn tại không
            $existingGroups = Group::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->get();

            if ($existingGroups->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng tạo bảng trước khi bốc thăm'
                ], 422);
            }

            // Xử lý bốc thăm
            if ($drawMethod === 'auto') {
                $this->drawAthletesByRandom($approvedAthletes, $existingGroups);
            } elseif ($drawMethod === 'seeded') {
                $this->drawAthletesBySeeding($approvedAthletes, $existingGroups);
            }

            // Refresh groups from DB để lấy dữ liệu mới nhất sau update
            $refreshedGroups = Group::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->get();

            $results = $this->getGroupedAthletes($refreshedGroups);

            return response()->json([
                'success' => true,
                'message' => 'Bốc thăm thành công',
                'athletes' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Draw athletes error: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi bốc thăm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bốc thăm ngẫu nhiên
     */
    private function drawAthletesByRandom($athletes, $groups)
    {
        // Xáo trộn danh sách VĐV
        $shuffled = $athletes->shuffle();

        // Reset group collection index để tránh sai lệch index
        $groupsCollection = $groups->values();

        // Chia đều vào các bảng
        $groupIndex = 0;
        $drawCount = 0;

        foreach ($shuffled as $athlete) {
            $group = $groupsCollection[$groupIndex % $groupsCollection->count()];

            // Lưu group_id vào tournament_athletes
            $updated = $athlete->update([
                'group_id' => $group->id,
                'seed_number' => null
            ]);

            Log::info('After update - checking DB', [
                'athlete_id' => $athlete->id,
                'db_group_id' => DB::table('tournament_athletes')->where('id', $athlete->id)->value('group_id'),
                'model_group_id' => $athlete->group_id,
                'updated_result' => $updated
            ]);

            if ($updated) {
                $drawCount++;
                Log::info('Draw athlete - Random', [
                    'athlete_id' => $athlete->id,
                    'athlete_name' => $athlete->athlete_name,
                    'category_id' => $athlete->category_id,
                    'group_id' => $group->id,
                    'group_name' => $group->group_name
                ]);
            }

            // Tạo GroupStanding record
            GroupStanding::updateOrCreate(
                [
                    'group_id' => $group->id,
                    'athlete_id' => $athlete->id,
                ],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'win_rate' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                    'is_advanced' => false,
                ]
            );

            $groupIndex++;
        }

        Log::info('Random draw completed', [
            'total_athletes' => $drawCount,
            'total_groups' => $groupsCollection->count()
        ]);

        // Cập nhật số lượng VĐV hiện tại trong từng bảng
        foreach ($groupsCollection as $group) {
            $count = TournamentAthlete::where('group_id', $group->id)->count();

            Log::info('Before update group', [
                'group_id' => $group->id,
                'query_group_id' => $group->id,
                'count_from_query' => $count,
                'all_with_group_id_8' => TournamentAthlete::where('group_id', 8)->count()
            ]);

            $group->update(['current_participants' => $count]);

            Log::info('Group participants updated', [
                'group_id' => $group->id,
                'group_name' => $group->group_name,
                'participants_count' => $count
            ]);
        }
    }

    /**
     * Bốc thăm theo hạt giống (seeding)
     */
    private function drawAthletesBySeeding($athletes, $groups)
    {
        // Sắp xếp VĐV theo position (hạt giống)
        $sorted = $athletes->sortBy('position')->values();

        // Reset group collection index để tránh sai lệch index
        $groupsCollection = $groups->values();

        // Chia vào các bảng theo cách "snake" (snake draft)
        $groupIndex = 0;
        $ascending = true;
        $drawCount = 0;

        foreach ($sorted as $index => $athlete) {
            $group = $groupsCollection[$groupIndex];

            // Lưu group_id và seed_number vào tournament_athletes
            $updated = $athlete->update([
                'group_id' => $group->id,
                'seed_number' => $index + 1
            ]);

            if ($updated) {
                $drawCount++;
                Log::info('Draw athlete - Seeded', [
                    'athlete_id' => $athlete->id,
                    'athlete_name' => $athlete->athlete_name,
                    'category_id' => $athlete->category_id,
                    'group_id' => $group->id,
                    'group_name' => $group->group_name,
                    'seed_number' => $index + 1
                ]);
            }

            // Tạo GroupStanding record
            GroupStanding::updateOrCreate(
                [
                    'group_id' => $group->id,
                    'athlete_id' => $athlete->id,
                ],
                [
                    'rank_position' => 0,
                    'matches_played' => 0,
                    'matches_won' => 0,
                    'matches_lost' => 0,
                    'matches_drawn' => 0,
                    'win_rate' => 0,
                    'points' => 0,
                    'sets_won' => 0,
                    'sets_lost' => 0,
                    'sets_differential' => 0,
                    'games_won' => 0,
                    'games_lost' => 0,
                    'games_differential' => 0,
                    'is_advanced' => false,
                ]
            );

            // Thay đổi hướng sau mỗi vòng (snake draft)
            if ($ascending) {
                $groupIndex++;
                if ($groupIndex >= $groups->count()) {
                    $groupIndex = $groups->count() - 1;
                    $ascending = false;
                }
            } else {
                $groupIndex--;
                if ($groupIndex < 0) {
                    $groupIndex = 0;
                    $ascending = true;
                }
            }
        }

        Log::info('Seeded draw completed', [
            'total_athletes' => $drawCount,
            'total_groups' => $groupsCollection->count()
        ]);

        // Cập nhật số lượng VĐV hiện tại trong từng bảng
        foreach ($groupsCollection as $group) {
            $count = TournamentAthlete::where('group_id', $group->id)->count();

            Log::info('Before update group', [
                'group_id' => $group->id,
                'query_group_id' => $group->id,
                'count_from_query' => $count,
                'all_with_group_id_8' => TournamentAthlete::where('group_id', 8)->count()
            ]);

            $group->update(['current_participants' => $count]);

            Log::info('Group participants updated', [
                'group_id' => $group->id,
                'group_name' => $group->group_name,
                'participants_count' => $count
            ]);
        }
    }

    /**
     * Lấy danh sách VĐV đã chia theo bảng
     */
    private function getGroupedAthletes($groups)
    {
        $result = [];

        foreach ($groups as $group) {
            $athletes = TournamentAthlete::where('group_id', $group->id)
                ->orderBy('seed_number')
                ->orderBy('id')
                ->get();

            $result[] = [
                'group_id' => $group->id,
                'group_name' => $group->group_name,
                'group_code' => $group->group_code,
                'athletes' => $athletes->map(function ($athlete) {
                    return [
                        'id' => $athlete->id,
                        'name' => $athlete->athlete_name,
                        'seed_number' => $athlete->seed_number,
                        'position' => $athlete->position
                    ];
                })->toArray()
            ];
        }

        return $result;
    }

    /**
     * Lấy kết quả bốc thăm
     */
    public function getDrawResults(Request $request, Tournament $tournament)
    {
        $categoryId = $request->input('category_id');

        if (!$categoryId) {
            return response()->json([
                'success' => false,
                'message' => 'category_id is required'
            ], 422);
        }

        $groups = Group::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $this->getGroupedAthletes($groups)
        ]);
    }

    /**
     * Xóa kết quả bốc thăm (reset)
     */
    public function resetDraw(Request $request, Tournament $tournament)
    {
        try {
            $this->authorize('update', $tournament);

            $categoryId = $request->input('category_id');

            if (!$categoryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'category_id is required'
                ], 422);
            }

            // Xóa gán bảng cho tất cả VĐV của nội dung này
            TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->update([
                    'group_id' => null,
                    'seed_number' => null
                ]);

            // Reset số lượng VĐV trong các bảng
            $groups = Group::where('tournament_id', $tournament->id)
                ->where('category_id', $request->category_id)
                ->get();

            foreach ($groups as $group) {
                $group->update(['current_participants' => 0]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa kết quả bốc thăm'
            ]);
        } catch (\Exception $e) {
            Log::error('Reset draw error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi reset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xuất danh sách VĐV ra CSV
     */
    public function exportAthletes(Tournament $tournament)
    {
        try {
            // Kiểm tra quyền
            $this->authorize('view', $tournament);

            // Lấy danh sách VĐV
            $athletes = TournamentAthlete::where('tournament_id', $tournament->id)
                ->with('category')
                ->get();

            if ($athletes->isEmpty()) {
                return back()->with('error', 'Giải đấu không có vận động viên nào để xuất');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }

        // Tạo tên file
        $fileName = 'VDV_' . str_replace(' ', '_', $tournament->name) . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        // Tạo CSV content với proper formatting
        $output = fopen('php://temp', 'r+');
        
        // BOM cho UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Header
        $headers = ['STT', 'Tên Vận Động Viên', 'Email', 'Số Điện Thoại', 'Nội Dung Thi Đấu', 'Trạng Thái', 'Trạng Thái Thanh Toán', 'Ngày Đăng Ký'];
        fputcsv($output, $headers);

        // Dữ liệu
        $stt = 1;
        foreach ($athletes as $athlete) {
            $status = $this->getStatusText($athlete->status);
            $paymentStatus = $this->getPaymentStatusText($athlete->payment_status);
            $categoryName = $athlete->category ? $athlete->category->category_name : '-';
            $registeredDate = $athlete->created_at ? $athlete->created_at->format('d/m/Y H:i') : '-';

            $email = $athlete->email ?? '-';
            // Thêm apostrophe để Excel không convert số điện thoại thành scientific notation
            $phone = "'" . ($athlete->phone ?? '-');

            $row = [
                $stt,
                $athlete->athlete_name,
                $email,
                $phone,
                $categoryName,
                $status,
                $paymentStatus,
                $registeredDate
            ];
            fputcsv($output, $row);
            $stt++;
        }

        // Lấy nội dung CSV
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Return as download
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Lấy text trạng thái VĐV
     */
    private function getStatusText($status)
    {
        $statusMap = [
            'pending' => 'Chờ phê duyệt',
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Từ chối'
        ];
        return $statusMap[$status] ?? $status;
    }

    /**
     * Lấy text trạng thái thanh toán
     */
    private function getPaymentStatusText($status)
    {
        $statusMap = [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'unpaid' => 'Chưa thanh toán'
        ];
        return $statusMap[$status] ?? $status;
    }

    public function bookingCourt(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'court_id' => 'required|exists:courts,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration_hours' => 'required|integer|min:1',
            'hourly_rate' => 'required|integer|min:0',
            'payment_method' => 'required|in:cash,card,transfer,wallet',
            'notes' => 'nullable|string',
        ]);

        if($validation->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validation->errors()->first()
            ]);
        }

        // Get court details
        $court = Court::findOrFail($request->court_id);

        // Calculate duration in hours
        $startTime = \DateTime::createFromFormat('H:i', $request->start_time);
        
        $durationHours = $request->duration_hours;
        $endTime = $startTime->modify('+' . $durationHours . ' hours');

        // Calculate total price
        $totalPrice = round($request->hourly_rate * $durationHours);

        // Check if slot is already booked
        $existingBooking = Booking::where('court_id', $request->court_id)
            ->where('booking_date', $request->booking_date)
            ->where('status', '!=', 'cancelled')
            ->whereRaw("TIME(start_time) < ? AND TIME(end_time) > ?", [$endTime->format('H:i'), $request->start_time])
            ->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Khoảng thời gian này đã được đặt. Vui lòng chọn thời gian khác.'
            ]);
        }

        // Create booking
        $booking = Booking::create([
            'court_id' => $request->court_id,
            'user_id' => auth()->id(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $endTime->format('H:i'),
            'duration_hours' => $durationHours,
            'hourly_rate' => $request->hourly_rate,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'notes' => $request->notes ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đặt sân thành công. Đơn đặt của bạn đang chờ xác nhận.',
            'booking' => [
                'id' => $booking->id,
                'booking_id' => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'status' => $booking->status,
            ]
        ]);
        
    }

    public function getAvailableSlots(Court $court, Request $request)
    {
        try {
            $date = $request->query('date');

            // Default time slots (6:00 - 21:00)
            $timeSlots = [];
            for ($hour = 6; $hour < 21; $hour++) {
                $time = sprintf('%02d:00', $hour);
                $timeSlots[] = $time;
            }

            // Get booked slots for this date
            $bookings = Booking::where('court_id', $court->id)
                ->where('booking_date', $date)
                ->where('status', '!=', 'cancelled')
                ->get(['start_time', 'end_time']);

            $bookedSlots = [];
            foreach ($bookings as $booking) {
                $bookedSlots[] = [
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                ];
            }

            return response()->json([
                'success' => true,
                'available_slots' => $timeSlots,
                'booked_slots' => $bookedSlots,
            ]);
        } catch (\Exception $e) {
            Log::error('Get slots error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy dữ liệu khoảng thời gian'
            ]);
        }
    }

    public function getBookingsByDate(Request $request)
    {
        $date = $request->query('date');

        if (!$date) {
            $date = now()->toDateString();
        }

        // Get all bookings for this date
        $bookings = Booking::where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get([
                'id',
                'court_id',
                'customer_name',
                'start_time',
                'end_time',
                'status',
            ])
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'court_id' => $booking->court_id,
                    'customer_name' => $booking->customer_name,
                    'start_time' => substr($booking->start_time, 0, 5),
                    'end_time' => substr($booking->end_time, 0, 5),
                    'status' => $booking->status,
                ];
            });

        return response()->json([
            'success' => true,
            'bookings' => $bookings,
            'date' => $date,
        ]);
        
    }

    public function getBookingStats(Request $request)
    {
        $stadiums = Stadium::where('user_id', auth()->id())->pluck('id');
        $courtsOfUser = Court::whereIn('stadium_id', $stadiums)->pluck('id');

        // Get current month's bookings
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $bookings = Booking::whereIn('court_id', $courtsOfUser)
            ->whereBetween('booking_date', [$startOfMonth, $endOfMonth]);

        $total = $bookings->count();
        $confirmed = $bookings->clone()->where('status', 'confirmed')->count();
        $pending = $bookings->clone()->where('status', 'pending')->count();
        $cancelled = Booking::whereIn('court_id', $courtsOfUser)
            ->whereBetween('booking_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'cancelled')
            ->count();

        return response()->json([
            'success' => true,
            'total' => $total,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'cancelled' => $cancelled,
        ]);
    }
}
