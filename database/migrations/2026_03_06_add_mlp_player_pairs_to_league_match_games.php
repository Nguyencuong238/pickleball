<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('league_match_games', function (Blueprint $table) {
            $table->foreignId('home_player_1_id')->nullable()->after('status')->constrained('league_team_players')->nullOnDelete();
            $table->foreignId('home_player_2_id')->nullable()->after('home_player_1_id')->constrained('league_team_players')->nullOnDelete();
            $table->foreignId('away_player_1_id')->nullable()->after('home_player_2_id')->constrained('league_team_players')->nullOnDelete();
            $table->foreignId('away_player_2_id')->nullable()->after('away_player_1_id')->constrained('league_team_players')->nullOnDelete();
        });

        Schema::table('league_team_players', function (Blueprint $table) {
            $table->unsignedTinyInteger('order')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('league_match_games', function (Blueprint $table) {
            $table->dropForeign(['home_player_1_id']);
            $table->dropForeign(['home_player_2_id']);
            $table->dropForeign(['away_player_1_id']);
            $table->dropForeign(['away_player_2_id']);
            $table->dropColumn(['home_player_1_id', 'home_player_2_id', 'away_player_1_id', 'away_player_2_id']);
        });

        Schema::table('league_team_players', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
