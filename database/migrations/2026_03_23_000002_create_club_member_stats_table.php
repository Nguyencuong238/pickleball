<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_member_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('total_matches')->default(0);
            $table->unsignedInteger('total_wins')->default(0);
            $table->unsignedInteger('total_losses')->default(0);
            $table->unsignedInteger('total_points_scored')->default(0);
            $table->unsignedInteger('total_points_against')->default(0);
            $table->unsignedInteger('activities_participated')->default(0);
            $table->decimal('current_oprs', 5, 2)->nullable();
            $table->dateTime('last_played_at')->nullable();
            $table->timestamps();

            $table->unique(['club_id', 'user_id']);
            $table->foreign('club_id')->references('id')->on('clubs')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('club_members', function (Blueprint $table) {
            $table->decimal('initial_oprs', 5, 2)->nullable()->after('role');
            $table->text('notes')->nullable()->after('initial_oprs');
            $table->enum('member_status', ['active', 'inactive', 'suspended'])->default('active')->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('club_members', function (Blueprint $table) {
            $table->dropColumn(['initial_oprs', 'notes', 'member_status']);
        });

        Schema::dropIfExists('club_member_stats');
    }
};
