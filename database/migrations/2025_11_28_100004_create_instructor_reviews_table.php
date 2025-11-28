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
        Schema::create('instructor_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id')->comment('Reference to instructors table');
            $table->unsignedBigInteger('user_id')->comment('Reference to users table');
            $table->unsignedTinyInteger('rating')->comment('Rating from 1 to 5');
            $table->text('content')->comment('Review content/text');
            $table->json('tags')->nullable()->comment('Review tags e.g. ["Patient", "Friendly"]');
            $table->boolean('is_approved')->default(true)->comment('Moderation approval status');
            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('instructor_id');
            $table->index('user_id');
            $table->index('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_reviews');
    }
};
