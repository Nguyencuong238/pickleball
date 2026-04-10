<div class="gems-balance-card">
    <div class="balance-content">
        <div class="balance-label">Tổng số Gems</div>
        <div class="balance-amount">{{ number_format($wallet->balance) }} Gems</div>
        <div class="balance-vnd">~ {{ number_format($wallet->balance * $exchangeRate) }} VND</div>
    </div>
    <div class="balance-actions">
        <button class="btn-gems-topup" onclick="openTopupModal()">Nạp Gems</button>
        <a href="#transactions" class="btn-gems-history">Lịch sử</a>
    </div>
</div>

<div class="gems-balance-subcards">
    <div class="balance-subcard spendable">
        <div class="sub-label">Có thể sử dụng</div>
        <div class="sub-amount">{{ number_format($spendableBalance) }} Gems</div>
        <div class="sub-hint">Số Gems bạn có thể dùng để thanh toán ngay</div>
    </div>
    <div class="balance-subcard locked"
         title="Gems nhận từ khách, sẽ mở khóa sau {{ $refundWindowDays }} ngày để đảm bảo chính sách hoàn tiền">
        <div class="sub-label">Đang khóa</div>
        <div class="sub-amount">{{ number_format($lockedBalance) }} Gems</div>
        <div class="sub-hint">Gems nhận từ khách, mở khóa sau {{ $refundWindowDays }} ngày</div>
    </div>
</div>

<style>
    .gems-balance-subcards {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-top: 1rem;
    }
    .balance-subcard {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border-left: 4px solid #006646;
    }
    .balance-subcard.locked { border-left-color: #f59e0b; }
    .balance-subcard .sub-label {
        font-size: 0.85rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .balance-subcard .sub-amount {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 0.25rem;
    }
    .balance-subcard .sub-hint {
        font-size: 0.78rem;
        color: #94a3b8;
        margin-top: 0.35rem;
    }
    @media (max-width: 520px) {
        .gems-balance-subcards { grid-template-columns: 1fr; }
    }
</style>
