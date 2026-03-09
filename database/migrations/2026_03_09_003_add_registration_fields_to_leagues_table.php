<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->unsignedTinyInteger('required_players_per_registration')->default(1)->after('registration_deadline');
            $table->decimal('registration_fee', 12, 0)->nullable()->after('required_players_per_registration');
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn(['required_players_per_registration', 'registration_fee']);
        });
    }
};
