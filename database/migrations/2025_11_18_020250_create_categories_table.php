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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Tên danh mục
            $table->string('slug')->unique(); // URL slug
            $table->text('description')->nullable(); // Mô tả
            $table->string('icon')->nullable(); // Icon cho danh mục
            $table->boolean('status')->default(true); // Trạng thái (active/inactive)
            $table->integer('order')->default(0); // Sắp xếp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
