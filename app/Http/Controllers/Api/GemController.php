<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GemTransaction;
use App\Services\GemWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GemController extends Controller
{
    public function __construct(private GemWalletService $gemWalletService)
    {
    }

    public function wallet(): JsonResponse
    {
        $user = auth()->user();
        $wallet = $this->gemWalletService->getOrCreateWallet($user);
        $recentTx = $user->gemTransactions()
            ->completed()
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'balance' => $wallet->balance,
            'balance_vnd' => $wallet->balance * config('gems.exchange_rate'),
            'exchange_rate' => config('gems.exchange_rate'),
            'recent_transactions' => $recentTx,
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

    public function transactions(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|in:top_up,payment,refund,admin_adjust',
            'status' => 'nullable|in:pending,completed,failed,cancelled',
        ]);

        $query = auth()->user()->gemTransactions()->latest();

        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->paginate(15);

        return response()->json([
            'success' => true,
            'balance' => $this->gemWalletService->getBalance(auth()->user()),
            'transactions' => $transactions,
        ]);
    }

    public function transactionDetail(int $transactionId): JsonResponse
    {
        $transaction = GemTransaction::where('id', $transactionId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'transaction' => $transaction,
        ]);
    }
}
