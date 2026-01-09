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

        $query = $club->activities();

        // Apply status filter
        if ($status = request('status')) {
            $query->where('status', $status);
        }

        // Apply sorting
        $sort = request('sort', 'date_desc');
        if ($sort === 'date_asc') {
            $query->orderBy('activity_date', 'asc');
        } else {
            $query->orderBy('activity_date', 'desc');
        }

        $activities = $query->paginate(10);

        // AJAX response
        if (request()->ajax()) {
            return response()->json([
                'activities' => $activities->items(),
                'hasMore' => $activities->hasMorePages()
            ]);
        }

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

        $activity = $club->activities()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'activity_date' => $validated['activity_date'],
            'location' => $validated['location'],
            'status' => $validated['status'],
        ]);

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Hoạt động được tạo thành công!',
                'activity' => $activity
            ]);
        }

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
            if ($request->ajax()) {
                return response()->json(['message' => 'Không tìm thấy'], 404);
            }
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

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Hoạt động được cập nhật thành công!',
                'activity' => $activity->fresh()
            ]);
        }

        return redirect()->route('clubs.activities.index', $club)
            ->with('success', 'Hoạt động được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Club $club, ClubActivity $activity)
    {
        $this->authorize('delete', $club);

        if ($activity->club_id !== $club->id) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Không tìm thấy'], 404);
            }
            abort(404);
        }

        $activity->delete();

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Hoạt động được xóa thành công!'
            ]);
        }

        return redirect()->route('clubs.activities.index', $club)
            ->with('success', 'Hoạt động được xóa thành công!');
    }
}
