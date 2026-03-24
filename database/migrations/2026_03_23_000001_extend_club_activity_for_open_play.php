<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'open_play' to type enum
        DB::statement("ALTER TABLE club_activities MODIFY COLUMN type ENUM('one_off', 'recurring', 'competition', 'open_play') DEFAULT 'one_off'");

        Schema::table('club_activities', function (Blueprint $table) {
            $table->string('qr_code')->nullable()->unique()->after('type');
            $table->unsignedInteger('courts_count')->default(1)->after('qr_code');
            $table->unsignedInteger('avg_match_duration')->nullable()->after('courts_count');
            $table->enum('rotation_mode', ['round_robin', 'oprs_based', 'random'])->default('oprs_based')->after('avg_match_duration');
            $table->boolean('gender_preference_enabled')->default(false)->after('rotation_mode');
            $table->decimal('oprs_weight', 3, 2)->default(0.50)->after('gender_preference_enabled');
            $table->boolean('allow_guests')->default(false)->after('oprs_weight');
            $table->dateTime('started_at')->nullable()->after('end_time');
            $table->dateTime('ended_at')->nullable()->after('started_at');
        });

        Schema::table('club_activity_participants', function (Blueprint $table) {
            $table->dateTime('checked_in_at')->nullable()->after('status');
            $table->string('gender_preference')->nullable()->after('checked_in_at');
            $table->enum('current_status', ['idle', 'queued', 'playing', 'left'])->default('idle')->after('gender_preference');
            $table->unsignedInteger('queue_position')->nullable()->after('current_status');
            $table->unsignedInteger('matches_played_count')->default(0)->after('queue_position');
            $table->dateTime('last_match_ended_at')->nullable()->after('matches_played_count');
        });

        Schema::table('club_activity_matches', function (Blueprint $table) {
            $table->unsignedBigInteger('club_activity_id')->nullable()->after('id');
            $table->unsignedInteger('match_number')->default(0)->after('match_type');
            $table->unsignedInteger('scheduled_court')->nullable()->after('match_number');
            $table->dateTime('started_at')->nullable()->after('scheduled_court');
            $table->dateTime('ended_at')->nullable()->after('started_at');
            $table->unsignedBigInteger('result_submitted_by')->nullable()->after('ended_at');
            $table->boolean('result_confirmed')->default(false)->after('result_submitted_by');
            $table->boolean('oprs_processed')->default(false)->after('result_confirmed');
            $table->json('set_scores')->nullable()->after('oprs_processed');

            // Make round_id nullable for open_play matches (no rounds)
            $table->foreign('club_activity_id')->references('id')->on('club_activities')->nullOnDelete();
            $table->foreign('result_submitted_by')->references('id')->on('users')->nullOnDelete();
        });

        // Make round_id nullable
        DB::statement('ALTER TABLE club_activity_matches MODIFY COLUMN round_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('club_activity_matches', function (Blueprint $table) {
            $table->dropForeign(['club_activity_id']);
            $table->dropForeign(['result_submitted_by']);
            $table->dropColumn([
                'club_activity_id', 'match_number', 'scheduled_court',
                'started_at', 'ended_at', 'result_submitted_by',
                'result_confirmed', 'oprs_processed', 'set_scores',
            ]);
        });

        Schema::table('club_activity_participants', function (Blueprint $table) {
            $table->dropColumn([
                'checked_in_at', 'gender_preference', 'current_status',
                'queue_position', 'matches_played_count', 'last_match_ended_at',
            ]);
        });

        Schema::table('club_activities', function (Blueprint $table) {
            $table->dropColumn([
                'qr_code', 'courts_count', 'avg_match_duration', 'rotation_mode',
                'gender_preference_enabled', 'oprs_weight', 'allow_guests',
                'started_at', 'ended_at',
            ]);
        });

        DB::statement("ALTER TABLE club_activities MODIFY COLUMN type ENUM('one_off', 'recurring', 'competition') DEFAULT 'one_off'");
    }
};
