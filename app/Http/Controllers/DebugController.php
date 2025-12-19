<?php

namespace App\Http\Controllers;

use App\Models\TournamentAthlete;
use App\Models\TournamentCategory;

class DebugController extends Controller
{
    public function checkAthletes($categoryId = 42)
    {
        $athletes = TournamentAthlete::where('category_id', $categoryId)
            ->where('status', 'approved')
            ->get();

        $category = TournamentCategory::find($categoryId);

        return response()->json([
            'category' => [
                'id' => $category?->id,
                'name' => $category?->category_name,
                'type' => $category?->category_type,
                'is_double' => strpos($category?->category_type ?? '', 'double') !== false
            ],
            'athletes_count' => $athletes->count(),
            'athletes' => $athletes->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->athlete_name,
                'partner_id' => $a->partner_id,
                'group_id' => $a->group_id,
                'seed_number' => $a->seed_number,
            ])->toArray()
        ]);
    }
}
