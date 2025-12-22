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
        Schema::table('stadiums', function (Blueprint $table) {
            $table->string('maps_link')->nullable()->comment('Google Maps link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stadiums', function (Blueprint $table) {
            $table->dropColumn('maps_link');
        });
    }
};
