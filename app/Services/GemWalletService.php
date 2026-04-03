<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\GemTransaction;
use App\Models\GemWallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GemWalletService
{
    public function getOrCreateWallet(User $user): GemWallet
    {
        return $user->gemWallet ?? $user->gemWallet()->create(['balance' => 0]);
    }

    public function getBalance(User $user): int
    {
        return $user->gemWallet?->balance ?? 0;
    }

    public function createTopUpRequest(User $user, int $amountVnd): array
    {
        $minVnd = config('gems.min_topup_vnd');
        $maxVnd = config('gems.max_topup_vnd');

        if ($amountVnd < $minVnd || $amountVnd > $maxVnd) {
            throw new \InvalidArgumentException(
                "So tien nap phai tu " . number_format($minVnd) . " den " . number_format($maxVnd) . " VND."
            );
        }

        $exchangeRate = config('gems.exchange_rate');
        $gems = (int) ($amountVnd / $exchangeRate);
        $wallet = $this->getOrCreateWallet($user);

        $transaction = GemTransaction::create([
            'user_id' => $user->id,
            'gem_wallet_id' => $wallet->id,
            'type' => 'top_up',
            'amount' => $gems,
            'balance_after' => $wallet->balance,
            'description' => "Nap " . number_format($gems) . " Gems",
            'metadata' => ['amount_vnd' => $amountVnd, 'exchange_rate' => $exchangeRate],
            'status' => 'pending',
        ]);

        $sepayService = app(SepayService::class);
        $description = "GEMS{$user->id}T{$transaction->id}";
        $qrUrl = $sepayService->buildQrUrl($amountVnd, $description);

        return [
            'transaction' => $transaction,
            'qr_url' => $qrUrl,
            'amount_vnd' => $amountVnd,
            'gems' => $gems,
            'transfer_content' => $description,
        ];
    }

    public function confirmTopUp(GemTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Lock transaction row to prevent duplicate processing from webhook retries
            $tx = GemTransaction::where('id', $transaction->id)->lockForUpdate()->first();
            if ($tx->status !== 'pending' || $tx->type !== 'top_up') {
                return;
            }

            $wallet = GemWallet::where('id', $tx->gem_wallet_id)->lockForUpdate()->first();
            $wallet->increment('balance', $tx->amount);

            $tx->update([
                'balance_after' => $wallet->balance,
                'status' => 'completed',
            ]);
        });
    }

    /**
     * Pay for a booking with Gems - atomic: deduct + confirm + cashback
     * Returns [GemTransaction, cashbackPoints] or throws on insufficient balance
     */
    public function payForBooking(User $user, Booking $booking, int $totalPrice): array
    {
        $gemsNeeded = (int) ceil($totalPrice / config('gems.exchange_rate'));

        $gemTx = $this->deduct(
            $user, $gemsNeeded, Booking::class, $booking->id,
            "Dat san - {$booking->booking_code}"
        );

        $booking->update(['confirmed_at' => now(), 'status' => 'confirmed']);

        app(GemCashbackService::class)->award($gemTx);
        $cashbackPoints = (int) (abs($gemTx->amount) * config('gems.cashback_percent') / 100);

        return [$gemTx, $cashbackPoints];
    }

    public function deduct(User $user, int $gems, string $refType, int $refId, string $desc): GemTransaction
    {
        return DB::transaction(function () use ($user, $gems, $refType, $refId, $desc) {
            $wallet = GemWallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            if ($wallet->balance < $gems) {
                throw new \RuntimeException("So du Gems khong du. Can {$gems} Gems, hien co {$wallet->balance} Gems.");
            }

            $wallet->decrement('balance', $gems);

            return GemTransaction::create([
                'user_id' => $user->id,
                'gem_wallet_id' => $wallet->id,
                'type' => 'payment',
                'amount' => -$gems,
                'balance_after' => $wallet->balance,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => $desc,
                'status' => 'completed',
            ]);
        });
    }

    public function refund(GemTransaction $originalTx): GemTransaction
    {
        if ($originalTx->status !== 'completed' || $originalTx->type !== 'payment') {
            throw new \RuntimeException('Chi co the hoan tien giao dich thanh toan da hoan tat.');
        }

        return DB::transaction(function () use ($originalTx) {
            $wallet = GemWallet::where('id', $originalTx->gem_wallet_id)->lockForUpdate()->first();
            $refundAmount = abs($originalTx->amount);
            $wallet->increment('balance', $refundAmount);

            return GemTransaction::create([
                'user_id' => $originalTx->user_id,
                'gem_wallet_id' => $wallet->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'balance_after' => $wallet->balance,
                'reference_type' => $originalTx->reference_type,
                'reference_id' => $originalTx->reference_id,
                'description' => "Hoan tien: {$originalTx->description}",
                'metadata' => ['original_transaction_id' => $originalTx->id],
                'status' => 'completed',
            ]);
        });
    }
}
