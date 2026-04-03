<?php

namespace App\Services;

use App\Models\GemTransaction;
use Illuminate\Support\Facades\Log;

class SepayService
{
    public function buildQrUrl(int $amountVnd, string $description): string
    {
        $params = http_build_query([
            'accountNumber' => config('gems.sepay.account_number'),
            'bankCode' => config('gems.sepay.bank_code'),
            'amount' => $amountVnd,
            'description' => $description,
        ]);

        return "https://qr.sepay.vn/img?{$params}";
    }

    public function handleWebhook(array $payload): void
    {
        if (!$this->isValidPayload($payload)) {
            Log::warning('SePay webhook: invalid payload', $payload);
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

        app(GemWalletService::class)->confirmTopUp($transaction);
        Log::info('SePay webhook: top-up confirmed', ['tx_id' => $txId, 'gems' => $transaction->amount]);
    }

    public function isValidPayload(array $payload): bool
    {
        return isset($payload['id'], $payload['content'], $payload['transferAmount'])
            && ($payload['transferType'] ?? '') === 'in';
    }
}
