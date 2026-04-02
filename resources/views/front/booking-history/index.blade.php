@extends('layouts.front')

@section('css')
    <style>
        /* Override Laravel Pagination - Force Horizontal Layout */
        nav[role="navigation"] {
            display: flex !important;
            justify-content: center !important;
            margin-top: 30px !important;
        }

        nav[role="navigation"] .pagination {
            display: flex !important;
            justify-content: center !important;
            gap: 8px !important;
            flex-wrap: wrap !important;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        nav[role="navigation"] .page-item {
            display: inline-block !important;
            margin: 0 !important;
        }

        nav[role="navigation"] .page-link {
            padding: 8px 12px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            text-decoration: none !important;
            color: #006646 !important;
            transition: all 0.3s ease !important;
            font-size: 14px !important;
            display: inline-block !important;
            background-color: white !important;
        }

        nav[role="navigation"] .page-link:hover {
            background-color: #006646 !important;
            color: white !important;
            border-color: #006646 !important;
        }

        nav[role="navigation"] .page-item.active .page-link {
            background-color: #006646 !important;
            color: white !important;
            border-color: #006646 !important;
        }

        nav[role="navigation"] .page-item.disabled .page-link {
            color: #999 !important;
            cursor: not-allowed !important;
            opacity: 0.5 !important;
            pointer-events: none !important;
        }

        .booking-history-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .booking-history-header {
            margin-bottom: 30px;
        }

        .booking-history-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .booking-history-header p {
            color: #666;
            font-size: 14px;
        }

        .booking-filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .booking-filters select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            color: #333;
            background-color: white;
            cursor: pointer;
        }

        .booking-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .booking-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .booking-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .booking-code-section {
            flex: 1;
        }

        .booking-code {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .booking-code-value {
            font-size: 16px;
            font-weight: 600;
            color: #006646;
            font-family: 'Courier New', monospace;
        }

        .booking-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .booking-status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .booking-status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .booking-status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .booking-status-locked {
            background-color: #cfe2ff;
            color: #084298;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 15px;
        }

        .booking-detail-item {
            display: flex;
            flex-direction: column;
        }

        .booking-detail-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .booking-detail-value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
        }

        .booking-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .booking-amount {
            font-size: 18px;
            font-weight: 600;
            color: #006646;
        }

        .booking-actions {
            display: flex;
            gap: 10px;
        }

        .btn-view {
            padding: 8px 16px;
            background-color: #006646;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn-view:hover {
            background-color: #00b894;
            text-decoration: none;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .empty-state a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #006646;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .empty-state a:hover {
            background-color: #00b894;
            text-decoration: none;
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

        @media (max-width: 768px) {
            .booking-details {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }

            .booking-card-header {
                flex-direction: column;
                gap: 15px;
            }

            .booking-status {
                align-self: flex-start;
            }

            .booking-card-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .booking-actions {
                width: 100%;
                margin-top: 15px;
            }

            .btn-view {
                flex: 1;
                text-align: center;
            }
        }
    </style>
@endsection

@section('content')
    <section class="booking-history-container">
        <div class="breadcrumb">
            <a href="/">Trang chủ</a> /
            <span>Lịch sử đặt sân</span>
        </div>

        <div class="booking-history-header">
            <h1>📋 Lịch sử đặt sân</h1>
            <p>Quản lý và theo dõi tất cả các đơn đặt sân của bạn</p>
        </div>

        @if ($bookings->count() > 0)
            {{-- Filters --}}
            <div class="booking-filters">
                <select id="statusFilter" onchange="filterByStatus()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="confirmed">✓ Đã xác nhận</option>
                    <option value="pending">⏳ Chờ xác nhận</option>
                    <option value="locked">🔒 Đang khóa</option>
                    <option value="cancelled">✗ Đã hủy</option>
                </select>
            </div>

            {{-- Booking Cards --}}
            <div id="bookingsList">
                @foreach ($bookings as $booking)
                    <div class="booking-card" data-status="{{ $booking->status }}">
                        <div class="booking-card-header">
                            <div class="booking-code-section">
                                <div class="booking-code">Mã đơn đặt</div>
                                <div class="booking-code-value">{{ $booking->formatted_booking_code }}</div>
                            </div>
                            <span class="booking-status booking-status-{{ $booking->status }}">
                                @switch($booking->status)
                                    @case('confirmed')
                                        ✓ Đã xác nhận
                                        @break
                                    @case('pending')
                                        ⏳ Chờ xác nhận
                                        @break
                                    @case('locked')
                                        🔒 Đang khóa
                                        @break
                                    @case('cancelled')
                                        ✗ Đã hủy
                                        @break
                                    @default
                                        {{ strtoupper($booking->status) }}
                                @endswitch
                            </span>
                        </div>

                        <div class="booking-details">
                            <div class="booking-detail-item">
                                <div class="booking-detail-label">Sân thi đấu</div>
                                <div class="booking-detail-value">
                                    @if ($booking->court)
                                        {{ $booking->court->court_name }}
                                    @else
                                        Chưa xác định
                                    @endif
                                </div>
                            </div>

                            <div class="booking-detail-item">
                                <div class="booking-detail-label">Ngày đặt</div>
                                <div class="booking-detail-value">
                                    {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}
                                </div>
                            </div>

                            <div class="booking-detail-item">
                                <div class="booking-detail-label">Thời gian</div>
                                <div class="booking-detail-value">
                                    {{ $booking->start_time }}
                                    @if ($booking->duration_hours)
                                        ({{ $booking->duration_hours }} giờ)
                                    @endif
                                </div>
                            </div>

                            <div class="booking-detail-item">
                                <div class="booking-detail-label">Người đặt</div>
                                <div class="booking-detail-value">{{ $booking->customer_name }}</div>
                            </div>

                            <div class="booking-detail-item">
                                <div class="booking-detail-label">Phương thức thanh toán</div>
                                <div class="booking-detail-value">
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
                                        @default
                                            {{ ucfirst($booking->payment_method) }}
                                    @endswitch
                                </div>
                            </div>
                        </div>

                        <div class="booking-card-footer">
                            <div>
                                <div class="booking-detail-label" style="margin-bottom: 5px;">Tổng tiền</div>
                                <div class="booking-amount">
                                    {{ number_format($booking->total_price + ($booking->service_fee ?? 0), 0, '.', ',') }}
                                    VND
                                </div>
                            </div>
                            <div class="booking-actions">
                                <a href="{{ route('user.booking-history.show', $booking) }}" class="btn-view">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            {{ $bookings->links('vendor.pagination.custom') }}
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>Chưa có đơn đặt sân nào</h3>
                <p>Bạn chưa thực hiện bất kỳ đơn đặt sân nào. Hãy khám phá các sân thể thao ngay!</p>
                <a href="{{ route('courts') }}">Tìm sân</a>
            </div>
        @endif
    </section>

    <script>
        function filterByStatus() {
            const status = document.getElementById('statusFilter').value;
            const bookings = document.querySelectorAll('.booking-card');

            bookings.forEach(booking => {
                if (status === '') {
                    booking.style.display = 'block';
                } else if (booking.dataset.status === status) {
                    booking.style.display = 'block';
                } else {
                    booking.style.display = 'none';
                }
            });
        }
    </script>
@endsection
