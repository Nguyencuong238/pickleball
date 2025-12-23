<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // Add denormalized referrer info for easy tracking
            $table->string('referrer_name')->after('referrer_id'); // Store referrer name at time of referral
        });

        // Populate referrer_name with current names
        \DB::table('referrals')
            ->join('users', 'referrals.referrer_id', '=', 'users.id')
            ->update(['referrer_name' => \DB::raw('users.name')]);
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn('referrer_name');
        });
    }
};
