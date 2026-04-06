<?php

namespace App\Services;

use App\Models\GemTransaction;
use Illuminate\Support\Facades\Log;

class SepayService
{
    /**
     * Build QR URL and return bank info used for the transfer.
     * Single source of truth: modal displays whatever config generated the QR.
     */
    public function buildPaymentInfo(int $amountVnd, string $description): array
    {
        if (config('gems.sepay.account_number')) {
            $accountNumber = config('gems.sepay.account_number');
            $bankCode = config('gems.sepay.bank_code');
            $params = http_build_query([
                'acc' => $accountNumber,
                'bank' => $bankCode,
                'amount' => $amountVnd,
                'des' => $description,
            ]);

            return [
                'qr_url' => "https://qr.sepay.vn/img?{$params}",
                'bank_name' => $bankCode,
                'account_number' => $accountNumber,
                'account_name' => config('gems.bank.account_name', ''),
            ];
        }

        $bin = config('gems.bank.bin');
        $accountNumber = config('gems.bank.account_number');
        $params = http_build_query([
            'amount' => $amountVnd,
            'addInfo' => $description,
            'accountName' => config('gems.bank.account_name'),
        ]);

        return [
            'qr_url' => "https://img.vietqr.io/image/{$bin}-{$accountNumber}-compact.png?{$params}",
            'bank_name' => config('gems.bank.name', ''),
            'account_number' => $accountNumber,
            'account_name' => config('gems.bank.account_name', ''),
        ];
    }

    public function handleWebhook(array $payload): void
    {
        if (!$this->isValidPayload($payload)) {
            Log::warning('SePay webhook: invalid payload', $payload);
            return;
        }

        $sepayId = (int) $payload['id'];

        // Idempotency: skip if this SePay transaction was already processed
        $alreadyProcessed = GemTransaction::where('type', 'top_up')
            ->where('status', 'completed')
            ->whereJsonContains('metadata->sepay_transaction_id', $sepayId)
            ->exists();

        if ($alreadyProcessed) {
            Log::info('SePay webhook: duplicate ignored', ['sepay_id' => $sepayId]);
            return;
        }

        $content = $payload['content'] ?? '';
        if (!preg_match('/GEMS(\d+)T(\d+)/', $content, $matches)) {
            Log::info('SePay webhook: no GEMS pattern in content', ['content' => $content]);
            return;
        }

        $userId = (int) $matches[1];
        $txId = (int) $matches[2];

        $transaction = GemTransaction::where('id', $txId)
            ->where('user_id', $userId)
            ->where('type', 'top_up')
            ->pending()
            ->first();

        if (!$transaction) {
            Log::warning('SePay webhook: no matching pending transaction', [
                'user_id' => $userId,
                'tx_id' => $txId,
            ]);
            return;
        }

        $exchangeRate = config('gems.exchange_rate');
        $expectedVnd = $transaction->amount * $exchangeRate;
        $receivedVnd = (int) ($payload['transferAmount'] ?? 0);

        if ($receivedVnd < $expectedVnd) {
            Log::warning('SePay webhook: amount mismatch', [
                'expected' => $expectedVnd,
                'received' => $receivedVnd,
                'tx_id' => $txId,
            ]);
            return;
        }

        // Store SePay transaction ID in metadata for audit trail and dedup
        $metadata = $transaction->metadata ?? [];
        $metadata['sepay_transaction_id'] = $sepayId;
        $metadata['sepay_reference_code'] = $payload['referenceCode'] ?? null;
        $metadata['sepay_gateway'] = $payload['gateway'] ?? null;
        $transaction->update(['metadata' => $metadata]);

        app(GemWalletService::class)->confirmTopUp($transaction);
        Log::info('SePay webhook: top-up confirmed', [
            'tx_id' => $txId,
            'gems' => $transaction->amount,
            'sepay_id' => $sepayId,
        ]);
    }

    public function isValidPayload(array $payload): bool
    {
        return isset($payload['id'], $payload['content'], $payload['transferAmount'])
            && ($payload['transferType'] ?? '') === 'in';
    }
}
