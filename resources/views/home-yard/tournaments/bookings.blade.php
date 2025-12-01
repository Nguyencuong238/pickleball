@extends('layouts/homeyard')

@section('css')
<style>
    /* Page-specific styles */
    .booking-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .booking-stat-card {
        background: var(--bg-white);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all var(--transition);
    }

    .booking-stat-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .booking-stat-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .booking-stat-icon.success {
        background: rgba(74, 222, 128, 0.1);
    }

    .booking-stat-icon.warning {
        background: rgba(255, 211, 61, 0.1);
    }

    .booking-stat-icon.info {
        background: rgba(0, 153, 204, 0.1);
    }

    .booking-stat-icon.danger {
        background: rgba(255, 107, 107, 0.1);
    }

    .booking-stat-content {
        flex: 1;
    }

    .booking-stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .booking-stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .booking-calendar {
        background: var(--bg-white);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 2rem;
    }

    .calendar-header-booking {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .calendar-title-booking {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .calendar-nav-booking {
         display: flex;
         gap: 0.5rem;
         align-items: center;
         flex-wrap: wrap;
     }

     .calendar-nav-booking input[type="date"] {
         min-width: 150px;
         border: 2px solid var(--border-color);
         border-radius: var(--radius-md);
         background: var(--bg-white);
         color: var(--text-primary);
         cursor: pointer;
         font-size: 0.875rem;
         transition: all var(--transition);
     }

     .calendar-nav-booking input[type="date"]:hover {
         border-color: var(--primary-color);
     }

     .calendar-nav-booking input[type="date"]:focus {
         outline: none;
         border-color: var(--primary-color);
         box-shadow: 0 0 0 3px rgba(0, 217, 181, 0.1);
     }

    .time-slots-container {
        display: grid;
        grid-template-columns: 100px 1fr;
        gap: 1rem;
    }

    .time-labels {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .time-label {
        height: 60px;
        display: flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .courts-grid {
        display: grid;
        grid-template-columns: repeat({{$courts->count()}}, 1fr);
        gap: 0.5rem;
    }

    .court-column {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .court-header-slot {
        height: 40px;
        background: var(--bg-light);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        color: var(--text-primary);
    }

    .time-slot {
        height: 60px;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all var(--transition);
        font-size: 0.75rem;
        position: relative;
    }

    .time-slot:hover {
        border-color: var(--primary-color);
        background: rgba(0, 217, 181, 0.05);
    }

    .time-slot.available {
        background: rgba(74, 222, 128, 0.1);
        border-color: var(--accent-green);
    }

    .time-slot.booked {
        background: rgba(255, 107, 107, 0.1);
        border-color: var(--accent-red);
        cursor: default;
    }

    .time-slot.pending {
        background: rgba(255, 211, 61, 0.1);
        border-color: #f59e0b;
    }

    .booking-list-table {
        background: var(--bg-white);
    }

    .booking-row {
        display: grid;
        grid-template-columns: auto 1fr auto auto auto;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
        transition: all var(--transition);
    }

    .booking-row:hover {
        background: var(--bg-light);
    }

    .booking-row:last-child {
        border-bottom: none;
    }

    .booking-id {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .booking-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .booking-customer {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .booking-details {
        font-size: 0.75rem;
        color: var(--text-light);
        display: flex;
        gap: 1rem;
    }

    .booking-price {
        font-weight: 700;
        font-size: 1rem;
        color: var(--primary-color);
    }

    .booking-actions {
        display: flex;
        gap: 0.5rem;
    }

    .filter-bar-booking {
        background: var(--bg-white);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
    }

    .filter-grid-booking {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .quick-date-filters {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .quick-date-btn {
        padding: 0.5rem 1rem;
        background: var(--bg-white);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-full);
        cursor: pointer;
        transition: all var(--transition);
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .quick-date-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .quick-date-btn.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: transparent;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        padding: 2rem;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-xl);
        animation: fadeIn 0.3s ease;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-light);
        transition: color var(--transition);
    }

    .modal-close:hover {
        color: var(--accent-red);
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 2px solid var(--border-color);
    }

    .booking-summary {
        background: var(--bg-light);
        padding: 1.5rem;
        border-radius: var(--radius-md);
        margin: 1rem 0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .summary-row:last-child {
        border-bottom: none;
        font-weight: 700;
        font-size: 1.125rem;
        color: var(--primary-color);
        padding-top: 1rem;
    }

    .summary-label {
        color: var(--text-secondary);
    }

    .summary-value {
        font-weight: 600;
        color: var(--text-primary);
    }

    .tabs-booking {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--border-color);
        overflow-x: auto;
    }

    .tab-booking {
        padding: 0.75rem 1.5rem;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all var(--transition);
        font-weight: 600;
        white-space: nowrap;
    }

    .tab-booking:hover {
        color: var(--primary-color);
    }

    .tab-booking.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .tab-content-booking {
        display: none;
    }

    .tab-content-booking.active {
        display: block;
    }
    .form-label {
        font-size: 1rem;
    }

    .pagination-container {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        align-items: center;
        padding: 1.5rem;
        flex-wrap: wrap;
    }

    .pagination-btn {
        padding: 0.5rem 0.75rem;
        min-width: 36px;
        height: 36px;
        border: 2px solid var(--border-color);
        background: var(--bg-white);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all var(--transition);
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pagination-btn:hover:not(:disabled) {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: rgba(0, 217, 181, 0.05);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: transparent;
        box-shadow: var(--shadow-md);
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: var(--bg-light);
    }

    #paginationContainer {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        align-items: center;
        padding: 1.5rem;
        flex-wrap: wrap;
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        margin-top: 1.5rem;
    }

    .pagination {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        align-items: center;
        padding: 1.5rem;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .top-header {
            margin-top: 100px;
        }
    }
</style>
@endsection

@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Header -->
            <div class="top-header">
                <div class="header-left">
                    <h1>Quản Lý Đặt Sân</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="{{route('homeyard.overview')}}" class="breadcrumb-link">Dashboard</a>
                        </span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-item">Đặt Sân</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" placeholder="Tìm kiếm đơn đặt...">
                    </div>
                    <div class="header-user">
                        <div class="user-avatar">{{ auth()->user()->getInitials() }}</div>
                        <div class="user-info">
                            <div class="user-name">{{auth()->user()->name}}</div>
                            <div class="user-role">{{auth()->user()->getFirstRoleName()}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Overview Stats -->
            <div class="booking-overview fade-in">
                <div class="booking-stat-card">
                    <div class="booking-stat-icon info">📊</div>
                    <div class="booking-stat-content">
                        <div class="booking-stat-value" id="totalBookingsCount">0</div>
                        <div class="booking-stat-label">Tổng Đơn Tháng Này</div>
                    </div>
                </div>
                <div class="booking-stat-card">
                    <div class="booking-stat-icon success">✅</div>
                    <div class="booking-stat-content">
                        <div class="booking-stat-value" id="confirmedBookingsCount">0</div>
                        <div class="booking-stat-label">Đã Xác Nhận</div>
                    </div>
                </div>
                <div class="booking-stat-card">
                    <div class="booking-stat-icon warning">⏳</div>
                    <div class="booking-stat-content">
                        <div class="booking-stat-value" id="pendingBookingsCount">0</div>
                        <div class="booking-stat-label">Chờ Xác Nhận</div>
                    </div>
                </div>
                <div class="booking-stat-card">
                    <div class="booking-stat-icon danger">❌</div>
                    <div class="booking-stat-content">
                        <div class="booking-stat-value" id="cancelledBookingsCount">0</div>
                        <div class="booking-stat-label">Đã Hủy</div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card fade-in">
                <div class="card-header" style="flex-wrap: wrap; gap: 1rem">
                    <h3 class="card-title" style="white-space: nowrap">Quản Lý Đặt Sân</h3>
                    <div class="card-actions">
                        <button class="btn btn-secondary btn-sm">📥 Xuất Excel</button>
                        <button class="btn btn-primary btn-sm" onclick="openNewBookingModal()">
                            ➕ Đặt Sân Mới
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tabs-booking">
                        <button class="tab-booking active" onclick="switchTabBooking('calendar')">
                            📅 Lịch Đặt Sân
                        </button>
                        <button class="tab-booking" onclick="switchTabBooking('list')">
                            📋 Danh Sách Đơn
                        </button>
                    </div>

                    <!-- Calendar Tab -->
                    <div class="tab-content-booking active" id="calendar">

                        <div class="booking-calendar">
                            <div class="calendar-header-booking">
                                <h3 class="calendar-title-booking" id="calendarTitle">Hôm nay</h3>
                                <div class="calendar-nav-booking">
                                    {{-- <button class="btn btn-secondary btn-sm" onclick="navigateDate(-1)">‹ Hôm qua</button>
                                    <button class="btn btn-secondary btn-sm" onclick="navigateDate(0)">Hôm nay</button>
                                    <button class="btn btn-secondary btn-sm" onclick="navigateDate(1)">Ngày mai ›</button> --}}
                                    <input type="date" id="calendarDatePicker" class="form-input" style="height: 36px; padding: 0.5rem;" onchange="selectDate(this.value)">
                                </div>
                            </div>

                            <div class="time-slots-container">
                                <div class="time-labels">
                                    <div class="time-label" style="height: 40px;"></div>
                                    <div class="time-label">06:00</div>
                                    <div class="time-label">07:00</div>
                                    <div class="time-label">08:00</div>
                                    <div class="time-label">09:00</div>
                                    <div class="time-label">10:00</div>
                                    <div class="time-label">11:00</div>
                                    <div class="time-label">12:00</div>
                                    <div class="time-label">13:00</div>
                                    <div class="time-label">14:00</div>
                                    <div class="time-label">15:00</div>
                                    <div class="time-label">16:00</div>
                                    <div class="time-label">17:00</div>
                                    <div class="time-label">18:00</div>
                                    <div class="time-label">19:00</div>
                                    <div class="time-label">20:00</div>
                                    <div class="time-label">21:00</div>
                                    <div class="time-label">22:00</div>
                                    <div class="time-label">23:00</div>
                                </div>

                                <div class="courts-grid">
                                    
                                </div>
                            </div>

                            <div
                                style="margin-top: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md);">
                                <div style="display: flex; gap: 2rem; justify-content: center; font-size: 0.875rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div
                                            style="width: 20px; height: 20px; background: rgba(74, 222, 128, 0.1); border: 2px solid var(--accent-green); border-radius: 4px;">
                                        </div>
                                        <span>Trống</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div
                                            style="width: 20px; height: 20px; background: rgba(255, 107, 107, 0.1); border: 2px solid var(--accent-red); border-radius: 4px;">
                                        </div>
                                        <span>Đã đặt</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div
                                            style="width: 20px; height: 20px; background: rgba(255, 211, 61, 0.1); border: 2px solid #f59e0b; border-radius: 4px;">
                                        </div>
                                        <span>Chờ xác nhận</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List Tab -->
                    <div class="tab-content-booking" id="list">
                        <div class="filter-bar-booking">
                            <div class="filter-grid-booking">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Tìm kiếm</label>
                                    <input type="text" id="searchInput" class="form-input" placeholder="Mã đơn, tên khách..." oninput="debouncedSearch()">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Trạng thái</label>
                                    <select id="statusFilter" class="form-select" onchange="applyFilters()">
                                        <option value="">Tất cả</option>
                                        <option value="confirmed">Đã xác nhận</option>
                                        <option value="pending">Chờ xác nhận</option>
                                        <option value="cancelled">Đã hủy</option>
                                        <option value="completed">Hoàn thành</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Sân</label>
                                    <select id="courtFilter" class="form-select" onchange="applyFilters()">
                                        <option value="">Tất cả</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Từ ngày</label>
                                    <input type="date" id="dateFromFilter" class="form-input" onchange="applyFilters()">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Đến ngày</label>
                                    <input type="date" id="dateToFilter" class="form-input" onchange="applyFilters()">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">&nbsp;</label>
                                    <button class="btn btn-primary" style="width: 100%;" onclick="resetFilters()">
                                        🔄 Đặt lại
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="booking-list-table" id="bookingListTable">
                            <!-- Bookings will be loaded here -->
                        </div>

                        <div style="margin-top: 1.5rem;">
                            <div class="pagination" id="paginationContainer">
                                <!-- Pagination will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="modal" id="newBookingModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Đặt Sân Mới</h3>
                <button class="modal-close" onclick="closeNewBookingModal()">×</button>
            </div>
            <form id="bookingForm" data-courts='@json($courts)'>
                <div class="form-group">
                    <label class="form-label">Tên khách hàng *</label>
                    <input type="text" class="form-input" name="customer_name" placeholder="Nhập tên khách hàng" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Số điện thoại *</label>
                    <input type="tel" class="form-input" name="customer_phone" placeholder="0901234567" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" name="customer_email" placeholder="example@email.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Chọn sân *</label>
                    <select class="form-select" name="court_id" required onchange="updateCourtRate()">
                        <option value="">Chọn sân</option>
                        @forelse($courts as $court)
                            <option value="{{ $court->id }}">{{ $court->court_name }} - {{ ucfirst(str_replace('-', ' ', $court->court_type)) }} {{ ucfirst(str_replace('-', ' ', $court->surface_type)) }}</option>
                        @empty
                            <option value="" disabled>Không có sân nào</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày đặt *</label>
                    <input type="date" class="form-input" name="booking_date" required onchange="updateCourtRate()">
                </div>
                <div class="form-group">
                    <label class="form-label">Giờ bắt đầu *</label>
                    <select class="form-select" name="start_time" required onchange="updateCourtRate()">
                        <option value="">Chọn giờ</option>
                        <option value="06:00">06:00</option>
                        <option value="07:00">07:00</option>
                        <option value="08:00">08:00</option>
                        <option value="09:00">09:00</option>
                        <option value="10:00">10:00</option>
                        <option value="11:00">11:00</option>
                        <option value="12:00">12:00</option>
                        <option value="13:00">13:00</option>
                        <option value="14:00">14:00</option>
                        <option value="15:00">15:00</option>
                        <option value="16:00">16:00</option>
                        <option value="17:00">17:00</option>
                        <option value="18:00">18:00</option>
                        <option value="19:00">19:00</option>
                        <option value="20:00">20:00</option>
                        <option value="21:00">21:00</option>
                        <option value="22:00">22:00</option>
                        <option value="23:00">23:00</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Thời gian (giờ) *</label>
                    <select class="form-select" name="duration_hours" required onchange="updateCourtRate()">
                        <option value="">Chọn thời gian</option>
                        <option value="1">1 giờ</option>
                        <option value="2">2 giờ</option>
                        <option value="3">3 giờ</option>
                        <option value="4">4 giờ</option>
                        <option value="5">5 giờ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Phương thức thanh toán *</label>
                    <select class="form-select" name="payment_method" required>
                        <option value="">Chọn phương thức</option>
                        <option value="cash">Tiền mặt</option>
                        <option value="card">Thẻ tín dụng</option>
                        <option value="transfer">Chuyển khoản</option>
                        <option value="wallet">Ví điện tử</option>
                    </select>
                </div>
                <div class="booking-summary">
                    <div class="summary-row">
                        <span class="summary-label">Giá sân</span>
                        <span class="summary-value" id="hourlyRateDisplay">₫0/giờ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Thời gian</span>
                        <span class="summary-value" id="durationDisplay">2 giờ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Tổng tiền</span>
                        <span class="summary-value" id="totalPriceDisplay">₫300,000</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-textarea" name="notes" placeholder="Yêu cầu đặc biệt..."></textarea>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeNewBookingModal()">Hủy</button>
                <button class="btn btn-primary" onclick="submitBooking()">💾 Xác Nhận Đặt Sân</button>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal" id="bookingDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Chi Tiết Đơn Đặt #BK-001</h3>
                <button class="modal-close" onclick="closeBookingDetailsModal()">×</button>
            </div>
            <div>
                <div class="form-group">
                    <label class="form-label">Thông Tin Khách Hàng</label>
                    <div class="court-info">
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Tên khách hàng:</div>
                            <div class="court-info-value" id="modalCustomerName">Nguyễn Văn An</div>
                        </div>
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Số điện thoại:</div>
                            <div class="court-info-value" id="modalCustomerPhone">0901234567</div>
                        </div>
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Email:</div>
                            <div class="court-info-value" id="modalCustomerEmail">nguyenvanan@email.com</div>
                        </div>
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Trạng thái:</div>
                            <div class="court-info-value" id="modalStatus">
                                <span class="badge badge-success">Đã xác nhận</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Thông Tin Đặt Sân</label>
                    <div class="court-info">
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Sân:</div>
                            <div class="court-info-value" id="modalCourtName">Sân 1 - Indoor Standard</div>
                        </div>
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Ngày đặt:</div>
                            <div class="court-info-value" id="modalBookingDate">20/01/2025</div>
                        </div>
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Giờ:</div>
                            <div class="court-info-value" id="modalTimeRange">14:00 - 16:00</div>
                        </div>
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Thời gian:</div>
                            <div class="court-info-value" id="modalDuration">2 giờ</div>
                        </div>
                    </div>
                </div>

                <div class="booking-summary">
                    <!-- Pricing summary will be populated dynamically -->
                    <div class="summary-row">
                        <span class="summary-label">Đang tải giá...</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ghi Chú</label>
                    <div id="modalNotes"
                        style="padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); color: var(--text-secondary);">
                        Yêu cầu chuẩn bị nước uống và khăn lạnh
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Thông Tin Đơn</label>
                    <div class="court-info">
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Ngày tạo:</div>
                            <div class="court-info-value" id="modalCreatedAt">18/01/2025 10:30</div>
                        </div>
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Người tạo:</div>
                            <div class="court-info-value" id="modalCreatedBy">Admin User</div>
                        </div>
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Thanh toán:</div>
                            <div class="court-info-value" id="modalPaymentMethod">Tiền mặt</div>
                        </div>
                        <div class="court-info-item grid grid-2 mb-1">
                            <div class="court-info-label">Mã đơn:</div>
                            <div class="court-info-value" id="modalBookingId">#BK-001</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                 <button class="btn btn-danger" onclick="deleteBookingFromModal()">🗑️ Xóa Vĩnh Viễn</button>
                 <button class="btn btn-warning" onclick="cancelBooking()">❌ Hủy Đơn</button>
                 <button class="btn btn-secondary" onclick="closeBookingDetailsModal()">Đóng</button>
                 {{-- <button class="btn btn-primary">✏️ Chỉnh Sửa</button> --}}
             </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        // Tab switching
        function switchTabBooking(tabName) {
            document.querySelectorAll('.tab-content-booking').forEach(content => {
                content.classList.remove('active');
            });

            document.querySelectorAll('.tab-booking').forEach(tab => {
                tab.classList.remove('active');
            });

            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            // Load bookings list when switching to list tab
            if (tabName === 'list') {
                loadBookingsList();
            }
        }

        // Load courts data from form
        let courtsData = {};
        let bookingsData = [];
        let currentSelectedDate = '{{$date}}';

        function initCourtsData() {
             const form = document.getElementById('bookingForm');
             const courstsJson = form.getAttribute('data-courts');
             if (courstsJson) {
                 try {
                     const courts = JSON.parse(courstsJson);
                     courts.forEach(court => {
                         // Default pricing: 150k per hour (can be customized based on court type)
                         courtsData[court.id] = {
                             id: court.id,
                             name: court.court_name,
                             hourly_rate: court.rental_price  // Default rate
                         };
                     });
                     // Initialize date picker with current selected date
                     updateCalendarDatePicker(currentSelectedDate);
                     // Load calendar for today
                     loadCalendarData(currentSelectedDate);
                 } catch (e) {
                     console.error('Error parsing courts data:', e);
                 }
             }
         }

        // Format date with day name
        function formatDateDisplay(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            const dayNames = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
            const day = dayNames[date.getDay()];
            const dateFormatted = date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
            return `${day}, ${dateFormatted}`;
        }

        // Navigate to different dates
         function navigateDate(days) {
             const currentDate = new Date(currentSelectedDate);
             currentDate.setDate(currentDate.getDate() + days);
             const newDate = currentDate.toISOString().split('T')[0];
             updateCalendarDatePicker(newDate);
             loadCalendarData(newDate);
         }

         // Select date from date picker
         function selectDate(dateStr) {
             if (dateStr) {
                 updateCalendarDatePicker(dateStr);
                 loadCalendarData(dateStr);
             }
         }

         // Update date picker value
         function updateCalendarDatePicker(dateStr) {
             document.getElementById('calendarDatePicker').value = dateStr;
             currentSelectedDate = dateStr;
         }

        // Load bookings data for selected date
        function loadCalendarData(date) {
            
            // Update calendar title
            document.getElementById('calendarTitle').textContent = formatDateDisplay(date);
            
            // Fetch bookings for this date
            fetch(`/homeyard/bookings/by-date?date=${date}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                bookingsData = data.bookings || [];
                renderCourtGrid(date);
            })
            .catch(error => {
                console.error('Error loading bookings:', error);
                renderCourtGrid(date);  // Render without bookings
            });
        }

        // Render the courts grid
        function renderCourtGrid(date) {
            const courts = Object.values(courtsData);
            let html = '';

            courts.forEach(court => {
                html += `
                    <div class="court-column">
                        <div class="court-header-slot">${court.name}</div>
                `;

                // Generate time slots from 6:00 to 21:00
                for (let hour = 6; hour < 24; hour++) {
                    const timeStr = String(hour).padStart(2, '0') + ':00';
                    const isBooked = isTimeSlotBooked(court.id, timeStr);
                    const slotClass = isBooked ? 'booked' : 'available';
                    const emoji = isBooked ? '🔴' : '✅';
                    const onclick = isBooked ? '' : `onclick="bookSlot(${court.id}, '${timeStr}', '${currentSelectedDate}')"`;

                    html += `<div class="time-slot ${slotClass}" ${onclick}>${emoji}</div>`;
                }

                html += '</div>';
            });

            document.querySelector('.courts-grid').innerHTML = html;
        }

        // Check if a time slot is booked
        function isTimeSlotBooked(courtId, timeStr) {
            return bookingsData.some(booking => {
                if (booking.court_id != courtId) return false;
                
                const bookStart = booking.start_time;
                const bookEnd = booking.end_time;
                
                // Check if the slot time is within the booking time range
                return timeStr >= bookStart && timeStr < bookEnd;
            });
        }

        // Book slot
        function bookSlot(court, time, date) {
            // Reset form first
            const form = document.getElementById('bookingForm');
            form.reset();
            document.getElementById('bookingForm').dataset.hourlyRate = 150000;
            
            // Then pre-fill court and start time
            form.querySelector('select[name="court_id"]').value = court;
            form.querySelector('select[name="start_time"]').value = time;
            form.querySelector('input[name="booking_date"]').value = date;
            
            updateCourtRate();
            openNewBookingModalDirect();
        }

        // Update court hourly rate with multi-price support
        function updateCourtRate() {
            const courtId = document.querySelector('select[name="court_id"]').value;
            const bookingDate = document.querySelector('input[name="booking_date"]').value;
            const startTime = document.querySelector('select[name="start_time"]').value.trim();
            const durationHours = parseFloat(document.querySelector('select[name="duration_hours"]').value) || 0;
            
            if (!courtId || !bookingDate || !startTime || durationHours === 0) {
                return;
            }
            
            // Validate start_time format (H:i)
            if (!/^\d{2}:\d{2}$/.test(startTime)) {
                console.error('Invalid start_time format. Expected H:i, got:', startTime);
                return;
            }
            
            // Call API to calculate price
            fetch('{{ route('homeyard.bookings.calculate-price') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    court_id: courtId,
                    booking_date: bookingDate,
                    start_time: startTime,
                    duration_hours: Math.floor(durationHours)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store pricing data
                    document.getElementById('bookingForm').dataset.hourlyRate = data.average_hourly_rate;
                    document.getElementById('bookingForm').dataset.totalPrice = data.total_price;
                    document.getElementById('bookingForm').dataset.priceBreakdown = JSON.stringify(data.price_breakdown);
                    document.getElementById('bookingForm').dataset.hasMultiPrice = data.has_multi_price;
                    
                    // Update summary display
                    updateBookingSummaryDisplay(data);
                } else {
                    console.error('Price calculation error:', data.message);
                    // Fallback to simple calculation
                    const courtInfo = courtsData[courtId];
                    const rate = (courtInfo && courtInfo.hourly_rate) ? courtInfo.hourly_rate : 150000;
                    document.getElementById('bookingForm').dataset.hourlyRate = rate;
                    document.getElementById('bookingForm').dataset.hasMultiPrice = false;
                    calculateTotal();
                }
            })
            .catch(error => {
                console.error('Error calculating price:', error);
                // Fallback to simple calculation
                const courtInfo = courtsData[courtId];
                const rate = (courtInfo && courtInfo.hourly_rate) ? courtInfo.hourly_rate : 150000;
                document.getElementById('bookingForm').dataset.hourlyRate = rate;
                document.getElementById('bookingForm').dataset.hasMultiPrice = false;
                calculateTotal();
            });
        }

        // Update booking summary display with multi-price support
        function updateBookingSummaryDisplay(data) {
            const summaryContainer = document.querySelector('.booking-summary');
            let html = '';
            
            if (data.has_multi_price && data.price_breakdown.length > 0) {
                // Multi-price display with breakdown sections
                html = '<h4 style="margin-bottom: 1rem; font-size: 0.95rem; color: var(--text-primary);">Chi tiết giá sân</h4>';
                
                // Show each hour's pricing
                data.price_breakdown.forEach(item => {
                    html += `
                        <div class="summary-row">
                            <span class="summary-label">${item.start_time} - ${item.end_time} (${item.pricing_label})</span>
                            <span class="summary-value">₫${item.price_per_hour.toLocaleString('vi-VN')}</span>
                        </div>
                    `;
                });
                
                // Add separator
                // html += '<div style="margin: 1rem 0; border-top: 2px solid var(--border-color);"></div>';
                
                // Show total
                html += `
                    <div class="summary-row">
                        <span class="summary-label">Tổng tiền (${data.duration_hours} giờ)</span>
                        <span class="summary-value" style="font-size: 1.25rem; color: var(--primary-color);">₫${data.total_price.toLocaleString('vi-VN')}</span>
                    </div>
                `;
            } else {
                // Simple single-price display
                const hourlyRate = data.average_hourly_rate;
                html = `
                    <div class="summary-row">
                        <span class="summary-label">Giá sân</span>
                        <span class="summary-value">₫${hourlyRate.toLocaleString('vi-VN')}/giờ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Thời gian</span>
                        <span class="summary-value">${data.duration_hours} giờ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Tổng tiền</span>
                        <span class="summary-value">₫${data.total_price.toLocaleString('vi-VN')}</span>
                    </div>
                `;
            }
            
            summaryContainer.innerHTML = html;
        }

        // Calculate total price (fallback for simple cases)
        function calculateTotal() {
            const duration = parseFloat(document.querySelector('select[name="duration_hours"]').value) || 0;
            const rate = parseFloat(document.getElementById('bookingForm').dataset.hourlyRate) || 0;
            const total = Math.round(rate * duration);
            
            document.getElementById('durationDisplay').textContent = duration + ' giờ';
            document.getElementById('totalPriceDisplay').textContent = '₫' + total.toLocaleString('vi-VN');
        }

        // Submit booking
        function submitBooking() {
            const form = document.getElementById('bookingForm');
            const formData = new FormData(form);
            
            // Add hourly_rate (use average rate, or fallback to stored rate)
            const hourlyRate = form.dataset.hourlyRate || 150000;
            formData.append('hourly_rate', hourlyRate);
            
            // If we have a calculated total price from multi-price calculation, 
            // we'll use that in the controller since total_price = hourly_rate * duration_hours
            formData.append('status', 'completed');

            fetch('{{ route('homeyard.bookings.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Response Error:', response.status, text);
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        closeNewBookingModal();
                        form.reset();
                        document.getElementById('bookingForm').dataset.hourlyRate = 150000;
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Booking error:', error);
                    toastr.error(error.message || 'Lỗi khi đặt sân');
                });
        }

        // Modal functions
        function openNewBookingModal() {
            // Reset form
            const form = document.getElementById('bookingForm');
            form.reset();
            document.getElementById('bookingForm').dataset.hourlyRate = 150000;
            calculateTotal();
            
            document.getElementById('newBookingModal').classList.add('active');
        }

        // Open modal without resetting form (used when pre-filling from calendar)
        function openNewBookingModalDirect() {
            document.getElementById('newBookingModal').classList.add('active');
        }

        function closeNewBookingModal() {
            document.getElementById('newBookingModal').classList.remove('active');
        }

        function viewBookingDetails(bookingId) {
            // Extract numeric ID from BK-XXXXXX format
            const numericId = bookingId.replace('BK-', '');
            
            fetch(`/homeyard/bookings/${numericId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.booking) {
                    populateBookingDetailsModal(data.booking);
                    document.getElementById('bookingDetailsModal').classList.add('active');
                } else {
                    toastr.error('Không thể tải chi tiết đơn đặt');
                }
            })
            .catch(error => {
                console.error('Error loading booking details:', error);
                toastr.error('Lỗi khi tải chi tiết đơn đặt');
            });
        }

        function populateBookingDetailsModal(booking) {
            // Format date
            const bookingDate = new Date(booking.booking_date).toLocaleDateString('vi-VN', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric' 
            });
            const createdAt = booking.created_at ? new Date(booking.created_at).toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : '-';
            
            // Calculate duration in hours
            const startTime = new Date(`2000-01-01 ${booking.start_time}`);
            const endTime = new Date(`2000-01-01 ${booking.end_time}`);
            const durationHours = Math.round((endTime - startTime) / (1000 * 60 * 60));
            
            // Calculate hourly rate
            const hourlyRate = booking.total_price ? Math.round(booking.total_price / durationHours) : 0;
            
            // Format times
            const startTimeStr = booking.start_time.substring(0, 5);
            const endTimeStr = booking.end_time.substring(0, 5);
            
            // Update modal title
            const bookingId = 'BK-' + String(booking.id).padStart(6, '0');
            document.getElementById('modalTitle').textContent = `Chi Tiết Đơn Đặt ${bookingId}`;
            
            // Update customer info
            document.getElementById('modalCustomerName').textContent = booking.customer_name || '-';
            document.getElementById('modalCustomerPhone').textContent = booking.customer_phone || '-';
            document.getElementById('modalCustomerEmail').textContent = booking.customer_email || '-';
            document.getElementById('modalStatus').innerHTML = getStatusBadge(booking.status);
            
            // Update booking info
            document.getElementById('modalCourtName').textContent = booking.court_name || 'Sân';
            document.getElementById('modalBookingDate').textContent = bookingDate;
            document.getElementById('modalTimeRange').textContent = `${startTimeStr} - ${endTimeStr}`;
            document.getElementById('modalDuration').textContent = `${durationHours} giờ`;
            
            // Fetch detailed pricing breakdown (normalize start_time to H:i format)
             console.log('Calling fetchAndUpdateBookingSummary with:', {
                 court_id: booking.court_id,
                 booking_date: booking.booking_date,
                 start_time: booking.start_time.substring(0, 5),
                 duration_hours: durationHours,
                 total_price: booking.total_price
             });
             fetchAndUpdateBookingSummary(booking.court_id, booking.booking_date, booking.start_time.substring(0, 5), durationHours, booking.total_price);
            
            // Update notes
            document.getElementById('modalNotes').textContent = booking.notes || 'Không có ghi chú';
            
            // Update booking info section
            document.getElementById('modalCreatedAt').textContent = createdAt;
            document.getElementById('modalCreatedBy').textContent = 'Admin User'; // This could be enhanced with actual creator info
            document.getElementById('modalPaymentMethod').textContent = booking.payment_method ? getPaymentMethodText(booking.payment_method) : '-';
            document.getElementById('modalBookingId').textContent = `#${bookingId}`;
        }

        // Fetch and update booking summary with detailed pricing
         function fetchAndUpdateBookingSummary(courtId, bookingDate, startTime, durationHours, totalPrice) {
             // Ensure startTime is in H:i format
             const normalizedStartTime = startTime.trim();
             
             // Validate start_time format (H:i)
             if (!/^\d{2}:\d{2}$/.test(normalizedStartTime)) {
                 console.error('Invalid start_time format. Expected H:i, got:', normalizedStartTime);
                 updateModalBookingSummarySimple(durationHours, totalPrice);
                 return;
             }
             
             console.log('Fetching pricing for:', { courtId, bookingDate, startTime: normalizedStartTime, durationHours });
             
             fetch('{{ route('homeyard.bookings.calculate-price') }}', {
                 method: 'POST',
                 headers: {
                     'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                     'Content-Type': 'application/json',
                     'Accept': 'application/json',
                 },
                 body: JSON.stringify({
                     court_id: parseInt(courtId),
                     booking_date: bookingDate,
                     start_time: normalizedStartTime,
                     duration_hours: parseInt(durationHours)
                 })
             })
             .then(response => {
                 console.log('Pricing response status:', response.status);
                 return response.json();
             })
             .then(data => {
                 console.log('Pricing data received:', data);
                 if (data.success) {
                     console.log('Price breakdown:', data.price_breakdown);
                     updateModalBookingSummary(data);
                 } else {
                     console.error('Pricing calculation failed:', data.message);
                     // Fallback to simple display
                     updateModalBookingSummarySimple(durationHours, totalPrice);
                 }
             })
             .catch(error => {
                 console.error('Error fetching pricing:', error);
                 // Fallback to simple display
                 updateModalBookingSummarySimple(durationHours, totalPrice);
             });
         }

        // Update modal summary with detailed pricing breakdown
         function updateModalBookingSummary(data) {
             // Get the active booking details modal, not the new booking modal
             const bookingDetailsModal = document.getElementById('bookingDetailsModal');
             const summaryContainer = bookingDetailsModal.querySelector('.booking-summary');
             console.log('summaryContainer found:', !!summaryContainer);
             let html = '';
             
             if (data.price_breakdown && data.price_breakdown.length > 0) {
                 console.log('Showing detailed pricing breakdown with', data.price_breakdown.length, 'items');
                 // Show pricing breakdown (multi-price or single-price with details)
                 if (data.has_multi_price) {
                     html = '<h4 style="margin-bottom: 1rem; font-size: 0.95rem; color: var(--text-primary);">Chi tiết giá sân</h4>';
                 } else {
                     html = '<h4 style="margin-bottom: 1rem; font-size: 0.95rem; color: var(--text-primary);">Chi tiết giá sân</h4>';
                 }
                 
                 // Show each hour's pricing
                 data.price_breakdown.forEach(item => {
                     html += `
                         <div class="summary-row">
                             <span class="summary-label">${item.start_time} - ${item.end_time} (${item.pricing_label})</span>
                             <span class="summary-value">₫${item.price_per_hour.toLocaleString('vi-VN')}</span>
                         </div>
                     `;
                 });
                 
                 // Add separator
                //  html += '<div style="margin: 1rem 0; border-top: 2px solid var(--border-color);"></div>';
                 
                 // Show total
                 html += `
                     <div class="summary-row">
                         <span class="summary-label">Tổng tiền (${data.duration_hours} giờ)</span>
                         <span class="summary-value" style="font-size: 1.25rem; color: var(--primary-color);">₫${data.total_price.toLocaleString('vi-VN')}</span>
                     </div>
                 `;
             } else {
                 console.log('Showing fallback simple pricing display');
                 // Simple single-price display (fallback)
                 const hourlyRate = data.average_hourly_rate;
                 html = `
                     <div class="summary-row">
                         <span class="summary-label">Giá sân</span>
                         <span class="summary-value">₫${hourlyRate.toLocaleString('vi-VN')}/giờ</span>
                     </div>
                     <div class="summary-row">
                         <span class="summary-label">Thời gian</span>
                         <span class="summary-value">${data.duration_hours} giờ</span>
                     </div>
                     <div class="summary-row">
                         <span class="summary-label">Tổng tiền</span>
                         <span class="summary-value">₫${data.total_price.toLocaleString('vi-VN')}</span>
                     </div>
                 `;
             }
             
             console.log('Setting innerHTML on summaryContainer');
             summaryContainer.innerHTML = html;
         }

        // Fallback simple summary display for booking details
        function updateModalBookingSummarySimple(durationHours, totalPrice) {
            const summaryContainer = document.querySelector('.booking-summary');
            const hourlyRate = totalPrice ? Math.round(totalPrice / durationHours) : 0;
            
            let html = `
                <div class="summary-row">
                    <span class="summary-label">Giá sân</span>
                    <span class="summary-value">₫${hourlyRate.toLocaleString('vi-VN')}/giờ</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Thời gian</span>
                    <span class="summary-value">${durationHours} giờ</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Tổng tiền</span>
                    <span class="summary-value">₫${parseInt(totalPrice).toLocaleString('vi-VN')}</span>
                </div>
            `;
            
            summaryContainer.innerHTML = html;
        }

        function getPaymentMethodText(method) {
            const methodMap = {
                'cash': 'Tiền mặt',
                'card': 'Thẻ tín dụng',
                'transfer': 'Chuyển khoản',
                'wallet': 'Ví điện tử'
            };
            return methodMap[method] || method;
        }

        function closeBookingDetailsModal() {
            document.getElementById('bookingDetailsModal').classList.remove('active');
        }

        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Load and update booking statistics
        function loadBookingStats() {
            fetch(`/homeyard/bookings/stats`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalBookingsCount').textContent = data.total || 0;
                    document.getElementById('confirmedBookingsCount').textContent = data.confirmed || 0;
                    document.getElementById('pendingBookingsCount').textContent = data.pending || 0;
                    document.getElementById('cancelledBookingsCount').textContent = data.cancelled || 0;
                }
            })
            .catch(error => {
                console.error('Error loading booking stats:', error);
            });
        }

        // Get status badge HTML
        function getStatusBadge(status) {
            const statusMap = {
                'confirmed': { class: 'badge-success', text: 'Đã xác nhận' },
                'pending': { class: 'badge-warning', text: 'Chờ xác nhận' },
                'cancelled': { class: 'badge-gray', text: 'Đã hủy' },
                'completed': { class: 'badge-info', text: 'Hoàn thành' }
            };
            const statusData = statusMap[status] || { class: 'badge-gray', text: status };
            return `<span class="badge ${statusData.class}">${statusData.text}</span>`;
        }

        // Load bookings list
        function loadBookingsList() {
            applyFilters(1); // Load first page with all bookings
        }

        // Render bookings list
        function renderBookingsList(bookings) {
            const container = document.getElementById('bookingListTable');
            let html = '';

            if (bookings.length === 0) {
                html = '<div style="padding: 2rem; text-align: center; color: var(--text-secondary);">Chưa có đơn đặt sân nào</div>';
            } else {
                bookings.forEach(booking => {
                    const bookingDate = new Date(booking.booking_date).toLocaleDateString('vi-VN', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    });
                    const startTime = booking.start_time.substring(0, 5);
                    const endTime = booking.end_time.substring(0, 5);
                    const bookingId = 'BK-' + String(booking.id).padStart(6, '0');
                    const totalPrice = parseInt(booking.total_price).toLocaleString('vi-VN');

                    html += `
                        <div class="booking-row">
                            <div class="booking-id">#${bookingId}</div>
                            <div class="booking-info">
                                <div class="booking-customer">${booking.customer_name}</div>
                                <div class="booking-details">
                                    <span>🏟️ ${booking.court_name || 'Sân'}</span>
                                    <span>📅 ${bookingDate}</span>
                                    <span>🕐 ${startTime} - ${endTime}</span>
                                    <span>📱 ${booking.customer_phone}</span>
                                </div>
                            </div>
                            <div class="booking-price">₫${totalPrice}</div>
                            ${getStatusBadge(booking.status)}
                            <div class="booking-actions">
                                <button class="btn btn-ghost btn-icon-sm" onclick="viewBookingDetails('${bookingId}')">👁️</button>
                                <button class="btn btn-ghost btn-icon-sm" onclick="deleteBooking('${bookingId}')">🗑️</button>
                            </div>
                        </div>
                    `;
                });
            }

            container.innerHTML = html;
        }

        // Render empty bookings list
        function renderBookingsListEmpty() {
            const container = document.getElementById('bookingListTable');
            container.innerHTML = '<div style="padding: 2rem; text-align: center; color: var(--text-secondary);">Không thể tải danh sách đơn đặt sân</div>';
        }

        // Current page for pagination
        let currentPage = 1;
        let totalPages = 1;

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Debounced search (wait 500ms after user stops typing)
        const debouncedSearch = debounce(() => {
            applyFilters(1); // Reset to page 1 when searching
        }, 500);

        // Apply filters with AJAX
        function applyFilters(page = 1) {
            const searchText = document.getElementById('searchInput').value.trim();
            const statusFilter = document.getElementById('statusFilter').value;
            const courtFilter = document.getElementById('courtFilter').value;
            const dateFromFilter = document.getElementById('dateFromFilter').value;
            const dateToFilter = document.getElementById('dateToFilter').value;

            const params = new URLSearchParams({
                search: searchText,
                status: statusFilter,
                court_id: courtFilter,
                date_from: dateFromFilter,
                date_to: dateToFilter,
                page: page
            });

            fetch(`/homeyard/bookings/search?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.bookings) {
                    currentPage = data.current_page;
                    totalPages = data.last_page;
                    renderBookingsList(data.bookings.data || data.bookings);
                    renderPagination(data);
                    // Update court filter options
                    if (data.courts) {
                        populateCourtFilterFromData(data.courts);
                    }
                } else {
                    renderBookingsListEmpty();
                    renderPaginationEmpty();
                }
            })
            .catch(error => {
                console.error('Error applying filters:', error);
                renderBookingsListEmpty();
                renderPaginationEmpty();
            });
        }

        // Reset all filters
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('courtFilter').value = '';
            document.getElementById('dateFromFilter').value = '';
            document.getElementById('dateToFilter').value = '';
            currentPage = 1;
            applyFilters(1);
        }

        // Go to specific page
        function goToPage(page) {
            if (page >= 1 && page <= totalPages) {
                applyFilters(page);
            }
        }

        // Render pagination controls
        function renderPagination(data) {
            const container = document.getElementById('paginationContainer');
            let html = '';

            const currentPage = data.current_page;
            const lastPage = data.last_page;

            // Only show pagination if there are 2 or more pages
            if (lastPage < 2) {
                container.innerHTML = '';
                return;
            }

            // Previous button
            if (currentPage > 1) {
                html += `<button class="pagination-btn" onclick="goToPage(${currentPage - 1})">‹ Trước</button>`;
            } else {
                html += `<button class="pagination-btn" disabled>‹ Trước</button>`;
            }

            // Page numbers
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(lastPage, currentPage + 2);

            if (startPage > 1) {
                html += `<button class="pagination-btn" onclick="goToPage(1)">1</button>`;
                if (startPage > 2) {
                    html += `<button class="pagination-btn" disabled>...</button>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === currentPage ? 'active' : '';
                html += `<button class="pagination-btn ${activeClass}" onclick="goToPage(${i})">${i}</button>`;
            }

            if (endPage < lastPage) {
                if (endPage < lastPage - 1) {
                    html += `<button class="pagination-btn" disabled>...</button>`;
                }
                html += `<button class="pagination-btn" onclick="goToPage(${lastPage})">${lastPage}</button>`;
            }

            // Next button
            if (currentPage < lastPage) {
                html += `<button class="pagination-btn" onclick="goToPage(${currentPage + 1})">Sau ›</button>`;
            } else {
                html += `<button class="pagination-btn" disabled>Sau ›</button>`;
            }

            container.innerHTML = html;
        }

        // Render empty pagination
        function renderPaginationEmpty() {
            document.getElementById('paginationContainer').innerHTML = '';
        }

        // Populate court filter dropdown from data
        function populateCourtFilterFromData(courts) {
            const courtFilter = document.getElementById('courtFilter');
            const currentValue = courtFilter.value;

            // Remove all options except the first one
            while (courtFilter.options.length > 1) {
                courtFilter.remove(1);
            }

            // Add courts
            courts.forEach(court => {
                const option = document.createElement('option');
                option.value = court.id;
                option.textContent = court.name;
                courtFilter.appendChild(option);
            });

            // Restore previous value if it exists
            if (currentValue) {
                courtFilter.value = currentValue;
            }
        }

        // Populate court filter dropdown (for initial load)
        function populateCourtFilter() {
            // This will be populated by the search endpoint
        }

        // Cancel booking
        let currentBookingIdForAction = null;

        function cancelBooking() {
            const bookingId = document.getElementById('modalBookingId').textContent.replace('#BK-', '');
            if (!bookingId) {
                toastr.error('Không tìm thấy mã đơn đặt');
                return;
            }

            if (!confirm('Bạn chắc chắn muốn hủy đơn đặt này?')) {
                return;
            }

            fetch(`/homeyard/bookings/${bookingId}/cancel`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    closeBookingDetailsModal();
                    loadBookingsList(); // Refresh list
                    loadBookingStats(); // Refresh stats
                } else {
                    toastr.error(data.message || 'Lỗi hủy đơn đặt');
                }
            })
            .catch(error => {
                console.error('Error cancelling booking:', error);
                toastr.error('Lỗi khi hủy đơn đặt');
            });
        }

        // Delete booking from modal
        function deleteBookingFromModal() {
            const bookingId = document.getElementById('modalBookingId').textContent.replace('#BK-', '');
            if (!bookingId) {
                toastr.error('Không tìm thấy mã đơn đặt');
                return;
            }

            if (!confirm('Bạn chắc chắn muốn xóa vĩnh viễn đơn đặt này? Hành động này không thể hoàn tác.')) {
                return;
            }

            fetch(`/homeyard/bookings/${bookingId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    closeBookingDetailsModal();
                    applyFilters(currentPage); // Refresh list
                    loadBookingStats(); // Refresh stats
                } else {
                    toastr.error(data.message || 'Lỗi xóa đơn đặt');
                }
            })
            .catch(error => {
                console.error('Error deleting booking:', error);
                toastr.error('Lỗi khi xóa đơn đặt');
            });
        }

        // Delete booking from list
        function deleteBooking(bookingId) {
            const numericId = bookingId.replace('BK-', '');
            
            if (!confirm('Bạn chắc chắn muốn xóa đơn đặt này? Hành động này không thể hoàn tác.')) {
                return;
            }

            fetch(`/homeyard/bookings/${numericId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    applyFilters(currentPage); // Refresh list
                    loadBookingStats(); // Refresh stats
                } else {
                    toastr.error(data.message || 'Lỗi xóa đơn đặt');
                }
            })
            .catch(error => {
                console.error('Error deleting booking:', error);
                toastr.error('Lỗi khi xóa đơn đặt');
            });
        }

        // Load page
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Booking Management Loaded');
            initCourtsData();
            loadBookingStats();
        });
        </script>
        @endsection
