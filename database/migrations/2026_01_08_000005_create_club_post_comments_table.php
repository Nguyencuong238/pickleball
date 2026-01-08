<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('club_post_comments')) {
            return;
        }

        Schema::create('club_post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_post_id')->constrained('club_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('club_post_comments')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['club_post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_post_comments');
    }
};
