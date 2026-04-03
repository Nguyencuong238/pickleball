<div id="topupModal" class="gems-modal" style="display:none;">
    <div class="gems-modal-overlay" onclick="closeTopupModal()"></div>
    <div class="gems-modal-content">
        <div class="gems-modal-header">
            <h3>Nạp Gems</h3>
            <button class="gems-modal-close" onclick="closeTopupModal()">&times;</button>
        </div>
        <div class="gems-modal-body">
            <!-- Step 1: Amount selection -->
            <div id="topupStep1">
                <label class="gems-input-label">Nhập số tiền (VND):</label>
                <input type="number" id="topupAmount" class="gems-input"
                    placeholder="{{ number_format($minTopup) }}"
                    min="{{ $minTopup }}" max="{{ $maxTopup }}"
                    data-exchange-rate="{{ $exchangeRate }}">

                <div class="gems-presets">
                    <button class="gems-preset-btn" data-amount="50000">50.000</button>
                    <button class="gems-preset-btn" data-amount="100000">100.000</button>
                    <button class="gems-preset-btn" data-amount="200000">200.000</button>
                    <button class="gems-preset-btn" data-amount="500000">500.000</button>
                </div>

                <div class="gems-calc">
                    Bạn sẽ nhận: <strong id="gemsPreview">0</strong> Gems
                </div>

                <button class="btn-gems-primary" id="btnCreateQr" onclick="createTopup()">
                    Tạo mã QR
                </button>
            </div>

            <!-- Step 2: QR display -->
            <div id="topupStep2" style="display:none;">
                <div class="gems-qr-container">
                    <img id="qrImage" src="" alt="QR Code" class="gems-qr-img">
                </div>

                <div class="gems-bank-info">
                    @if(config('gems.bank.name'))
                    <div class="gems-info-row">
                        <span>Ngân hàng:</span>
                        <strong>{{ config('gems.bank.name') }}</strong>
                    </div>
                    @endif
                    @if(config('gems.bank.account_number'))
                    <div class="gems-info-row">
                        <span>Số tài khoản:</span>
                        <strong>{{ config('gems.bank.account_number') }}</strong>
                        <button class="btn-copy" onclick="copyText(this, 'bankAccountRaw')">Sao chép</button>
                        <input type="hidden" id="bankAccountRaw" value="{{ config('gems.bank.account_number') }}">
                    </div>
                    @endif
                    @if(config('gems.bank.account_name'))
                    <div class="gems-info-row">
                        <span>Chủ tài khoản:</span>
                        <strong>{{ config('gems.bank.account_name') }}</strong>
                    </div>
                    @endif
                    <div class="gems-info-row">
                        <span>Số tiền:</span>
                        <strong id="qrAmountVnd"></strong>
                        <button class="btn-copy" onclick="copyText(this, 'qrAmountVndRaw')">Sao chép</button>
                        <input type="hidden" id="qrAmountVndRaw">
                    </div>
                    <div class="gems-info-row">
                        <span>Nội dung CK:</span>
                        <strong id="qrTransferContent"></strong>
                        <button class="btn-copy" onclick="copyText(this, 'qrTransferContentRaw')">Sao chép</button>
                        <input type="hidden" id="qrTransferContentRaw">
                    </div>
                </div>

                <div class="gems-polling-status" id="pollingStatus">
                    Đang chờ thanh toán...
                    <div class="gems-countdown" id="countdown"></div>
                </div>

                <button class="btn-gems-secondary" onclick="cancelTopup()">Hủy</button>
            </div>
        </div>
    </div>
</div>
