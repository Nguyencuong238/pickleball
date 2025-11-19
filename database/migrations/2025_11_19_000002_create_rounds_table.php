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
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('tournament_categories')->onDelete('cascade');
            $table->string('round_name'); // e.g., "Vòng bảng", "Vòng 1/8", "Tứ kết", "Bán kết", "Chung kết"
            $table->integer('round_number')->default(1); // 1, 2, 3, etc.
            $table->enum('round_type', [
                'group_stage',
                'round_of_64',
                'round_of_32',
                'round_of_16',
                'quarterfinal',
                'semifinal',
                'final',
                'third_place',
                'custom'
            ])->default('custom');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->integer('total_matches')->default(0);
            $table->integer('completed_matches')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('tournament_id');
            $table->index('category_id');
            $table->index(['tournament_id', 'round_number']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
