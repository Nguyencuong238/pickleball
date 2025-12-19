<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StadiumResource;
use App\Models\Stadium;
use Illuminate\Http\Request;

class StadiumController extends Controller
{
    /**
     * Get all stadiums
     */
    public function index(Request $request)
    {
        $query = Stadium::with('province', 'courts', 'reviews');

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by province
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // Sort
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        // Pagination
        $per_page = $request->get('per_page', 15);
        $stadiums = $query->paginate($per_page);

        return StadiumResource::collection($stadiums)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Get stadium details
     */
    public function show($id)
    {
        $stadium = Stadium::with('province', 'courts', 'reviews')->find($id);

        if (!$stadium) {
            return response()->json([
                'success' => false,
                'message' => 'Stadium not found',
            ], 404);
        }

        return new StadiumResource($stadium);
    }
}
