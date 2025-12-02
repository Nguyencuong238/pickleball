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
                        <div class="booking-card">
                            <h2>Chọn ngày & giờ</h2>

                            <!-- Chọn Sân -->
                            <div class="form-group">
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
                                <h3>Chọn giờ *</h3>
                                <div class="slots-grid" id="slotsGrid">
                                    <!-- Time slots will be generated dynamically -->
                                    <p style="grid-column: 1/-1; text-align: center; color: #666;">Vui lòng chọn sân và ngày trước</p>
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

                            <div class="form-group">
                                <label>Phương thức thanh toán *</label>
                                <select class="form-control bg-white" name="payment_method" required>
                                    <option value="">-- Chọn phương thức --</option>
                                    <option value="cash">Tiền mặt</option>
                                    <option value="card">Thẻ tín dụng</option>
                                    <option value="transfer">Chuyển khoản</option>
                                    <option value="wallet">Ví điện tử</option>
                                </select>
                            </div>

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
                        <p class="payment-note">🔒 Thanh toán an toàn với VNPay, Momo, Banking</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const courtSelect = document.getElementById('courtSelect');
            const bookingDate = document.getElementById('bookingDate');
            const slotsGrid = document.getElementById('slotsGrid');
            const selectedSlot = document.getElementById('selectedSlot');
            const hourlyRate = document.getElementById('hourlyRate');
            const bookingForm = document.getElementById('bookingForm');
            let timeSlots = [];

            // Load available time slots from API
            async function loadAvailableSlots() {
                const courtId = courtSelect.value;
                const date = bookingDate.value;

                if (!courtId || !date) {
                    slotsGrid.innerHTML =
                        '<p style="grid-column: 1/-1; text-align: center; color: #666;">Vui lòng chọn sân và ngày trước</p>';
                    return;
                }

                try {
                    slotsGrid.innerHTML =
                        '<p style="grid-column: 1/-1; text-align: center; color: #666;">Đang tải...</p>';

                    const response = await fetch(`/api/courts/${courtId}/available-slots?date=${date}`);
                    const result = await response.json();

                    if (result.success && result.available_slots) {
                        timeSlots = result.available_slots;
                        generateTimeSlots();
                    } else {
                        slotsGrid.innerHTML =
                            '<p style="grid-column: 1/-1; text-align: center; color: #666;">Không thể tải khoảng thời gian</p>';
                    }
                } catch (error) {
                    console.error('Error loading slots:', error);
                    slotsGrid.innerHTML =
                        '<p style="grid-column: 1/-1; text-align: center; color: #666;">Lỗi khi tải dữ liệu</p>';
                }
            }

            // Generate time slots dynamically
            function generateTimeSlots() {
                slotsGrid.innerHTML = '';
                if (timeSlots.length === 0) {
                    slotsGrid.innerHTML =
                        '<p style="grid-column: 1/-1; text-align: center; color: #666;">Không có khoảng thời gian khả dụng</p>';
                    return;
                }

                timeSlots.forEach(slot => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'slot-btn' + (slot.is_booked ||slot.is_pending ? ' disabled' : '');

                    const priceDisplay = slot.price ? (slot.price / 1000).toFixed(0) + 'k' : '0k';
                    const statusText = slot.is_booked ? 'Đã đặt' : (slot.is_pending ? 'Đang chờ' : priceDisplay);

                    button.innerHTML =
                        `${slot.time} - ${String(slot.end_hour).padStart(2, '0')}:00<span>${statusText}</span>`;

                    if (!slot.is_booked) {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            selectSlot(slot, button);
                        });
                    } else {
                        button.disabled = true;
                    }

                    slotsGrid.appendChild(button);
                });
            }

            function selectSlot(slot, buttonElement) {
                document.querySelectorAll('.slot-btn:not(.disabled)').forEach(btn => btn.classList.remove(
                'active'));
                buttonElement.classList.add('active');
                selectedSlot.value = slot.time;
                hourlyRate.value = slot.price;
                updateSummary();
            }

            function updateSummary() {
                const courtId = courtSelect.value;
                const date = bookingDate.value;
                const time = selectedSlot.value;
                const rate = parseInt(hourlyRate.value) || 0;
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

                // Calculate total
                const subtotal = rate * duration;
                const fee = Math.round(subtotal * 0.05); // 5% service fee
                const total = subtotal + fee;

                document.getElementById('summaryDuration').textContent = `${duration} giờ`;
                document.getElementById('summaryHourlyRate').textContent = (rate ? rate.toLocaleString('vi-VN') :
                    0) + 'đ';
                document.getElementById('summarySubtotal').textContent = subtotal.toLocaleString('vi-VN') + 'đ';
                document.getElementById('summaryFee').textContent = fee.toLocaleString('vi-VN') + 'đ';
                document.getElementById('summaryTotal').textContent = total.toLocaleString('vi-VN') + 'đ';
            }

            // Event listeners
            courtSelect.addEventListener('change', function() {
                loadAvailableSlots();
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
                    const response = await fetch('/api/bookings', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            court_id: parseInt(courtId),
                            customer_name: customerName,
                            customer_phone: customerPhone,
                            customer_email: formData.get('customer_email') || null,
                            booking_date: bookingDate,
                            start_time: startTime,
                            duration_hours: parseInt(formData.get('duration_hours')),
                            hourly_rate: parseInt(formData.get('hourly_rate')),
                            payment_method: formData.get('payment_method'),
                            notes: formData.get('notes') || null,
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        toastr.success('Đặt sân thành công! Mã đơn đặt của bạn: ' + result.booking.booking_id +
                            '\n\nVui lòng chờ xác nhận.');
                        // Reset form
                        bookingForm.reset();
                        generateTimeSlots();
                        slotsGrid.innerHTML =
                        '<p style="grid-column: 1/-1; text-align: center; color: #666;">Vui lòng chọn sân và ngày trước</p>';
                        updateSummary();
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Đặt sân';
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
            });

            // Initialize
            updateSummary();
        });
    </script>
@endsection
