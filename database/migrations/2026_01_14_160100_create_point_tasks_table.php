<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('points');
            $table->string('role', 50);
            $table->enum('category', ['daily', 'social', 'event', 'tournament']);
            $table->enum('frequency', ['unlimited', 'daily', 'weekly', 'monthly', 'once']);
            $table->boolean('requires_approval')->default(false);
            $table->enum('proof_type', ['none', 'image', 'link', 'qr_code'])->default('none');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_tasks');
    }
};
