<?php

namespace Tests\Feature\Gems\Concerns;

use App\Models\GemWallet;
use PHPUnit\Framework\Assert;

/**
 * Các bất biến về ví Gems — phải đúng ở đầu và cuối mọi test case.
 * balance >= 0 && locked_balance >= 0 && locked_balance <= balance
 */
trait AssertsWalletInvariants
{
    protected function assertWalletInvariants(int $userId, string $label = ''): void
    {
        $wallet = GemWallet::where('user_id', $userId)->first();
        if (!$wallet) {
            return;
        }

        $context = $label ? " [{$label}]" : '';

        Assert::assertGreaterThanOrEqual(
            0,
            $wallet->balance,
            "Vi phạm: balance < 0 cho user {$userId}{$context}"
        );
        Assert::assertGreaterThanOrEqual(
            0,
            $wallet->locked_balance,
            "Vi phạm: locked_balance < 0 cho user {$userId}{$context}"
        );
        Assert::assertLessThanOrEqual(
            $wallet->balance,
            $wallet->locked_balance,
            "Vi phạm: locked_balance > balance cho user {$userId}{$context}"
        );
    }
}
