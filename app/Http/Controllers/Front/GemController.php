<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\GemWalletService;

class GemController extends Controller
{
    public function index(GemWalletService $gemWalletService)
    {
        $user = auth()->user();
        $wallet = $gemWalletService->getOrCreateWallet($user);
        $transactions = $user->gemTransactions()
            ->latest()
            ->paginate(15);

        return view('front.gems.index', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'exchangeRate' => config('gems.exchange_rate'),
            'minTopup' => config('gems.min_topup_vnd'),
            'maxTopup' => config('gems.max_topup_vnd'),
        ]);
    }
}
