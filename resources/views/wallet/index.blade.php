@extends('layouts.front')

@section('seo')
    <title>Ví điểm - OnePickleball</title>
    <meta name="description" content="Quản lý ví điểm của bạn trên OnePickleball">
@endsection

@section('content')
<div class="wallet-container" style="margin-top: 100px">
    <!-- Header -->
    <div class="wallet-header">
        <div class="container">
            <h1 class="wallet-title">💰 Ví Điểm Của Bạn</h1>
            <p class="wallet-subtitle">Quản lý và theo dõi điểm của bạn</p>
        </div>
    </div>

    <div class="container py-5">
        <!-- Balance Card -->
        <div class="wallet-balance-card">
            <div class="balance-content">
                <div class="balance-label">Tổng điểm hiện có</div>
                <div class="balance-amount">{{ $wallet->getFormattedPoints() }}</div>
                <p class="balance-info">Điểm có thể dùng để đăng ký giải đấu, hoán đổi voucher và mua sản phẩm đặc biệt</p>
            </div>
            <div class="balance-icon">
                🎯
            </div>
        </div>

        <div class="wallet-content mt-5">
            <!-- Main Content -->
            <div class="wallet-main">
                <!-- Transactions History -->
                <div class="transactions-section">
                    <div class="section-header">
                        <h2 class="section-title">Lịch sử giao dịch</h2>
                        <span class="transaction-count">{{ $transactions->total() }} giao dịch</span>
                    </div>

                    @if($transactions->count() > 0)
                        <div class="transactions-list">
                            @foreach($transactions as $transaction)
                                <a href="{{ route('user.wallet.show', $transaction->id) }}" class="transaction-card">
                                    <div class="transaction-left">
                                        <div class="transaction-icon {{ $transaction->isPositive() ? 'positive' : 'negative' }}">
                                            @if($transaction->type === 'earn')
                                                ⬆️
                                            @elseif($transaction->type === 'use')
                                                ⬇️
                                            @elseif($transaction->type === 'refund')
                                                🔄
                                            @elseif($transaction->type === 'admin')
                                                👨‍💼
                                            @else
                                                📝
                                            @endif
                                        </div>
                                        <div class="transaction-details">
                                            <div class="transaction-title">
                                                {{ $transaction->getTypeLabel() }}
                                            </div>
                                            @if($transaction->description)
                                                <div class="transaction-description">
                                                    {{ $transaction->description }}
                                                </div>
                                            @endif
                                            <div class="transaction-date">
                                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="transaction-right">
                                        <span class="transaction-amount {{ $transaction->isPositive() ? 'positive' : 'negative' }}">
                                            {{ $transaction->getFormattedPoints() }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($transactions->hasPages())
                            <div class="pagination-wrapper mt-4">
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">🎁</div>
                            <h3>Chưa có giao dịch nào</h3>
                            <p>Kiếm điểm bằng cách tham gia các hoạt động trên nền tảng</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="wallet-sidebar">
                <!-- How to Earn Points -->
                <div class="info-card">
                    <h3 class="info-title">📈 Cách kiếm điểm</h3>
                    <ul class="info-list">
                        <li>
                            <span class="icon">🏆</span>
                            <span>Tham gia giải đấu</span>
                        </li>
                        <li>
                            <span class="icon">⭐</span>
                            <span>Viết review sân & trọng tài</span>
                        </li>
                        <li>
                            <span class="icon">👥</span>
                            <span>Giới thiệu bạn bè</span>
                        </li>
                        <li>
                            <span class="icon">🎯</span>
                            <span>Hoàn thành thử thách</span>
                        </li>
                        <li>
                            <span class="icon">🌟</span>
                            <span>Hoạt động cộng đồng</span>
                        </li>
                    </ul>
                </div>

                <!-- How to Use Points -->
                <div class="info-card">
                    <h3 class="info-title">💎 Cách dùng điểm</h3>
                    <ul class="info-list">
                        <li>
                            <span class="icon">📝</span>
                            <span>Đăng ký giải đấu</span>
                        </li>
                        <li>
                            <span class="icon">🎟️</span>
                            <span>Hoán đổi voucher</span>
                        </li>
                        <li>
                            <span class="icon">🛍️</span>
                            <span>Mua sản phẩm đặc biệt</span>
                        </li>
                        <li>
                            <span class="icon">🎁</span>
                            <span>Nhân dịp đặc biệt</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

    .py-5 {
        padding-top: 3rem;
        padding-bottom: 3rem;
    }

    .mt-5 {
        margin-top: 3rem;
    }

    .mt-4 {
        margin-top: 1.5rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .wallet-container {
        background: #f8fafc;
        min-height: 100vh;
    }

    /* Header */
    .wallet-header {
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
    }

    .wallet-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .wallet-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }

    /* Balance Card */
    .wallet-balance-card {
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        border-radius: 16px;
        padding: 2.5rem;
        color: white;
        box-shadow: 0 20px 40px rgba(0, 217, 181, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        overflow: hidden;
        position: relative;
    }

    .wallet-balance-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        pointer-events: none;
    }

    .balance-content {
        position: relative;
        z-index: 1;
        flex: 1;
    }

    .balance-label {
        font-size: 0.95rem;
        opacity: 0.9;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .balance-amount {
        font-size: 3rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .balance-info {
        font-size: 0.9rem;
        opacity: 0.85;
        margin: 0;
    }

    .balance-icon {
        font-size: 6rem;
        opacity: 0.1;
        z-index: 0;
    }

    /* Wallet Content Layout */
    .wallet-content {
        display: flex;
        gap: 2rem;
        margin-top: 3rem;
    }

    .wallet-main {
        flex: 1;
        min-width: 0;
    }

    .wallet-sidebar {
        width: 320px;
        flex-shrink: 0;
    }

    /* Transactions Section */
    .transactions-section {
        background: white;
        border-radius: 12px;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        margin: 0;
        color: #1e293b;
    }

    .transaction-count {
        background: #e0f2fe;
        color: #0369a1;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Transactions List */
    .transactions-list {
        display: flex;
        flex-direction: column;
    }

    .transaction-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .transaction-card:last-child {
        border-bottom: none;
    }

    .transaction-card:hover {
        background: #f8fafc;
    }

    .transaction-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .transaction-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .transaction-icon.positive {
        background: #d1fae5;
    }

    .transaction-icon.negative {
        background: #fee2e2;
    }

    .transaction-details {
        flex: 1;
    }

    .transaction-title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }

    .transaction-description {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 0.25rem;
    }

    .transaction-date {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .transaction-right {
        text-align: right;
        flex-shrink: 0;
        margin-left: 1rem;
    }

    .transaction-amount {
        font-weight: 700;
        font-size: 1.1rem;
        display: block;
    }

    .transaction-amount.positive {
        color: #10b981;
    }

    .transaction-amount.negative {
        color: #ef4444;
    }

    /* Empty State */
    .empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
        color: #64748b;
    }

    .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    /* Info Cards */
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .info-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #1e293b;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-list li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        color: #475569;
        font-size: 0.95rem;
    }

    .info-list .icon {
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper nav {
        background: white;
        border-radius: 8px;
        padding: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .wallet-header {
            padding: 2rem 0;
            margin-bottom: 1.5rem;
        }

        .wallet-title {
            font-size: 1.8rem;
        }

        .wallet-subtitle {
            font-size: 1rem;
        }

        .wallet-balance-card {
            padding: 1.5rem;
            flex-direction: column;
            text-align: center;
        }

        .balance-icon {
            font-size: 3rem;
            margin-top: 1rem;
        }

        .balance-amount {
            font-size: 2.5rem;
        }

        .wallet-content {
            flex-direction: column;
            gap: 1.5rem;
        }

        .wallet-sidebar {
            width: 100%;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .transaction-card {
            padding: 1rem;
        }

        .transaction-left {
            gap: 0.75rem;
        }

        .transaction-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .transaction-title {
            font-size: 0.9rem;
        }

        .transaction-amount {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .wallet-title {
            font-size: 1.5rem;
        }

        .balance-amount {
            font-size: 2rem;
        }

        .transaction-card {
            padding: 0.75rem;
        }

        .transaction-right {
            margin-left: 0.5rem;
        }

        .section-header {
            padding: 1rem;
        }

        .wallet-content {
            gap: 1rem;
        }

        .wallet-sidebar {
            width: 100%;
        }
    }
</style>
@endsection
