<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_competition_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->foreignId('home_team_id')->constrained('club_competition_teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->nullable()->constrained('club_competition_teams')->nullOnDelete();
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('scheduled');
            $table->unsignedSmallInteger('home_score')->nullable();
            $table->unsignedSmallInteger('away_score')->nullable();
            $table->foreignId('winner_team_id')->nullable()->constrained('club_competition_teams')->nullOnDelete();
            $table->string('pool_label')->nullable();
            $table->string('bracket_position')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['club_activity_id', 'round_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_competition_matches');
    }
};
