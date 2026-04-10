<?php

namespace Tests\Feature\Gems;

use App\Exceptions\GemTransferException;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\GemTransaction;
use App\Models\GemWallet;
use App\Models\User;
use App\Services\GemPaymentProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Gems\Concerns\AssertsWalletInvariants;
use Tests\TestCase;

class GemPaymentProcessorTest extends TestCase
{
    use RefreshDatabase;
    use AssertsWalletInvariants;

    private GemPaymentProcessor $processor;
    private User $owner;
    private User $player;
    private Club $club;
    private ClubActivity $activity;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'gems.transfer_enabled' => true,
            'gems.refund_window_days' => 1,
            'gems.exchange_rate' => 1000,
            'gems.platform_fee_percent' => 0,
        ]);

        $this->processor = app(GemPaymentProcessor::class);
        $this->owner = User::factory()->create();
        $this->player = User::factory()->create();
        GemWallet::create(['user_id' => $this->player->id, 'balance' => 1000]);
        GemWallet::create(['user_id' => $this->owner->id, 'balance' => 0]);

        $this->club = Club::factory()->create(['user_id' => $this->owner->id]);
        $this->activity = ClubActivity::factory()->create([
            'club_id' => $this->club->id,
            'created_by' => $this->owner->id,
            'fee_gems' => 50,
        ]);
    }

    public function test_processor_pay_throws_missing_payer_when_club_activity_without_explicit_payer(): void
    {
        $this->expectException(GemTransferException::class);
        $this->processor->pay($this->activity); // no payer arg
    }

    public function test_processor_pay_transfers_to_club_owner_with_explicit_payer(): void
    {
        [$debit, $credit] = $this->processor->pay($this->activity, $this->player);

        $this->assertSame(-50, $debit->amount);
        $this->assertSame(50, $credit->amount);
        $this->assertSame($this->player->id, $debit->user_id);
        $this->assertSame($this->owner->id, $credit->user_id);

        $ownerWallet = GemWallet::where('user_id', $this->owner->id)->first();
        $this->assertSame(50, $ownerWallet->balance);
        $this->assertSame(50, $ownerWallet->locked_balance);

        $this->assertWalletInvariants($this->player->id);
        $this->assertWalletInvariants($this->owner->id);
    }

    public function test_processor_refund_for_locates_debit_via_unique_index(): void
    {
        $this->processor->pay($this->activity, $this->player);

        [$refund, $clawback] = $this->processor->refundFor($this->activity, $this->player);

        $this->assertSame(50, $refund->amount);
        $this->assertNotNull($clawback);
        $this->assertSame(-50, $clawback->amount);

        $owner = GemWallet::where('user_id', $this->owner->id)->first();
        $this->assertSame(0, $owner->balance);
        $this->assertSame(0, $owner->locked_balance);

        $this->assertWalletInvariants($this->player->id);
        $this->assertWalletInvariants($this->owner->id);
    }
}
