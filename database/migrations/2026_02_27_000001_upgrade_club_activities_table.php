<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_activities', function (Blueprint $table) {
            $table->enum('type', ['one_off', 'recurring', 'competition'])->default('one_off')->after('club_id');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('club_id');
            $table->time('end_time')->nullable()->after('activity_date');
            $table->unsignedTinyInteger('recurrence_day')->nullable()->after('end_time');
            $table->foreignId('parent_activity_id')->nullable()->constrained('club_activities')->nullOnDelete()->after('recurrence_day');
            $table->boolean('auto_approve')->default(true)->after('parent_activity_id');
            $table->string('min_skill_level', 5)->nullable()->after('auto_approve');
            $table->string('max_skill_level', 5)->nullable()->after('min_skill_level');
            $table->json('competition_config')->nullable()->after('max_skill_level');

            $table->index(['type', 'status']);
            $table->index(['parent_activity_id']);
        });
    }

    public function down(): void
    {
        Schema::table('club_activities', function (Blueprint $table) {
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['parent_activity_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['parent_activity_id']);
            $table->dropColumn([
                'type', 'created_by', 'end_time', 'recurrence_day',
                'parent_activity_id', 'auto_approve',
                'min_skill_level', 'max_skill_level', 'competition_config',
            ]);
        });
    }
};
