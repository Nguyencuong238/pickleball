<?php

namespace Tests\Feature\Gems;

use App\Models\Booking;
use App\Models\GemTransaction;
use App\Models\GemWallet;
use App\Models\User;
use App\Services\GemWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Gems\Concerns\AssertsWalletInvariants;
use Tests\TestCase;

class GemsReleaseLockedCommandTest extends TestCase
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
        GemWallet::create(['user_id' => $this->payer->id, 'balance' => 1000]);
        GemWallet::create(['user_id' => $this->payee->id, 'balance' => 0]);
    }

    public function test_release_command_unlocks_matured_receipts(): void
    {
        [, $credit] = $this->svc->transfer($this->payer, $this->payee, 200, Booking::class, 1, 'Matured');

        // Đáo hạn thủ công
        GemTransaction::where('id', $credit->id)->update(['available_at' => now()->subMinute()]);

        $this->artisan('gems:release-locked')->assertSuccessful();

        $payee = GemWallet::where('user_id', $this->payee->id)->first();
        $this->assertSame(200, $payee->balance);
        $this->assertSame(0, $payee->locked_balance);
        $this->assertNotNull($credit->fresh()->released_at);

        $this->assertWalletInvariants($this->payee->id);
    }

    public function test_release_command_skips_immature_receipts(): void
    {
        [, $credit] = $this->svc->transfer($this->payer, $this->payee, 100, Booking::class, 2, 'Immature');

        $this->artisan('gems:release-locked')->assertSuccessful();

        $payee = GemWallet::where('user_id', $this->payee->id)->first();
        $this->assertSame(100, $payee->locked_balance);
        $this->assertNull($credit->fresh()->released_at);
    }

    public function test_release_command_idempotent_on_second_run(): void
    {
        [, $credit] = $this->svc->transfer($this->payer, $this->payee, 150, Booking::class, 3, 'Idem');
        GemTransaction::where('id', $credit->id)->update(['available_at' => now()->subMinute()]);

        $this->artisan('gems:release-locked')->assertSuccessful();
        $this->artisan('gems:release-locked')->assertSuccessful();

        $payee = GemWallet::where('user_id', $this->payee->id)->first();
        $this->assertSame(150, $payee->balance);
        $this->assertSame(0, $payee->locked_balance);
    }
}
