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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('tournament_athlete_id')->constrained('tournament_athletes')->onDelete('cascade');
            $table->string('payment_reference')->unique(); // Unique payment reference number
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('VND');
            $table->enum('payment_method', [
                'cash',
                'bank_transfer',
                'credit_card',
                'debit_card',
                'e_wallet',
                'momo',
                'zalopay',
                'vnpay',
                'other'
            ])->default('bank_transfer');
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'refunded',
                'cancelled'
            ])->default('pending');
            $table->string('transaction_id')->nullable(); // External payment gateway transaction ID
            $table->text('payment_details')->nullable(); // Additional payment info as JSON
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_url')->nullable(); // Link to receipt/invoice
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('tournament_id');
            $table->index('tournament_athlete_id');
            $table->index('status');
            $table->index('payment_method');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
