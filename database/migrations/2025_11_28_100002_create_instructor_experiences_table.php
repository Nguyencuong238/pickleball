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
        Schema::create('instructor_experiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id')->comment('Reference to instructors table');
            $table->string('title')->comment('Position/job title');
            $table->string('organization')->comment('Organization or club name');
            $table->text('description')->nullable()->comment('Description of role and responsibilities');
            $table->year('start_year')->comment('Start year of position');
            $table->year('end_year')->nullable()->comment('End year (null = current position)');
            $table->boolean('is_current')->default(false)->comment('Whether currently in this position');
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
        Schema::dropIfExists('instructor_experiences');
    }
};
