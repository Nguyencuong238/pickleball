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
        Schema::create('opr_verification_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('media')->nullable();
            $table->json('links')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('verifier_id')->nullable();
            $table->text('verifier_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('verifier_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_verification_requests');
    }
};
