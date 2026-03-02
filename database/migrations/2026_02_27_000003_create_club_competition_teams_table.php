<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_competition_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('captain_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'withdrawn'])->default('active');
            $table->timestamps();

            $table->index('club_activity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_competition_teams');
    }
};
