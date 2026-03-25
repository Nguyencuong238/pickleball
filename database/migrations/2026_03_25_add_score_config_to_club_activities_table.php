<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_activities', function (Blueprint $table) {
            $table->unsignedTinyInteger('best_of')->default(1)->after('oprs_weight');
            $table->unsignedTinyInteger('points_per_set')->default(21)->after('best_of');
        });
    }

    public function down(): void
    {
        Schema::table('club_activities', function (Blueprint $table) {
            $table->dropColumn(['best_of', 'points_per_set']);
        });
    }
};
