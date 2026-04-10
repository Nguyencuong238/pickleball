<?php

namespace App\Services;

use App\Exceptions\GemTransferException;
use App\Models\Booking;
use App\Models\GemTransaction;
use App\Models\GemWallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GemWalletService
{
    public function getOrCreateWallet(User $user): GemWallet
    {
        return $user->gemWallet ?? $user->gemWallet()->create(['balance' => 0, 'locked_balance' => 0]);
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
                "Số tiền nạp phải từ " . number_format($minVnd) . " đến " . number_format($maxVnd) . " VND."
            );
        }

        $exchangeRate = config('gems.exchange_rate');
        $gems = (int) ($amountVnd / $exchangeRate);
        $wallet = $this->getOrCreateWallet($user);

        $transaction = GemTransaction::create([
            'user_id' => $user->id,
            'gem_wallet_id' => $wallet->id,
            'type' => GemTransaction::TYPE_TOP_UP,
            'amount' => $gems,
            'balance_after' => $wallet->balance,
            'description' => "Nạp " . number_format($gems) . " Gems",
            'metadata' => ['amount_vnd' => $amountVnd, 'exchange_rate' => $exchangeRate],
            'status' => GemTransaction::STATUS_PENDING,
        ]);

        $sepayService = app(SepayService::class);
        $description = "GEMS{$user->id}T{$transaction->id}";
        $paymentInfo = $sepayService->buildPaymentInfo($amountVnd, $description);

        return [
            'transaction' => $transaction,
            'qr_url' => $paymentInfo['qr_url'],
            'bank_name' => $paymentInfo['bank_name'],
            'account_number' => $paymentInfo['account_number'],
            'account_name' => $paymentInfo['account_name'],
            'amount_vnd' => $amountVnd,
            'gems' => $gems,
            'transfer_content' => $description,
        ];
    }

    public function confirmTopUp(GemTransaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $tx = GemTransaction::where('id', $transaction->id)->lockForUpdate()->first();
            if ($tx->status !== GemTransaction::STATUS_PENDING || $tx->type !== GemTransaction::TYPE_TOP_UP) {
                return false;
            }

            $wallet = GemWallet::where('id', $tx->gem_wallet_id)->lockForUpdate()->first();
            $wallet->increment('balance', $tx->amount);

            $tx->update([
                'balance_after' => $wallet->balance,
                'status' => GemTransaction::STATUS_COMPLETED,
            ]);

            return true;
        });
    }

    public function cancelTopUp(GemTransaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $tx = GemTransaction::where('id', $transaction->id)->lockForUpdate()->first();
            if ($tx->status !== GemTransaction::STATUS_PENDING || $tx->type !== GemTransaction::TYPE_TOP_UP) {
                return false;
            }
            $tx->update(['status' => GemTransaction::STATUS_CANCELLED]);
            return true;
        });
    }

    /**
     * Chuyển Gems từ payer → payee với khóa escrow bên nhận.
     * Trả về [$debitTx, $creditTx] đã liên kết counterparty hai chiều.
     *
     * @throws GemTransferException
     */
    public function transfer(
        User $payer,
        User $payee,
        int $gems,
        string $refType,
        int $refId,
        string $desc,
        ?int $platformFeePercent = null
    ): array {
        // Tiền kiểm tra rẻ tiền trước khi mở transaction
        if ($gems <= 0) {
            throw GemTransferException::invalidAmount($gems);
        }
        if (!$payee) {
            throw GemTransferException::missingOwner();
        }
        if ($payer->id === $payee->id) {
            throw GemTransferException::selfPayment();
        }

        $feePercent = $platformFeePercent ?? (int) config('gems.platform_fee_percent', 0);
        $fee = intdiv($gems * $feePercent, 100);
        $netToPayee = $gems - $fee;

        // Đảm bảo ví tồn tại trước khi lock (tránh nested create trong transaction)
        $this->getOrCreateWallet($payer);
        $this->getOrCreateWallet($payee);

        $windowDays = (int) config('gems.refund_window_days', 1);
        $availableAt = now()->addDays($windowDays);

        // Chống trùng lặp: kiểm tra đã có payment completed cho (ref, payer) chưa
        $existingPayment = GemTransaction::where([
            'reference_type' => $refType,
            'reference_id' => $refId,
            'type' => GemTransaction::TYPE_PAYMENT,
            'user_id' => $payer->id,
            'status' => GemTransaction::STATUS_COMPLETED,
        ])->exists();

        if ($existingPayment) {
            throw GemTransferException::duplicateTransaction($refType, $refId);
        }

        return DB::transaction(function () use (
            $payer, $payee, $gems, $fee, $netToPayee,
            $refType, $refId, $desc, $availableAt
        ) {
                // Lock hai ví theo thứ tự user_id ASC để tránh deadlock
                [$payerWallet, $payeeWallet] = $this->lockWalletsOrdered($payer->id, $payee->id);

                // Kiểm tra số dư khả dụng của payer trong lock
                $spendable = max(0, (int) $payerWallet->balance - (int) $payerWallet->locked_balance);
                if ($spendable < $gems) {
                    throw GemTransferException::insufficientSpendable($spendable, $gems);
                }

                // Trừ toàn bộ $gems từ payer (kể cả phần phí bị đốt)
                $payerWallet->decrement('balance', $gems);
                $payerWallet->refresh();

                // Cộng phần thực nhận vào payee và đồng thời khóa
                $payeeWallet->increment('balance', $netToPayee);
                $payeeWallet->increment('locked_balance', $netToPayee);
                $payeeWallet->refresh();

                // Ghi dòng nợ (payer side)
                $debitTx = GemTransaction::create([
                    'user_id' => $payer->id,
                    'gem_wallet_id' => $payerWallet->id,
                    'counterparty_user_id' => $payee->id,
                    'type' => GemTransaction::TYPE_PAYMENT,
                    'amount' => -$gems,
                    'balance_after' => $payerWallet->balance,
                    'platform_fee' => $fee,
                    'reference_type' => $refType,
                    'reference_id' => $refId,
                    'description' => $desc,
                    'status' => GemTransaction::STATUS_COMPLETED,
                ]);

                // Ghi dòng có (payee side), liên kết về dòng nợ
                $creditTx = GemTransaction::create([
                    'user_id' => $payee->id,
                    'gem_wallet_id' => $payeeWallet->id,
                    'counterparty_user_id' => $payer->id,
                    'counterparty_transaction_id' => $debitTx->id,
                    'type' => GemTransaction::TYPE_RECEIPT,
                    'amount' => $netToPayee,
                    'balance_after' => $payeeWallet->balance,
                    'platform_fee' => 0,
                    'reference_type' => $refType,
                    'reference_id' => $refId,
                    'description' => $desc,
                    'status' => GemTransaction::STATUS_COMPLETED,
                    'available_at' => $availableAt,
                ]);

                // Liên kết ngược dòng nợ → dòng có (1 UPDATE)
                $debitTx->update(['counterparty_transaction_id' => $creditTx->id]);

                return [$debitTx->fresh(), $creditTx];
            });
    }

    /**
     * Lock hai ví theo thứ tự user_id ASC nhằm tránh deadlock.
     * Trả về [payerWallet, payeeWallet] đúng thứ tự tham số đầu vào.
     */
    private function lockWalletsOrdered(int $payerId, int $payeeId): array
    {
        $ids = [$payerId, $payeeId];
        sort($ids);

        $wallets = GemWallet::whereIn('user_id', $ids)
            ->orderBy('user_id')
            ->lockForUpdate()
            ->get()
            ->keyBy('user_id');

        if (!$wallets->has($payerId) || !$wallets->has($payeeId)) {
            throw GemTransferException::missingOwner();
        }

        return [$wallets->get($payerId), $wallets->get($payeeId)];
    }

    /**
     * API cũ (mô hình đốt) — giữ lại làm nhánh fallback cho feature flag OFF.
     * Bản gọi từ controllers: sẽ rẽ nhánh theo config('gems.transfer_enabled') trong phase 04.
     */
    public function payForBooking(User $user, Booking $booking, int $totalPrice): array
    {
        if (config('gems.transfer_enabled')) {
            [$debitTx, ] = app(GemPaymentProcessor::class)->pay($booking, $user);
            $cashbackPoints = (int) (abs($debitTx->amount) * config('gems.cashback_percent') / 100);
            return [$debitTx, $cashbackPoints];
        }
        return $this->legacyPayForBooking($user, $booking, $totalPrice);
    }

    private function legacyPayForBooking(User $user, Booking $booking, int $totalPrice): array
    {
        $gemsNeeded = (int) ceil($totalPrice / config('gems.exchange_rate'));

        $gemTx = $this->deduct(
            $user, $gemsNeeded, Booking::class, $booking->id,
            "Đặt sân - {$booking->booking_code}"
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

            $spendable = max(0, (int) $wallet->balance - (int) $wallet->locked_balance);
            if ($spendable < $gems) {
                throw new \RuntimeException("Số dư Gems không đủ. Cần {$gems} Gems, hiện có {$spendable} Gems khả dụng.");
            }

            $wallet->decrement('balance', $gems);

            return GemTransaction::create([
                'user_id' => $user->id,
                'gem_wallet_id' => $wallet->id,
                'type' => GemTransaction::TYPE_PAYMENT,
                'amount' => -$gems,
                'balance_after' => $wallet->balance,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => $desc,
                'status' => GemTransaction::STATUS_COMPLETED,
            ]);
        });
    }

    /**
     * Hoàn Gems cho payer và thu hồi (clawback) từ ví đang khóa của payee.
     * Chỉ chạy trong cửa sổ hoàn tiền — bị khóa cứng nếu dòng credit đã released.
     *
     * @return array [$refundTx, $clawbackTx]
     * @throws GemTransferException
     */
    public function refund(GemTransaction $originalDebitTx, ?string $reason = null): array
    {
        if ($originalDebitTx->type !== GemTransaction::TYPE_PAYMENT) {
            throw GemTransferException::refundTargetNotFound();
        }
        if ($originalDebitTx->status === GemTransaction::STATUS_REFUNDED) {
            throw GemTransferException::alreadyRefunded();
        }
        if (!$originalDebitTx->counterparty_transaction_id) {
            // Luồng legacy (đốt): hoàn trực tiếp cho payer, không có clawback
            return $this->legacyRefund($originalDebitTx, $reason);
        }

        $creditTx = GemTransaction::find($originalDebitTx->counterparty_transaction_id);
        if (!$creditTx) {
            throw GemTransferException::refundTargetNotFound();
        }
        if ($creditTx->released_at !== null) {
            throw GemTransferException::refundOutsideWindow();
        }
        if ($creditTx->status === GemTransaction::STATUS_REFUNDED) {
            throw GemTransferException::alreadyRefunded();
        }

        $payerId = (int) $originalDebitTx->user_id;
        $payeeId = (int) $creditTx->user_id;

        return DB::transaction(function () use ($originalDebitTx, $creditTx, $payerId, $payeeId, $reason) {
            // Lock lại hai ví theo thứ tự
            [$payerWallet, $payeeWallet] = $this->lockWalletsOrdered($payerId, $payeeId);

            // Re-check inside lock chống race với release job
            $freshCredit = GemTransaction::where('id', $creditTx->id)->lockForUpdate()->first();
            if ($freshCredit->released_at !== null) {
                throw GemTransferException::refundOutsideWindow();
            }
            if ($freshCredit->status === GemTransaction::STATUS_REFUNDED) {
                throw GemTransferException::alreadyRefunded();
            }

            $freshDebit = GemTransaction::where('id', $originalDebitTx->id)->lockForUpdate()->first();
            if ($freshDebit->status === GemTransaction::STATUS_REFUNDED) {
                throw GemTransferException::alreadyRefunded();
            }

            // Bảo toàn cung: payer được hoàn đúng số đã chuyển đi cho payee (không bao gồm phí bị đốt).
            // Phí là khoản không hoàn lại — payer mất phần này làm phí dịch vụ.
            $clawbackAmount = (int) $freshCredit->amount; // net đã cộng bên payee
            $refundAmount = $clawbackAmount;

            $payerWallet->increment('balance', $refundAmount);
            $payerWallet->refresh();

            // Thu hồi từ payee: giảm cả balance và locked_balance
            $payeeWallet->decrement('balance', $clawbackAmount);
            $payeeWallet->decrement('locked_balance', $clawbackAmount);
            $payeeWallet->refresh();

            // Đánh dấu creditTx đã released + refunded
            $freshCredit->update([
                'released_at' => now(),
                'status' => GemTransaction::STATUS_REFUNDED,
            ]);
            $freshDebit->update(['status' => GemTransaction::STATUS_REFUNDED]);

            // Audit rows
            $refundTx = GemTransaction::create([
                'user_id' => $payerId,
                'gem_wallet_id' => $payerWallet->id,
                'counterparty_user_id' => $payeeId,
                'type' => GemTransaction::TYPE_REFUND,
                'amount' => $refundAmount,
                'balance_after' => $payerWallet->balance,
                'reference_type' => $originalDebitTx->reference_type,
                'reference_id' => $originalDebitTx->reference_id,
                'description' => "Hoàn Gems: {$originalDebitTx->description}" . ($reason ? " ({$reason})" : ''),
                'metadata' => ['original_transaction_id' => $originalDebitTx->id, 'reason' => $reason],
                'status' => GemTransaction::STATUS_COMPLETED,
            ]);

            $clawbackTx = GemTransaction::create([
                'user_id' => $payeeId,
                'gem_wallet_id' => $payeeWallet->id,
                'counterparty_user_id' => $payerId,
                'counterparty_transaction_id' => $refundTx->id,
                'type' => GemTransaction::TYPE_REFUND_CLAWBACK,
                'amount' => -$clawbackAmount,
                'balance_after' => $payeeWallet->balance,
                'reference_type' => $creditTx->reference_type,
                'reference_id' => $creditTx->reference_id,
                'description' => "Thu hồi Gems cho hoàn giao dịch: {$creditTx->description}",
                'metadata' => ['original_credit_transaction_id' => $creditTx->id, 'reason' => $reason],
                'status' => GemTransaction::STATUS_COMPLETED,
            ]);

            $refundTx->update(['counterparty_transaction_id' => $clawbackTx->id]);

            return [$refundTx->fresh(), $clawbackTx];
        });
    }

    /**
     * Hoàn Gems kiểu legacy (mô hình đốt, không có đối ứng).
     * Chỉ cộng lại số dư cho payer — không có clawback.
     *
     * @return array [$refundTx, null]
     */
    private function legacyRefund(GemTransaction $originalDebitTx, ?string $reason = null): array
    {
        return DB::transaction(function () use ($originalDebitTx, $reason) {
            $wallet = GemWallet::where('id', $originalDebitTx->gem_wallet_id)->lockForUpdate()->first();
            $refundAmount = abs((int) $originalDebitTx->amount);
            $wallet->increment('balance', $refundAmount);
            $wallet->refresh();

            $refundTx = GemTransaction::create([
                'user_id' => $originalDebitTx->user_id,
                'gem_wallet_id' => $wallet->id,
                'type' => GemTransaction::TYPE_REFUND,
                'amount' => $refundAmount,
                'balance_after' => $wallet->balance,
                'reference_type' => $originalDebitTx->reference_type,
                'reference_id' => $originalDebitTx->reference_id,
                'description' => "Hoàn Gems: {$originalDebitTx->description}" . ($reason ? " ({$reason})" : ''),
                'metadata' => ['original_transaction_id' => $originalDebitTx->id, 'reason' => $reason, 'legacy' => true],
                'status' => GemTransaction::STATUS_COMPLETED,
            ]);

            $originalDebitTx->update(['status' => GemTransaction::STATUS_REFUNDED]);

            return [$refundTx, null];
        });
    }

    /**
     * Giải phóng một receipt đã đáo hạn: giảm locked_balance, set released_at.
     * An toàn với race condition: loser sẽ trả về false mà không throw.
     */
    public function releaseReceipt(GemTransaction $receiptTx): bool
    {
        if ($receiptTx->type !== GemTransaction::TYPE_RECEIPT) {
            return false;
        }

        return DB::transaction(function () use ($receiptTx) {
            $fresh = GemTransaction::where('id', $receiptTx->id)->lockForUpdate()->first();
            if (!$fresh || $fresh->released_at !== null) {
                return false;
            }
            if ($fresh->status === GemTransaction::STATUS_REFUNDED) {
                return false;
            }
            if ($fresh->available_at === null || $fresh->available_at->isFuture()) {
                return false;
            }

            $wallet = GemWallet::where('id', $fresh->gem_wallet_id)->lockForUpdate()->first();
            if (!$wallet) {
                return false;
            }

            $amount = (int) $fresh->amount;
            // Chỉ giảm locked_balance — balance không đổi (Gems vẫn thuộc payee)
            $newLocked = max(0, (int) $wallet->locked_balance - $amount);
            $wallet->update(['locked_balance' => $newLocked]);

            $fresh->update(['released_at' => now()]);

            return true;
        });
    }
}
