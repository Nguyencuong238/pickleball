<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all provinces
     */
    public function getProvinces(Request $request)
    {
        $provinces = Province::query();

        // Search by name
        if ($request->filled('search')) {
            $provinces->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json([
            'success' => true,
            'data' => $provinces->select(['id', 'name'])->get()
        ]);
    }
}
