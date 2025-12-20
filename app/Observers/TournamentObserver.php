<?php

namespace App\Observers;

use App\Models\Tournament;
use Illuminate\Support\Str;

class TournamentObserver
{
    /**
     * Handle the Tournament "created" event.
     */
    public function created(Tournament $tournament): void
    {
        $this->generateSlug($tournament);
    }

    /**
     * Handle the Tournament "updated" event.
     */
    public function updated(Tournament $tournament): void
    {
        if ($tournament->isDirty('name') && empty($tournament->slug)) {
            $this->generateSlug($tournament);
        }
    }

    /**
     * Generate slug for tournament
     */
    private function generateSlug(Tournament $tournament): void
    {
        if (empty($tournament->slug)) {
            $slug = Str::slug($tournament->name);
            
            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (Tournament::where('slug', $slug)->where('id', '!=', $tournament->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $tournament->slug = $slug;
            $tournament->saveQuietly();
        }
    }

    /**
     * Handle the Tournament "deleted" event.
     */
    public function deleted(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "restored" event.
     */
    public function restored(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "force deleted" event.
     */
    public function forceDeleted(Tournament $tournament): void
    {
        //
    }
}
