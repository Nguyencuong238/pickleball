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
        Schema::create('skill_domains', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('name', 100);
            $table->string('name_vi', 100);
            $table->text('description')->nullable();
            $table->decimal('weight', 5, 4); // 0.1000 - 0.2000
            $table->decimal('anchor_min', 3, 1)->default(2.0);
            $table->decimal('anchor_max', 3, 1)->default(6.0);
            $table->smallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_domains');
    }
};
