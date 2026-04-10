<?php

namespace App\Services;

use App\Contracts\Payable;
use App\Exceptions\GemTransferException;
use App\Models\GemTransaction;
use App\Models\User;

/**
 * Điều phối thanh toán Gems cho mọi thực thể Payable.
 * Tạo một chỗ duy nhất xử lý: resolve payer/payee → transfer → confirm domain → cashback.
 */
class GemPaymentProcessor
{
    public function __construct(
        private GemWalletService $wallet,
        private GemCashbackService $cashback,
    ) {
    }

    /**
     * Thanh toán Gems cho một Payable.
     * Nếu caller không truyền $payer, sẽ thử lấy từ $item->getPayer().
     *
     * @return array [$debitTx, $creditTx]
     */
    public function pay(Payable $item, ?User $payer = null): array
    {
        $payer ??= $item->getPayer();
        $payee = $item->getPayee();

        if (!$payer) {
            throw GemTransferException::missingPayer();
        }
        if (!$payee) {
            throw GemTransferException::missingOwner();
        }
        if ($payer->id === $payee->id) {
            throw GemTransferException::selfPayment();
        }

        $gems = (int) ceil($item->getPayableAmountVnd() / (int) config('gems.exchange_rate'));

        [$debitTx, $creditTx] = $this->wallet->transfer(
            $payer,
            $payee,
            $gems,
            $item->getPayableReferenceType(),
            $item->getPayableReferenceId(),
            $item->getPayableDescription(),
        );

        $item->markPaidWithGems($debitTx);
        $this->cashback->award($debitTx);

        return [$debitTx, $creditTx];
    }

    /**
     * Hoàn Gems cho một Payable bằng cách định vị lại giao dịch payment
     * thông qua unique index (reference_type, reference_id, type, user_id).
     *
     * @return array [$refundTx, $clawbackTxOrNull]
     */
    public function refundFor(Payable $item, ?User $payer = null): array
    {
        $payer ??= $item->getPayer();
        if (!$payer) {
            throw GemTransferException::missingPayer();
        }

        $debitTx = GemTransaction::where([
            'reference_type' => $item->getPayableReferenceType(),
            'reference_id'   => $item->getPayableReferenceId(),
            'type'           => GemTransaction::TYPE_PAYMENT,
            'user_id'        => $payer->id,
        ])
            ->whereIn('status', [GemTransaction::STATUS_COMPLETED])
            ->firstOrFail();

        return $this->wallet->refund($debitTx);
    }
}
