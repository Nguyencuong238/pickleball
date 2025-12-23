<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateReferralCodes extends Command
{
    protected $signature = 'referral:generate-codes';
    protected $description = 'Generate referral codes for users without one';

    public function handle()
    {
        $users = User::whereNull('referral_code')->get();
        $count = 0;

        foreach ($users as $user) {
            $referralCode = $this->generateUniqueReferralCode();
            $user->update(['referral_code' => $referralCode]);
            $count++;
        }

        $this->info("Generated referral codes for {$count} users.");
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());
        
        return $code;
    }
}
