<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_standings', function (Blueprint $table) {
            $table->integer('manual_rank_override')->nullable()->after('is_advanced');
        });
    }

    public function down(): void
    {
        Schema::table('group_standings', function (Blueprint $table) {
            $table->dropColumn('manual_rank_override');
        });
    }
};
