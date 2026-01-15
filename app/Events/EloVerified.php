<?php

namespace App\Events;

use App\Models\OprVerificationRequest;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EloVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public OprVerificationRequest $request,
        public User $verifier,
        public User $verifiedUser
    ) {}
}
