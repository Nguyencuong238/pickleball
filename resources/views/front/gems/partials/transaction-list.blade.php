<div class="gems-transactions" id="transactions">
    <div class="section-header">
        <h2 class="section-title">Lich su giao dich</h2>
        <span class="transaction-count">{{ $transactions->total() }} giao dich</span>
    </div>

    @if($transactions->count() > 0)
        <div class="transactions-list">
            @foreach($transactions as $tx)
                <div class="transaction-card">
                    <div class="transaction-left">
                        <div class="transaction-icon {{ $tx->amount >= 0 ? 'positive' : 'negative' }}">
                            @if($tx->type === 'top_up')
                                <span>+</span>
                            @elseif($tx->type === 'payment')
                                <span>-</span>
                            @elseif($tx->type === 'refund')
                                <span>R</span>
                            @else
                                <span>A</span>
                            @endif
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-title">
                                @switch($tx->type)
                                    @case('top_up') Nap Gems @break
                                    @case('payment') Thanh toan @break
                                    @case('refund') Hoan tien @break
                                    @case('admin_adjust') Dieu chinh @break
                                @endswitch
                            </div>
                            @if($tx->description)
                                <div class="transaction-description">{{ $tx->description }}</div>
                            @endif
                            <div class="transaction-date">{{ $tx->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                    <div class="transaction-right">
                        <span class="transaction-amount {{ $tx->amount >= 0 ? 'positive' : 'negative' }}">
                            {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }} Gems
                        </span>
                        <span class="transaction-status status-{{ $tx->status }}">
                            @switch($tx->status)
                                @case('completed') Hoan tat @break
                                @case('pending') Cho xu ly @break
                                @case('failed') That bai @break
                                @case('cancelled') Da huy @break
                            @endswitch
                        </span>
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
            <h3>Chua co giao dich nao</h3>
            <p>Nap Gems de bat dau su dung</p>
        </div>
    @endif
</div>
