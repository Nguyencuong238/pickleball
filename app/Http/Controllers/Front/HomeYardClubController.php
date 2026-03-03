<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeYardClubController extends Controller
{
    /**
     * Display a listing of clubs/groups for homeyard
     */
    public function index()
    {
        $userId = Auth::id();

        // Clubs user has joined (not as creator)
        $joinedClubs = Club::with(['creator', 'members', 'provinces'])
            ->whereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('user_id', '!=', $userId)
            ->get();

        // Clubs user created
        $clubs = Club::with(['creator', 'members', 'provinces'])
            ->where('user_id', $userId)
            ->paginate(15);

        return view('home-yard.clubs.index', compact('clubs', 'joinedClubs'));
    }

    /**
     * Show the form for creating a new club/group
     */
    public function create()
    {
        $provinces = Province::all();
        $users = User::where('id', '!=', Auth::id())->get();
        
        return view('home-yard.clubs.create', compact('provinces', 'users'));
    }

    /**
     * Store a newly created club/group
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
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
            $path = $request->file('image')->store('clubs', config('filesystems.default'));
            $club->update(['image' => $path]);
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('clubs/banners', config('filesystems.default'));
            $club->update(['banner' => $path]);
        }

        // Add creator as member
        $club->members()->attach(Auth::id(), ['role' => 'creator']);

        // Add selected members
        if (!empty($validated['members'])) {
            $club->members()->attach($validated['members'], ['role' => 'member']);
        }

        // Add provinces
        $club->provinces()->attach($validated['provinces']);

        return redirect()->route('homeyard.clubs.index')
            ->with('success', 'Nhóm/CLB được tạo thành công!');
    }

    /**
     * Show the form for editing a club/group
     */
    public function edit(Club $club)
    {
        $this->authorize('update', $club);
        
        $provinces = Province::all();
        $users = User::where('id', '!=', Auth::id())->get();
        $selectedProvinces = $club->provinces->pluck('id')->toArray();
        $selectedMembers = $club->members->pluck('id')->toArray();
        
        return view('home-yard.clubs.edit', compact('club', 'provinces', 'users', 'selectedProvinces', 'selectedMembers'));
    }

    /**
     * Update the specified club/group
     */
    public function update(Request $request, Club $club)
    {
        $this->authorize('update', $club);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
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
            $path = $request->file('image')->store('clubs', config('filesystems.default'));
            $club->update(['image' => $path]);
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('clubs/banners', config('filesystems.default'));
            $club->update(['banner' => $path]);
        }

        $club->provinces()->sync($validated['provinces']);
        $club->members()->sync(array_merge([Auth::id()], $validated['members'] ?? []), false);

        return redirect()->route('homeyard.clubs.index')
            ->with('success', 'Nhóm/CLB được cập nhật thành công!');
    }

    /**
     * Remove the specified club/group
     */
    public function destroy(Club $club)
    {
        $this->authorize('delete', $club);
        
        $club->delete();
        
        return redirect()->route('homeyard.clubs.index')
            ->with('success', 'Nhóm/CLB được xóa thành công!');
    }
}
