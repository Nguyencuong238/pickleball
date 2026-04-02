@extends('layouts.front')

@section('css')
    <style>
        .booking-detail-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .breadcrumb {
            margin-bottom: 30px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #006646;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb span {
            color: #999;
        }

        .booking-header {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .booking-header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .booking-header-title {
            flex: 1;
        }

        .booking-header-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .booking-header-subtitle {
            color: #666;
            font-size: 14px;
        }

        .booking-status-badge {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .booking-status-badge-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .booking-status-badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .booking-status-badge-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .booking-status-badge-locked {
            background-color: #cfe2ff;
            color: #084298;
        }

        .booking-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .info-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #006646;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
            word-break: break-word;
        }

        .info-value.highlight {
            color: #006646;
            font-weight: 600;
            font-size: 18px;
            font-family: 'Courier New', monospace;
        }

        .booking-summary {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .summary-label {
            color: #555;
            font-weight: 500;
        }

        .summary-value {
            color: #333;
            font-weight: 600;
        }

        .summary-total {
            background: white;
            padding: 15px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            border: 2px solid #006646;
        }

        .summary-total .summary-label {
            font-size: 16px;
            color: #333;
        }

        .summary-total .summary-value {
            font-size: 22px;
            color: #006646;
        }

        .transfer-proof {
            margin-top: 20px;
        }

        .transfer-proof-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .transfer-proof-image {
            max-width: 100%;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn-primary,
        .btn-secondary {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #006646;
            color: white;
        }

        .btn-primary:hover {
            background-color: #00b894;
            text-decoration: none;
            color: white;
        }

        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
            text-decoration: none;
        }

        .notes-section {
            background: #f0f8ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }

        .notes-section-title {
            font-weight: 600;
            color: #0066cc;
            margin-bottom: 10px;
        }

        .notes-section-content {
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .booking-header-top {
                flex-direction: column;
                gap: 15px;
            }

            .info-section {
                margin-bottom: 15px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                text-align: center;
            }
        }
    </style>
@endsection

@section('content')
    <section class="booking-detail-container">
        <div class="breadcrumb">
            <a href="/">Trang chủ</a> /
            <a href="{{ route('user.booking-history.index') }}">Lịch sử đặt sân</a> /
            <span>Chi tiết đơn đặt</span>
        </div>

        <div class="booking-header">
            <div class="booking-header-top">
                <div class="booking-header-title">
                    <h1>Chi tiết đơn đặt sân</h1>
                    <div class="booking-header-subtitle">
                        Mã đơn đặt: <strong>{{ $booking->formatted_booking_code }}</strong>
                    </div>
                </div>
                <span class="booking-status-badge booking-status-badge-{{ $booking->status }}">
                    @switch($booking->status)
                        @case('confirmed')
                            ✓ Đã xác nhận
                            @break
                        @case('pending')
                            ⏳ Chờ xác nhận
                            @break
                        @case('cancelled')
                            ✗ Đã hủy
                            @break
                        @case('locked')
                            🔒 Đang khóa
                            @break
                        @default
                            {{ strtoupper($booking->status) }}
                    @endswitch
                </span>
            </div>
        </div>

        <div class="booking-summary">
            <div class="summary-row">
                <span class="summary-label">📍 Sân thi đấu:</span>
                <span class="summary-value">
                    @if ($booking->court)
                        {{ $booking->court->court_name }}
                        @if ($booking->court->stadium)
                            ({{ $booking->court->stadium->name }})
                        @endif
                    @else
                        Chưa xác định
                    @endif
                </span>
            </div>
            <div class="summary-row">
                <span class="summary-label">📅 Ngày đặt:</span>
                <span class="summary-value">
                    {{ \Carbon\Carbon::parse($booking->booking_date)->format('l, d/m/Y') }}
                </span>
            </div>
            <div class="summary-row">
                <span class="summary-label">⏰ Thời gian:</span>
                <span class="summary-value">
                    {{ $booking->start_time }}
                    @if ($booking->duration_hours)
                        ({{ $booking->duration_hours }} giờ)
                    @endif
                </span>
            </div>
            <div class="summary-row">
                <span class="summary-label">💳 Phương thức thanh toán:</span>
                <span class="summary-value">
                    @switch($booking->payment_method)
                        @case('transfer')
                            💳 Chuyển khoản
                            @break
                        @case('cash')
                            💵 Tiền mặt
                            @break
                        @case('card')
                            💳 Thẻ tín dụng
                            @break
                        @case('wallet')
                            👛 Ví điện tử
                            @break
                    @endswitch
                </span>
            </div>
            <div class="summary-total">
                <span class="summary-label">Tổng cộng:</span>
                <span class="summary-value">
                    {{ number_format($booking->total_price + ($booking->service_fee ?? 0), 0, '.', ',') }}
                    VND
                </span>
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">👤 Thông tin người đặt</div>
            <div class="info-item">
                <div class="info-label">Tên người đặt</div>
                <div class="info-value">{{ $booking->customer_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Số điện thoại</div>
                <div class="info-value">{{ $booking->customer_phone }}</div>
            </div>
            @if ($booking->customer_email)
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $booking->customer_email }}</div>
                </div>
            @endif
        </div>

        <div class="info-section">
            <div class="section-title">💰 Chi tiết thanh toán</div>
            <div class="info-item">
                <div class="info-label">Giá sân/giờ</div>
                <div class="info-value">
                    {{ number_format($booking->hourly_rate, 0, '.', ',') }} VND
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Tiền sân ({{ $booking->duration_hours ?? 1 }} giờ)</div>
                <div class="info-value">
                    {{ number_format($booking->total_price, 0, '.', ',') }} VND
                </div>
            </div>
            @if ($booking->service_fee)
                <div class="info-item">
                    <div class="info-label">Phí dịch vụ (5%)</div>
                    <div class="info-value">
                        {{ number_format($booking->service_fee, 0, '.', ',') }} VND
                    </div>
                </div>
            @endif
            <div class="info-item">
                <div class="info-label">Tổng tiền</div>
                <div class="info-value highlight">
                    {{ number_format($booking->total_price + ($booking->service_fee ?? 0), 0, '.', ',') }} VND
                </div>
            </div>
        </div>

        @if ($booking->transfer_proof)
            <div class="info-section">
                <div class="section-title">📸 Chứng minh chuyển khoản</div>
                <div class="transfer-proof">
                    <img src="{{ $booking->transfer_proof_url }}" alt="Transfer Proof" class="transfer-proof-image">
                </div>
            </div>
        @endif

        @if ($booking->notes)
            <div class="notes-section">
                <div class="notes-section-title">📝 Ghi chú</div>
                <div class="notes-section-content">
                    {{ $booking->notes }}
                </div>
            </div>
        @endif

        <div class="action-buttons">
            <a href="{{ route('user.booking-history.index') }}" class="btn-secondary">← Quay lại</a>
        </div>
    </section>
@endsection
