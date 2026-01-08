<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: Modify ENUM to include moderator
        DB::statement("ALTER TABLE club_members MODIFY COLUMN role ENUM('creator', 'admin', 'moderator', 'member') DEFAULT 'member'");
    }

    public function down(): void
    {
        // Revert: first update any moderators to members
        DB::table('club_members')->where('role', 'moderator')->update(['role' => 'member']);
        DB::statement("ALTER TABLE club_members MODIFY COLUMN role ENUM('creator', 'admin', 'member') DEFAULT 'member'");
    }
};
