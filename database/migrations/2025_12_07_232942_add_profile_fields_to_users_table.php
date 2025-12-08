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
            $table->string('avatar')->nullable()->after('phone');
            $table->string('location')->nullable()->after('avatar');
            $table->unsignedBigInteger('province_id')->nullable()->after('location');
            $table->foreign('province_id')->references('id')->on('provinces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropColumn(['avatar', 'location', 'province_id']);
        });
    }
};
