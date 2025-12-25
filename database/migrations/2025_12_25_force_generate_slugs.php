<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use App\Models\Instructor;
use App\Models\Club;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Force generate Instructor slugs
        Instructor::all()->each(function ($instructor) {
            if (empty($instructor->slug)) {
                $slug = Str::slug($instructor->name);
                $originalSlug = $slug;
                $count = 1;
                
                while (Instructor::where('slug', $slug)->where('id', '!=', $instructor->id)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                
                $instructor->update(['slug' => $slug]);
            }
        });

        // Force generate Club slugs
        Club::all()->each(function ($club) {
            if (empty($club->slug)) {
                $slug = Str::slug($club->name);
                $originalSlug = $slug;
                $count = 1;
                
                while (Club::where('slug', $slug)->where('id', '!=', $club->id)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                
                $club->update(['slug' => $slug]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
