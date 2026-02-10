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
        Schema::table('bookings', function (Blueprint $table) {
            // Thêm cột để tracking thời điểm xác nhận
            if (!Schema::hasColumn('bookings', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('status');
            }
            // Thêm cột để tracking thời điểm booking được tạo (tự động)
            if (!Schema::hasColumn('bookings', 'lock_expires_at')) {
                $table->integer('lock_expires_at')->nullable()->after('confirmed_at')->comment('Unix timestamp khi khóa hết hạn');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'lock_expires_at']);
        });
    }
};
