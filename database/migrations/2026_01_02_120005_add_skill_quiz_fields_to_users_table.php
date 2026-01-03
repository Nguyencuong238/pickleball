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
            $table->timestamp('last_skill_quiz_at')->nullable()->after('opr_level');
            $table->unsignedSmallInteger('skill_quiz_count')->default(0)->after('last_skill_quiz_at');
            $table->boolean('elo_is_provisional')->default(true)->after('skill_quiz_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_skill_quiz_at', 'skill_quiz_count', 'elo_is_provisional']);
        });
    }
};
