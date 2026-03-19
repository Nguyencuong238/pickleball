<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TournamentGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request, Tournament $tournament): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);

        $categoryId = (int) $request->query('category_id');

        $groups = Group::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->orderBy('group_code')
            ->get(['id', 'group_name', 'group_code', 'max_participants', 'current_participants']);

        return response()->json(['success' => true, 'groups' => $groups]);
    }

    public function setup(Request $request, Tournament $tournament): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'category_id'      => 'required|integer',
            'count'            => 'required|integer|min:1|max:16',
            'max_participants' => 'required|integer|min:2|max:100',
            'advancing_count'  => 'nullable|integer|min:1|max:10',
        ]);

        $categoryId      = (int) $validated['category_id'];
        $count           = (int) $validated['count'];
        $maxParticipants = (int) $validated['max_participants'];
        $advancingCount  = (int) ($validated['advancing_count'] ?? 1);

        // Block if athletes already drawn into groups
        $hasDrawn = TournamentAthlete::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->whereNotNull('group_id')
            ->exists();

        if ($hasDrawn) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có VĐV được bốc thăm. Cần reset bốc thăm trước khi cấu hình lại bảng.',
            ], 422);
        }

        DB::transaction(function () use ($tournament, $categoryId, $count, $maxParticipants, $advancingCount) {
            Group::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->delete();

            for ($i = 0; $i < $count; $i++) {
                $label = chr(ord('A') + $i);
                Group::create([
                    'tournament_id'        => $tournament->id,
                    'category_id'          => $categoryId,
                    'group_name'           => 'Bảng ' . $label,
                    'group_code'           => $label,
                    'max_participants'     => $maxParticipants,
                    'current_participants' => 0,
                    'advancing_count'      => $advancingCount,
                    'status'               => 'active',
                ]);
            }
        });

        $groups = Group::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->orderBy('group_code')
            ->get(['id', 'group_name', 'group_code', 'max_participants', 'current_participants']);

        return response()->json([
            'success' => true,
            'message' => "Đã tạo {$count} bảng đấu.",
            'groups'  => $groups,
        ]);
    }
}
