<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_activity_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['confirmed', 'waitlisted', 'cancelled'])->default('confirmed');
            $table->unsignedInteger('waitlist_position')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['club_activity_id', 'user_id']);
            $table->index(['club_activity_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_activity_participants');
    }
};
