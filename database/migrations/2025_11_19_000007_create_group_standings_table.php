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
        Schema::create('group_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('athlete_id')->constrained('tournament_athletes')->onDelete('cascade');
            $table->integer('rank_position')->default(0);
            $table->integer('matches_played')->default(0);
            $table->integer('matches_won')->default(0);
            $table->integer('matches_lost')->default(0);
            $table->integer('matches_drawn')->default(0); // For round-robin draws
            $table->decimal('win_rate', 5, 2)->default(0); // Percentage
            $table->integer('points')->default(0); // 3 for win, 1 for draw, 0 for loss (or custom)
            $table->integer('sets_won')->default(0);
            $table->integer('sets_lost')->default(0);
            $table->integer('sets_differential')->default(0); // sets_won - sets_lost
            $table->integer('games_won')->default(0); // Total games/points won
            $table->integer('games_lost')->default(0); // Total games/points lost
            $table->integer('games_differential')->default(0); // games_won - games_lost
            $table->boolean('is_advanced')->default(false); // Advanced to next round
            $table->timestamps();

            // Indexes
            $table->index('group_id');
            $table->index('athlete_id');
            $table->index(['group_id', 'rank_position']);
            $table->index('is_advanced');
            $table->unique(['group_id', 'athlete_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_standings');
    }
};
