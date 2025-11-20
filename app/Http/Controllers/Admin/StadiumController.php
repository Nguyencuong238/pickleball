<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stadium;
use Illuminate\Http\Request;

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
            'court_surface' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
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

        $stadium = Stadium::create($data);

        // Upload gallery images
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $stadium->addMedia($image)
                    ->toMediaCollection('images');
            }
        }

        // Upload banner image
        if ($request->hasFile('banner')) {
            $stadium->addMedia($request->file('banner'))
                ->toMediaCollection('banner');
        }

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
            'court_surface' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'utilities' => 'nullable|string',
            'regulations' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'featured_status' => 'nullable|in:featured,normal',
            'verified' => 'nullable|boolean',
            'rating' => 'nullable|numeric|between:0,5',
            'rating_count' => 'nullable|integer|min:0',
            'deleted_media_ids' => 'nullable|string',
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
            'utilities',
            'regulations',
            'status',
            'featured_status',
            'verified',
            'rating',
            'rating_count',
        ]);

        $stadium->update($data);

        // Delete marked media files
        if ($request->filled('deleted_media_ids')) {
            $deletedIds = array_filter(explode(',', $request->input('deleted_media_ids')));
            foreach ($deletedIds as $mediaId) {
                $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                if ($media) {
                    $media->delete();
                }
            }
        }

        // Upload gallery images
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $stadium->addMedia($image)
                    ->toMediaCollection('images');
            }
        }

        // Update banner image
        if ($request->hasFile('banner')) {
            // Delete old banner
            $stadium->clearMediaCollection('banner');
            $stadium->addMedia($request->file('banner'))
                ->toMediaCollection('banner');
        }

        return redirect()->route('admin.stadiums.index')->with('success', 'Stadium updated successfully.');
    }

    public function destroy(Stadium $stadium)
    {
        // Delete all media files
        $stadium->clearMediaCollection('images');
        $stadium->clearMediaCollection('banner');

        $stadium->delete();

        return redirect()->route('admin.stadiums.index')->with('success', 'Stadium deleted successfully.');
    }

}
