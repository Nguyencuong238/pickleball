<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename 'media' column to 'media_paths' to avoid conflict with Spatie Media Library
     */
    public function up(): void
    {
        Schema::table('opr_verification_requests', function (Blueprint $table) {
            $table->renameColumn('media', 'media_paths');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opr_verification_requests', function (Blueprint $table) {
            $table->renameColumn('media_paths', 'media');
        });
    }
};
