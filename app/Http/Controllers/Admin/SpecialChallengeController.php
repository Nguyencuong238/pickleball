<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecialChallengeController extends Controller
{
    /**
     * List challenges
     */
    public function index(): View
    {
        $this->authorize('viewAny', SpecialChallenge::class);

        $challenges = SpecialChallenge::orderByDesc('created_at')->paginate(20);

        return view('admin.special-challenges.index', compact('challenges'));
    }

    /**
     * Create form
     */
    public function create(): View
    {
        $this->authorize('create', SpecialChallenge::class);

        return view('admin.special-challenges.create');
    }

    /**
     * Store new challenge
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SpecialChallenge::class);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'points' => 'required|integer|min:1|max:1000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        SpecialChallenge::create($validated);

        return redirect()
            ->route('admin.special-challenges.index')
            ->with('success', 'Challenge created successfully.');
    }

    /**
     * Edit form
     */
    public function edit(SpecialChallenge $specialChallenge): View
    {
        $this->authorize('update', $specialChallenge);

        return view('admin.special-challenges.edit', compact('specialChallenge'));
    }

    /**
     * Update challenge
     */
    public function update(Request $request, SpecialChallenge $specialChallenge): RedirectResponse
    {
        $this->authorize('update', $specialChallenge);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'points' => 'required|integer|min:1|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_participants' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $specialChallenge->update($validated);

        return redirect()
            ->route('admin.special-challenges.index')
            ->with('success', 'Challenge updated successfully.');
    }

    /**
     * Delete challenge
     */
    public function destroy(SpecialChallenge $specialChallenge): RedirectResponse
    {
        $this->authorize('delete', $specialChallenge);

        $specialChallenge->delete();

        return redirect()
            ->route('admin.special-challenges.index')
            ->with('success', 'Challenge deleted.');
    }
}
