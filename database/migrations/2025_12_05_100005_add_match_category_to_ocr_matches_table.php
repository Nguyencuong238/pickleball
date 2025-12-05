<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add match_category field to ocr_matches for OPRS categorization
     */
    public function up(): void
    {
        Schema::table('ocr_matches', function (Blueprint $table) {
            $table->enum('match_category', [
                'official',         // OnePickleball official tournaments
                'partner',          // Partner tournaments
                'ocr',              // OCR challenge matches
                'ranked_challenge'  // Supervised 1v1/2v2
            ])->default('ocr')->after('match_type');

            $table->index('match_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocr_matches', function (Blueprint $table) {
            $table->dropIndex(['match_category']);
            $table->dropColumn('match_category');
        });
    }
};
