<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixMissingReferralCodes extends Command
{
    protected $signature = 'referral:fix-missing';
    protected $description = 'Fix missing referral codes for all users';

    public function handle()
    {
        $users = User::whereNull('referral_code')->orWhere('referral_code', '')->get();
        $count = 0;

        foreach ($users as $user) {
            $code = $this->generateUniqueReferralCode();
            $user->update(['referral_code' => $code]);
            $count++;
            $this->info("User {$user->id} ({$user->name}) assigned code: {$code}");
        }

        $this->info("✓ Fixed {$count} users with missing referral codes.");
        
        // Show 5 users with their codes
        $this->line("\nSample users:");
        User::limit(5)->get(['id', 'name', 'referral_code'])->each(function ($user) {
            $this->line("  - {$user->name}: {$user->referral_code}");
        });
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());
        
        return $code;
    }
}
