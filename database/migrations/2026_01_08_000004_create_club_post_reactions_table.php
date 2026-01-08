<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('club_post_reactions')) {
            return;
        }

        Schema::create('club_post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_post_id')->constrained('club_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['like', 'love', 'fire']);
            $table->timestamps();

            $table->unique(['club_post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_post_reactions');
    }
};
