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
        if($request->sort && in_array($request->sort, ['name', 'created_at'])) {
            $query->orderBy($request->sort, $request->input('direction', 'asc'));
        }
        

        // Pagination
        $stadiums = $query->paginate( $request->input('per_page', 15));

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
