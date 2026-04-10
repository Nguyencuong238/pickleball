<?php

namespace Tests\Feature\Gems;

use App\Exceptions\GemTransferException;
use App\Models\Booking;
use App\Models\GemTransaction;
use App\Models\GemWallet;
use App\Models\User;
use App\Services\GemWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Gems\Concerns\AssertsWalletInvariants;
use Tests\TestCase;

class GemRefundTest extends TestCase
{
    use RefreshDatabase;
    use AssertsWalletInvariants;

    private GemWalletService $svc;
    private User $payer;
    private User $payee;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gems.transfer_enabled' => true, 'gems.refund_window_days' => 1]);
        $this->svc = app(GemWalletService::class);
        $this->payer = User::factory()->create();
        $this->payee = User::factory()->create();
        GemWallet::create(['user_id' => $this->payer->id, 'balance' => 500]);
        GemWallet::create(['user_id' => $this->payee->id, 'balance' => 0]);
    }

    private function doTransfer(int $gems = 100, int $refId = 1): array
    {
        return $this->svc->transfer($this->payer, $this->payee, $gems, Booking::class, $refId, 'Test');
    }

    public function test_refund_within_window_credits_payer_and_clawsback_payee(): void
    {
        [$debit, ] = $this->doTransfer(100, 1);

        [$refund, $clawback] = $this->svc->refund($debit, 'Khách hủy');

        $payer = GemWallet::where('user_id', $this->payer->id)->first();
        $payee = GemWallet::where('user_id', $this->payee->id)->first();

        $this->assertSame(500, $payer->balance); // restored
        $this->assertSame(0, $payee->balance);
        $this->assertSame(0, $payee->locked_balance);

        $this->assertSame(100, $refund->amount);
        $this->assertSame(-100, $clawback->amount);

        $this->assertWalletInvariants($this->payer->id);
        $this->assertWalletInvariants($this->payee->id);
    }

    public function test_refund_throws_after_release(): void
    {
        [$debit, ] = $this->doTransfer(100, 2);

        // Giả lập release đã xảy ra
        GemTransaction::where('id', $debit->counterparty_transaction_id)
            ->update(['released_at' => now()]);

        $this->expectException(GemTransferException::class);
        $this->svc->refund($debit->fresh());
    }

    public function test_refund_throws_when_already_refunded(): void
    {
        [$debit, ] = $this->doTransfer(100, 3);
        $this->svc->refund($debit);

        $this->expectException(GemTransferException::class);
        $this->svc->refund($debit->fresh());
    }

    public function test_refund_preserves_invariants_with_fee(): void
    {
        config(['gems.platform_fee_percent' => 10]);
        [$debit, ] = $this->svc->transfer($this->payer, $this->payee, 100, Booking::class, 4, 'Phí 10%');

        [$refund, ] = $this->svc->refund($debit);

        $payer = GemWallet::where('user_id', $this->payer->id)->first();
        $payee = GemWallet::where('user_id', $this->payee->id)->first();

        // Payer chỉ được hoàn net (90), phí 10 không hoàn lại — bảo toàn cung
        $this->assertSame(490, $payer->balance);
        $this->assertSame(0, $payee->balance);
        $this->assertSame(0, $payee->locked_balance);

        $this->assertWalletInvariants($this->payer->id);
        $this->assertWalletInvariants($this->payee->id);
    }
}
