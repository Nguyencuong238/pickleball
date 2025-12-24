<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Stadium;
use App\Models\Province;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Str;

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
        $stadium = new Stadium();
        $provinces = Province::all();
        return view('home-yard.stadiums.create', compact('stadium', 'provinces'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'province_id' => 'nullable|exists:provinces,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'court_surface' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'regulations' => 'nullable|string',
            'maps_address' => 'nullable|string',
            'maps_link' => 'nullable|string|url',
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'province_id',
            'phone',
            'email',
            'website',
            'court_surface',
            'opening_hours',
            'amenities',
            'regulations',
            'maps_address',
            'maps_link',
        ]);

        $data['slug'] = Str::slug($request->name);
        $data['user_id'] = auth()->id();
        $stadium = Stadium::create($data);

        // Log activity
        ActivityLog::log("Sân '{$stadium->name}' được tạo", 'Stadium', $stadium->id);

        // Sync gallery images
        $stadium->syncMediaCollection('gallery', 'gallery', $request);

        // Sync banner image
        $stadium->syncMediaCollection('banner', 'banner', $request);

        return redirect()->route('homeyard.stadiums.index')->with('success', 'Stadium created successfully.');
    }

    public function edit(Stadium $stadium)
    {
        $this->checkOwnership($stadium);
        $provinces = Province::all();
        return view('home-yard.stadiums.edit', compact('stadium', 'provinces'));
    }

    public function update(Request $request, Stadium $stadium)
    {
        $this->checkOwnership($stadium);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'province_id' => 'nullable|exists:provinces,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'court_surface' => 'nullable|string|max:255',
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'regulations' => 'nullable|string',
            'maps_address' => 'nullable|string',
            'maps_link' => 'nullable|string|url',
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'province_id',
            'phone',
            'email',
            'website',
            'court_surface',
            'opening_hours',
            'amenities',
            'regulations',
            'maps_address',
            'maps_link',
        ]);

        $stadium->update($data);

        // Sync gallery images
        if ($request->has('gallery')) {
            $stadium->syncMediaCollection('gallery', 'gallery', $request);
        }

        // Sync banner image
        if ($request->has('banner')) {
            $stadium->syncMediaCollection('banner', 'banner', $request);
        }

        return redirect()->back()->with('success', 'Stadium updated successfully.');
    }

    public function destroy(Stadium $stadium)
    {
        $this->checkOwnership($stadium);

        // Delete all media collections
        $stadium->clearMediaCollection('gallery');
        $stadium->clearMediaCollection('banner');

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
