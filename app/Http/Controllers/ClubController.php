<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clubs = Club::with(['creator', 'members', 'provinces'])
            ->paginate(12);
        
        return view('clubs.index', compact('clubs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = Province::all();
        $users = User::where('id', '!=', Auth::id())->get();
        
        return view('clubs.create', compact('provinces', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'founded_date' => 'required|date',
            'objectives' => 'nullable|string',
            'type' => 'required|in:club,group',
            'provinces' => 'required|array|min:1',
            'provinces.*' => 'exists:provinces,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        $club = Club::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'founded_date' => $validated['founded_date'],
            'objectives' => $validated['objectives'],
            'type' => $validated['type'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('clubs', 'public');
            $club->update(['image' => $path]);
        }

        // Add creator as member
        $club->members()->attach(Auth::id(), ['role' => 'creator']);

        // Add selected members
        if (!empty($validated['members'])) {
            $club->members()->attach($validated['members'], ['role' => 'member']);
        }

        // Add provinces
        $club->provinces()->attach($validated['provinces']);

        return redirect()->route('clubs.show', $club)
            ->with('success', 'Câu lạc bộ/Nhóm được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Club $club)
    {
        $club->load(['creator', 'members', 'provinces', 'activities']);
        
        return view('clubs.show', compact('club'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Club $club)
    {
        $this->authorize('update', $club);
        
        $provinces = Province::all();
        $users = User::where('id', '!=', Auth::id())->get();
        $selectedProvinces = $club->provinces->pluck('id')->toArray();
        $selectedMembers = $club->members->pluck('id')->toArray();
        
        return view('clubs.edit', compact('club', 'provinces', 'users', 'selectedProvinces', 'selectedMembers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Club $club)
    {
        $this->authorize('update', $club);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'founded_date' => 'required|date',
            'objectives' => 'nullable|string',
            'type' => 'required|in:club,group',
            'provinces' => 'required|array|min:1',
            'provinces.*' => 'exists:provinces,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        $club->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'founded_date' => $validated['founded_date'],
            'objectives' => $validated['objectives'],
            'type' => $validated['type'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('clubs', 'public');
            $club->update(['image' => $path]);
        }

        $club->provinces()->sync($validated['provinces']);
        $club->members()->sync(array_merge([Auth::id()], $validated['members'] ?? []), false);

        return redirect()->route('clubs.show', $club)
            ->with('success', 'Câu lạc bộ/Nhóm được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club)
    {
        $this->authorize('delete', $club);
        
        $club->delete();
        
        return redirect()->route('clubs.index')
            ->with('success', 'Câu lạc bộ/Nhóm được xóa thành công!');
    }
}
