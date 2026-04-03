<div class="gems-balance-card">
    <div class="balance-content">
        <div class="balance-label">So du Gems</div>
        <div class="balance-amount">{{ number_format($wallet->balance) }} Gems</div>
        <div class="balance-vnd">~ {{ number_format($wallet->balance * $exchangeRate) }} VND</div>
    </div>
    <div class="balance-actions">
        <button class="btn-gems-topup" onclick="openTopupModal()">Nap Gems</button>
        <a href="#transactions" class="btn-gems-history">Lich su</a>
    </div>
</div>
