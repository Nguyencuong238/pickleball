<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create challenge_results table for OPRS challenge score tracking
     */
    public function up(): void
    {
        Schema::create('challenge_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('challenge_type', [
                'dinking_rally',
                'drop_shot',
                'serve_accuracy',
                'monthly_test'
            ]);
            $table->unsignedSmallInteger('score');
            $table->boolean('passed')->default(false);
            $table->decimal('points_earned', 10, 2)->default(0);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('challenge_type');
            $table->index(['user_id', 'challenge_type']);
            $table->index(['user_id', 'passed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_results');
    }
};
