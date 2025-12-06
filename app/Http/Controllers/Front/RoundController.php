<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\Round;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:home_yard']);
    }

    /**
     * Store a newly created round in storage.
     */
    public function store(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:tournament_categories,id',
            'round_name' => 'required|string|max:255',
            'round_number' => 'required|integer|min:1|max:20',
            'round_type' => 'required|string|in:group_stage,knockout,quarterfinal,semifinal,final,bronze',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        // Verify category belongs to this tournament if provided
        if (isset($validated['category_id'])) {
            $category = TournamentCategory::findOrFail($validated['category_id']);
            if ($category->tournament_id !== $tournament->id) {
                abort(403);
            }
        }

        $validated['tournament_id'] = $tournament->id;
        $validated['status'] = 'pending';
        $validated['total_matches'] = 0;
        $validated['completed_matches'] = 0;

        Round::create($validated);

        return redirect()->back()->with('success', "Vòng '{$validated['round_name']}' đã được thêm thành công!")->with('activeTab', 'rounds')->with('step', 3);
    }

    /**
     * Update the specified round in storage.
     */
    public function update(Request $request, Tournament $tournament, Round $round)
    {
        $this->authorize('update', $tournament);

        if ($round->tournament_id !== $tournament->id) {
            abort(403);
        }

        $validated = $request->validate([
            'round_name' => 'required|string|max:255',
            'round_number' => 'required|integer|min:1|max:20',
            'round_type' => 'required|string|in:group_stage,custom,quarterfinal,semifinal,final,round_of_64,round_of_32,round_of_16,third_place',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $round->update($validated);

        return redirect()->back()->with('success', "Vòng '{$validated['round_name']}' đã được cập nhật!")->with('step', 3);
    }

    /**
     * Remove the specified round from storage.
     */
    public function destroy(Tournament $tournament, Round $round)
    {
        $this->authorize('update', $tournament);

        if ($round->tournament_id !== $tournament->id) {
            abort(403);
        }

        $roundName = $round->round_name;
        $round->delete();

        return redirect()->back()->with('success', "Vòng '{$roundName}' đã được xóa!")->with('activeTab', 'rounds');
    }
}
