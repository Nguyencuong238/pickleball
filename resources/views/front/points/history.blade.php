@extends('layouts.front')

@section('title', 'Lịch Sử Điểm')

@section('css')
<style>
    .history-header {
        background: linear-gradient(135deg, #006646 0%, #52c98c 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        color: white;
    }
    .balance-display {
        font-size: 2rem;
        font-weight: 700;
    }
    .transaction-card {
        border-radius: 12px;
        overflow: hidden;
    }
    .transaction-row {
        transition: background 0.2s;
    }
    .transaction-row:hover {
        background: #f8f9fa;
    }
    .points-positive {
        color: #198754;
        font-weight: 600;
    }
    .points-negative {
        color: #dc3545;
        font-weight: 600;
    }
    .task-code {
        font-size: 0.75rem;
        color: #6c757d;
        font-family: monospace;
    }
</style>
@endsection

@section('content')
<div class="container py-4 page-content">
    {{-- Header with Balance --}}
    <div class="history-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 text-white">Lịch Sử Điểm</h1>
                <p class="mb-0 opacity-75">Xem chi tiết các giao dịch điểm của bạn</p>
            </div>
            <div class="text-end">
                <div class="balance-display">{{ number_format($balance) }}</div>
                <small class="opacity-75">Điểm hiện tại</small>
            </div>
        </div>
    </div>

    {{-- Back Link --}}
    <div class="mb-3">
        <a href="{{ route('user.points.index') }}" class="btn btn-outline-primary btn-sm">← Quay lại Kiếm Điểm</a>
    </div>

    {{-- Transactions Table --}}
    <div class="card transaction-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ngày</th>
                            <th>Mô Tả</th>
                            <th>Loại</th>
                            <th class="text-end">Điểm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="transaction-row">
                                <td>
                                    <span>{{ $tx->created_at->format('d/m/Y') }}</span>
                                    <br><small class="text-muted">{{ $tx->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <span>{{ $tx->description }}</span>
                                    @if(isset($tx->metadata['task_code']))
                                        <br><span class="task-code">{{ $tx->metadata['task_code'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $tx->isPositive() ? 'bg-success' : 'bg-danger' }}">
                                        {{ $tx->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="text-end {{ $tx->isPositive() ? 'points-positive' : 'points-negative' }}">
                                    {{ $tx->getFormattedPoints() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    📭 Chưa có giao dịch nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
