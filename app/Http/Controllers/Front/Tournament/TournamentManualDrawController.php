<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\Tournament\Traits\DrawAuthorizationTrait;
use App\Models\Tournament;
use App\Services\Tournament\TournamentDrawService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TournamentManualDrawController extends Controller
{
    use DrawAuthorizationTrait;

    public function __construct(
        private TournamentDrawService $drawService
    ) {
        $this->middleware(['auth']);
    }

    public function getManualDraw(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $categoryId = (int) $request->query('category_id');

        if (!$this->resolveCategory($tournament, $categoryId)) {
            return $this->invalidCategoryResponse();
        }

        return response()->json(
            ['success' => true] + $this->drawService->getManualDrawData($tournament, $categoryId)
        );
    }

    public function saveManualDraw(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'category_id'                  => 'required|integer',
            'assignments'                  => 'required|array',
            'assignments.*'                => 'array',
            'assignments.*.*.athlete_id'   => 'required|integer',
            'assignments.*.*.partner_id'   => 'nullable|integer',
            'assignments.*.*.draw_order'   => 'nullable|integer',
        ]);

        $categoryId = (int) $validated['category_id'];

        if (!$this->resolveCategory($tournament, $categoryId)) {
            return $this->invalidCategoryResponse();
        }

        try {
            $count = $this->drawService->saveManualDraw($tournament, $categoryId, $validated['assignments']);

            return response()->json([
                'success' => true,
                'message' => "Đã lưu bốc thăm thủ công ({$count} vận động viên).",
            ]);
        } catch (\Exception $e) {
            Log::error('Lưu bốc thăm thủ công thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Lỗi khi lưu bốc thăm. Vui lòng thử lại.'], 500);
        }
    }
}
