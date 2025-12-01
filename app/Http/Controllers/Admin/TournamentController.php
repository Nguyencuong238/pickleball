<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TournamentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|home_yard']);
    }

    public function index()
    {
        $user = auth()->user();

        // Admins see all tournaments
        if ($user->hasRole('admin')) {
            $tournaments = Tournament::latest()->paginate(10);
        } else {
            // Home yard users only see their own tournaments
            $tournaments = Tournament::where('user_id', $user->id)->latest()->paginate(10);
        }

        return view('admin.tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        return view('admin.tournaments.create');
    }

    public function store(Request $request)
    {
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
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
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
            $data['image'] = $request->file('image')->store('tournament_images', 'public');
        }

        // Handle gallery JSON (from admin form)
        if ($request->has('gallery_json') && !empty($request->gallery_json)) {
            try {
                $data['gallery'] = json_decode($request->gallery_json, true) ?? [];
            } catch (\Exception $e) {
                $data['gallery'] = [];
            }
        }
        // Handle gallery file uploads (legacy support)
        elseif ($request->hasFile('gallery')) {
            $gallery = [];
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $file->store('tournament_gallery', 'public');
            }
            $data['gallery'] = $gallery;
        }

        // Handle banner file upload
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('tournament_banner', 'public');
        }

        $data['user_id'] = auth()->id();

        $tournament = Tournament::create($data);

        // Log activity
        ActivityLog::log("Giải đấu '{$tournament->name}' được tạo", 'Tournament', $tournament->id);

        // Redirect to homeyard tournaments if user is home_yard, else admin
        $redirectRoute = auth()->user()->hasRole('home_yard') ? 'homeyard.tournaments' : 'admin.tournaments.index';

        return redirect()->route($redirectRoute)->with('success', 'Tournament created successfully.');
    }

    public function show(Tournament $tournament)
    {
        $this->authorize('view', $tournament);
        $athletes = $tournament->athletes()->paginate(15);
        return view('admin.tournaments.show', compact('tournament', 'athletes'));
    }

    public function edit(Tournament $tournament)
    {
        $this->authorize('update', $tournament);
        return view('admin.tournaments.edit', compact('tournament'));
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
            'competition_format' => 'nullable|string|in:single,double,mixed',
            'registration_benefits' => 'nullable|string',
            'competition_rules' => 'nullable|string',
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
            'registration_benefits',
            'competition_rules',
        ]);

        // Sync gallery images
        $tournament->syncMediaCollection('gallery', 'gallery', $request);

        // Sync banner image
        $tournament->syncMediaCollection('banner', 'banner', $request);

        $tournament->update($data);

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament updated successfully.');
    }

    public function destroy(Tournament $tournament)
    {
        $this->authorize('delete', $tournament);

        // Delete media files if they exist
        try {
            $bannerMedia = $tournament->getMedia('banner');
            foreach ($bannerMedia as $media) {
                $media->delete();
            }
        } catch (\Exception $e) {
            // Log error but continue with deletion
            \Log::warning('Failed to delete tournament media: ' . $e->getMessage());
        }

        $tournament->delete();

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament deleted successfully.');
    }
}
