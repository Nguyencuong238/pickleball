<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_code', 14)->nullable()->unique()->after('id');
            $table->index(['court_id', 'booking_date', 'booking_code'], 'bookings_court_date_code_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_court_date_code_idx');
            $table->dropColumn('booking_code');
        });
    }
};
