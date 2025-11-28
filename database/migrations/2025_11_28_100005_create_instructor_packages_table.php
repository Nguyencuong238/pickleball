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
        Schema::create('instructor_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id')->comment('Reference to instructors table');
            $table->string('name', 100)->comment('Package name');
            $table->string('description', 255)->nullable()->comment('Package description');
            $table->decimal('price', 12, 0)->comment('Price in VND');
            $table->unsignedTinyInteger('sessions_count')->default(1)->comment('Number of sessions in package');
            $table->unsignedTinyInteger('discount_percent')->default(0)->comment('Discount percentage');
            $table->boolean('is_group')->default(false)->comment('Whether this is a group lesson package');
            $table->unsignedTinyInteger('max_group_size')->nullable()->comment('Max students if group lesson');
            $table->boolean('is_popular')->default(false)->comment('Popular badge flag');
            $table->boolean('is_active')->default(true)->comment('Whether package is active');
            $table->unsignedTinyInteger('sort_order')->default(0)->comment('Display order');
            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
            $table->index('instructor_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_packages');
    }
};
