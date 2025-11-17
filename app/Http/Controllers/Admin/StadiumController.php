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
            'courts_count' => 'required|integer|min:1',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
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
            'courts_count' => 'required|integer|min:1',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'opening_hours' => 'nullable|string',
            'amenities' => 'nullable|array',
            'status' => 'required|in:active,inactive',
            'deleted_media_ids' => 'nullable|string',
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'phone',
            'email',
            'website',
            'courts_count',
            'opening_hours',
            'amenities',
            'status',
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
