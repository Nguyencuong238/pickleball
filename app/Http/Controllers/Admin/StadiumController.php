<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stadium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StadiumController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|home_yard']);
    }

    public function index()
    {
        $stadiums = Stadium::latest()->paginate(10);
        return view('admin.stadiums.index', compact('stadiums'));
    }

    public function create()
    {
        return view('admin.stadiums.create');
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
            'courts_count',
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

        Stadium::create($data);

        return redirect()->route('admin.stadiums.index')->with('success', 'Stadium created successfully.');
    }

    public function edit(Stadium $stadium)
    {
        return view('admin.stadiums.edit', compact('stadium'));
    }

    public function update(Request $request, Stadium $stadium)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'courts_count' => 'required|integer|min:1',
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
            'courts_count',
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

        return redirect()->route('admin.stadiums.index')->with('success', 'Stadium updated successfully.');
    }

    public function destroy(Stadium $stadium)
    {
        if ($stadium->image && Storage::disk('public')->exists($stadium->image)) {
            Storage::disk('public')->delete($stadium->image);
        }

        $stadium->delete();

        return redirect()->route('admin.stadiums.index')->with('success', 'Stadium deleted successfully.');
    }
}
