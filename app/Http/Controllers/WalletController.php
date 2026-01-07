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
     * Show transaction history with detailed stats
     */
    public function history()
    {
        $user = auth()->user();
        $transactions = $user->pointTransactions()->latest()->paginate(20);

        // Calculate statistics
        $totalPoints = $user->getPoints();
        $earnedPoints = $user->pointTransactions()
            ->where('points', '>', 0)
            ->sum('points');
        $usedPoints = abs($user->pointTransactions()
            ->where('points', '<', 0)
            ->sum('points'));
        $referralPoints = $user->pointTransactions()
            ->where('type', 'referral')
            ->sum('points');

        return view('user.wallet.history', compact(
            'transactions',
            'totalPoints',
            'earnedPoints',
            'usedPoints',
            'referralPoints'
        ));
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
