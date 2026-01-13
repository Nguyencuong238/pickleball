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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('athlete_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('athlete_types')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('athlete_types');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('athlete_type', ['athlete_international', 'athlete_vietnam'])->nullable();
        });
    }
};
