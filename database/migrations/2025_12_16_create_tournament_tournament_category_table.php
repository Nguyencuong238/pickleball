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
        Schema::create('tournament_tournament_category', function (Blueprint $table) {
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('tournament_category_id')->constrained('tournament_categories')->onDelete('cascade');
            $table->timestamps();

            // Composite primary key
            $table->primary(['tournament_id', 'tournament_category_id']);

            // Indexes
            $table->index('tournament_id');
            $table->index('tournament_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_tournament_category');
    }
};
