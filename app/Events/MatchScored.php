<?php

namespace App\Events;

use App\Models\MatchModel;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchScored
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public MatchModel $match,
        public User $referee
    ) {}
}
