<?php

namespace App\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventCheckedIn
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Event $event
    ) {}
}
