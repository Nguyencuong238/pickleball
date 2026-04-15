<?php

namespace App\Services\Tournament;

use App\Models\GroupStanding;
use App\Models\Tournament;

/**
 * Handles ranking query and formatting for tournaments.
 * Used internally by TournamentStandingService.
 */
class RankingQueryHelper
{
    public function getRankings(Tournament $tournament, ?int $categoryId, ?int $groupId, int $page, int $perPage): array
    {
        $query = GroupStanding::with([
            'athlete' => fn($q) => $q->select('id', 'athlete_name', 'category_id'),
            'athlete.category' => fn($q) => $q->select('id', 'category_name'),
        ])->whereHas('group', fn($q) => $q->where('tournament_id', $tournament->id));

        if ($categoryId) {
            $query->whereHas('athlete', fn($q) => $q->where('category_id', $categoryId));
        }
        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $standings = $query->get()
            ->sort(function ($a, $b) {
                return ($b->points <=> $a->points)
                    ?: ($b->games_differential <=> $a->games_differential)
                    ?: ($a->games_lost <=> $b->games_lost);
            })
            ->values();

        $totalCount = $standings->count();
        $totalPages = $totalCount > 0 ? (int)ceil($totalCount / $perPage) : 1;
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $rankings = $standings->slice($offset, $perPage)->values()->map(function ($standing, $index) use ($offset) {
            $athlete = $standing->athlete;
            $matchesPlayed = $standing->matches_played ?? 0;
            $matchesWon = $standing->matches_won ?? 0;

            return [
                'rank' => $offset + $index + 1,
                'athlete_id' => $standing->athlete_id,
                'athlete_name' => $athlete?->athlete_name ?? 'N/A',
                'category_id' => $athlete?->category_id,
                'category_name' => $athlete?->category?->category_name ?? 'N/A',
                'matches_played' => $matchesPlayed,
                'matches_won' => $matchesWon,
                'matches_lost' => $standing->matches_lost ?? 0,
                'matches_drawn' => $standing->matches_drawn ?? 0,
                'points' => $standing->points ?? 0,
                'win_rate' => $matchesPlayed > 0 ? round(($matchesWon / $matchesPlayed) * 100, 2) : 0,
                'sets_won' => $standing->sets_won ?? 0,
                'sets_lost' => $standing->sets_lost ?? 0,
                'sets_differential' => $standing->sets_differential ?? 0,
                'games_won' => $standing->games_won ?? 0,
                'games_lost' => $standing->games_lost ?? 0,
                'games_differential' => $standing->games_differential ?? 0,
                'is_advanced' => $standing->is_advanced ?? false,
            ];
        });

        return [
            'rankings' => $rankings->all(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'total_pages' => $totalPages,
            ],
        ];
    }

    public function getAllTournamentsRankings(array $tournamentIds, ?string $categoryType, int $page, int $perPage): array
    {
        $query = GroupStanding::select([
            'id', 'athlete_id', 'group_id', 'points', 'matches_played',
            'matches_won', 'matches_lost', 'matches_drawn', 'win_rate',
            'sets_won', 'sets_lost', 'sets_differential',
            'games_won', 'games_lost', 'games_differential', 'is_advanced',
        ])->with([
            'athlete' => fn($q) => $q->select('id', 'athlete_name', 'email', 'phone', 'category_id'),
            'athlete.category' => fn($q) => $q->select('id', 'category_type', 'category_name'),
        ])->whereHas('group', fn($q) => $q->whereIn('tournament_id', $tournamentIds));

        if ($categoryType) {
            $query->whereHas('athlete.category', fn($q) => $q->where('category_type', $categoryType));
        }

        $standings = $query->get();

        $athleteStats = $standings->groupBy('athlete_id')->map(function ($rows, $athleteId) {
            $athlete = $rows->first()->athlete;
            $played = $rows->sum('matches_played');
            $won = $rows->sum('matches_won');

            return [
                'athlete_id' => $athleteId,
                'athlete' => $athlete ? [
                    'id' => $athlete->id,
                    'athlete_name' => $athlete->athlete_name,
                    'email' => $athlete->email,
                    'phone' => $athlete->phone,
                    'category_id' => $athlete->category_id,
                    'category_name' => $athlete->category?->category_name ?? 'N/A',
                ] : null,
                'points' => $rows->sum('points'),
                'matches_played' => $played,
                'matches_won' => $won,
                'matches_lost' => $rows->sum('matches_lost'),
                'games_differential' => $rows->sum('games_differential'),
                'games_lost' => $rows->sum('games_lost'),
                'win_rate' => $played > 0 ? round(($won / $played) * 100, 2) : 0,
                'tournaments_count' => $rows->count(),
                'rank_change' => 0,
            ];
        });

        $sorted = $athleteStats->sortBy([
            ['points', 'desc'],
            ['games_differential', 'desc'],
            ['games_lost', 'asc'],
        ])->values();
        $total = $sorted->count();
        $offset = ($page - 1) * $perPage;

        return [
            'standings' => $sorted->slice($offset, $perPage)->values()->all(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $total > 0 ? (int)ceil($total / $perPage) : 1,
        ];
    }
}
