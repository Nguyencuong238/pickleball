<?php

namespace App\Events;

use App\Models\Stadium;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StadiumUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Stadium $stadium,
        public User $owner
    ) {}
}
