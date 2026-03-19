<?php

namespace App\Http\Controllers\Front\Tournament\Traits;

use App\Models\Group;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\TournamentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * Shared authorization + data-loading helpers for TournamentDrawController.
 */
trait DrawAuthorizationTrait
{
    protected function authorizeOwner(Tournament $tournament): void
    {
        abort_if($tournament->user_id !== auth()->id(), 403);
    }

    /**
     * Resolve category that belongs to tournament or return null-JSON response.
     */
    protected function resolveCategory(Tournament $tournament, int $categoryId): TournamentCategory|null
    {
        return TournamentCategory::where('id', $categoryId)
            ->where('tournament_id', $tournament->id)
            ->first();
    }

    protected function invalidCategoryResponse(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Nội dung không hợp lệ.'], 422);
    }

    /**
     * Load groups for a category.
     */
    protected function loadGroups(Tournament $tournament, int $categoryId): Collection
    {
        return Group::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->get();
    }

    /**
     * Load approved athletes for a category.
     */
    protected function loadApprovedAthletes(Tournament $tournament, int $categoryId, bool $withPartner = false): Collection
    {
        $query = TournamentAthlete::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->where('status', 'approved');

        if ($withPartner) {
            $query->with('partner');
        }

        return $query->get();
    }

    /**
     * Build category summary data for the index view.
     */
    protected function buildCategorySummary(TournamentCategory $cat, Tournament $tournament): array
    {
        $approvedCount = TournamentAthlete::where('tournament_id', $tournament->id)
            ->where('category_id', $cat->id)
            ->where('status', 'approved')
            ->count();

        $drawnCount = TournamentAthlete::where('tournament_id', $tournament->id)
            ->where('category_id', $cat->id)
            ->whereNotNull('group_id')
            ->count();

        $groupCount = Group::where('tournament_id', $tournament->id)
            ->where('category_id', $cat->id)
            ->count();

        return [
            'id'             => $cat->id,
            'category_name'  => $cat->category_name,
            'category_type'  => $cat->category_type,
            'is_doubles'     => $cat->isDoubles(),
            'approved_count' => $approvedCount,
            'drawn_count'    => $drawnCount,
            'group_count'    => $groupCount,
            'is_drawn'       => $drawnCount > 0 && $groupCount > 0,
        ];
    }
}
