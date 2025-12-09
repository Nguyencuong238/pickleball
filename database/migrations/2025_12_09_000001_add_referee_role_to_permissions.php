<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: Actual role/permission creation handled by PermissionSeeder
     */
    public function up(): void
    {
        // Permissions handled by seeder
        // This migration serves as a marker for version control
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cleanup handled by seeder rollback if needed
    }
};
