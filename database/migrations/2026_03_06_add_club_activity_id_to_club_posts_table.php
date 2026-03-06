<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_posts', function (Blueprint $table) {
            $table->foreignId('club_activity_id')->nullable()->after('user_id')
                ->constrained('club_activities')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('club_posts', function (Blueprint $table) {
            $table->dropForeign(['club_activity_id']);
            $table->dropColumn('club_activity_id');
        });
    }
};
