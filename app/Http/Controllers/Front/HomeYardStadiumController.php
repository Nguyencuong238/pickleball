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
        $stadium = new Stadium();
        return view('home-yard.stadiums.create', compact('stadium'));
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
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'regulations' => 'nullable|string',
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'phone',
            'email',
            'website',
            'court_surface',
            'opening_hours',
            'amenities',
            'regulations',
        ]);

        $data['user_id'] = auth()->id();
        $stadium = Stadium::create($data);

        // Sync gallery images
        $stadium->syncMediaCollection('gallery', 'gallery', $request);

        // Sync banner image
        $stadium->syncMediaCollection('banner', 'banner', $request);

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
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'regulations' => 'nullable|string',
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'phone',
            'email',
            'website',
            'court_surface',
            'opening_hours',
            'amenities',
            'regulations',
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
