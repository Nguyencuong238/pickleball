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
            $table->longText('maps_address')->nullable()->comment('Google Maps iframe embed code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stadiums', function (Blueprint $table) {
            $table->dropColumn('maps_address');
        });
    }
};
