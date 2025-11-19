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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('tournament_categories')->onDelete('cascade');
            $table->foreignId('round_id')->nullable()->constrained('rounds')->onDelete('cascade');
            $table->string('group_name'); // e.g., "Bảng A", "Group A", "Pool 1"
            $table->string('group_code')->nullable(); // e.g., "A", "B", "C"
            $table->integer('max_participants')->default(4);
            $table->integer('current_participants')->default(0);
            $table->integer('advancing_count')->default(2); // How many advance from this group
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('tournament_id');
            $table->index('category_id');
            $table->index('round_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
