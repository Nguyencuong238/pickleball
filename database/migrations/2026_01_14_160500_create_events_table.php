<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('location', 500)->nullable();
            $table->foreignId('stadium_id')->nullable()->constrained()->onDelete('set null');
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->integer('points')->default(5);
            $table->integer('max_attendees')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('qr_code_data', 100)->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('stadium_id');
            $table->index('start_datetime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
