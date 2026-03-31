<?php

namespace App\Http\Controllers\Front\Tournament\Traits;

use App\Models\MatchModel;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait MatchListFormatterTrait
{
    /**
     * Return grouped match list as JSON.
     */
    protected function jsonMatchList(Request $request, Tournament $tournament): JsonResponse
    {
        $categoryId = $request->query('category');
        $status     = $request->query('status');

        $query = MatchModel::where('tournament_id', $tournament->id)
            ->with(['athlete1', 'athlete2', 'group', 'category', 'round']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $matches = $query->orderBy('group_id')->orderBy('round_id')->orderBy('match_number')->get();

        $grouped = [];
        foreach ($matches as $match) {
            $catId = $match->category_id;

            // Bracket matches have no group_id; bucket them by round to show round names.
            // Group-stage matches use group_id as the bucket key (prefixed to avoid collision).
            if ($match->group_id) {
                $bucketKey  = 'g_' . $match->group_id;
                $bucketName = $match->group->group_name ?? 'Vòng bảng';
            } else {
                $bucketKey  = 'r_' . ($match->round_id ?? 0);
                $bucketName = $match->round->round_name ?? 'Vòng loại trực tiếp';
            }

            $grouped[$catId] ??= [
                'category_id'   => $catId,
                'category_name' => $match->category->category_name ?? 'Không rõ',
                'groups'        => [],
            ];

            $grouped[$catId]['groups'][$bucketKey] ??= [
                'group_id'   => $bucketKey,
                'group_name' => $bucketName,
                'matches'    => [],
            ];

            $grouped[$catId]['groups'][$bucketKey]['matches'][] = [
                'id'             => $match->id,
                'match_number'   => $match->match_number,
                'athlete1_id'    => $match->athlete1_id,
                'athlete1_name'  => $match->athlete1_name ?? ($match->athlete1->athlete_name ?? 'Chưa xác định'),
                'athlete2_id'    => $match->athlete2_id,
                'athlete2_name'  => $match->athlete2_name ?? ($match->athlete2->athlete_name ?? 'Chưa xác định'),
                'athlete1_score' => $match->athlete1_score,
                'athlete2_score' => $match->athlete2_score,
                'winner_id'      => $match->winner_id,
                'status'         => $match->status,
                'final_score'    => $match->final_score,
                'set_scores'     => $match->set_scores ?? [],
                'best_of'        => $match->best_of ?? 3,
                'match_date'     => $match->match_date?->format('d/m/Y'),
                'match_time'     => $match->match_time,
            ];
        }

        // Ensure all tournament categories appear even when they have no matches yet
        foreach ($tournament->categories as $cat) {
            if (!isset($grouped[$cat->id])) {
                $grouped[$cat->id] = [
                    'category_id'   => $cat->id,
                    'category_name' => $cat->category_name,
                    'groups'        => [],
                ];
            }
        }

        $result = array_values(array_map(function ($cat) {
            $cat['groups'] = array_values($cat['groups']);
            return $cat;
        }, $grouped));

        return response()->json([
            'success'    => true,
            'categories' => $result,
            'stats'      => [
                'total'       => $matches->count(),
                'completed'   => $matches->where('status', 'completed')->count(),
                'in_progress' => $matches->where('status', 'in_progress')->count(),
                'scheduled'   => $matches->where('status', 'scheduled')->count(),
            ],
        ]);
    }
}
