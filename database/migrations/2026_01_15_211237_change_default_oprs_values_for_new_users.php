<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change default OPRS values for new users:
     * - total_oprs: 700 -> 0
     * - opr_level: '2.0' -> '1.0'
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('total_oprs', 10, 2)->default(0)->change();
            $table->string('opr_level', 10)->default('1.0')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('total_oprs', 10, 2)->default(700)->change();
            $table->string('opr_level', 10)->default('2.0')->change();
        });
    }
};
