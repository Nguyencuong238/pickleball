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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('duration_hours', 3, 1)->comment('Duration in hours');
            $table->integer('hourly_rate')->comment('Price per hour in VND');
            $table->integer('total_price')->comment('Total booking price in VND');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'wallet'])->default('cash');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('court_id');
            $table->index('user_id');
            $table->index('booking_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
