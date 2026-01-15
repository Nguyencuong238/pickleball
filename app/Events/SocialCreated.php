<?php

namespace App\Events;

use App\Models\Social;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SocialCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Social $social,
        public User $creator
    ) {}
}
