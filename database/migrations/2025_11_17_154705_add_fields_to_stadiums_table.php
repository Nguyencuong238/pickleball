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
        Schema::table('stadiums', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->default(0)->after('status');
            $table->integer('rating_count')->default(0)->after('rating');
            $table->string('court_surface')->nullable()->after('courts_count'); // e.g., "Acrylic chuyên dụng"
            $table->string('featured_status')->default('normal')->after('status'); // featured, normal
            $table->boolean('verified')->default(false)->after('featured_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stadiums', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_count', 'court_surface', 'featured_status', 'verified']);
        });
    }
};
