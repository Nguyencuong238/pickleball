<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_activities', function (Blueprint $table) {
            $table->unsignedInteger('fee_gems')->nullable()->after('max_participants');
        });

        Schema::table('club_activity_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('gem_transaction_id')->nullable()->after('waitlist_position');
            $table->foreign('gem_transaction_id')->references('id')->on('gem_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('club_activity_participants', function (Blueprint $table) {
            $table->dropForeign(['gem_transaction_id']);
            $table->dropColumn('gem_transaction_id');
        });

        Schema::table('club_activities', function (Blueprint $table) {
            $table->dropColumn('fee_gems');
        });
    }
};
