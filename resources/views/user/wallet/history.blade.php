@extends('layouts.front')

@section('content')
<style>
    .wallet-container {
        padding: clamp(20px, 3vw, 40px);
        max-width: 900px;
        margin: 0 auto;
    }

    .wallet-header {
        margin-bottom: clamp(30px, 5vw, 50px);
    }

    .wallet-header h2 {
        font-size: clamp(1.8rem, 5vw, 2.5rem);
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }

    .wallet-header p {
        color: #6b7280;
        font-size: clamp(0.9rem, 2vw, 1rem);
    }

    .wallet-card {
        background: white;
        border-radius: 15px;
        padding: clamp(20px, 3vw, 30px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .wallet-card h4 {
        font-size: 1.2rem;
        color: #1f2937;
        margin-bottom: 20px;
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 1px solid #f3f4f6;
    }

    .points-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .point-box {
        background: linear-gradient(135deg, #f0fffe 0%, #d1fae5 100%);
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
    }

    .point-number {
        font-size: 2rem;
        font-weight: 700;
        color: #00D9B5;
        margin-bottom: 8px;
    }

    .point-label {
        font-size: 0.9rem;
        color: #065f46;
        font-weight: 500;
    }

    .transaction-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .transaction-table thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .transaction-table th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }

    .transaction-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        color: #6b7280;
    }

    .transaction-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .points-positive {
        color: #10b981;
        font-weight: 600;
    }

    .points-negative {
        color: #ef4444;
        font-weight: 600;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .badge-referral {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-earn {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-use {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-refund {
        background: #fef08a;
        color: #78350f;
    }

    .badge-admin {
        background: #f3e8ff;
        color: #6b21a8;
    }

    .transaction-date {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .empty-state {
        padding: 40px 20px;
        text-align: center;
        color: #6b7280;
    }

    .empty-state-icon {
        font-size: 3rem;
        margin-bottom: 10px;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
    }

    .pagination-link {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        color: #00D9B5;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .pagination-link:hover {
        background: #00D9B5;
        color: white;
    }

    .pagination-link.active {
        background: #00D9B5;
        color: white;
        border-color: #00D9B5;
    }

    @media (max-width: 768px) {
        .transaction-table {
            font-size: 0.85rem;
        }

        .transaction-table th,
        .transaction-table td {
            padding: 8px 12px;
        }

        .points-summary {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="wallet-container">
    <div class="wallet-header">
        <h2>💰 Lịch Sử Điểm</h2>
        <p>Xem chi tiết tất cả các giao dịch điểm của bạn</p>
    </div>

    {{-- Points Summary --}}
    <div class="wallet-card">
        <h4>Tổng Hợp Điểm</h4>
        <div class="points-summary">
            <div class="point-box">
                <div class="point-number">{{ $totalPoints }}</div>
                <div class="point-label">Điểm Hiện Tại</div>
            </div>
            <div class="point-box">
                <div class="point-number">{{ $earnedPoints }}</div>
                <div class="point-label">Điểm Kiếm Được</div>
            </div>
            <div class="point-box">
                <div class="point-number">{{ $usedPoints }}</div>
                <div class="point-label">Điểm Đã Dùng</div>
            </div>
            <div class="point-box">
                <div class="point-number">{{ $referralPoints }}</div>
                <div class="point-label">Từ Referral</div>
            </div>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="wallet-card">
        <h4>Lịch Sử Giao Dịch</h4>
        
        @if($transactions && $transactions->count() > 0)
            <div style="overflow-x: auto;">
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>Loại</th>
                            <th>Mô Tả</th>
                            <th>Điểm</th>
                            <th>Ngày</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <span class="badge badge-{{ $transaction->type }}">
                                    {{ $transaction->getTypeLabel() }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $transaction->description }}</div>
                                @if($transaction->metadata && isset($transaction->metadata['referred_user_id']))
                                    <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 4px;">
                                        Người dùng: #{{ $transaction->metadata['referred_user_id'] }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($transaction->points > 0)
                                    <span class="points-positive">+{{ number_format($transaction->points) }}</span>
                                @else
                                    <span class="points-negative">{{ number_format($transaction->points) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="transaction-date">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($transactions->hasPages())
                <div class="pagination-container">
                    {{-- Previous Page Link --}}
                    @if ($transactions->onFirstPage())
                        <span class="pagination-link" style="opacity: 0.5; cursor: not-allowed;">← Trước</span>
                    @else
                        <a href="{{ $transactions->previousPageUrl() }}" class="pagination-link">← Trước</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($transactions->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                        @if ($page == $transactions->currentPage())
                            <span class="pagination-link active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($transactions->hasMorePages())
                        <a href="{{ $transactions->nextPageUrl() }}" class="pagination-link">Tiếp →</a>
                    @else
                        <span class="pagination-link" style="opacity: 0.5; cursor: not-allowed;">Tiếp →</span>
                    @endif
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p>Bạn chưa có giao dịch nào. Hãy kiếm điểm bằng cách giới thiệu bạn bè hoặc tham gia các hoạt động!</p>
            </div>
        @endif
    </div>
</div>

@endsection
