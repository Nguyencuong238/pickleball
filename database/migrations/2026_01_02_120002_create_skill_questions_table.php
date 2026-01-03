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
        Schema::create('skill_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('skill_domains')->cascadeOnDelete();
            $table->text('question_vi');
            $table->text('question_en')->nullable();
            $table->decimal('anchor_level', 3, 1);
            $table->smallInteger('order_in_domain');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['domain_id', 'order_in_domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_questions');
    }
};
