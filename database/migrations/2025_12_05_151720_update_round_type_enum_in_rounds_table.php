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
        Schema::table('rounds', function (Blueprint $table) {
            $table->enum('round_type', ['group_stage', 'knockout', 'quarterfinal', 'semifinal', 'final', 'bronze'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback to previous enum values if needed
        Schema::table('rounds', function (Blueprint $table) {
            //
        });
    }
};
