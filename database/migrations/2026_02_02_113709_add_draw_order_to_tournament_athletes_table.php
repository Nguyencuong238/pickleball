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
        Schema::table('tournament_athletes', function (Blueprint $table) {
            $table->integer('draw_order')->nullable()->after('group_id')->comment('Order position within group from manual draw');
            $table->index(['group_id', 'draw_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_athletes', function (Blueprint $table) {
            $table->dropIndex(['tournament_athletes_group_id_draw_order_index']);
            $table->dropColumn('draw_order');
        });
    }
};
