<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\GemWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GemController extends Controller
{
    public function __construct(private GemWalletService $gemWalletService)
    {
    }

    public function index()
    {
        $user = auth()->user();
        $wallet = $this->gemWalletService->getOrCreateWallet($user);
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

    public function topUp(Request $request): JsonResponse
    {
        $request->validate([
            'amount_vnd' => 'required|integer|min:' . config('gems.min_topup_vnd') . '|max:' . config('gems.max_topup_vnd'),
        ]);

        try {
            $result = $this->gemWalletService->createTopUpRequest(
                auth()->user(),
                (int) $request->amount_vnd
            );

            return response()->json([
                'success' => true,
                'transaction_id' => $result['transaction']->id,
                'qr_url' => $result['qr_url'],
                'amount_vnd' => $result['amount_vnd'],
                'gems' => $result['gems'],
                'transfer_content' => $result['transfer_content'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function transactionStatus(int $transaction): JsonResponse
    {
        $tx = \App\Models\GemTransaction::where('id', $transaction)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'transaction' => ['status' => $tx->status],
        ]);
    }
}
