<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_competition_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
            $table->foreignId('club_competition_team_id')->constrained('club_competition_teams')->cascadeOnDelete();
            $table->unsignedInteger('played')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->unsignedInteger('draws')->default(0);
            $table->integer('points')->default(0);
            $table->unsignedInteger('rank')->default(0);
            $table->timestamps();

            $table->unique(['club_activity_id', 'club_competition_team_id'], 'club_comp_standings_activity_team_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_competition_standings');
    }
};
