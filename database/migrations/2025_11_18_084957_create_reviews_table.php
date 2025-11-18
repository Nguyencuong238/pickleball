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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('stadium_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->unsigned()->between(1, 5); // 1-5 stars
            $table->text('comment')->nullable();
            $table->integer('helpful_count')->default(0); // Số người bảo đánh giá này hữu ích
            $table->boolean('is_verified')->default(false); // Xác minh người đã đặt sân
            $table->timestamps();
            
            // Index for better query performance
            $table->index(['stadium_id', 'created_at']);
            $table->index('user_id');
            
            // Prevent duplicate reviews from same user
            $table->unique(['user_id', 'stadium_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
