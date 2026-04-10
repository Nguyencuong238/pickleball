<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gem_wallets', function (Blueprint $table) {
            // Số dư Gems đang bị khóa (chờ giải phóng sau cửa sổ hoàn tiền)
            $table->unsignedInteger('locked_balance')->default(0)->after('balance');
        });
    }

    public function down(): void
    {
        Schema::table('gem_wallets', function (Blueprint $table) {
            $table->dropColumn('locked_balance');
        });
    }
};
