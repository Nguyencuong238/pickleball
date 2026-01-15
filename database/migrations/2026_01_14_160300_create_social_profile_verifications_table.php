<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_profile_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('platform', ['facebook', 'youtube', 'tiktok']);
            $table->string('profile_url', 500);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['user_id', 'platform']);
            $table->unique('profile_url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_profile_verifications');
    }
};
