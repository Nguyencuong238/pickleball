<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_registration_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_registration_id')->constrained('league_registrations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 20);
            $table->string('name', 255);
            $table->string('skill_level', 50)->nullable();
            $table->string('province', 100)->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->date('birthday')->nullable();
            $table->string('photo', 255)->nullable();
            $table->text('message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('phone');
            $table->index(['league_registration_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_registration_players');
    }
};
