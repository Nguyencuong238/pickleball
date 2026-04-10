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

class GemTransferTest extends TestCase
{
    use RefreshDatabase;
    use AssertsWalletInvariants;

    private GemWalletService $svc;
    private User $payer;
    private User $payee;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gems.transfer_enabled' => true, 'gems.refund_window_days' => 1, 'gems.platform_fee_percent' => 0]);
        $this->svc = app(GemWalletService::class);
        $this->payer = User::factory()->create();
        $this->payee = User::factory()->create();
        GemWallet::create(['user_id' => $this->payer->id, 'balance' => 500, 'locked_balance' => 0]);
        GemWallet::create(['user_id' => $this->payee->id, 'balance' => 0, 'locked_balance' => 0]);

        $this->assertWalletInvariants($this->payer->id, 'setUp payer');
        $this->assertWalletInvariants($this->payee->id, 'setUp payee');
    }

    public function test_transfer_happy_path_credits_payee_with_lock(): void
    {
        [$debit, $credit] = $this->svc->transfer(
            $this->payer, $this->payee, 100, Booking::class, 1, 'Đặt sân test'
        );

        $payerWallet = GemWallet::where('user_id', $this->payer->id)->first();
        $payeeWallet = GemWallet::where('user_id', $this->payee->id)->first();

        $this->assertSame(400, $payerWallet->balance);
        $this->assertSame(0, $payerWallet->locked_balance);
        $this->assertSame(100, $payeeWallet->balance);
        $this->assertSame(100, $payeeWallet->locked_balance);
        $this->assertSame(-100, $debit->amount);
        $this->assertSame(100, $credit->amount);
        $this->assertNotNull($credit->available_at);

        $this->assertWalletInvariants($this->payer->id, 'end payer');
        $this->assertWalletInvariants($this->payee->id, 'end payee');
    }

    public function test_transfer_throws_on_self_payment(): void
    {
        $this->expectException(GemTransferException::class);
        $this->svc->transfer($this->payer, $this->payer, 10, Booking::class, 1, 'x');
    }

    public function test_transfer_throws_on_non_positive_amount(): void
    {
        $this->expectException(GemTransferException::class);
        $this->svc->transfer($this->payer, $this->payee, 0, Booking::class, 1, 'x');
    }

    public function test_transfer_throws_when_spendable_is_insufficient(): void
    {
        // Khóa toàn bộ balance của payer → spendable = 0
        GemWallet::where('user_id', $this->payer->id)->update(['locked_balance' => 500]);

        $this->expectException(GemTransferException::class);
        try {
            $this->svc->transfer($this->payer, $this->payee, 100, Booking::class, 1, 'x');
        } finally {
            $this->assertWalletInvariants($this->payer->id, 'after fail');
        }
    }

    public function test_transfer_respects_platform_fee_percent(): void
    {
        [$debit, $credit] = $this->svc->transfer(
            $this->payer, $this->payee, 100, Booking::class, 2, 'Phí 10%', 10
        );

        $this->assertSame(-100, $debit->amount);
        $this->assertSame(10, $debit->platform_fee);
        $this->assertSame(90, $credit->amount); // payee thực nhận 90

        $payee = GemWallet::where('user_id', $this->payee->id)->first();
        $this->assertSame(90, $payee->balance);
        $this->assertSame(90, $payee->locked_balance);

        $this->assertWalletInvariants($this->payee->id);
    }

    public function test_transfer_back_links_counterparty_transaction_ids(): void
    {
        [$debit, $credit] = $this->svc->transfer(
            $this->payer, $this->payee, 50, Booking::class, 3, 'Link test'
        );

        $this->assertSame($credit->id, $debit->counterparty_transaction_id);
        $this->assertSame($debit->id, $credit->counterparty_transaction_id);
        $this->assertSame($this->payee->id, $debit->counterparty_user_id);
        $this->assertSame($this->payer->id, $credit->counterparty_user_id);
    }

    public function test_transfer_duplicate_throws_via_unique_index(): void
    {
        $this->svc->transfer($this->payer, $this->payee, 20, Booking::class, 99, 'First');

        $this->expectException(GemTransferException::class);
        $this->svc->transfer($this->payer, $this->payee, 20, Booking::class, 99, 'Second');
    }

    public function test_transfer_allows_re_enrollment_after_refund(): void
    {
        [$debit1, ] = $this->svc->transfer($this->payer, $this->payee, 50, Booking::class, 100, 'First');
        $this->svc->refund($debit1);

        // Phải cho phép đăng ký lại sau khi hoàn (cùng reference)
        [$debit2, $credit2] = $this->svc->transfer($this->payer, $this->payee, 50, Booking::class, 100, 'Re-enroll');
        $this->assertSame(-50, $debit2->amount);
        $this->assertSame(50, $credit2->amount);

        $this->assertWalletInvariants($this->payer->id);
        $this->assertWalletInvariants($this->payee->id);
    }

    public function test_spendable_balance_accessor_never_negative(): void
    {
        $wallet = GemWallet::where('user_id', $this->payer->id)->first();
        $wallet->update(['locked_balance' => 9999]); // > balance
        $wallet->refresh();
        $this->assertSame(0, $wallet->spendable_balance);
    }
}
