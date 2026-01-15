<?php

namespace App\Events;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TournamentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Tournament $tournament,
        public User $creator
    ) {}
}
