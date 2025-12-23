<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Check if columns already exist before adding
        if (!Schema::hasColumn('users', 'referral_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('referral_code')->nullable()->after('id');
                $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
            });
        }

        // Generate referral codes for all existing users
        $users = DB::table('users')->whereNull('referral_code')->get();
        foreach ($users as $user) {
            $code = $this->generateUniqueReferralCode();
            DB::table('users')->where('id', $user->id)->update(['referral_code' => $code]);
        }

        // Add unique constraint and foreign key
        if (!Schema::hasColumn('users', 'referral_code')) {
            return; // Already exists from previous run
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('referral_code');
            });
        } catch (\Exception $e) {
            // Constraint already exists
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Foreign key already exists
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropForeign(['referred_by']);
            } catch (\Exception $e) {}
            
            try {
                $table->dropUnique(['referral_code']);
            } catch (\Exception $e) {}
            
            $table->dropColumnIfExists('referral_code');
            $table->dropColumnIfExists('referred_by');
        });
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (DB::table('users')->where('referral_code', $code)->exists());
        
        return $code;
    }
};
