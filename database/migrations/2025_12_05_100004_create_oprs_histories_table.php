<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create oprs_histories table for OPRS change auditing
     */
    public function up(): void
    {
        Schema::create('oprs_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('elo_score', 10, 2);
            $table->decimal('challenge_score', 10, 2);
            $table->decimal('community_score', 10, 2);
            $table->decimal('total_oprs', 10, 2);
            $table->string('opr_level', 10);
            $table->string('change_reason', 100);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'created_at']);
            $table->index('change_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oprs_histories');
    }
};
