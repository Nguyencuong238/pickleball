<div class="gems-balance-card">
    <div class="balance-content">
        <div class="balance-label">Số dư Gems</div>
        <div class="balance-amount">{{ number_format($wallet->balance) }} Gems</div>
        <div class="balance-vnd">~ {{ number_format($wallet->balance * $exchangeRate) }} VND</div>
    </div>
    <div class="balance-actions">
        <button class="btn-gems-topup" onclick="openTopupModal()">Nạp Gems</button>
        <a href="#transactions" class="btn-gems-history">Lịch sử</a>
    </div>
</div>
