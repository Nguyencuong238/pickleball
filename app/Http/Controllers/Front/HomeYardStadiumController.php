<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Stadium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeYardStadiumController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:home_yard']);
    }

    public function index()
    {
        $stadiums = Stadium::where('user_id', auth()->id())->latest()->paginate(10);
        return view('home-yard.stadiums.index', compact('stadiums'));
    }

    public function create()
    {
        return view('home-yard.stadiums.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'courts_count' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'phone',
            'email',
            'website',
            'courts_count',
            'latitude',
            'longitude',
            'opening_hours',
            'amenities',
            'status',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('stadium_images', 'public');
        }

        $data['user_id'] = auth()->id();
        Stadium::create($data);

        return redirect()->route('homeyard.stadiums.index')->with('success', 'Stadium created successfully.');
    }

    public function edit(Stadium $stadium)
    {
        $this->checkOwnership($stadium);
        return view('home-yard.stadiums.edit', compact('stadium'));
    }

    public function update(Request $request, Stadium $stadium)
    {
        $this->checkOwnership($stadium);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'courts_count' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'phone',
            'email',
            'website',
            'courts_count',
            'latitude',
            'longitude',
            'opening_hours',
            'amenities',
            'status',
        ]);

        if ($request->hasFile('image')) {
            if ($stadium->image && Storage::disk('public')->exists($stadium->image)) {
                Storage::disk('public')->delete($stadium->image);
            }
            $data['image'] = $request->file('image')->store('stadium_images', 'public');
        }

        $stadium->update($data);

        return redirect()->route('homeyard.stadiums.index')->with('success', 'Stadium updated successfully.');
    }

    public function destroy(Stadium $stadium)
    {
        $this->checkOwnership($stadium);
        
        if ($stadium->image && Storage::disk('public')->exists($stadium->image)) {
            Storage::disk('public')->delete($stadium->image);
        }

        $stadium->delete();

        return redirect()->route('homeyard.stadiums.index')->with('success', 'Stadium deleted successfully.');
    }

    private function checkOwnership(Stadium $stadium)
    {
        if ($stadium->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
