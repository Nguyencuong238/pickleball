<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Video;
use App\Models\User;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate slugs for videos
        Video::whereNull('slug')->orWhere('slug', '')->each(function ($video) {
            $slug = Str::slug($video->name);
            $originalSlug = $slug;
            $count = 1;

            // Ensure unique slug
            while (Video::where('slug', $slug)->where('id', '!=', $video->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $video->update(['slug' => $slug]);
        });

        // Populate slugs for users (profile slugs)
        User::whereNull('slug')->orWhere('slug', '')->each(function ($user) {
            $slug = Str::slug($user->name);
            $originalSlug = $slug;
            $count = 1;

            // Ensure unique slug
            while (User::where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $user->update(['slug' => $slug]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all slugs to null on rollback
        Video::update(['slug' => null]);
        User::update(['slug' => null]);
    }
};
