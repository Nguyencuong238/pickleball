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
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stadium_id')->nullable()->constrained('stadiums')->onDelete('cascade');
            $table->foreignId('tournament_id')->nullable()->constrained('tournaments')->onDelete('cascade');
            $table->string('court_name'); // e.g., "Sân số 1", "Court A"
            $table->string('court_number')->nullable();
            $table->enum('court_type', ['indoor', 'outdoor'])->default('indoor');
            $table->string('surface_type')->nullable(); // e.g., "Acrylic", "Concrete", "Synthetic"
            $table->enum('status', ['available', 'in_use', 'maintenance', 'reserved'])->default('available');
            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // lighting, net quality, etc.
            $table->boolean('is_active')->default(true);
            $table->integer('daily_matches')->default(0); // Track match count per day
            $table->timestamps();

            // Indexes
            $table->index('stadium_id');
            $table->index('tournament_id');
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
