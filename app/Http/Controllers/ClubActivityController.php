<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Club $club)
    {
        $this->authorize('view', $club);
        
        $activities = $club->activities()
            ->orderBy('activity_date', 'desc')
            ->paginate(10);
        
        return view('clubs.activities.index', compact('club', 'activities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Club $club)
    {
        $this->authorize('update', $club);
        
        return view('clubs.activities.create', compact('club'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Club $club)
    {
        $this->authorize('update', $club);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'required|date_format:Y-m-d\TH:i',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:upcoming,completed,cancelled',
        ]);

        $club->activities()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'activity_date' => $validated['activity_date'],
            'location' => $validated['location'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('clubs.activities.index', $club)
            ->with('success', 'Hoạt động được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Club $club, ClubActivity $activity)
    {
        $this->authorize('view', $club);
        
        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        return view('clubs.activities.show', compact('club', 'activity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Club $club, ClubActivity $activity)
    {
        $this->authorize('update', $club);
        
        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        return view('clubs.activities.edit', compact('club', 'activity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Club $club, ClubActivity $activity)
    {
        $this->authorize('update', $club);
        
        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'required|date_format:Y-m-d\TH:i',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:upcoming,completed,cancelled',
        ]);

        $activity->update($validated);

        return redirect()->route('clubs.activities.index', $club)
            ->with('success', 'Hoạt động được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club, ClubActivity $activity)
    {
        $this->authorize('delete', $club);
        
        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        $activity->delete();

        return redirect()->route('clubs.activities.index', $club)
            ->with('success', 'Hoạt động được xóa thành công!');
    }
}
