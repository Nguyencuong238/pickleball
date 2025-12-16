<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\Round;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:home_yard']);
    }

    /**
     * Store a newly created group in storage.
     */
    public function store(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'category_id' => 'required|exists:tournament_categories,id,tournament_id,' . $tournament->id,
            'round_id' => 'nullable|exists:rounds,id',
            'group_name' => 'required|string|max:255',
            'group_code' => 'required|string|max:10',
            'max_participants' => 'required|integer|min:2|max:128',
            'advancing_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Verify category belongs to this tournament
        $category = \App\Models\TournamentCategory::findOrFail($validated['category_id']);
        if ($category->tournament_id !== $tournament->id) {
            abort(403);
        }

        // Verify round belongs to this tournament if provided
        if (isset($validated['round_id'])) {
            $round = Round::findOrFail($validated['round_id']);
            if ($round->tournament_id !== $tournament->id) {
                abort(403);
            }
        }

        $validated['tournament_id'] = $tournament->id;
        $validated['status'] = 'completed';
        $validated['current_participants'] = 0;

        Group::create($validated);

        return redirect()->back()->with('success', "Bảng '{$validated['group_name']}' đã được thêm thành công!")->with('activeTab', 'brackets')->with('step', 4);
    }

    /**
     * Update the specified group in storage.
     */
    public function update(Request $request, Tournament $tournament, Group $group)
    {
        \Log::info('GroupController@update called', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'wants_json' => $request->wantsJson(),
            'request_data' => $request->all(),
        ]);

        $this->authorize('update', $tournament);

        if ($group->tournament_id !== $tournament->id) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'category_id' => 'nullable|exists:tournament_categories,id,tournament_id,' . $tournament->id,
                'round_id' => 'nullable|exists:rounds,id',
                'group_name' => 'required|string|max:255',
                'group_code' => 'required|string|max:10',
                'max_participants' => 'required|integer|min:2|max:128',
                'advancing_count' => 'required|integer|min:1',
                'description' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        // Verify category belongs to this tournament if updating it
        if (isset($validated['category_id'])) {
            $category = \App\Models\TournamentCategory::findOrFail($validated['category_id']);
            if ($category->tournament_id !== $tournament->id) {
                abort(403);
            }
        }

        // Verify round belongs to this tournament if provided
        if (isset($validated['round_id']) && !empty($validated['round_id'])) {
            $round = Round::findOrFail($validated['round_id']);
            if ($round->tournament_id !== $tournament->id) {
                abort(403);
            }
        }

        // Remove _token if present
        unset($validated['_token']);

        $group->update($validated);
        \Log::info('Group updated successfully', ['group_id' => $group->id]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Bảng '{$validated['group_name']}' đã được cập nhật!",
                'data' => $group
            ]);
        }

        return redirect()->back()->with('success', "Bảng '{$validated['group_name']}' đã được cập nhật!")->with('step', 4);
    }

    /**
     * Remove the specified group from storage.
     */
    public function destroy(Tournament $tournament, Group $group)
    {
        $this->authorize('update', $tournament);

        if ($group->tournament_id !== $tournament->id) {
            abort(403);
        }

        $groupName = $group->group_name;
        $group->delete();

        return redirect()->back()->with('success', "Bảng '{$groupName}' đã được xóa!")->with('activeTab', 'brackets');
    }
}
