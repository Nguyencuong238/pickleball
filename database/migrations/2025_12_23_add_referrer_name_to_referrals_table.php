<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('referrer_id');
                $table->unsignedBigInteger('referred_user_id');
                $table->string('status')->default('pending'); // pending, completed
                $table->timestamp('referred_at');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('referred_user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['referrer_id', 'referred_user_id']);
            });
        }
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
