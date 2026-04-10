<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thay unique index cứng bằng non-unique index.
 * Lý do: cho phép chu kỳ RSVP → hủy → RSVP lại (mỗi lần là một giao dịch payment hợp lệ).
 * Chống trùng lặp được bảo đảm ở tầng service thông qua kiểm tra STATUS_COMPLETED trước khi chuyển.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('gem_transactions', function (Blueprint $table) {
            $table->dropUnique('ux_gem_tx_ref_type_user');
            $table->index(
                ['reference_type', 'reference_id', 'type', 'user_id'],
                'idx_gem_tx_ref_type_user'
            );
        });
    }

    public function down(): void
    {
        Schema::table('gem_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_gem_tx_ref_type_user');
            $table->unique(
                ['reference_type', 'reference_id', 'type', 'user_id'],
                'ux_gem_tx_ref_type_user'
            );
        });
    }
};
