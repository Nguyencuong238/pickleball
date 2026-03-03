<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->foreignId('club_id')->nullable()->after('user_id')
                ->constrained('clubs')->nullOnDelete();
            $table->enum('competition_format', ['traditional', 'mlp'])
                ->default('traditional')->after('config');
        });

        Schema::table('league_rounds', function (Blueprint $table) {
            $table->time('scheduled_time')->nullable()->after('scheduled_date');
            $table->string('venue', 255)->nullable()->after('scheduled_time');
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
            $table->dropColumn(['club_id', 'competition_format']);
        });

        Schema::table('league_rounds', function (Blueprint $table) {
            $table->dropColumn(['scheduled_time', 'venue']);
        });
    }
};
