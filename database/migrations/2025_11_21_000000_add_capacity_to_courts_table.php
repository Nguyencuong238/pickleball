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
        Schema::table('courts', function (Blueprint $table) {
            $table->integer('capacity')->nullable()->after('surface_type')->comment('Court capacity in number of people');
            $table->string('size')->nullable()->after('capacity')->comment('Court size in square meters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courts', function (Blueprint $table) {
            $table->dropColumn('capacity');
            $table->dropColumn('size');
        });
    }
};
