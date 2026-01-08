<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('club_post_media')) {
            return;
        }

        Schema::create('club_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_post_id')->constrained('club_posts')->cascadeOnDelete();
            $table->enum('type', ['image', 'video', 'youtube']);
            $table->string('path')->nullable(); // for uploads
            $table->string('disk')->default('public'); // local, s3
            $table->string('youtube_url')->nullable();
            $table->unsignedInteger('size')->nullable(); // bytes
            $table->tinyInteger('order')->default(0);
            $table->timestamps();

            $table->index(['club_post_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_post_media');
    }
};
