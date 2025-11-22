<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tournament_athletes', function (Blueprint $table) {
            // Drop the old unique constraint on (tournament_id, athlete_name)
            $table->dropUnique('tournament_athletes_tournament_id_athlete_name_unique');
            
            // Add new unique constraint on (tournament_id, athlete_name, category_id)
            // Using a shorter constraint name to avoid identifier length issues
            $table->unique(['tournament_id', 'athlete_name', 'category_id'], 'ta_tid_atname_catid_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_athletes', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('ta_tid_atname_catid_unique');
            
            // Restore the old unique constraint
            $table->unique(['tournament_id', 'athlete_name'], 'tournament_athletes_tournament_id_athlete_name_unique');
        });
    }
};
