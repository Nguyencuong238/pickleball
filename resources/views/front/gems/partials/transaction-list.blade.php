<div class="gems-transactions" id="transactions">
    <div class="section-header">
        <h2 class="section-title">Lịch sử giao dịch</h2>
        <span class="transaction-count">{{ $transactions->total() }} giao dịch</span>
    </div>

    @if($transactions->count() > 0)
        <div class="transactions-list">
            @foreach($transactions as $tx)
                @php
                    $isReceipt = $tx->type === 'receipt';
                    $isLockedReceipt = $isReceipt && !$tx->released_at;
                    $typeLabel = match($tx->type) {
                        'top_up' => 'Nạp Gems',
                        'payment' => 'Thanh toán',
                        'receipt' => 'Nhận Gems',
                        'refund' => 'Hoàn từ giao dịch',
                        'refund_clawback' => 'Hoàn Gems cho khách',
                        'admin_adjust' => 'Điều chỉnh',
                        default => 'Giao dịch',
                    };
                    $typeIcon = match($tx->type) {
                        'top_up', 'receipt', 'refund' => '↓',
                        'payment', 'refund_clawback' => '↑',
                        default => '*',
                    };
                    $amountClass = $tx->amount >= 0 ? 'positive' : 'negative';
                @endphp
                <div class="transaction-card">
                    <div class="transaction-left">
                        <div class="transaction-icon {{ $amountClass }}">
                            <span>{{ $typeIcon }}</span>
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-title">{{ $typeLabel }}</div>
                            @if($tx->description)
                                <div class="transaction-description">{{ $tx->description }}</div>
                            @endif
                            <div class="transaction-date">{{ $tx->created_at->format('d/m/Y H:i') }}</div>
                            @if($isLockedReceipt && $tx->available_at)
                                <div class="transaction-locked-hint">
                                    Mở khóa lúc {{ $tx->available_at->format('H:i d/m/Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="transaction-right">
                        <span class="transaction-amount {{ $amountClass }}">
                            {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }} Gems
                        </span>
                        @if($isLockedReceipt)
                            <span class="transaction-status status-locked">Đang chờ giải phóng</span>
                        @else
                            <span class="transaction-status status-{{ $tx->status }}">
                                @switch($tx->status)
                                    @case('completed') Hoàn tất @break
                                    @case('pending') Chờ xử lý @break
                                    @case('failed') Thất bại @break
                                    @case('cancelled') Đã hủy @break
                                    @case('refunded') Đã hoàn @break
                                @endswitch
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($transactions->hasPages())
            <div class="pagination-wrapper">
                {{ $transactions->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <h3>Chưa có giao dịch nào</h3>
            <p>Nạp Gems để bắt đầu sử dụng</p>
        </div>
    @endif
</div>

<style>
    .transaction-locked-hint {
        font-size: 0.75rem;
        color: #d97706;
        margin-top: 0.2rem;
    }
    .status-locked {
        background: #fef3c7;
        color: #d97706;
    }
    .status-refunded {
        background: #e0e7ff;
        color: #4338ca;
    }
</style>
