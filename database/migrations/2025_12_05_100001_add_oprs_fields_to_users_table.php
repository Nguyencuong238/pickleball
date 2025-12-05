<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add OPRS (OnePickleball Rating Score) fields to users table
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('challenge_score', 10, 2)->default(0)->after('ocr_losses');
            $table->decimal('community_score', 10, 2)->default(0)->after('challenge_score');
            $table->decimal('total_oprs', 10, 2)->default(700)->after('community_score');
            $table->string('opr_level', 10)->default('2.0')->after('total_oprs');

            $table->index('total_oprs');
            $table->index('opr_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['total_oprs']);
            $table->dropIndex(['opr_level']);
            $table->dropColumn(['challenge_score', 'community_score', 'total_oprs', 'opr_level']);
        });
    }
};
