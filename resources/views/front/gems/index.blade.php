@extends('layouts.front')

@section('seo')
    <title>Vi Gems - OnePickleball</title>
    <meta name="description" content="Quan ly vi Gems cua ban tren OnePickleball">
@endsection

@section('content')
<div class="gems-container" style="margin-top: 100px">
    <div class="gems-header">
        <div class="container">
            <h1 class="gems-title">Vi Gems</h1>
            <p class="gems-subtitle">Nap Gems de thanh toan dat san nhanh chong</p>
        </div>
    </div>

    <div class="container py-5">
        @include('front.gems.partials.balance-card')

        <div class="gems-content mt-5">
            <div class="gems-main">
                @include('front.gems.partials.transaction-list')
            </div>

            <div class="gems-sidebar">
                <div class="info-card">
                    <h3 class="info-title">Gems la gi?</h3>
                    <ul class="info-list">
                        <li>
                            <span class="icon">1</span>
                            <span>Nap tien vao vi Gems qua chuyen khoan</span>
                        </li>
                        <li>
                            <span class="icon">2</span>
                            <span>Dung Gems de thanh toan dat san</span>
                        </li>
                        <li>
                            <span class="icon">3</span>
                            <span>Nhan {{ config('gems.cashback_percent') }}% hoan diem cho moi giao dich</span>
                        </li>
                    </ul>
                </div>

                <div class="info-card">
                    <h3 class="info-title">Ty gia</h3>
                    <p class="exchange-rate-display">
                        1 Gem = {{ number_format($exchangeRate) }} VND
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('front.gems.partials.topup-modal')

