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
        Schema::create('instructor_teaching_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id')->comment('Reference to instructors table');
            $table->string('title', 100)->comment('Method title');
            $table->text('description')->comment('Method description');
            $table->string('icon', 50)->nullable()->comment('Icon name for display');
            $table->unsignedTinyInteger('sort_order')->default(0)->comment('Display order');
            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
            $table->index('instructor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_teaching_methods');
    }
};
