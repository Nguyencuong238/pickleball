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
        Schema::table('instructors', function (Blueprint $table) {
            $table->string('bio', 500)->nullable()->after('name')->comment('Short tagline/bio of instructor');
            $table->text('description')->nullable()->after('bio')->comment('Full description/about section');
            $table->unsignedTinyInteger('experience_years')->default(0)->after('experience')->comment('Years of teaching experience');
            $table->unsignedInteger('student_count')->default(0)->after('experience_years')->comment('Total number of students taught');
            $table->unsignedInteger('total_hours')->default(0)->after('student_count')->comment('Total teaching hours');
            $table->json('specialties')->nullable()->after('total_hours')->comment('Array of specialties e.g. ["1-1", "Group"]');
            $table->string('phone', 20)->nullable()->after('specialties')->comment('Phone number');
            $table->string('zalo', 50)->nullable()->after('phone')->comment('Zalo contact');
            $table->string('email', 100)->nullable()->after('zalo')->comment('Email address');
            $table->decimal('price_per_session', 12, 0)->nullable()->after('email')->comment('Base price per session in VND');
            $table->boolean('is_verified')->default(false)->after('price_per_session')->comment('Verified badge status');
            $table->boolean('is_certified')->default(false)->after('is_verified')->comment('Certified badge status');
            $table->decimal('rating', 2, 1)->default(0)->after('is_certified')->comment('Average rating (1-5)');
            $table->unsignedInteger('reviews_count')->default(0)->after('rating')->comment('Total number of reviews');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'description',
                'experience_years',
                'student_count',
                'total_hours',
                'specialties',
                'phone',
                'zalo',
                'email',
                'price_per_session',
                'is_verified',
                'is_certified',
                'rating',
                'reviews_count',
            ]);
        });
    }
};
