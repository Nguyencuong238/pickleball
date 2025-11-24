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
        Schema::create('court_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->onDelete('cascade');
            
            // Time period
            $table->time('start_time')->comment('Start time of pricing period (HH:MM)');
            $table->time('end_time')->comment('End time of pricing period (HH:MM)');
            
            // Pricing
            $table->integer('price_per_hour')->comment('Price per hour in VND');
            
            // Days of week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
            $table->json('days_of_week')->nullable()->comment('Days this pricing applies to: [0,1,2,3,4,5,6] for all days, or specific days');
            
            // Status and metadata
            $table->boolean('is_active')->default(true)->comment('Whether this pricing is currently active');
            $table->date('valid_from')->nullable()->comment('Date when this pricing becomes valid');
            $table->date('valid_to')->nullable()->comment('Date when this pricing expires');
            
            $table->text('description')->nullable()->comment('e.g., "Peak hours", "Off-peak pricing", "Weekend rates"');
            $table->timestamps();

            // Indexes
            $table->index('court_id');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('is_active');
            $table->index(['court_id', 'is_active']);
            
            // Unique constraint to prevent overlapping time slots for same court
            $table->unique(['court_id', 'start_time', 'end_time'], 'cp_court_time_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_pricing');
    }
};
