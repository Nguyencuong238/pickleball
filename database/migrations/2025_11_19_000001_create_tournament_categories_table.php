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
        Schema::create('tournament_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->string('category_name'); // e.g., "Nam đơn 18+"
            $table->enum('category_type', [
                'single_men',
                'single_women',
                'double_men',
                'double_women',
                'double_mixed'
            ])->default('single_men');
            $table->string('age_group')->default('open'); // open, u18, 18+, 35+, 45+, 55+
            $table->integer('max_participants')->default(32);
            $table->decimal('prize_money', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'open', 'closed', 'ongoing', 'completed'])->default('draft');
            $table->integer('current_participants')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('tournament_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_categories');
    }
};
