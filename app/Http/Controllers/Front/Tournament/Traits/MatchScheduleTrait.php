<?php

namespace App\Http\Controllers\Front\Tournament\Traits;

use App\Models\MatchModel;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait MatchScheduleTrait
{
    public function updateSchedule(Request $request, Tournament $tournament, MatchModel $match): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);
        abort_unless($match->tournament_id === $tournament->id, 404);

        $validated = $request->validate([
            'match_date' => 'nullable|date',
            'match_time' => 'nullable|string|max:10',
        ]);

        $match->update([
            'match_date' => $validated['match_date'] ?? null,
            'match_time' => $validated['match_time'] ?? null,
        ]);

        return response()->json(['success' => true, 'match' => $match->only(['id', 'match_date', 'match_time'])]);
    }
}
