<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointTaskController extends Controller
{
    /**
     * List all point tasks
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PointTask::class);

        $query = PointTask::query()->orderBy('role')->orderBy('category');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $tasks = $query->get();

        // Group by role for display
        $tasksByRole = $tasks->groupBy('role');

        return view('admin.point-tasks.index', compact('tasks', 'tasksByRole'));
    }

    /**
     * Update task configuration
     */
    public function update(Request $request, PointTask $pointTask): RedirectResponse
    {
        $this->authorize('update', $pointTask);

        $request->validate([
            'points' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $pointTask->update([
            'points' => $request->input('points'),
            'is_active' => $request->boolean('is_active'),
            'description' => $request->input('description'),
        ]);

        return back()->with('success', 'Task "' . e($pointTask->name) . '" updated.');
    }
}
