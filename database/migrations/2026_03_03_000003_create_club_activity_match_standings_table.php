<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_activity_match_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('matches_played')->default(0);
            $table->unsignedTinyInteger('wins')->default(0);
            $table->unsignedTinyInteger('losses')->default(0);
            $table->unsignedSmallInteger('points_scored')->default(0);
            $table->unsignedSmallInteger('points_against')->default(0);
            $table->timestamps();

            $table->unique(['club_activity_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_activity_match_standings');
    }
};
