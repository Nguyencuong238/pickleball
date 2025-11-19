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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('tournament_categories')->onDelete('cascade');
            $table->foreignId('round_id')->nullable()->constrained('rounds')->onDelete('set null');
            $table->foreignId('court_id')->nullable()->constrained('courts')->onDelete('set null');
            $table->unsignedBigInteger('group_id')->nullable(); // Foreign key added in separate migration

            $table->string('match_number')->nullable(); // e.g., "M1", "Match 1"
            $table->integer('bracket_position')->nullable(); // Position in bracket tree

            // Player/Team 1
            $table->foreignId('athlete1_id')->nullable()->constrained('tournament_athletes')->onDelete('set null');
            $table->string('athlete1_name')->nullable(); // Cached name for quick display
            $table->integer('athlete1_score')->default(0); // Total sets won

            // Player/Team 2
            $table->foreignId('athlete2_id')->nullable()->constrained('tournament_athletes')->onDelete('set null');
            $table->string('athlete2_name')->nullable(); // Cached name for quick display
            $table->integer('athlete2_score')->default(0); // Total sets won

            // Match Details
            $table->foreignId('winner_id')->nullable()->constrained('tournament_athletes')->onDelete('set null');
            $table->date('match_date');
            $table->time('match_time')->nullable();
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();

            // Match Status
            $table->enum('status', [
                'scheduled',
                'ready',
                'in_progress',
                'completed',
                'cancelled',
                'postponed',
                'bye'
            ])->default('scheduled');

            // Scoring Details
            $table->integer('best_of')->default(3); // Best of 3 or 5 sets
            $table->json('set_scores')->nullable(); // Store all set scores as JSON
            $table->string('final_score')->nullable(); // e.g., "11-7, 11-5" or "2-0"

            // Additional Info
            $table->text('notes')->nullable();

            // Next Match Navigation (for bracket advancement)
            $table->foreignId('next_match_id')->nullable()->constrained('matches')->onDelete('set null');
            $table->enum('winner_advances_to', ['athlete1', 'athlete2'])->nullable();

            $table->timestamps();

            // Indexes
            $table->index('tournament_id');
            $table->index('category_id');
            $table->index('round_id');
            $table->index('court_id');
            $table->index('group_id');
            $table->index('athlete1_id');
            $table->index('athlete2_id');
            $table->index('winner_id');
            $table->index('status');
            $table->index('match_date');
            $table->index(['tournament_id', 'match_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
