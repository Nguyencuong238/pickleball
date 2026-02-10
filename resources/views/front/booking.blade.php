@extends('layouts.front')

@section('css')
    <style>
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        .slot-btn.active {
            background-color: #007bff !important;
            color: white;
            border-color: #007bff;
        }

        .slot-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #e9ecef;
            color: #6c757d;
        }

        .slot-btn.disabled:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        /* Calendar Grid Styles */
        .calendar-legend {
            display: flex;
            gap: 20px;
            margin: 15px 0 20px 0;
            flex-wrap: wrap;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        /* Booking Instruction */
        .booking-instruction {
            background-color: #f0f8ff;
            border-left: 4px solid #2196F3;
            padding: 12px 15px;
            margin: 15px 0 20px 0;
            border-radius: 4px;
        }

        .instruction-item {
            font-size: 13px;
            color: #333;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .instruction-item:last-child {
            margin-bottom: 0;
        }

        .instruction-item kbd {
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 2px 6px;
            font-family: monospace;
            font-weight: 600;
            font-size: 12px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
        }

        .calendar-table-wrapper {
            overflow-x: auto;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            max-height: 600px;
            /* Hide scrollbar nhưng vẫn scroll */
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .calendar-table-wrapper::-webkit-scrollbar {
            display: none;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .calendar-table thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #ddd;
        }

        .calendar-table th {
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            border-right: 1px solid #ddd;
            min-width: 70px;
        }

        .calendar-table th:first-child {
            min-width: 80px;
            text-align: left;
            padding-left: 15px;
        }

        .calendar-table td {
            padding: 0;
            border-right: 1px solid #ddd;
            height: 50px;
        }

        .calendar-table tr {
            border-bottom: 1px solid #ddd;
        }

        .calendar-table tr:hover {
            background-color: #f9f9f9;
        }

        .calendar-table tr:last-child {
            border-bottom: none;
        }

        .calendar-court-name {
            padding: 12px 15px;
            font-weight: 500;
            text-align: center;
            background-color: #f8f9fa;
            border-right: 2px solid #ddd;
            min-width: 80px;
        }

        .time-slot-cell {
            position: relative;
            cursor: pointer;
            border: none;
            padding: 0;
            height: 100%;
            min-width: 70px;
        }

        .time-slot-cell.available {
            background-color: #fff;
            border-right: 1px solid #ddd;
        }

        .time-slot-cell.available:hover {
            background-color: #e3f2fd;
        }

        .time-slot-cell.booked {
            background-color: #ff6b6b;
            cursor: not-allowed;
        }

        .time-slot-cell.locked {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .time-slot-cell.event {
            background-color: #ffd700;
            cursor: not-allowed;
        }

        .time-slot-cell.selected {
            background-color: #2196F3;
            color: white;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
        }

        .time-slot-cell-content {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 13px;
            font-weight: 500;
            padding: 0 4px;
        }

        .calendar-table.merged-cells .time-slot-cell {
            border-right: none;
        }

        .calendar-table.merged-cells .time-slot-cell:last-child {
            border-right: 1px solid #ddd;
        }

        /* Layout Styles */
        .booking-layout {
            display: block;
        }

        .booking-form-section {
            margin-bottom: 30px;
        }

        .booking-summary {
            margin-top: 20px;
        }

        /* Modal QR Code Styles */
        .modal.show {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid #e9ecef;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .modal-title {
            font-weight: 600;
            color: #333;
        }

        .modal-body {
            padding: 30px 20px;
        }

        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 12px 12px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal.show {
            display: block !important;
        }

        .modal-dialog {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 500px;
            width: 85%;
            max-height: 95vh;
            overflow-y: auto;
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            margin: 0;
        }

        .modal-header {
            border-bottom: 1px solid #e9ecef;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        .close {
            font-size: 24px;
            font-weight: 300;
            color: #999;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
        }

        .close:hover {
            color: #333;
        }

        .modal-body {
            padding: 30px 20px;
            text-align: center;
        }

        .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-radius: 0 0 12px 12px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-primary {
            background-color: #00d9b5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #00b894;
        }

        #confirmPaymentBtn {
            padding: 12px 50px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            min-width: 200px;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .calendar-table th,
            .calendar-table td {
                padding: 8px 4px;
                min-width: 50px;
                font-size: 12px;
            }

            .time-slot-cell-content {
                font-size: 11px;
            }

            .calendar-legend {
                gap: 10px;
                font-size: 12px;
            }

            .modal-body {
                padding: 20px 15px;
            }
        }
    </style>
@endsection

@section('content')
    <section class="booking-section">
        <div class="container">
            <div class="breadcrumb">
                <a href="/">Trang chủ</a> /
                <a href="/courts">Sân thi đấu</a> /
                <span>Đặt sân</span>
            </div>

            <h1 class="page-title">Đặt Sân Pickleball</h1>

            <div class="booking-layout">
                <div class="booking-form-section">
                    <div class="step-indicator">
                        <div class="step active" id="step1">
                            <div class="step-number">1</div>
                            <div class="step-label">Chọn thời gian</div>
                        </div>
                        <div class="step" id="step2">
                            <div class="step-number">2</div>
                            <div class="step-label">Thông tin</div>
                        </div>
                        <div class="step" id="step3">
                            <div class="step-number">3</div>
                            <div class="step-label">Thanh toán</div>
                        </div>
                    </div>

                    <form id="bookingForm">
                        @csrf
                        <input type="hidden" id="stadiumId" value="{{ $stadium->id }}">
                        <div class="booking-card">
                            <h2>Chọn ngày & giờ</h2>

                            <!-- Chọn Sân - Hidden (selected from calendar grid) -->
                            <div class="form-group" style="display: none;">
                                <label>Chọn sân *</label>
                                <select class="form-control bg-white" id="courtSelect" name="court_id" required>
                                    <option value="">-- Chọn sân --</option>
                                    @if (isset($courts) && count($courts) > 0)
                                        @foreach ($courts as $court)
                                            <option value="{{ $court->id }}">{{ $court->court_name }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Không có sân nào khả dụng</option>
                                    @endif
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Ngày đặt sân *</label>
                                <input type="date" class="form-control" id="bookingDate" name="booking_date"
                                    min="{{ now()->format('Y-m-d') }}" required>
                            </div>

                            <div class="time-slots">
                                <h3>Chọn ngày & giờ *</h3>
                                <div id="calendarLegend" class="calendar-legend">
                                    <div class="legend-item">
                                        <span class="legend-color"
                                            style="background-color: #fff; border: 1px solid #ddd;"></span>
                                        <span>Trống</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: #ff6b6b;"></span>
                                        <span>Đã đặt</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: #ccc;"></span>
                                        <span>Khoá</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: #ffd700;"></span>
                                        <span>Sự kiện</span>
                                    </div>
                                </div>

                                <!-- Hướng dẫn chọn giờ -->
                                <div class="booking-instruction">
                                    <div class="instruction-item">
                                        <strong>📌 Cách 1 - Chọn nhiều giờ liền (cùng sân):</strong> Click khung giờ bắt
                                        đầu, giữ <kbd>Shift</kbd>, rồi click khung giờ kết thúc
                                    </div>
                                    <div class="instruction-item">
                                        <strong>📌 Cách 2 - Chọn thời lượng:</strong> Click khung giờ bắt đầu, rồi chọn
                                        "Thời lượng" ở dưới
                                    </div>
                                    <div class="instruction-item">
                                        <strong>📌 Cách 3 - Chọn nhiều sân:</strong> Click từng sân khác nhau để chọn nhiều
                                        sân (không cần Shift)
                                    </div>
                                    <div class="instruction-item">
                                        <strong>💳 Lưu ý phương thức thanh toán:</strong>
                                        <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                                            <li><strong style="color: #28a745;">Tiền mặt:</strong> Đặt sân xác nhận ngay
                                            </li>
                                            <li><strong style="color: #ffc107;">Chuyển khoản:</strong> Khóa 15 phút, nếu
                                                không xác nhận sẽ hủy tự động</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="calendar-table-wrapper" id="calendarWrapper">
                                    <p style="text-align: center; color: #666; padding: 20px;">Vui lòng chọn sân và ngày
                                        trước</p>
                                </div>
                                <input type="hidden" id="selectedSlot" name="start_time" required>
                            </div>

                            <div class="form-group">
                                <label>Thời lượng (giờ) *</label>
                                <select class="form-control bg-white" id="durationHours" name="duration_hours" required>
                                    <option value="">-- Chọn thời lượng --</option>
                                    <option value="1">1 giờ</option>
                                    <option value="2">2 giờ</option>
                                    <option value="3">3 giờ</option>
                                    <option value="4">4 giờ</option>
                                    <option value="5">5 giờ</option>
                                    <option value="6">6 giờ</option>
                                </select>
                            </div>

                            <h2>Thông tin người đặt</h2>
                            <div class="form-group">
                                <label>Họ tên *</label>
                                <input type="text" class="form-control" name="customer_name" placeholder="Nguyễn Văn A"
                                    value="{{ auth()->check() ? auth()->user()->name : '' }}" required>
                            </div>
                            <div class="form-group">
                                <label>Số điện thoại *</label>
                                <input type="tel" class="form-control" name="customer_phone" placeholder="0901234567"
                                    value="{{ auth()->check() ? auth()->user()->phone : '' }}" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="customer_email"
                                    placeholder="email@example.com"
                                    value="{{ auth()->check() ? auth()->user()->email : '' }}">
                            </div>
                            <div class="form-group">
                                <label>Ghi chú</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Ghi chú thêm..."></textarea>
                            </div>

                            <!-- Payment method fixed to transfer -->
                            <input type="hidden" name="payment_method" value="transfer">

                            <input type="hidden" id="hourlyRate" name="hourly_rate" value="0">
                        </div>
                    </form>
                </div>

                <div class="booking-summary">
                    <div class="summary-card">
                        <h3>Chi tiết đặt sân</h3>
                        <div class="summary-item">
                            <span>Cụm sân:</span>
                            <strong>{{ $stadium->name }}</strong>
                        </div>
                        <div class="summary-item">
                            <span>Sân:</span>
                            <strong id="summaryCourtName">Chưa chọn</strong>
                        </div>
                        <div class="summary-item">
                            <span>Ngày:</span>
                            <strong id="summaryDate">Chưa chọn</strong>
                        </div>
                        <div class="summary-item">
                            <span>Giờ:</span>
                            <strong id="summaryTime">Chưa chọn</strong>
                        </div>
                        <div class="summary-item">
                            <span>Thời lượng:</span>
                            <strong id="summaryDuration">1 giờ</strong>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-item">
                            <span>Giá/giờ:</span>
                            <strong id="summaryHourlyRate">0đ</strong>
                        </div>
                        <div class="summary-item">
                            <span>Tạm tính:</span>
                            <strong id="summarySubtotal">0đ</strong>
                        </div>
                        <div class="summary-item">
                            <span>Phí dịch vụ:</span>
                            <strong id="summaryFee">0đ</strong>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-total">
                            <span>Tổng cộng:</span>
                            <strong id="summaryTotal">0đ</strong>
                        </div>
                        <button type="submit" form="bookingForm" class="btn btn-primary btn-block btn-lg"
                            id="submitBtn">Đặt sân</button>
                        <p class="payment-note">🔒 Thanh toán an toàn với Banking</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Success Booking -->
    <div class="modal fade" id="bookingSuccessModal" tabindex="-1" role="dialog"
        aria-labelledby="bookingSuccessLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #27ae60; border: none;">
                    <h5 class="modal-title" id="bookingSuccessLabel" style="color: white; font-weight: 600;">✓ Đặt sân
                        thành công!</h5>
                </div>
                <div class="modal-body text-center" style="padding: 30px;">
                    <div style="font-size: 48px; margin-bottom: 20px;">✓</div>
                    <p style="font-size: 16px; margin-bottom: 15px; color: #333;">
                        <strong>Yêu cầu đặt sân của bạn đã được gửi thành công!</strong>
                    </p>
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="margin-bottom: 8px; color: #666;">
                            <strong>Mã đơn đặt:</strong>
                        </p>
                        <p style="font-size: 20px; font-weight: 600; color: #00d9b5; margin: 0;" id="successBookingCode">-
                        </p>
                    </div>
                    <div
                        style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; border-radius: 4px; text-align: left; margin-bottom: 20px;">
                        <p style="margin: 0; font-size: 14px; color: #856404;">
                            <strong>📋 Trạng thái:</strong> <span style="color: #ffc107; font-weight: 600;">⏳ Chờ xác nhận từ sân</span>
                        </p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #666;">
                            Sân sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận hoặc từ chối yêu cầu đặt sân.
                        </p>
                    </div>
                    <div style="background-color: #e8f5e9; border-left: 4px solid #27ae60; padding: 12px; border-radius: 4px; text-align: left;">
                        <p style="margin: 0; font-size: 13px; color: #2e7d32;">
                            <strong>💡 Ghi chú:</strong> Bạn có thể theo dõi tình trạng đơn đặt trong mục "Lịch sử đặt sân".
                        </p>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal QR Code Thanh toán -->
    <div class="modal fade" id="paymentQRModal" tabindex="-1" role="dialog" aria-labelledby="paymentQRLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentQRLabel">Thanh toán bằng chuyển khoản</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div style="margin-bottom: 20px; margin: 0 auto;width: fit-content;">
                        <img id="qrCodeImage" src="" alt="QR Code" style="max-width: 250px; width: 100%;">
                    </div>
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; text-align: left;">
                        <p style="margin-bottom: 10px;">
                            <strong>Chủ sân:</strong> <span id="bankOwner">-</span>
                        </p>
                        <p style="margin-bottom: 10px;">
                            <strong>Số tài khoản:</strong> <span id="bankAccount">-</span>
                        </p>
                        <p style="margin-bottom: 10px;">
                            <strong>Ngân hàng:</strong> <span id="bankName">-</span>
                        </p>
                        <p style="margin-bottom: 0;">
                            <strong>Số tiền:</strong> <span id="qrAmount">0</span> VND
                        </p>
                        <p style="margin-top: 10px">
                            <strong>Nội dung chuyển:</strong> <span id="qrContent">-</span>
                        </p>
                    </div>
                    <!-- Upload transfer proof -->
                    <div style="margin-top: 15px; border: 2px dashed #ccc; border-radius: 8px; padding: 15px; text-align: center;"
                        id="proofUploadArea">
                        <input type="file" id="transferProofInput" accept="image/jpeg,image/png,image/jpg"
                            style="display: none;">
                        <button type="button" id="selectProofBtn"
                            style="background: none; border: 2px dashed #00d9b5; color: #00d9b5; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600;">
                            Chọn ảnh xác nhận chuyển khoản
                        </button>
                        <p style="color: #888; font-size: 12px; margin-top: 8px; margin-bottom: 0;">JPG, PNG - Tối đa 5MB
                        </p>
                    </div>
                    <!-- Preview uploaded image -->
                    <div id="proofPreviewContainer" style="display: none; margin-top: 10px; text-align: center;">
                        <img id="proofPreviewImg" src="" alt="Preview"
                            style="max-width: 200px; border-radius: 8px; border: 1px solid #ddd;">
                        <p id="proofUploadStatus"
                            style="color: #27ae60; font-size: 13px; margin-top: 5px; font-weight: 600;"></p>
                    </div>
                    <!-- Upload progress bar -->
                    <div id="proofProgressBar" style="display: none; margin-top: 10px;">
                        <div style="background: #eee; border-radius: 4px; overflow: hidden; height: 6px;">
                            <div id="proofProgressFill"
                                style="background: #00d9b5; height: 100%; width: 0%; transition: width 0.3s;"></div>
                        </div>
                        <p style="color: #888; font-size: 12px; margin-top: 4px;">Đang tải lên...</p>
                    </div>

                    <p style="color: #e74c3c; margin-top: 15px; font-size: 13px;">
                        [!] <strong>Lưu ý:</strong> Sân sẽ được khóa trong 5 phút. Nếu không xác nhận thanh toán sẽ tự động
                        hủy.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirmPaymentBtn" disabled>Tôi đã thanh
                        toán</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const courtSelect = document.getElementById('courtSelect');
            const bookingDate = document.getElementById('bookingDate');
            const calendarWrapper = document.getElementById('calendarWrapper');
            const selectedSlot = document.getElementById('selectedSlot');
            const durationSelect = document.getElementById('durationHours');
            const hourlyRate = document.getElementById('hourlyRate');
            const bookingForm = document.getElementById('bookingForm');
            let timeSlots = [];
            let closingHour = 22;
            let openingHour = 6;

            // Load available time slots from API
            async function loadAvailableSlots() {
                const date = bookingDate.value;
                const stadiumId = document.getElementById('stadiumId').value;

                if (!date) {
                    calendarWrapper.innerHTML =
                        '<p style="text-align: center; color: #666; padding: 20px;">Vui lòng chọn ngày trước</p>';
                    return;
                }

                try {
                    calendarWrapper.innerHTML =
                        '<p style="text-align: center; color: #666; padding: 20px;">Đang tải...</p>';

                    // Load all courts for the stadium on this date
                    const response = await fetch(`/api/bookings/stadium/${stadiumId}/slots-all?date=${date}`);
                    const result = await response.json();

                    console.log('Slots response:', result);

                    if (result.success && result.available_slots && result.available_slots.length > 0) {
                        timeSlots = result.available_slots;
                        if (timeSlots.length > 0) {
                            const hours = timeSlots.map(s => s.hour);
                            openingHour = Math.min(...hours);
                            closingHour = Math.max(...(timeSlots.map(s => s.end_hour || s.hour + 1)));
                        }
                        generateCalendarTable();
                    } else if (result.success && result.available_slots && result.available_slots.length ===
                        0) {
                        calendarWrapper.innerHTML =
                            '<p style="text-align: center; color: #666; padding: 20px;">Không có khoảng thời gian khả dụng cho ngày này</p>';
                    } else {
                        calendarWrapper.innerHTML =
                            '<p style="text-align: center; color: #666; padding: 20px;">Không thể tải khoảng thời gian: ' +
                            (result.message || 'Lỗi không xác định') + '</p>';
                    }
                } catch (error) {
                    console.error('Error loading slots:', error);
                    calendarWrapper.innerHTML =
                        '<p style="text-align: center; color: #666; padding: 20px;">Lỗi khi tải dữ liệu</p>';
                }
            }

            // Generate calendar table
            function generateCalendarTable() {
                calendarWrapper.innerHTML = '';
                const table = document.createElement('table');
                table.className = 'calendar-table';

                // Create header with time slots
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');
                const emptyHeader = document.createElement('th');
                emptyHeader.textContent = 'Sân';
                headerRow.appendChild(emptyHeader);

                for (let hour = openingHour; hour < closingHour; hour++) {
                    const th = document.createElement('th');
                    th.textContent = `${String(hour).padStart(2, '0')}:00`;
                    headerRow.appendChild(th);
                }

                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Create body with courts
                const tbody = document.createElement('tbody');
                const courts = getCourtsFromSelect();

                courts.forEach(court => {
                    const row = document.createElement('tr');

                    // Court name cell
                    const nameCell = document.createElement('td');
                    nameCell.className = 'calendar-court-name';
                    nameCell.textContent = court.name;
                    row.appendChild(nameCell);

                    // Time slot cells
                    for (let hour = openingHour; hour < closingHour; hour++) {
                        const slotCell = document.createElement('td');
                        const slot = findSlot(court.id, hour);

                        if (slot) {
                            slotCell.className = `time-slot-cell ${getSlotStatus(slot)}`;
                            slotCell.dataset.slotTime = slot.time;
                            slotCell.dataset.slotHour = slot.hour;
                            slotCell.dataset.slotPrice = slot.price;
                            slotCell.dataset.courtId = slot.court_id;

                            const content = document.createElement('div');
                            content.className = 'time-slot-cell-content';
                            content.textContent = slot.price.toLocaleString('vi-VN') + 'đ';
                            slotCell.appendChild(content);

                            // Add click handler for available slots
                            if (!slot.is_booked && !slot.is_locked && !slot.is_pending) {
                                slotCell.addEventListener('click', (e) => {
                                    // Shift+Click để chọn range giờ (cùng sân)
                                    if (e.shiftKey) {
                                        const selectedCells = document.querySelectorAll(
                                            '.time-slot-cell.selected');
                                        if (selectedCells.length > 0) {
                                            const firstSelected = selectedCells[0];
                                            const firstHour = parseInt(firstSelected.dataset
                                                .slotHour);
                                            const firstCourt = parseInt(firstSelected.dataset
                                                .courtId);
                                            const secondHour = slot.hour;
                                            const secondCourt = slot.court_id;

                                            // Chỉ cho phép chọn range nếu cùng sân
                                            if (firstCourt === secondCourt) {
                                                const startHour = Math.min(firstHour, secondHour);
                                                const endHour = Math.max(firstHour, secondHour);

                                                // Highlight range
                                                let firstSlotInRange = null;
                                                const allCells = document.querySelectorAll(
                                                    '.time-slot-cell');
                                                allCells.forEach(cell => {
                                                    const cellHour = parseInt(cell.dataset
                                                        .slotHour);
                                                    const cellCourt = parseInt(cell.dataset
                                                        .courtId);

                                                    if (cellCourt === secondCourt &&
                                                        cellHour >= startHour && cellHour <=
                                                        endHour) {
                                                        if (!cell.classList.contains(
                                                                'booked') && !cell.classList
                                                            .contains('locked')) {
                                                            cell.classList.add('selected');
                                                            // Lưu slot đầu tiên trong range
                                                            if (!firstSlotInRange) {
                                                                firstSlotInRange = cell;
                                                            }
                                                        }
                                                    } else {
                                                        cell.classList.remove('selected');
                                                    }
                                                });

                                                // Set start_time bằng slot đầu tiên, không phải slot cuối
                                                if (firstSlotInRange) {
                                                    const firstSlotTime = firstSlotInRange.dataset
                                                        .slotTime;
                                                    courtSelect.value = secondCourt;
                                                    selectedSlot.value = firstSlotTime;
                                                    updateDurationOptions(startHour);

                                                    // Tính duration từ range (include both start and end hours)
                                                    const duration = endHour - startHour + 1;
                                                    if (duration > 0 && duration <= 6) {
                                                        durationSelect.value = duration.toString();
                                                    }
                                                    updateSummary();
                                                }
                                            }
                                        }
                                    } else {
                                        // Click thường - chọn 1 ô (support multi-court selection)
                                        selectSlot(slot, slotCell);
                                    }
                                });
                            }
                        } else {
                            slotCell.className = 'time-slot-cell locked';
                        }

                        row.appendChild(slotCell);
                    }

                    tbody.appendChild(row);
                });

                table.appendChild(tbody);
                calendarWrapper.appendChild(table);
            }

            function getCourtsFromSelect() {
                const courts = [];
                const options = courtSelect.querySelectorAll('option');
                options.forEach(opt => {
                    if (opt.value) {
                        courts.push({
                            id: opt.value,
                            name: opt.textContent
                        });
                    }
                });
                return courts;
            }

            function findSlot(courtId, hour) {
                return timeSlots.find(s => s.court_id == courtId && s.hour == hour);
            }

            function getSlotStatus(slot) {
                if (slot.is_booked) return 'booked';
                if (slot.is_locked) return 'locked';
                if (slot.is_event) return 'event';
                return 'available';
            }

            function updateDurationOptions(startHour) {
                // Calculate maximum duration based on closing hour
                const maxDuration = closingHour - startHour;

                // Hide/show duration options
                const options = durationSelect.querySelectorAll('option');
                options.forEach(option => {
                    const duration = parseInt(option.value);
                    if (duration > 0 && duration > maxDuration) {
                        option.hidden = true;
                    } else if (duration > 0) {
                        option.hidden = false;
                    }
                });

                // Reset duration to first available option if current is hidden
                if (durationSelect.selectedIndex > 0 && durationSelect.options[durationSelect.selectedIndex]
                    .hidden) {
                    durationSelect.value = '';
                    for (let i = 1; i < options.length; i++) {
                        if (!options[i].hidden) {
                            durationSelect.value = options[i].value;
                            break;
                        }
                    }
                }
            }

            function selectSlot(slot, cellElement, isRangeEnd = false) {
                // Allow multi-court selection: check if already selected
                if (cellElement.classList.contains('selected')) {
                    // If already selected, deselect it
                    cellElement.classList.remove('selected');
                } else {
                    // Add to selection (don't remove others)
                    cellElement.classList.add('selected');

                    // Auto-select the court from the slot
                    courtSelect.value = slot.court_id;
                    selectedSlot.value = slot.time;
                }

                // If not range end, calculate duration based on selected cells
                if (!isRangeEnd) {
                    updateDurationOptions(slot.hour);
                    // Set default duration to 1 if not set
                    if (!durationSelect.value) {
                        durationSelect.value = '1';
                    }
                }

                // Calculate duration from selected range
                const selectedCells = document.querySelectorAll('.time-slot-cell.selected');
                if (selectedCells.length > 1) {
                    const firstCell = selectedCells[0];
                    const lastCell = selectedCells[selectedCells.length - 1];
                    const startHour = parseInt(firstCell.dataset.slotHour);
                    const endHour = parseInt(lastCell.dataset.slotHour) + 1;
                    const duration = endHour - startHour;

                    if (duration > 0 && duration <= 6) {
                        durationSelect.value = duration.toString();
                    }
                }

                updateSummary();
            }

            function calculateAverageRate() {
                const courtId = courtSelect.value;
                const time = selectedSlot.value;
                const duration = parseInt(document.getElementById('durationHours').value) || 0;

                if (!time || !courtId || duration <= 0) return 0;

                const selectedSlotObj = timeSlots.find(s => s.time === time && s.court_id == courtId);
                if (!selectedSlotObj) return 0;

                let subtotal = 0;
                for (let i = 0; i < duration; i++) {
                    const slotIndex = timeSlots.findIndex(s => s.hour === selectedSlotObj.hour + i && s.court_id ==
                        courtId);
                    if (slotIndex !== -1) {
                        subtotal += timeSlots[slotIndex].price || 0;
                    }
                }

                return duration > 0 ? Math.round(subtotal / duration) : 0;
            }

            function updateSummary() {
                const courtId = courtSelect.value;
                const date = bookingDate.value;
                const time = selectedSlot.value;
                const duration = parseInt(document.getElementById('durationHours').value) || 0;

                // Update court name
                if (courtId) {
                    const selectedOption = courtSelect.options[courtSelect.selectedIndex];
                    document.getElementById('summaryCourtName').textContent = selectedOption.text;
                }

                // Update date
                if (date) {
                    const dateObj = new Date(date + 'T00:00:00');
                    const dateStr = dateObj.toLocaleDateString('vi-VN');
                    document.getElementById('summaryDate').textContent = dateStr;
                }

                // Update time and get correct hour from selected slot
                if (time && duration > 0) {
                    const selectedSlotObj = timeSlots.find(s => s.time === time);
                    if (selectedSlotObj) {
                        const endHour = selectedSlotObj.hour + duration;
                        document.getElementById('summaryTime').textContent =
                            `${selectedSlotObj.time} - ${String(endHour).padStart(2, '0')}:00`;
                    }
                }

                // Calculate total with multi-hour pricing
                let subtotal = 0;
                const averageRate = calculateAverageRate();

                if (time && duration > 0) {
                    const selectedSlotObj = timeSlots.find(s => s.time === time && s.court_id == courtId);
                    if (selectedSlotObj) {
                        for (let i = 0; i < duration; i++) {
                            const slotIndex = timeSlots.findIndex(s => s.hour === selectedSlotObj.hour + i && s
                                .court_id == courtId);
                            if (slotIndex !== -1) {
                                subtotal += timeSlots[slotIndex].price || 0;
                            }
                        }
                    }
                }

                hourlyRate.value = averageRate;
                const fee = Math.round(subtotal * 0.05); // 5% service fee
                const total = subtotal + fee;

                document.getElementById('summaryDuration').textContent = `${duration} giờ`;
                document.getElementById('summaryHourlyRate').textContent = (averageRate ? averageRate
                    .toLocaleString('vi-VN') :
                    0) + 'đ';
                document.getElementById('summarySubtotal').textContent = subtotal.toLocaleString('vi-VN') + 'đ';
                document.getElementById('summaryFee').textContent = fee.toLocaleString('vi-VN') + 'đ';
                document.getElementById('summaryTotal').textContent = total.toLocaleString('vi-VN') + 'đ';
            }

            // Event listeners
            courtSelect.addEventListener('change', function() {
                updateSummary();
            });

            bookingDate.addEventListener('change', function() {
                loadAvailableSlots();
                updateSummary();
            });

            document.getElementById('durationHours').addEventListener('change', function() {
                updateSummary();
            });

            // Form submission
            bookingForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(bookingForm);
                const submitBtn = document.getElementById('submitBtn');

                // Validate inputs
                const courtId = formData.get('court_id');
                const customerName = formData.get('customer_name');
                const customerPhone = formData.get('customer_phone');
                const bookingDate = formData.get('booking_date');
                const startTime = formData.get('start_time');
                const durationHours = formData.get('duration_hours');

                if (!courtId) {
                    toastr.warning('Vui lòng chọn sân');
                    return;
                }

                if (!bookingDate) {
                    toastr.warning('Vui lòng chọn ngày đặt sân');
                    return;
                }

                if (!startTime) {
                    toastr.warning('Vui lòng chọn thời gian');
                    return;
                }

                if (!durationHours || durationHours <= 0) {
                    toastr.warning('Vui lòng chọn thời lượng');
                    return;
                }

                if (!customerName || !customerPhone) {
                    toastr.warning('Vui lòng nhập đầy đủ thông tin người đặt');
                    return;
                }

                const paymentMethod = formData.get('payment_method');
                if (!paymentMethod) {
                    toastr.warning('Vui lòng chọn phương thức thanh toán');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang xử lý...';

                try {
                    const amount = document.getElementById('summaryTotal').textContent.replace(/[^\d]/g,
                        '');
                    const bookingData = {
                        court_id: parseInt(courtId),
                        customer_name: customerName,
                        customer_phone: customerPhone,
                        customer_email: formData.get('customer_email') || null,
                        booking_date: bookingDate,
                        start_time: startTime,
                        duration_hours: parseInt(formData.get('duration_hours')),
                        hourly_rate: parseInt(formData.get('hourly_rate')),
                        payment_method: paymentMethod,
                        notes: formData.get('notes') || null,
                        total_amount: parseInt(amount)
                    };

                    // Always submit booking first (server generates booking_code)
                    submitBooking(bookingData, submitBtn, paymentMethod);
                } catch (error) {
                    toastr.error('Đã xảy ra lỗi khi gửi yêu cầu. Vui lòng thử lại.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Đặt sân';
                }
            });

            // Track current booking for proof upload
            let currentBookingId = null;
            let proofUploaded = false;

            // Select proof button
            document.getElementById('selectProofBtn').addEventListener('click', function() {
                document.getElementById('transferProofInput').click();
            });

            // Handle file selection
            document.getElementById('transferProofInput').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Client-side validation
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    toastr.error('Chỉ chấp nhận ảnh JPG, JPEG, PNG.');
                    this.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    toastr.error('Ảnh không được vượt quá 5MB.');
                    this.value = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('proofPreviewImg').src = ev.target.result;
                    document.getElementById('proofPreviewContainer').style.display = 'block';
                    document.getElementById('proofUploadStatus').textContent = '';
                };
                reader.readAsDataURL(file);

                // Upload
                uploadProofImage(file);
            });

            // Upload proof image
            async function uploadProofImage(file) {
                if (!currentBookingId) {
                    toastr.error('Không tìm thấy đơn đặt sân.');
                    return;
                }

                const formData = new FormData();
                formData.append('proof_image', file);

                // Show progress
                document.getElementById('proofProgressBar').style.display = 'block';
                document.getElementById('proofProgressFill').style.width = '30%';

                try {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', `/api/bookings/${currentBookingId}/upload-proof`);
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('input[name="_token"]').value);

                    xhr.upload.onprogress = function(e) {
                        if (e.lengthComputable) {
                            const pct = Math.round((e.loaded / e.total) * 100);
                            document.getElementById('proofProgressFill').style.width = pct + '%';
                        }
                    };

                    xhr.onload = function() {
                        document.getElementById('proofProgressBar').style.display = 'none';
                        const result = JSON.parse(xhr.responseText);

                        if (xhr.status === 200 && result.success) {
                            proofUploaded = true;
                            document.getElementById('proofUploadStatus').textContent =
                                '[OK] Đã tải ảnh thành công!';
                            document.getElementById('proofUploadStatus').style.color = '#27ae60';
                            document.getElementById('confirmPaymentBtn').disabled = false;
                            document.getElementById('selectProofBtn').textContent =
                            '[UPLOAD] Chọn ảnh khác';
                            toastr.success('Upload ảnh xác nhận thành công!');
                        } else {
                            document.getElementById('proofUploadStatus').textContent = result.message ||
                                'Upload thất bại.';
                            document.getElementById('proofUploadStatus').style.color = '#e74c3c';
                            toastr.error(result.message || 'Upload thất bại.');
                        }
                    };

                    xhr.onerror = function() {
                        document.getElementById('proofProgressBar').style.display = 'none';
                        document.getElementById('proofUploadStatus').textContent =
                            'Lỗi kết nối. Vui lòng thử lại.';
                        document.getElementById('proofUploadStatus').style.color = '#e74c3c';
                        toastr.error('Lỗi kết nối. Vui lòng thử lại.');
                    };

                    xhr.send(formData);
                } catch (error) {
                    document.getElementById('proofProgressBar').style.display = 'none';
                    toastr.error('Lỗi khi upload ảnh.');
                }
            }

            // Function to show payment QR modal with server-generated booking code
            async function showPaymentQRModal(bookingData, bookingCode, bookingId) {
                const amount = bookingData.total_amount;
                const stadiumId = document.getElementById('stadiumId').value;

                // Store booking code for later use in success modal
                document.getElementById('successBookingCode').textContent = bookingCode;

                // Set booking ID and reset upload UI
                currentBookingId = bookingId;
                proofUploaded = false;
                document.getElementById('transferProofInput').value = '';
                document.getElementById('proofPreviewContainer').style.display = 'none';
                document.getElementById('proofProgressBar').style.display = 'none';
                document.getElementById('proofUploadStatus').textContent = '';
                document.getElementById('selectProofBtn').textContent =
                    '[UPLOAD] Chọn ảnh xác nhận chuyển khoản';
                document.getElementById('confirmPaymentBtn').disabled = true;

                try {
                    // Fetch bank info from API
                    const response = await fetch(`/api/stadiums/${stadiumId}/bank-info`, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    });

                    const result = await response.json();

                    if (!result.success || !result.data) {
                        toastr.error('Không thể lấy thông tin ngân hàng. Vui lòng thử lại.');
                        return;
                    }

                    const bankInfo = result.data;

                    // Generate QR code URL using server booking code
                    const qrUrl =
                        `https://img.vietqr.io/image/${bankInfo.bank_code}-${bankInfo.account_number}-compact.png?amount=${amount}&addInfo=${encodeURIComponent(bookingCode)}`;

                    // Update modal with QR info
                    document.getElementById('qrCodeImage').src = qrUrl;
                    document.getElementById('bankOwner').textContent = bankInfo.account_name;
                    document.getElementById('bankAccount').textContent = bankInfo.account_number;
                    document.getElementById('bankName').textContent = bankInfo.bank_name || 'Ngân hàng';
                    document.getElementById('qrAmount').textContent = amount.toLocaleString('vi-VN');
                    document.getElementById('qrContent').textContent = bookingCode;

                    // Show modal
                    const modal = document.getElementById('paymentQRModal');
                    modal.classList.add('show');
                    modal.style.display = 'block';
                    modal.style.zIndex = '10000';
                    document.body.style.overflow = 'hidden';
                } catch (error) {
                    console.error('Error fetching bank info:', error);
                    toastr.error('Không thể lấy thông tin ngân hàng. Vui lòng thử lại.');
                }
            }

            // Close modal functions
            function closePaymentModal(skipWarning) {
                if (!skipWarning && !proofUploaded) {
                    if (!confirm('Bạn chưa tải ảnh xác nhận chuyển khoản. Bạn có chắc chắn muốn đóng?')) {
                        return;
                    }
                }
                const modal = document.getElementById('paymentQRModal');
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            // Close button handler
            document.querySelectorAll('[data-dismiss="modal"]').forEach(btn => {
                btn.addEventListener('click', closePaymentModal);
            });

            // Click outside modal to close
            document.getElementById('paymentQRModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closePaymentModal();
                }
            });

            // Confirm payment button - show success modal after confirming payment
            document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
                const bookingCode = document.getElementById('successBookingCode').textContent;
                closePaymentModal(true);
                setTimeout(function() {
                    showBookingSuccessModal(bookingCode);
                }, 300);
            });

            // Function to submit booking
            async function submitBooking(bookingData, submitBtn, paymentMethod) {
                try {
                    const response = await fetch('/api/bookings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify(bookingData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        const displayCode = result.booking.formatted_booking_code || result.booking.booking_id;

                        if (paymentMethod === 'transfer') {
                            // For transfer: show QR modal first, then success modal after payment confirmation
                            showPaymentQRModal(bookingData, displayCode, result.booking.id);
                        } else {
                            // For other payment methods: show success modal immediately
                            showBookingSuccessModal(displayCode);
                            
                            // Reset form after 2 seconds
                            setTimeout(function() {
                                bookingForm.reset();
                                loadAvailableSlots();
                                updateSummary();
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Đặt sân';
                                window.pendingBookingData = null;
                            }, 2000);
                        }
                    } else {
                        toastr.error('Lỗi: ' + (result.message || 'Đặt sân thất bại'));
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Đặt sân';
                    }
                } catch (error) {
                    toastr.error('Đã xảy ra lỗi khi gửi yêu cầu. Vui lòng thử lại.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Đặt sân';
                }
            }

            // Helper function to show booking success modal
            function showBookingSuccessModal(bookingCode) {
                document.getElementById('successBookingCode').textContent = bookingCode;
                const successModal = document.getElementById('bookingSuccessModal');
                successModal.classList.add('show');
                successModal.style.display = 'block';
                successModal.style.zIndex = '10000';
                document.body.style.overflow = 'hidden';

                // Setup close button handler
                const closeBtn = successModal.querySelector('[data-dismiss="modal"]');
                closeBtn.replaceWith(closeBtn.cloneNode(true));
                document.querySelector('#bookingSuccessModal [data-dismiss="modal"]').addEventListener('click', function() {
                    closeBookingSuccessModal();
                    // Reset form after closing
                    bookingForm.reset();
                    loadAvailableSlots();
                    updateSummary();
                    const submitBtn = document.getElementById('submitBtn');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Đặt sân';
                    window.pendingBookingData = null;
                });
            }

            // Helper function to close booking success modal
            function closeBookingSuccessModal() {
                const successModal = document.getElementById('bookingSuccessModal');
                successModal.classList.remove('show');
                successModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            // Initialize
            updateSummary();
        });
    </script>
@endsection
