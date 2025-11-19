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
            // Category assignment
            $table->foreignId('category_id')->nullable()->constrained('tournament_categories')->onDelete('set null')->after('tournament_id');

            // Group assignment for group stage
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null')->after('category_id');

            // Seeding information
            $table->integer('seed_number')->nullable()->after('group_id'); // Seeding rank (1, 2, 3, etc.)

            // Payment information
            $table->enum('payment_status', [
                'unpaid',
                'pending',
                'paid',
                'partially_paid',
                'refunded',
                'waived'
            ])->default('unpaid')->after('status');
            $table->decimal('registration_fee', 10, 2)->default(0)->after('payment_status');
            $table->decimal('amount_paid', 10, 2)->default(0)->after('registration_fee');

            // Registration details
            $table->dateTime('registered_at')->nullable()->after('amount_paid');
            $table->dateTime('confirmed_at')->nullable()->after('registered_at');

            // Performance tracking
            $table->integer('matches_played')->default(0)->after('position');
            $table->integer('matches_won')->default(0)->after('matches_played');
            $table->integer('matches_lost')->default(0)->after('matches_won');
            $table->decimal('win_rate', 5, 2)->default(0)->after('matches_lost');
            $table->integer('total_points')->default(0)->after('win_rate');
            $table->integer('sets_won')->default(0)->after('total_points');
            $table->integer('sets_lost')->default(0)->after('sets_won');

            // Indexes
            $table->index('category_id');
            $table->index('group_id');
            $table->index('seed_number');
            $table->index('payment_status');
            $table->index(['tournament_id', 'seed_number']);
            $table->index('registered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_athletes', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['group_id']);
            $table->dropColumn([
                'category_id',
                'group_id',
                'seed_number',
                'payment_status',
                'registration_fee',
                'amount_paid',
                'registered_at',
                'confirmed_at',
                'matches_played',
                'matches_won',
                'matches_lost',
                'win_rate',
                'total_points',
                'sets_won',
                'sets_lost'
            ]);
        });
    }
};
