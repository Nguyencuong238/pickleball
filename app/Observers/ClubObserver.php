<?php

namespace App\Observers;

use App\Models\Club;
use Illuminate\Support\Str;

class ClubObserver
{
    /**
     * Handle the Club "created" event.
     */
    public function created(Club $club): void
    {
        $this->generateSlug($club);
    }

    /**
     * Handle the Club "updated" event.
     */
    public function updated(Club $club): void
    {
        if ($club->isDirty('name') && empty($club->slug)) {
            $this->generateSlug($club);
        }
    }

    /**
     * Generate slug for club
     */
    private function generateSlug(Club $club): void
    {
        if (empty($club->slug)) {
            $slug = Str::slug($club->name);
            
            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (Club::where('slug', $slug)->where('id', '!=', $club->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $club->slug = $slug;
            $club->saveQuietly();
        }
    }

    /**
     * Handle the Club "deleted" event.
     */
    public function deleted(Club $club): void
    {
        //
    }

    /**
     * Handle the Club "restored" event.
     */
    public function restored(Club $club): void
    {
        //
    }

    /**
     * Handle the Club "force deleted" event.
     */
    public function forceDeleted(Club $club): void
    {
        //
    }
}
