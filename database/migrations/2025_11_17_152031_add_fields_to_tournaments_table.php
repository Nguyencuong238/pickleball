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
        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('competition_format')->nullable()->after('prizes'); // Single/Double/Mixed
            $table->string('tournament_rank')->nullable()->after('competition_format'); // Beginner/Intermediate/Advanced/Professional
            $table->text('registration_benefits')->nullable()->after('tournament_rank'); // Benefits for registering
            $table->text('competition_rules')->nullable()->after('registration_benefits'); // Detailed rules
            $table->text('event_timeline')->nullable()->after('competition_rules'); // Schedule of events
            $table->text('social_information')->nullable()->after('event_timeline'); // Social media links, networking info
            $table->string('organizer_email')->nullable()->after('social_information');
            $table->string('organizer_hotline')->nullable()->after('organizer_email');
            $table->text('competition_schedule')->nullable()->after('organizer_hotline'); // Detailed schedule
            $table->longText('results')->nullable()->after('competition_schedule'); // Tournament results
            $table->json('gallery')->nullable()->after('results'); // JSON array of gallery images
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn([
                'competition_format',
                'tournament_rank',
                'registration_benefits',
                'competition_rules',
                'event_timeline',
                'social_information',
                'organizer_email',
                'organizer_hotline',
                'competition_schedule',
                'results',
                'gallery',
            ]);
        });
    }
};
