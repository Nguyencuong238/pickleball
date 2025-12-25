<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use App\Models\Stadium;
use App\Models\Tournament;
use App\Models\Instructor;
use App\Models\Club;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate Stadium slugs
        Stadium::whereNull('slug')->each(function ($stadium) {
            $slug = Str::slug($stadium->name);
            $originalSlug = $slug;
            $count = 1;
            
            while (Stadium::where('slug', $slug)->where('id', '!=', $stadium->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $stadium->update(['slug' => $slug]);
        });

        // Populate Tournament slugs
        Tournament::whereNull('slug')->each(function ($tournament) {
            $slug = Str::slug($tournament->name);
            $originalSlug = $slug;
            $count = 1;
            
            while (Tournament::where('slug', $slug)->where('id', '!=', $tournament->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $tournament->update(['slug' => $slug]);
        });

        // Populate Instructor slugs
        Instructor::whereNull('slug')->each(function ($instructor) {
            $slug = Str::slug($instructor->name);
            $originalSlug = $slug;
            $count = 1;
            
            while (Instructor::where('slug', $slug)->where('id', '!=', $instructor->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $instructor->update(['slug' => $slug]);
        });

        // Populate Club slugs
        Club::whereNull('slug')->each(function ($club) {
            $slug = Str::slug($club->name);
            $originalSlug = $slug;
            $count = 1;
            
            while (Club::where('slug', $slug)->where('id', '!=', $club->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $club->update(['slug' => $slug]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data migration, we'll keep the slugs on rollback
    }
};
