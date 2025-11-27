<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Province;
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

    public function edit(Stadium $stadium)
    {
        $provinces = Province::all();
        return view('admin.stadiums.edit', compact('stadium', 'provinces'));
    }

    public function update(Request $request, Stadium $stadium)
    {
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
            'regulations' => 'nullable|string'
        ]);

        $data = $request->only([
            'name',
            'description',
            'address',
            'province_id',
            'phone',
            'email',
            'court_surface',
            'opening_hours',
            'amenities',
            'regulations',
        ]);

        $data['status'] = $request->status ? 'active' : 'inactive';
        $data['is_featured'] = $request->is_featured ? 1 : 0;
        $data['is_verified'] = $request->is_verified ? 1 : 0;

        $stadium->update($data);

        // Sync gallery images
        if ($request->has('gallery')) {
            $stadium->syncMediaCollection('gallery', 'gallery', $request);
        }

        // Sync banner image
        if ($request->has('banner')) {
            $stadium->syncMediaCollection('banner', 'banner', $request);
        }

        return redirect()->route('admin.stadiums.index')->with('success', 'Cập nhật cụm sân thành công.');
    }

    public function destroy(Stadium $stadium)
    {
        // Delete all media files
        $stadium->clearMediaCollection('gallery');
        $stadium->clearMediaCollection('banner');

        $stadium->delete();

        return redirect()->route('admin.stadiums.index')->with('success', 'Xóa cụm sân thành công.');
    }
}
