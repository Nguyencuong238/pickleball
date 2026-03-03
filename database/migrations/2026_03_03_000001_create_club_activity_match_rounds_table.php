<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_activity_match_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_activity_id')->constrained('club_activities')->cascadeOnDelete();
            $table->unsignedTinyInteger('round_number');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();

            $table->index('club_activity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_activity_match_rounds');
    }
};
