<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Show user's wallet with point transactions
     */
    public function index()
    {
        $user = auth()->user();
        $wallet = $user->getOrCreateWallet();
        $transactions = $user->pointTransactions()->latest()->paginate(20);

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    /**
     * Show transaction details
     */
    public function show($id)
    {
        $transaction = auth()->user()->pointTransactions()->findOrFail($id);
        return view('wallet.show', compact('transaction'));
    }
}
