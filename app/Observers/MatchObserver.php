<?php

namespace App\Observers;

use App\Models\MatchModel;
use App\Models\User;

class MatchObserver
{
    /**
     * Handle the MatchModel "saving" event.
     * Sync referee_name cache when referee_id changes
     */
    public function saving(MatchModel $match): void
    {
        if ($match->isDirty('referee_id')) {
            if ($match->referee_id) {
                $referee = User::find($match->referee_id);
                $match->referee_name = $referee?->name;
            } else {
                $match->referee_name = null;
            }
        }
    }
}
