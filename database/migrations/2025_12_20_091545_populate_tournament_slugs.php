<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Tournament;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate slug for all tournaments without a slug
        Tournament::whereNull('slug')->orWhere('slug', '')->each(function ($tournament) {
            $slug = Str::slug($tournament->name);
            
            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (Tournament::where('slug', $slug)->where('id', '!=', $tournament->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            $tournament->update(['slug' => $slug]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all slugs to null on rollback
        Tournament::update(['slug' => null]);
    }
};
