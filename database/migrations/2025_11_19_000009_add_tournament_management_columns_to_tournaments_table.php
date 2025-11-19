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
            // Tournament code/identifier
            $table->string('tournament_code')->unique()->nullable()->after('name');

            // Tournament format configuration
            $table->enum('format_type', [
                'knockout',
                'single_elimination',
                'double_elimination',
                'round_robin',
                'group_knockout',
                'swiss',
                'custom'
            ])->default('knockout')->after('competition_format');

            // Tournament settings
            $table->boolean('seeding_enabled')->default(true)->after('format_type');
            $table->boolean('auto_bracket_generation')->default(false)->after('seeding_enabled');
            $table->boolean('balanced_groups')->default(false)->after('auto_bracket_generation');
            $table->integer('group_count')->nullable()->after('balanced_groups');
            $table->integer('players_per_group')->default(4)->after('group_count');

            // Bracket data
            $table->json('bracket_data')->nullable()->after('players_per_group');

            // Tournament progress tracking
            $table->enum('tournament_stage', [
                'registration',
                'draw_completed',
                'in_progress',
                'finals',
                'completed',
                'cancelled'
            ])->default('registration')->after('status');

            // Additional metadata
            $table->integer('total_matches')->default(0)->after('tournament_stage');
            $table->integer('completed_matches')->default(0)->after('total_matches');

            // Indexes
            $table->index('tournament_code');
            $table->index('format_type');
            $table->index('tournament_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn([
                'tournament_code',
                'format_type',
                'seeding_enabled',
                'auto_bracket_generation',
                'balanced_groups',
                'group_count',
                'players_per_group',
                'bracket_data',
                'tournament_stage',
                'total_matches',
                'completed_matches'
            ]);
        });
    }
};
