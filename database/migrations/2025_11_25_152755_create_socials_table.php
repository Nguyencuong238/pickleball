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
        Schema::create('socials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('object');
            $table->unsignedBigInteger('stadium_id');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('days_of_week')->nullable()->after('end_time');
            $table->integer('fee')->nullable()->default(0);
            $table->integer('max_participants')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('stadium_id')->references('id')->on('stadiums')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socials');
    }
};
