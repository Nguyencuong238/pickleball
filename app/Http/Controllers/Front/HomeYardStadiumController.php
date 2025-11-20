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
            'court_surface' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'utilities' => 'nullable|string',
            'regulations' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'featured_status' => 'nullable|in:featured,normal',
            'verified' => 'nullable|boolean',
            'rating' => 'nullable|numeric|between:0,5',
            'rating_count' => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'phone',
            'email',
            'website',
            'court_surface',
            'latitude',
            'longitude',
            'opening_hours',
            'amenities',
            'utilities',
            'regulations',
            'status',
            'featured_status',
            'verified',
            'rating',
            'rating_count',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('stadium_images', 'public');
        }

        // Convert utilities text to array
        if (!empty($data['utilities'])) {
            $data['utilities'] = array_filter(array_map('trim', explode("\n", $data['utilities'])));
        } else {
            $data['utilities'] = null;
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
            'court_surface' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'utilities' => 'nullable|string',
            'regulations' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'featured_status' => 'nullable|in:featured,normal',
            'verified' => 'nullable|boolean',
            'rating' => 'nullable|numeric|between:0,5',
            'rating_count' => 'nullable|integer|min:0',
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'phone',
            'email',
            'website',
            'court_surface',
            'latitude',
            'longitude',
            'opening_hours',
            'amenities',
            'utilities',
            'regulations',
            'status',
            'featured_status',
            'verified',
            'rating',
            'rating_count',
        ]);

        if ($request->hasFile('image')) {
            if ($stadium->image && Storage::disk('public')->exists($stadium->image)) {
                Storage::disk('public')->delete($stadium->image);
            }
            $data['image'] = $request->file('image')->store('stadium_images', 'public');
        }

        // Convert utilities text to array
        if (!empty($data['utilities'])) {
            $data['utilities'] = array_filter(array_map('trim', explode("\n", $data['utilities'])));
        } else {
            $data['utilities'] = null;
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
