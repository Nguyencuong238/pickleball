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
            $table->text('referee_bio')->nullable();
            $table->string('referee_status')->default('active'); // active, inactive
            $table->integer('matches_officiated')->default(0);
            $table->decimal('referee_rating', 3, 2)->nullable(); // 0.00-5.00
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referee_bio', 'referee_status', 'matches_officiated', 'referee_rating']);
        });
    }
};
