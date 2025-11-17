<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'max_participants' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'rules' => 'nullable|string',
            'prizes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
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
            'prizes',
            'status',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('tournament_images', 'public');
        }

        $data['user_id'] = auth()->id();

        Tournament::create($data);

        return redirect()->route('homeyard.tournaments.index')->with('success', 'Tournament created successfully.');
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
            'location' => 'nullable|string|max:255',
            'max_participants' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'rules' => 'nullable|string',
            'prizes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
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
            'prizes',
            'status',
        ]);

        if ($request->hasFile('image')) {
            if ($tournament->image && Storage::disk('public')->exists($tournament->image)) {
                Storage::disk('public')->delete($tournament->image);
            }
            $data['image'] = $request->file('image')->store('tournament_images', 'public');
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
}
