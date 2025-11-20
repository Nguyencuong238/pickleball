<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:home_yard']);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|string|in:single_men,single_women,double_men,double_women,double_mixed',
            'age_group' => 'required|string|in:open,u18,18+,35+,45+',
            'max_participants' => 'required|integer|min:4|max:128',
            'prize_money' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['tournament_id'] = $tournament->id;
        $validated['status'] = 'ongoing';
        $validated['current_participants'] = 0;

        TournamentCategory::create($validated);

        return redirect()->back()->with('success', "Nội dung '{$validated['category_name']}' đã được thêm thành công!")->with('step', 2);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Tournament $tournament, TournamentCategory $category)
    {
        $this->authorize('update', $tournament);

        if ($category->tournament_id !== $tournament->id) {
            abort(403);
        }

        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|string|in:single_men,single_women,double_men,double_women,double_mixed',
            'age_group' => 'required|string|in:open,u18,18+,35+,45+',
            'max_participants' => 'required|integer|min:4|max:128',
            'prize_money' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', "Nội dung '{$validated['category_name']}' đã được cập nhật!") ->with('step', 2);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Tournament $tournament, TournamentCategory $category)
    {
        $this->authorize('update', $tournament);

        if ($category->tournament_id !== $tournament->id) {
            abort(403);
        }

        $categoryName = $category->category_name;
        $category->delete();

        return redirect()->back()->with('success', "Nội dung '{$categoryName}' đã được xóa!");
    }
}
