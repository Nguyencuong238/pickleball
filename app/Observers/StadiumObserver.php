<?php

namespace App\Observers;

use App\Models\Stadium;
use Illuminate\Support\Str;

class StadiumObserver
{
    /**
     * Handle the Stadium "created" event.
     */
    public function created(Stadium $stadium): void
    {
        $this->generateSlug($stadium);
    }

    /**
     * Handle the Stadium "updated" event.
     */
    public function updated(Stadium $stadium): void
    {
        if ($stadium->isDirty('name') && empty($stadium->slug)) {
            $this->generateSlug($stadium);
        }
    }

    /**
     * Generate slug for stadium
     */
    private function generateSlug(Stadium $stadium): void
    {
        if (empty($stadium->slug)) {
            $slug = Str::slug($stadium->name);
            
            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (Stadium::where('slug', $slug)->where('id', '!=', $stadium->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $stadium->slug = $slug;
            $stadium->saveQuietly();
        }
    }

    /**
     * Handle the Stadium "deleted" event.
     */
    public function deleted(Stadium $stadium): void
    {
        //
    }

    /**
     * Handle the Stadium "restored" event.
     */
    public function restored(Stadium $stadium): void
    {
        //
    }

    /**
     * Handle the Stadium "force deleted" event.
     */
    public function forceDeleted(Stadium $stadium): void
    {
        //
    }
}
