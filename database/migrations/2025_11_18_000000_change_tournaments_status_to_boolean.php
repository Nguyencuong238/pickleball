<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing string data to integers BEFORE changing column type
        DB::table('tournaments')
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->update(['status' => 1]);

        DB::table('tournaments')
            ->whereIn('status', ['completed', 'cancelled'])
            ->update(['status' => 0]);

        // Now change the column type to boolean
        Schema::table('tournaments', function (Blueprint $table) {
            $table->boolean('status')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('status')->default('upcoming')->change();
        });

        // Revert numeric values back to strings
        DB::table('tournaments')
            ->where('status', 1)
            ->update(['status' => DB::raw("'upcoming'")]);

        DB::table('tournaments')
            ->where('status', 0)
            ->update(['status' => DB::raw("'completed'")]);
    }
};
