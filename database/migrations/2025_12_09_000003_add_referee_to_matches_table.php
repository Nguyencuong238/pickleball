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
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('referee_id')->nullable()->after('winner_id')->constrained('users')->nullOnDelete();
            $table->string('referee_name')->nullable()->after('referee_id');

            $table->index('referee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['referee_id']);
            $table->dropIndex(['referee_id']);
            $table->dropColumn(['referee_id', 'referee_name']);
        });
    }
};