<style>
    .gems-container {
        background: #f8fafc;
        min-height: 100vh;
    }

    .gems-header {
        background: linear-gradient(135deg, #006646 0%, #52c98c 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
    }

    .gems-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .gems-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }

    /* Balance Card */
    .gems-balance-card {
        background: linear-gradient(135deg, #006646 0%, #52c98c 100%);
        border-radius: 16px;
        padding: 2.5rem;
        color: white;
        box-shadow: 0 20px 40px rgba(0, 217, 181, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .gems-balance-card::before {
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

    .gems-balance-card .balance-content {
        position: relative;
        z-index: 1;
    }

    .gems-balance-card .balance-label {
        font-size: 0.95rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .gems-balance-card .balance-amount {
        font-size: 3rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .gems-balance-card .balance-vnd {
        font-size: 1rem;
        opacity: 0.8;
    }

    .balance-actions {
        display: flex;
        gap: 0.75rem;
        position: relative;
        z-index: 1;
    }

    .btn-gems-topup {
        background: white;
        color: #006646;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .btn-gems-topup:hover {
        transform: scale(1.05);
    }

    .btn-gems-history {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 1px solid rgba(255,255,255,0.4);
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-gems-history:hover {
        background: rgba(255,255,255,0.3);
        color: white;
    }

    /* Content Layout */
    .gems-content {
        display: flex;
        gap: 2rem;
    }

    .gems-main {
        flex: 1;
        min-width: 0;
    }

    .gems-sidebar {
        width: 320px;
        flex-shrink: 0;
    }

    /* Transactions */
    .gems-transactions {
        background: white;
        border-radius: 12px;
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
    }

    .transaction-card:last-child {
        border-bottom: none;
    }

    .transaction-left {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .transaction-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .transaction-icon.positive {
        background: #d1fae5;
        color: #059669;
    }

    .transaction-icon.negative {
        background: #fee2e2;
        color: #dc2626;
    }

    .transaction-details {
        flex: 1;
    }

    .transaction-title {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .transaction-description {
        font-size: 0.85rem;
        color: #64748b;
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
        font-size: 1.05rem;
        display: block;
    }

    .transaction-amount.positive { color: #10b981; }
    .transaction-amount.negative { color: #ef4444; }

    .transaction-status {
        font-size: 0.75rem;
        padding: 0.15rem 0.5rem;
        border-radius: 10px;
        display: inline-block;
        margin-top: 0.25rem;
    }

    .status-completed { background: #d1fae5; color: #059669; }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-failed { background: #fee2e2; color: #dc2626; }
    .status-cancelled { background: #e2e8f0; color: #64748b; }

    /* Info cards */
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
        width: 28px;
        height: 28px;
        background: #006646;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .exchange-rate-display {
        font-size: 1.5rem;
        font-weight: 700;
        color: #006646;
        text-align: center;
        padding: 1rem 0;
        margin: 0;
    }

    /* Modal */
    .gems-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .gems-modal-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.5);
    }

    .gems-modal-content {
        position: relative;
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 440px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .gems-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .gems-modal-header h3 {
        margin: 0;
        font-size: 1.2rem;
    }

    .gems-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #64748b;
        padding: 0;
        line-height: 1;
    }

    .gems-modal-body {
        padding: 1.5rem;
    }

    .gems-input-label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #1e293b;
    }

    .gems-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1.1rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .gems-input:focus {
        border-color: #006646;
    }

    .gems-presets {
        display: flex;
        gap: 0.5rem;
        margin: 1rem 0;
        flex-wrap: wrap;
    }

    .gems-preset-btn {
        flex: 1;
        min-width: 70px;
        padding: 0.5rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .gems-preset-btn:hover,
    .gems-preset-btn.active {
        border-color: #006646;
        background: #f0fdf4;
        color: #006646;
    }

    .gems-calc {
        background: #f0fdf4;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        text-align: center;
        margin: 1rem 0;
        color: #006646;
        font-size: 1rem;
    }

    .btn-gems-primary {
        width: 100%;
        padding: 0.875rem;
        background: #006646;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-gems-primary:hover {
        background: #005538;
    }

    .btn-gems-primary:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }

    .btn-gems-secondary {
        width: 100%;
        padding: 0.75rem;
        background: #f1f5f9;
        color: #475569;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        margin-top: 1rem;
    }

    .gems-qr-container {
        text-align: center;
        padding: 1rem 0;
    }

    .gems-qr-img {
        width: 200px;
        height: 200px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
    }

    .gems-bank-info {
        margin: 1rem 0;
    }

    .gems-info-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }

    .gems-info-row span:first-child {
        color: #64748b;
        min-width: 90px;
    }

    .btn-copy {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
        cursor: pointer;
        margin-left: auto;
        white-space: nowrap;
    }

    .btn-copy:hover {
        background: #e2e8f0;
    }

    .gems-polling-status {
        text-align: center;
        padding: 1rem;
        color: #d97706;
        font-weight: 600;
    }

    .gems-countdown {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    /* Empty & pagination */
    .empty-state {
        padding: 3rem 1.5rem;
        text-align: center;
        color: #64748b;
    }

    .empty-state h3 {
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        padding: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .gems-header { padding: 2rem 0; }
        .gems-title { font-size: 1.8rem; }
        .gems-balance-card {
            flex-direction: column;
            text-align: center;
            padding: 1.5rem;
        }
        .gems-balance-card .balance-amount { font-size: 2.5rem; }
        .balance-actions { margin-top: 1rem; }
        .gems-content { flex-direction: column; }
        .gems-sidebar { width: 100%; }
    }

    @media (max-width: 480px) {
        .gems-title { font-size: 1.5rem; }
        .gems-balance-card .balance-amount { font-size: 2rem; }
        .balance-actions { flex-direction: column; }
        .gems-presets { flex-wrap: wrap; }
        .gems-preset-btn { min-width: 45%; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var amountInput = document.getElementById('topupAmount');
    var exchangeRate = parseInt(amountInput.dataset.exchangeRate);
    var presetBtns = document.querySelectorAll('.gems-preset-btn');

    // Preset buttons
    presetBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var amount = parseInt(this.dataset.amount);
            amountInput.value = amount;
            presetBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            updateGemsPreview();
        });
    });

    // Amount input change
    amountInput.addEventListener('input', function() {
        presetBtns.forEach(function(b) { b.classList.remove('active'); });
        updateGemsPreview();
    });

    function updateGemsPreview() {
        var amount = parseInt(amountInput.value) || 0;
        var gems = Math.floor(amount / exchangeRate);
        document.getElementById('gemsPreview').textContent = gems.toLocaleString();
    }
});

var pollingTimer = null;
var countdownTimer = null;
var currentTxId = null;

function openTopupModal() {
    document.getElementById('topupModal').style.display = 'flex';
    document.getElementById('topupStep1').style.display = 'block';
    document.getElementById('topupStep2').style.display = 'none';
}

function closeTopupModal() {
    document.getElementById('topupModal').style.display = 'none';
    cancelPolling();
}

function createTopup() {
    var amount = parseInt(document.getElementById('topupAmount').value);
    if (!amount || amount < {{ $minTopup }} || amount > {{ $maxTopup }}) {
        if (typeof toastr !== 'undefined') {
            toastr.error('Vui long nhap so tien hop le ({{ number_format($minTopup) }} - {{ number_format($maxTopup) }} VND)');
        }
        return;
    }

    var btn = document.getElementById('btnCreateQr');
    btn.disabled = true;
    btn.textContent = 'Dang tao...';

    var token = document.querySelector('meta[name="csrf-token"]');
    var headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    if (token) headers['X-CSRF-TOKEN'] = token.content;

    // Try JWT token from localStorage
    var jwtToken = localStorage.getItem('token');
    if (jwtToken) {
        headers['Authorization'] = 'Bearer ' + jwtToken;
    }

    fetch('/api/gems/topup', {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ amount_vnd: amount })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.textContent = 'Tao ma QR';

        if (data.success) {
            currentTxId = data.transaction_id;
            document.getElementById('qrImage').src = data.qr_url;
            document.getElementById('qrAmountVnd').textContent = Number(data.amount_vnd).toLocaleString() + ' VND';
            document.getElementById('qrAmountVndRaw').value = data.amount_vnd;
            document.getElementById('qrTransferContent').textContent = data.transfer_content;
            document.getElementById('qrTransferContentRaw').value = data.transfer_content;

            document.getElementById('topupStep1').style.display = 'none';
            document.getElementById('topupStep2').style.display = 'block';

            startPolling(data.transaction_id);
        } else {
            if (typeof toastr !== 'undefined') toastr.error(data.message || 'Co loi xay ra');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = 'Tao ma QR';
        if (typeof toastr !== 'undefined') toastr.error('Khong the ket noi. Vui long thu lai.');
    });
}

function startPolling(txId) {
    var deadline = Date.now() + 15 * 60 * 1000; // 15 minutes

    var headers = { 'Accept': 'application/json' };
    var jwtToken = localStorage.getItem('token');
    if (jwtToken) headers['Authorization'] = 'Bearer ' + jwtToken;

    pollingTimer = setInterval(function() {
        fetch('/api/gems/transactions/' + txId, { headers: headers })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.transaction && data.transaction.status === 'completed') {
                cancelPolling();
                if (typeof toastr !== 'undefined') toastr.success('Nap Gems thanh cong!');
                closeTopupModal();
                location.reload();
            }
        });
    }, 5000);

    countdownTimer = setInterval(function() {
        var remaining = Math.max(0, deadline - Date.now());
        var mins = Math.floor(remaining / 60000);
        var secs = Math.floor((remaining % 60000) / 1000);
        document.getElementById('countdown').textContent =
            'Het han sau: ' + mins + ':' + (secs < 10 ? '0' : '') + secs;

        if (remaining <= 0) {
            cancelPolling();
            document.getElementById('pollingStatus').textContent = 'Da het han. Vui long tao giao dich moi.';
            document.getElementById('countdown').textContent = '';
        }
    }, 1000);
}

function cancelPolling() {
    if (pollingTimer) { clearInterval(pollingTimer); pollingTimer = null; }
    if (countdownTimer) { clearInterval(countdownTimer); countdownTimer = null; }
}

function cancelTopup() {
    cancelPolling();
    document.getElementById('topupStep1').style.display = 'block';
    document.getElementById('topupStep2').style.display = 'none';
}

function copyText(btn, inputId) {
    var value = document.getElementById(inputId).value;
    navigator.clipboard.writeText(value).then(function() {
        var original = btn.textContent;
        btn.textContent = 'Da sao chep!';
        setTimeout(function() { btn.textContent = original; }, 2000);
    });
}
</script>
@endsection
