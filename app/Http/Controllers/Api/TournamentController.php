<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    /**
     * Get all tournaments
     */
    public function index(Request $request)
    {
        $query = Tournament::query();

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Sort
        $sort = $request->get('sort', 'start_date');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        // Pagination
        $per_page = $request->get('per_page', 15);
        $tournaments = $query->paginate($per_page);

        return TournamentResource::collection($tournaments)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Get tournament details
     */
    public function show($id)
    {
        $tournament = Tournament::with('categories', 'athletes', 'rounds', 'groups')->find($id);

        if (!$tournament) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found',
            ], 404);
        }

        return new TournamentResource($tournament);
    }

    /**
     * Get tournament standings/leaderboard
     */
    public function standings($id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found',
            ], 404);
        }

        $athletes = $tournament->athletes()
            ->orderBy('rank', 'asc')
            ->get();

        return response()->json([
            'data' => $athletes,
        ], 200);
    }
}
