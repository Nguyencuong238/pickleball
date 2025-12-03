<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('elo_rating')->default(1000)->after('status');
            $table->string('elo_rank')->default('Bronze')->after('elo_rating');
            $table->unsignedInteger('total_ocr_matches')->default(0)->after('elo_rank');
            $table->unsignedInteger('ocr_wins')->default(0)->after('total_ocr_matches');
            $table->unsignedInteger('ocr_losses')->default(0)->after('ocr_wins');

            $table->index('elo_rating');
            $table->index('elo_rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['elo_rating']);
            $table->dropIndex(['elo_rank']);

            $table->dropColumn([
                'elo_rating',
                'elo_rank',
                'total_ocr_matches',
                'ocr_wins',
                'ocr_losses',
            ]);
        });
    }
};
