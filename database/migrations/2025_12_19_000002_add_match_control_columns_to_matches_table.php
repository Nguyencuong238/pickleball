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
        Schema::table('matches', function (Blueprint $table) {
            $table->json('match_state')->nullable()->after('notes');
            $table->integer('current_game')->default(1)->after('match_state');
            $table->integer('games_won_athlete1')->default(0)->after('current_game');
            $table->integer('games_won_athlete2')->default(0)->after('games_won_athlete1');
            $table->json('game_scores')->nullable()->after('games_won_athlete2');
            $table->enum('serving_team', ['athlete1', 'athlete2'])->nullable()->after('game_scores');
            $table->integer('server_number')->nullable()->after('serving_team');
            $table->integer('timer_seconds')->default(0)->after('server_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn([
                'match_state',
                'current_game',
                'games_won_athlete1',
                'games_won_athlete2',
                'game_scores',
                'serving_team',
                'server_number',
                'timer_seconds',
            ]);
        });
    }
};
