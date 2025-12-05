<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create community_activities table for OPRS community score tracking
     */
    public function up(): void
    {
        Schema::create('community_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('activity_type', 50); // check_in, event, referral, weekly_matches, monthly_challenge
            $table->decimal('points_earned', 10, 2);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type', 100)->nullable(); // For polymorphic references
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('activity_type');
            $table->index(['user_id', 'activity_type']);
            $table->index(['user_id', 'created_at']);
            $table->index(['reference_id', 'reference_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_activities');
    }
};
