<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialResource;
use App\Models\Social;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    /**
     * Get all socials
     */
    public function index(Request $request)
    {
        $query = Social::query();

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Pagination
        $per_page = $request->input('per_page', 15);
        $socials = $query->paginate($per_page);

        return SocialResource::collection($socials)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Get social details
     */
    public function show($id)
    {
        $social = Social::with('participants')->find($id);

        if (!$social) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Thi đấu social',
            ], 404);
        }

        return new SocialResource($social);
    }

    /**
     * Get social participants
     */
    public function participants($id)
    {
        $social = Social::find($id);

        if (!$social) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Thi đấu social',
            ], 404);
        }

        $participants = $social->participants()->paginate(15);

        return SocialResource::collection($participants)
            ->response()
            ->setStatusCode(200);
    }
}
