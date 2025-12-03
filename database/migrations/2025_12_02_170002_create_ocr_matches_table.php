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
        Schema::create('ocr_matches', function (Blueprint $table) {
            $table->id();

            // Match type
            $table->enum('match_type', ['singles', 'doubles'])->default('singles');

            // Challenger team
            $table->foreignId('challenger_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('challenger_partner_id')->nullable()->constrained('users')->nullOnDelete();

            // Opponent team
            $table->foreignId('opponent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('opponent_partner_id')->nullable()->constrained('users')->nullOnDelete();

            // Scores
            $table->unsignedTinyInteger('challenger_score')->default(0);
            $table->unsignedTinyInteger('opponent_score')->default(0);
            $table->enum('winner_team', ['challenger', 'opponent'])->nullable();

            // Status workflow
            $table->enum('status', [
                'pending',          // Waiting for opponent to accept
                'accepted',         // Opponent accepted, match scheduled
                'in_progress',      // Match started
                'result_submitted', // One party submitted result
                'confirmed',        // Both parties confirmed result
                'disputed',         // Result disputed
                'cancelled'         // Match cancelled
            ])->default('pending');

            // Schedule
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();

            // Result tracking
            $table->foreignId('result_submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('result_submitted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('disputed_reason')->nullable();

            // Elo tracking
            $table->integer('elo_challenger_before')->nullable();
            $table->integer('elo_opponent_before')->nullable();
            $table->integer('elo_challenger_after')->nullable();
            $table->integer('elo_opponent_after')->nullable();
            $table->integer('elo_change')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('challenger_id');
            $table->index('opponent_id');
            $table->index('status');
            $table->index('scheduled_date');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocr_matches');
    }
};
