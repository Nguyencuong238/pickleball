<?php

namespace App\Services;

use App\Models\GemTransaction;
use App\Models\User;

class GemCashbackService
{
    public function award(GemTransaction $transaction): void
    {
        if ($transaction->type !== 'payment' || $transaction->status !== 'completed') {
            return;
        }

        $percent = config('gems.cashback_percent');
        $points = (int) (abs($transaction->amount) * $percent / 100);

        if ($points <= 0) {
            return;
        }

        $user = User::find($transaction->user_id);
        if (!$user) {
            return;
        }

        $user->addPoints($points, 'gems_cashback', "Hoan diem tu thanh toan Gems", [
            'gem_transaction_id' => $transaction->id,
            'gems_spent' => abs($transaction->amount),
            'cashback_percent' => $percent,
        ]);
    }
}
