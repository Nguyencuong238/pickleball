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
        Schema::create('booking_instructors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id')->comment('Reference to instructors table');
            $table->unsignedBigInteger('package_id')->comment('Reference to instructor_packages table');
            $table->string('customer_name', 100)->comment('Customer name');
            $table->string('customer_phone', 20)->comment('Customer phone number');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending')->comment('Booking status');
            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('instructor_packages')->onDelete('cascade');
            $table->index('instructor_id');
            $table->index('package_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_instructors');
    }
};
