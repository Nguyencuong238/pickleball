<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_match_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_match_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('game_number');
            $table->string('game_type'); // WD, MD, MXD
            $table->unsignedInteger('home_score')->default(0);
            $table->unsignedInteger('away_score')->default(0);
            $table->foreignId('winner_team_id')->nullable()->constrained('league_teams')->nullOnDelete();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();

            $table->index('league_match_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_match_games');
    }
};
