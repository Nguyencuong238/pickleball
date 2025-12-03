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
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('badge_type'); // e.g., 'first_win', 'streak_5', 'rank_gold'
            $table->timestamp('earned_at');
            $table->json('metadata')->nullable(); // Extra data like streak count
            $table->timestamps();

            $table->index('user_id');
            $table->unique(['user_id', 'badge_type']); // One badge per type per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
