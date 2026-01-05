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
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->bigInteger('points')->default(0)->comment('Số điểm trong ví');
            $table->timestamps();
            $table->unique('user_id');
        });

        // Bảng lịch sử giao dịch điểm
        Schema::create('user_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->bigInteger('points')->comment('Số điểm cộng/trừ');
            $table->string('type')->comment('Loại giao dịch: earn, use, refund, admin, etc');
            $table->string('description')->nullable()->comment('Mô tả giao dịch');
            $table->json('metadata')->nullable()->comment('Dữ liệu bổ sung');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_point_transactions');
        Schema::dropIfExists('user_wallets');
    }
};
