<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GemTransaction;
use App\Services\GemWalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GemTopupController extends Controller
{
    public function __construct(
        private GemWalletService $walletService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->input('status');

        $query = GemTransaction::where('type', 'top_up')
            ->with('user')
            ->latest();

        if ($status && in_array($status, ['pending', 'completed', 'cancelled'])) {
            $query->where('status', $status);
        }

        $transactions = $query->paginate(20)->withQueryString();

        $pendingCount = GemTransaction::where('type', 'top_up')->pending()->count();
        $approvedToday = GemTransaction::where('type', 'top_up')
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->count();
        $totalGemsToday = GemTransaction::where('type', 'top_up')
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->sum('amount');

        return view('admin.gem-topups.index', compact(
            'transactions', 'status', 'pendingCount', 'approvedToday', 'totalGemsToday'
        ));
    }

    public function approve(GemTransaction $transaction): RedirectResponse
    {
        try {
            $confirmed = $this->walletService->confirmTopUp($transaction);
            if (!$confirmed) {
                return back()->with('error', 'Giao dịch đã được xử lý trước đó.');
            }
            return redirect()
                ->route('admin.gem-topups.index')
                ->with('success', "Đã duyệt nạp {$transaction->amount} Gems cho {$transaction->user->name}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Duyệt thất bại: ' . $e->getMessage());
        }
    }

    public function reject(GemTransaction $transaction): RedirectResponse
    {
        try {
            $cancelled = $this->walletService->cancelTopUp($transaction);
            if (!$cancelled) {
                return back()->with('error', 'Giao dịch đã được xử lý trước đó.');
            }
            return redirect()
                ->route('admin.gem-topups.index')
                ->with('success', 'Đã từ chối yêu cầu nạp Gems.');
        } catch (\Exception $e) {
            return back()->with('error', 'Từ chối thất bại: ' . $e->getMessage());
        }
    }
}
