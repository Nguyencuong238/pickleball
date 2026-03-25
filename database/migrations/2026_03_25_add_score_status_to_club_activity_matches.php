<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_activity_matches', function (Blueprint $table) {
            $table->string('score_status', 20)->nullable()->after('result_confirmed');
            $table->unsignedBigInteger('score_confirmed_by')->nullable()->after('score_status');
            $table->foreign('score_confirmed_by')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement("ALTER TABLE club_activity_matches MODIFY COLUMN status ENUM('scheduled','in_progress','completed','pending_score') NOT NULL DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        Schema::table('club_activity_matches', function (Blueprint $table) {
            $table->dropForeign(['score_confirmed_by']);
            $table->dropColumn(['score_status', 'score_confirmed_by']);
        });
    }
};
