@extends('layouts.front')

@section('seo')
    <title>Chi tiết giao dịch - OnePickleball</title>
@endsection

@section('content')
<div class="transaction-detail-container">
    <!-- Header -->
    <div class="transaction-detail-header">
        <div class="container">
            <a href="{{ route('user.wallet.index') }}" class="back-link">
                ← Quay lại ví
            </a>
            <h1 class="detail-title">Chi tiết giao dịch</h1>
        </div>
    </div>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-7 mx-auto">
                <!-- Transaction Card -->
                <div class="transaction-detail-card">
                    <!-- Transaction Type & Amount -->
                    <div class="transaction-header">
                        <div class="transaction-icon-large {{ $transaction->isPositive() ? 'positive' : 'negative' }}">
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
                        <div class="transaction-header-content">
                            <h2 class="transaction-type">
                                {{ $transaction->getTypeLabel() }}
                            </h2>
                            <p class="transaction-status">
                                @if($transaction->isPositive())
                                    <span class="badge-success">Nhận điểm</span>
                                @else
                                    <span class="badge-danger">Sử dụng điểm</span>
                                @endif
                            </p>
                        </div>
                        <div class="transaction-amount-large {{ $transaction->isPositive() ? 'positive' : 'negative' }}">
                            {{ $transaction->getFormattedPoints() }}
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="transaction-details-section">
                        <div class="detail-item">
                            <div class="detail-label">Loại giao dịch</div>
                            <div class="detail-value">
                                {{ $transaction->getTypeLabel() }}
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Số điểm</div>
                            <div class="detail-value">
                                <span class="amount-badge {{ $transaction->isPositive() ? 'positive' : 'negative' }}">
                                    {{ $transaction->getFormattedPoints() }} điểm
                                </span>
                            </div>
                        </div>

                        @if($transaction->description)
                            <div class="detail-item">
                                <div class="detail-label">Mô tả</div>
                                <div class="detail-value">
                                    {{ $transaction->description }}
                                </div>
                            </div>
                        @endif

                        <div class="detail-item">
                            <div class="detail-label">Thời gian giao dịch</div>
                            <div class="detail-value">
                                <div class="time-info">
                                    <div class="date">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                    <div class="time">{{ $transaction->created_at->format('H:i:s') }}</div>
                                </div>
                            </div>
                        </div>

                        @if($transaction->metadata && count($transaction->metadata) > 0)
                            <div class="detail-item">
                                <div class="detail-label">Thông tin bổ sung</div>
                                <div class="detail-value">
                                    <div class="metadata-box">
                                        @foreach($transaction->metadata as $key => $value)
                                            <div class="metadata-item">
                                                <span class="metadata-key">{{ $key }}:</span>
                                                <span class="metadata-value">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Info Box -->
                <div class="info-box">
                    <div class="info-icon">ℹ️</div>
                    <div class="info-content">
                        <h4>Ghi chú</h4>
                        <p>Tất cả giao dịch điểm được ghi nhận và lưu trữ an toàn. Bạn có thể xem lại lịch sử giao dịch bất cứ lúc nào.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .transaction-detail-container {
        background: #f8fafc;
        min-height: 100vh;
    }

    /* Header */
    .transaction-detail-header {
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
    }

    .back-link {
        display: inline-block;
        color: white;
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 1rem;
        transition: opacity 0.2s;
    }

    .back-link:hover {
        opacity: 0.8;
    }

    .detail-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    /* Transaction Card */
    .transaction-detail-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    /* Transaction Header */
    .transaction-header {
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .transaction-icon-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
    }

    .transaction-icon-large.positive {
        background: #d1fae5;
    }

    .transaction-icon-large.negative {
        background: #fee2e2;
    }

    .transaction-header-content {
        flex: 1;
    }

    .transaction-type {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .transaction-status {
        margin: 0;
    }

    .badge-success {
        display: inline-block;
        background: #d1fae5;
        color: #065f46;
        padding: 0.3rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .badge-danger {
        display: inline-block;
        background: #fee2e2;
        color: #7f1d1d;
        padding: 0.3rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .transaction-amount-large {
        font-size: 2rem;
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
    }

    .transaction-amount-large.positive {
        color: #10b981;
    }

    .transaction-amount-large.negative {
        color: #ef4444;
    }

    /* Details Section */
    .transaction-details-section {
        padding: 2rem;
    }

    .detail-item {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .detail-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .detail-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .detail-value {
        color: #1e293b;
        font-size: 1.05rem;
        line-height: 1.6;
    }

    .amount-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
    }

    .amount-badge.positive {
        background: #d1fae5;
        color: #065f46;
    }

    .amount-badge.negative {
        background: #fee2e2;
        color: #7f1d1d;
    }

    .time-info {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .time-info .date {
        font-weight: 600;
        color: #1e293b;
    }

    .time-info .time {
        background: #f1f5f9;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }

    /* Metadata */
    .metadata-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
    }

    .metadata-item {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .metadata-item:last-child {
        margin-bottom: 0;
    }

    .metadata-key {
        font-weight: 600;
        color: #475569;
        min-width: 100px;
    }

    .metadata-value {
        color: #64748b;
        word-break: break-all;
    }

    /* Info Box */
    .info-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        gap: 1rem;
    }

    .info-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .info-content h4 {
        color: #1e40af;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .info-content p {
        color: #1e3a8a;
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .transaction-header {
            flex-direction: column;
            text-align: center;
        }

        .transaction-amount-large {
            text-align: center;
            width: 100%;
        }

        .detail-title {
            font-size: 1.5rem;
        }

        .transaction-type {
            font-size: 1.3rem;
        }

        .transaction-amount-large {
            font-size: 1.8rem;
        }
    }

    @media (max-width: 480px) {
        .transaction-detail-header {
            padding: 1.5rem 0;
        }

        .detail-title {
            font-size: 1.3rem;
        }

        .transaction-header {
            padding: 1.5rem;
        }

        .transaction-icon-large {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }

        .transaction-details-section {
            padding: 1.5rem;
        }

        .detail-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .info-box {
            padding: 1rem;
        }
    }
</style>
@endsection
