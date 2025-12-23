<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        // Generate referral code if not already set
        if (!$user->referral_code) {
            $user->referral_code = $this->generateUniqueReferralCode();
        }
    }

    /**
     * Generate a unique referral code
     */
    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());
        
        return $code;
    }
}
