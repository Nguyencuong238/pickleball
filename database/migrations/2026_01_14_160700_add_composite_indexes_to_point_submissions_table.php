<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_submissions', function (Blueprint $table) {
            // Composite index for admin queries (filtering by status and sorting by created_at)
            $table->index(['status', 'created_at'], 'point_submissions_status_created_idx');

            // Composite index for user + status queries
            $table->index(['user_id', 'status', 'created_at'], 'point_submissions_user_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('point_submissions', function (Blueprint $table) {
            $table->dropIndex('point_submissions_status_created_idx');
            $table->dropIndex('point_submissions_user_status_created_idx');
        });
    }
};
