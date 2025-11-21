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
    
    public function destroy(Stadium $stadium)
    {
        // Delete all media files
        $stadium->clearMediaCollection('images');
        $stadium->clearMediaCollection('banner');

        $stadium->delete();

        return redirect()->route('admin.stadiums.index')->with('success', 'Stadium deleted successfully.');
    }

}
