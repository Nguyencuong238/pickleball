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
        Schema::create('skill_quiz_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attempt_id');
            $table->foreignId('question_id')->constrained('skill_questions')->cascadeOnDelete();
            $table->smallInteger('answer_value'); // 0-3
            $table->timestamp('answered_at');
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('attempt_id')
                ->references('id')
                ->on('skill_quiz_attempts')
                ->cascadeOnDelete();

            $table->index('attempt_id');
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_quiz_answers');
    }
};
