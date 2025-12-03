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
        Schema::create('elo_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ocr_match_id')->nullable()->constrained('ocr_matches')->nullOnDelete();
            $table->integer('elo_before');
            $table->integer('elo_after');
            $table->integer('change_amount');
            $table->enum('change_reason', ['match_win', 'match_loss', 'admin_adjustment'])->default('match_win');
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elo_histories');
    }
};
