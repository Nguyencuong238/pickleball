<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get all users without referral code
        $users = DB::table('users')->whereNull('referral_code')->get();
        
        foreach ($users as $user) {
            $code = $this->generateUniqueReferralCode();
            DB::table('users')->where('id', $user->id)->update(['referral_code' => $code]);
        }

        // If still some nulls exist, generate for ALL
        $nullCount = DB::table('users')->whereNull('referral_code')->count();
        if ($nullCount > 0) {
            DB::table('users')->whereNull('referral_code')->each(function ($user) {
                $code = $this->generateUniqueReferralCode();
                DB::table('users')->where('id', $user->id)->update(['referral_code' => $code]);
            }, 100);
        }
    }

    public function down(): void
    {
        // This migration only populates, don't revert
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (DB::table('users')->where('referral_code', $code)->exists());
        
        return $code;
    }
};
