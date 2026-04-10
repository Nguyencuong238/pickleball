<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Chuyển ENUM → VARCHAR để dễ mở rộng loại giao dịch trong tương lai
        DB::statement("ALTER TABLE gem_transactions MODIFY type VARCHAR(32) NOT NULL");
        DB::statement("ALTER TABLE gem_transactions MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");

        Schema::table('gem_transactions', function (Blueprint $table) {
            // Người đối ứng trong giao dịch chuyển Gems (payer↔payee)
            $table->unsignedBigInteger('counterparty_user_id')->nullable()->after('user_id');
            // ID của giao dịch đối ứng (liên kết nợ ↔ có)
            $table->unsignedBigInteger('counterparty_transaction_id')->nullable()->after('counterparty_user_id');
            // Phí nền tảng (Gems bị đốt trong giai đoạn 1)
            $table->unsignedInteger('platform_fee')->default(0)->after('balance_after');
            // Thời điểm Gems được giải phóng khỏi khóa escrow
            $table->timestamp('available_at')->nullable()->after('metadata');
            // Thời điểm giao dịch nhận đã được giải phóng thực tế
            $table->timestamp('released_at')->nullable()->after('available_at');

            $table->foreign('counterparty_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('counterparty_transaction_id')->references('id')->on('gem_transactions')->nullOnDelete();

            $table->index(['type', 'available_at', 'released_at'], 'idx_release_scan');
            $table->index('counterparty_user_id', 'idx_counterparty');
            // Chốt chống trùng lặp: mỗi người chỉ có 1 giao dịch 1 loại cho 1 tham chiếu
            $table->unique(
                ['reference_type', 'reference_id', 'type', 'user_id'],
                'ux_gem_tx_ref_type_user'
            );
        });
    }

    public function down(): void
    {
        Schema::table('gem_transactions', function (Blueprint $table) {
            $table->dropUnique('ux_gem_tx_ref_type_user');
            $table->dropIndex('idx_counterparty');
            $table->dropIndex('idx_release_scan');
            $table->dropForeign(['counterparty_transaction_id']);
            $table->dropForeign(['counterparty_user_id']);
            $table->dropColumn([
                'counterparty_user_id',
                'counterparty_transaction_id',
                'platform_fee',
                'available_at',
                'released_at',
            ]);
        });

        DB::statement("ALTER TABLE gem_transactions MODIFY type ENUM('top_up','payment','refund','admin_adjust') NOT NULL");
        DB::statement("ALTER TABLE gem_transactions MODIFY status ENUM('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
