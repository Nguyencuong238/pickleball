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
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->onDelete('cascade');
            $table->enum('event_type', [
                'score',
                'side_out',
                'timeout',
                'fault',
                'game_end',
                'match_start',
                'match_end',
                'undo',
                'server_change',
                'rally_won',
                'rally_lost'
            ]);
            $table->enum('team', ['left', 'right'])->nullable();
            $table->json('data')->nullable();
            $table->integer('timer_seconds')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['match_id', 'event_type']);
            $table->index(['match_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_events');
    }
};
