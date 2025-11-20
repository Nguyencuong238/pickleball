<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\Court;
use App\Models\Stadium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
            'max_participants' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'rules' => 'nullable|string',
            'prizes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'status' => 'required|in:0,1',
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
            'status',
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
        $this->authorize('update', $tournament);

        $request->validate([
            'athlete_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
        ]);

        TournamentAthlete::create([
            'tournament_id' => $tournament->id,
            'user_id' => auth()->id(),
            'athlete_name' => $request->athlete_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->back()->with('success', 'Athlete added successfully.');
    }

    public function removeAthlete(Tournament $tournament, TournamentAthlete $athlete)
    {
        $this->authorize('update', $tournament);
        $athlete->delete();
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
        
        $athletes = TournamentAthlete::whereHas('tournament', function($q) {
            $q->where('user_id', auth()->id());
        })
        ->where('status', $status)
        ->with('tournament')
        ->latest()
        ->paginate(15);

        $stats = [
            'pending' => TournamentAthlete::whereHas('tournament', function($q) {
                $q->where('user_id', auth()->id());
            })->where('status', 'pending')->count(),
            'approved' => TournamentAthlete::whereHas('tournament', function($q) {
                $q->where('user_id', auth()->id());
            })->where('status', 'approved')->count(),
            'rejected' => TournamentAthlete::whereHas('tournament', function($q) {
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
        return view('home-yard.tournaments.tournaments');
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
                'amenities' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            // Parse amenities string into array
            $amenitiesArray = [];
            if (!empty($validated['amenities'])) {
                $amenitiesArray = array_map('trim', explode(',', $validated['amenities']));
            }

            Court::create([
                'stadium_id' => $validated['stadium_id'],
                'court_name' => $validated['court_name'],
                'court_number' => $validated['court_number'] ?? null,
                'court_type' => $validated['court_type'],
                'surface_type' => $validated['surface_type'],
                'description' => $validated['description'] ?? null,
                'amenities' => !empty($amenitiesArray) ? $amenitiesArray : null,
                'status' => 'available',
                'is_active' => true,
            ]);

            return response()->json(['success' => true, 'message' => 'Sân được thêm thành công']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Court creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating court: ' . $e->getMessage()
            ], 500);
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

    public function bookings()
    {
        return view('home-yard.tournaments.bookings');
    }
}
