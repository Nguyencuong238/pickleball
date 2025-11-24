<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing matches with athlete names from tournament_athletes
        // Prioritize athlete_name field, fallback to user.name
        DB::statement('
            UPDATE matches m
            SET m.athlete1_name = COALESCE(
                (SELECT ta.athlete_name FROM tournament_athletes ta WHERE ta.id = m.athlete1_id LIMIT 1),
                (SELECT u.name FROM tournament_athletes ta JOIN users u ON ta.user_id = u.id WHERE ta.id = m.athlete1_id LIMIT 1),
                "Unknown"
            )
            WHERE m.athlete1_name IS NULL AND m.athlete1_id IS NOT NULL
        ');

        DB::statement('
            UPDATE matches m
            SET m.athlete2_name = COALESCE(
                (SELECT ta.athlete_name FROM tournament_athletes ta WHERE ta.id = m.athlete2_id LIMIT 1),
                (SELECT u.name FROM tournament_athletes ta JOIN users u ON ta.user_id = u.id WHERE ta.id = m.athlete2_id LIMIT 1),
                "Unknown"
            )
            WHERE m.athlete2_name IS NULL AND m.athlete2_id IS NOT NULL
        ');

        // Generate match numbers for matches that don't have them
        $matches = DB::table('matches')
            ->whereNull('match_number')
            ->orderBy('tournament_id')
            ->orderBy('category_id')
            ->orderBy('created_at')
            ->get();

        foreach ($matches as $match) {
            $matchCount = DB::table('matches')
                ->where('tournament_id', $match->tournament_id)
                ->where('category_id', $match->category_id)
                ->where('id', '<=', $match->id)
                ->count();

            DB::table('matches')
                ->where('id', $match->id)
                ->update(['match_number' => 'M' . $matchCount]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is data-only, no rollback needed
    }
};
