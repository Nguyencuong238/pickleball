<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('league_teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('league_teams')->cascadeOnDelete();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->unsignedInteger('home_score')->default(0);
            $table->unsignedInteger('away_score')->default(0);
            $table->foreignId('winner_team_id')->nullable()->constrained('league_teams')->nullOnDelete();
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('league_round_id');
            $table->index('home_team_id');
            $table->index('away_team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_matches');
    }
};
