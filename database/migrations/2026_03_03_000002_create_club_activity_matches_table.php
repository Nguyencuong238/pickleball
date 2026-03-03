<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_activity_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('club_activity_match_rounds')->cascadeOnDelete();
            $table->unsignedTinyInteger('court_number')->nullable();
            $table->enum('match_type', ['singles', 'doubles']);
            $table->unsignedBigInteger('player1_id');
            $table->unsignedBigInteger('player2_id')->nullable();
            $table->unsignedBigInteger('player3_id');
            $table->unsignedBigInteger('player4_id')->nullable();
            $table->unsignedTinyInteger('team1_score')->nullable();
            $table->unsignedTinyInteger('team2_score')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('scheduled');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('player1_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('player2_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('player3_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('player4_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_activity_matches');
    }
};
