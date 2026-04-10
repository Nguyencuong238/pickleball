<?php

namespace App\Console\Commands;

use App\Models\GemTransaction;
use App\Services\GemWalletService;
use Illuminate\Console\Command;

class GemsReleaseLockedCommand extends Command
{
    protected $signature = 'gems:release-locked';

    protected $description = 'Giải phóng các khoản Gems đã đáo hạn cửa sổ hoàn tiền về số dư khả dụng.';

    public function handle(GemWalletService $svc): int
    {
        $released = 0;
        $skipped = 0;

        GemTransaction::query()
            ->where('type', GemTransaction::TYPE_RECEIPT)
            ->whereNull('released_at')
            ->where('status', '!=', GemTransaction::STATUS_REFUNDED)
            ->where('available_at', '<=', now())
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($svc, &$released, &$skipped) {
                foreach ($rows as $row) {
                    if ($svc->releaseReceipt($row)) {
                        $released++;
                    } else {
                        $skipped++;
                    }
                }
            });

        $this->info("Đã giải phóng {$released} giao dịch nhận Gems (bỏ qua {$skipped}).");

        return self::SUCCESS;
    }
}
