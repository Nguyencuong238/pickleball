<?php

namespace App\Observers;

use App\Models\Instructor;
use Illuminate\Support\Str;

class InstructorObserver
{
    /**
     * Handle the Instructor "created" event.
     */
    public function created(Instructor $instructor): void
    {
        $this->generateSlug($instructor);
    }

    /**
     * Handle the Instructor "updated" event.
     */
    public function updated(Instructor $instructor): void
    {
        if ($instructor->isDirty('name') && empty($instructor->slug)) {
            $this->generateSlug($instructor);
        }
    }

    /**
     * Generate slug for instructor
     */
    private function generateSlug(Instructor $instructor): void
    {
        if (empty($instructor->slug)) {
            $slug = Str::slug($instructor->name);
            
            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (Instructor::where('slug', $slug)->where('id', '!=', $instructor->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $instructor->slug = $slug;
            $instructor->saveQuietly();
        }
    }

    /**
     * Handle the Instructor "deleted" event.
     */
    public function deleted(Instructor $instructor): void
    {
        //
    }

    /**
     * Handle the Instructor "restored" event.
     */
    public function restored(Instructor $instructor): void
    {
        //
    }

    /**
     * Handle the Instructor "force deleted" event.
     */
    public function forceDeleted(Instructor $instructor): void
    {
        //
    }
}
