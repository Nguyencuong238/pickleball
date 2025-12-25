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
        // Populate remaining Instructor slugs
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

        // Populate remaining Club slugs
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
        //
    }
};
