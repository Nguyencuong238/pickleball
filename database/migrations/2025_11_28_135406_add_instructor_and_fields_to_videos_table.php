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
        Schema::table('videos', function (Blueprint $table) {
            $table->unsignedBigInteger('instructor_id')->nullable()->after('category_id');
            $table->string('duration')->nullable()->after('video_link');
            $table->string('level')->nullable()->after('duration');
            $table->integer('views_count')->default(0)->after('level');
            $table->decimal('rating', 3, 1)->nullable()->after('views_count');
            $table->integer('rating_count')->default(0)->after('rating');
            
            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->dropColumn(['instructor_id', 'duration', 'level', 'views_count', 'rating', 'rating_count']);
        });
    }
};
