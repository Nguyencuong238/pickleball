@extends('layouts/homeyard')
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
        grid-template-columns: repeat(6, 1fr);
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
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Header -->
            <div class="top-header">
                <div class="header-left">
                    <h1>Quản Lý Đặt Sân</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="overview.html" class="breadcrumb-link">Trang chủ</a>
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
                    <div class="header-notifications">
                        <button class="notification-btn">
                            🔔
                            <span class="notification-badge">5</span>
                        </button>
                    </div>
                    <div class="header-user">
                        <div class="user-avatar">AD</div>
                        <div class="user-info">
                            <div class="user-name">Admin User</div>
                            <div class="user-role">Quản trị viên</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Overview Stats -->
            <div class="booking-overview fade-in">
                <div class="booking-stat-card">
                    <div class="booking-stat-icon info">📊</div>
                    <div class="booking-stat-content">
                        <div class="booking-stat-value">248</div>
                        <div class="booking-stat-label">Tổng Đơn Tháng Này</div>
                    </div>
                </div>
                <div class="booking-stat-card">
                    <div class="booking-stat-icon success">✅</div>
                    <div class="booking-stat-content">
                        <div class="booking-stat-value">189</div>
                        <div class="booking-stat-label">Đã Xác Nhận</div>
                    </div>
                </div>
                <div class="booking-stat-card">
                    <div class="booking-stat-icon warning">⏳</div>
                    <div class="booking-stat-content">
                        <div class="booking-stat-value">45</div>
                        <div class="booking-stat-label">Chờ Xác Nhận</div>
                    </div>
                </div>
                <div class="booking-stat-card">
                    <div class="booking-stat-icon danger">❌</div>
                    <div class="booking-stat-content">
                        <div class="booking-stat-value">14</div>
                        <div class="booking-stat-label">Đã Hủy</div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card fade-in">
                <div class="card-header">
                    <h3 class="card-title">Quản Lý Đặt Sân</h3>
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
                        <div class="quick-date-filters">
                            <button class="quick-date-btn active">Hôm Nay</button>
                            <button class="quick-date-btn">Ngày Mai</button>
                            <button class="quick-date-btn">Tuần Này</button>
                            <button class="quick-date-btn">Tuần Sau</button>
                        </div>

                        <div class="booking-calendar">
                            <div class="calendar-header-booking">
                                <h3 class="calendar-title-booking">Thứ Sáu, 20/01/2025</h3>
                                <div class="calendar-nav-booking">
                                    <button class="btn btn-secondary btn-sm">‹ Hôm qua</button>
                                    <button class="btn btn-secondary btn-sm">Hôm nay</button>
                                    <button class="btn btn-secondary btn-sm">Ngày mai ›</button>
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
                                </div>

                                <div class="courts-grid">
                                    <!-- Sân 1 -->
                                    <div class="court-column">
                                        <div class="court-header-slot">Sân 1</div>
                                        <div class="time-slot available" onclick="bookSlot(1, '06:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(1, '07:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(1, '10:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(1, '11:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(1, '13:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(1, '16:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(1, '17:00')">✅</div>
                                        <div class="time-slot pending">⏳</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(1, '20:00')">✅</div>
                                    </div>

                                    <!-- Sân 2 -->
                                    <div class="court-column">
                                        <div class="court-header-slot">Sân 2</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(2, '07:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(2, '08:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(2, '11:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(2, '12:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot pending">⏳</div>
                                        <div class="time-slot available" onclick="bookSlot(2, '16:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(2, '17:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(2, '19:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(2, '20:00')">✅</div>
                                    </div>

                                    <!-- Sân 3 -->
                                    <div class="court-column">
                                        <div class="court-header-slot">Sân 3</div>
                                        <div class="time-slot available" onclick="bookSlot(3, '06:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(3, '09:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(3, '10:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(3, '12:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(3, '13:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(3, '17:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(3, '18:00')">✅</div>
                                        <div class="time-slot pending">⏳</div>
                                        <div class="time-slot available" onclick="bookSlot(3, '20:00')">✅</div>
                                    </div>

                                    <!-- Sân 4 -->
                                    <div class="court-column">
                                        <div class="court-header-slot">Sân 4</div>
                                        <div class="time-slot available" onclick="bookSlot(4, '06:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(4, '07:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(4, '08:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(4, '09:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(4, '12:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(4, '14:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(4, '15:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot pending">⏳</div>
                                        <div class="time-slot available" onclick="bookSlot(4, '19:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(4, '20:00')">✅</div>
                                    </div>

                                    <!-- Sân 5 -->
                                    <div class="court-column">
                                        <div class="court-header-slot">Sân 5</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(5, '08:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(5, '09:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(5, '11:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(5, '12:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(5, '15:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(5, '16:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(5, '19:00')">✅</div>
                                        <div class="time-slot pending">⏳</div>
                                    </div>

                                    <!-- Sân 6 -->
                                    <div class="court-column">
                                        <div class="court-header-slot">Sân 6</div>
                                        <div class="time-slot available" onclick="bookSlot(6, '06:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(6, '07:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(6, '10:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(6, '13:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(6, '14:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(6, '17:00')">✅</div>
                                        <div class="time-slot available" onclick="bookSlot(6, '18:00')">✅</div>
                                        <div class="time-slot booked">🔴</div>
                                        <div class="time-slot available" onclick="bookSlot(6, '20:00')">✅</div>
                                    </div>
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
                                    <input type="text" class="form-input" placeholder="Mã đơn, tên khách...">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select">
                                        <option value="">Tất cả</option>
                                        <option value="confirmed">Đã xác nhận</option>
                                        <option value="pending">Chờ xác nhận</option>
                                        <option value="cancelled">Đã hủy</option>
                                        <option value="completed">Hoàn thành</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Sân</label>
                                    <select class="form-select">
                                        <option value="">Tất cả</option>
                                        <option value="1">Sân 1</option>
                                        <option value="2">Sân 2</option>
                                        <option value="3">Sân 3</option>
                                        <option value="4">Sân 4</option>
                                        <option value="5">Sân 5</option>
                                        <option value="6">Sân 6</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">&nbsp;</label>
                                    <button class="btn btn-primary" style="width: 100%;">
                                        🔍 Lọc
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="booking-list-table">
                            <div class="booking-row">
                                <div class="booking-id">#BK-001</div>
                                <div class="booking-info">
                                    <div class="booking-customer">Nguyễn Văn An</div>
                                    <div class="booking-details">
                                        <span>🏟️ Sân 1</span>
                                        <span>📅 20/01/2025</span>
                                        <span>🕐 14:00 - 16:00</span>
                                        <span>📱 0901234567</span>
                                    </div>
                                </div>
                                <div class="booking-price">₫300,000</div>
                                <span class="badge badge-success">Đã xác nhận</span>
                                <div class="booking-actions">
                                    <button class="btn btn-ghost btn-icon-sm"
                                        onclick="viewBookingDetails('BK-001')">👁️</button>
                                    <button class="btn btn-ghost btn-icon-sm">✏️</button>
                                    <button class="btn btn-ghost btn-icon-sm">🗑️</button>
                                </div>
                            </div>

                            <div class="booking-row">
                                <div class="booking-id">#BK-002</div>
                                <div class="booking-info">
                                    <div class="booking-customer">Trần Thu Linh</div>
                                    <div class="booking-details">
                                        <span>🏟️ Sân 2</span>
                                        <span>📅 20/01/2025</span>
                                        <span>🕐 08:00 - 10:00</span>
                                        <span>📱 0912345678</span>
                                    </div>
                                </div>
                                <div class="booking-price">₫250,000</div>
                                <span class="badge badge-warning">Chờ xác nhận</span>
                                <div class="booking-actions">
                                    <button class="btn btn-ghost btn-icon-sm"
                                        onclick="viewBookingDetails('BK-002')">👁️</button>
                                    <button class="btn btn-ghost btn-icon-sm">✏️</button>
                                    <button class="btn btn-ghost btn-icon-sm">🗑️</button>
                                </div>
                            </div>

                            <div class="booking-row">
                                <div class="booking-id">#BK-003</div>
                                <div class="booking-info">
                                    <div class="booking-customer">Lê Minh Hoàng</div>
                                    <div class="booking-details">
                                        <span>🏟️ Sân 3</span>
                                        <span>📅 21/01/2025</span>
                                        <span>🕐 16:00 - 18:00</span>
                                        <span>📱 0923456789</span>
                                    </div>
                                </div>
                                <div class="booking-price">₫300,000</div>
                                <span class="badge badge-success">Đã xác nhận</span>
                                <div class="booking-actions">
                                    <button class="btn btn-ghost btn-icon-sm"
                                        onclick="viewBookingDetails('BK-003')">👁️</button>
                                    <button class="btn btn-ghost btn-icon-sm">✏️</button>
                                    <button class="btn btn-ghost btn-icon-sm">🗑️</button>
                                </div>
                            </div>

                            <div class="booking-row">
                                <div class="booking-id">#BK-004</div>
                                <div class="booking-info">
                                    <div class="booking-customer">Phạm Thu Hà</div>
                                    <div class="booking-details">
                                        <span>🏟️ Sân 1</span>
                                        <span>📅 19/01/2025</span>
                                        <span>🕐 10:00 - 12:00</span>
                                        <span>📱 0934567890</span>
                                    </div>
                                </div>
                                <div class="booking-price">₫250,000</div>
                                <span class="badge badge-gray">Đã hủy</span>
                                <div class="booking-actions">
                                    <button class="btn btn-ghost btn-icon-sm"
                                        onclick="viewBookingDetails('BK-004')">👁️</button>
                                    <button class="btn btn-ghost btn-icon-sm">✏️</button>
                                    <button class="btn btn-ghost btn-icon-sm">🗑️</button>
                                </div>
                            </div>

                            <div class="booking-row">
                                <div class="booking-id">#BK-005</div>
                                <div class="booking-info">
                                    <div class="booking-customer">Đỗ Văn Toàn</div>
                                    <div class="booking-details">
                                        <span>🏟️ Sân 5</span>
                                        <span>📅 21/01/2025</span>
                                        <span>🕐 19:00 - 21:00</span>
                                        <span>📱 0945678901</span>
                                    </div>
                                </div>
                                <div class="booking-price">₫320,000</div>
                                <span class="badge badge-warning">Chờ xác nhận</span>
                                <div class="booking-actions">
                                    <button class="btn btn-ghost btn-icon-sm"
                                        onclick="viewBookingDetails('BK-005')">👁️</button>
                                    <button class="btn btn-ghost btn-icon-sm">✏️</button>
                                    <button class="btn btn-ghost btn-icon-sm">🗑️</button>
                                </div>
                            </div>

                            <div class="booking-row">
                                <div class="booking-id">#BK-006</div>
                                <div class="booking-info">
                                    <div class="booking-customer">Vũ Thu Lan</div>
                                    <div class="booking-details">
                                        <span>🏟️ Sân 6</span>
                                        <span>📅 20/01/2025</span>
                                        <span>🕐 17:00 - 19:00</span>
                                        <span>📱 0956789012</span>
                                    </div>
                                </div>
                                <div class="booking-price">₫280,000</div>
                                <span class="badge badge-success">Đã xác nhận</span>
                                <div class="booking-actions">
                                    <button class="btn btn-ghost btn-icon-sm"
                                        onclick="viewBookingDetails('BK-006')">👁️</button>
                                    <button class="btn btn-ghost btn-icon-sm">✏️</button>
                                    <button class="btn btn-ghost btn-icon-sm">🗑️</button>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 1.5rem;">
                            <div class="pagination">
                                <button class="pagination-btn" disabled>‹ Trước</button>
                                <button class="pagination-btn active">1</button>
                                <button class="pagination-btn">2</button>
                                <button class="pagination-btn">3</button>
                                <button class="pagination-btn">4</button>
                                <button class="pagination-btn">5</button>
                                <button class="pagination-btn">Sau ›</button>
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
            <form>
                <div class="form-group">
                    <label class="form-label">Tên khách hàng *</label>
                    <input type="text" class="form-input" placeholder="Nhập tên khách hàng" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Số điện thoại *</label>
                    <input type="tel" class="form-input" placeholder="0901234567" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" placeholder="example@email.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Chọn sân *</label>
                    <select class="form-select" required>
                        <option value="">Chọn sân</option>
                        <option value="1">Sân 1 - Indoor Standard</option>
                        <option value="2">Sân 2 - Indoor Premium</option>
                        <option value="3">Sân 3 - Outdoor Standard</option>
                        <option value="4">Sân 4 - Indoor Standard</option>
                        <option value="5">Sân 5 - Indoor Standard</option>
                        <option value="6">Sân 6 - Outdoor Premium</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày đặt *</label>
                    <input type="date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Giờ bắt đầu *</label>
                    <select class="form-select" required>
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
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Thời gian (giờ) *</label>
                    <select class="form-select" required>
                        <option value="">Chọn thời gian</option>
                        <option value="1">1 giờ</option>
                        <option value="1.5">1.5 giờ</option>
                        <option value="2">2 giờ</option>
                        <option value="2.5">2.5 giờ</option>
                        <option value="3">3 giờ</option>
                    </select>
                </div>
                <div class="booking-summary">
                    <div class="summary-row">
                        <span class="summary-label">Giá sân</span>
                        <span class="summary-value">₫150,000/giờ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Thời gian</span>
                        <span class="summary-value">2 giờ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Tổng tiền</span>
                        <span class="summary-value">₫300,000</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-textarea" placeholder="Yêu cầu đặc biệt..."></textarea>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeNewBookingModal()">Hủy</button>
                <button class="btn btn-primary">💾 Xác Nhận Đặt Sân</button>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal" id="bookingDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Chi Tiết Đơn Đặt #BK-001</h3>
                <button class="modal-close" onclick="closeBookingDetailsModal()">×</button>
            </div>
            <div>
                <div class="form-group">
                    <label class="form-label">Thông Tin Khách Hàng</label>
                    <div class="court-info">
                        <div class="court-info-item">
                            <div class="court-info-label">Tên khách hàng</div>
                            <div class="court-info-value">Nguyễn Văn An</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Số điện thoại</div>
                            <div class="court-info-value">0901234567</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Email</div>
                            <div class="court-info-value">nguyenvanan@email.com</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Trạng thái</div>
                            <div class="court-info-value">
                                <span class="badge badge-success">Đã xác nhận</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Thông Tin Đặt Sân</label>
                    <div class="court-info">
                        <div class="court-info-item">
                            <div class="court-info-label">Sân</div>
                            <div class="court-info-value">Sân 1 - Indoor Standard</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Ngày đặt</div>
                            <div class="court-info-value">20/01/2025</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Giờ</div>
                            <div class="court-info-value">14:00 - 16:00</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Thời gian</div>
                            <div class="court-info-value">2 giờ</div>
                        </div>
                    </div>
                </div>

                <div class="booking-summary">
                    <div class="summary-row">
                        <span class="summary-label">Giá sân</span>
                        <span class="summary-value">₫150,000/giờ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Thời gian</span>
                        <span class="summary-value">2 giờ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Tổng tiền</span>
                        <span class="summary-value">₫300,000</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ghi Chú</label>
                    <div
                        style="padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); color: var(--text-secondary);">
                        Yêu cầu chuẩn bị nước uống và khăn lạnh
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Thông Tin Đơn</label>
                    <div class="court-info">
                        <div class="court-info-item">
                            <div class="court-info-label">Ngày tạo</div>
                            <div class="court-info-value">18/01/2025 10:30</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Người tạo</div>
                            <div class="court-info-value">Admin User</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Thanh toán</div>
                            <div class="court-info-value">Tiền mặt</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Mã đơn</div>
                            <div class="court-info-value">#BK-001</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger">❌ Hủy Đơn</button>
                <button class="btn btn-secondary" onclick="closeBookingDetailsModal()">Đóng</button>
                <button class="btn btn-primary">✏️ Chỉnh Sửa</button>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        }

        // Mobile menu toggle
        if (window.innerWidth <= 1024) {
            toggleSidebar();
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth <= 1024) {
                document.getElementById('sidebar').classList.add('collapsed');
                document.getElementById('mainContent').classList.add('sidebar-collapsed');
            }
        });

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
        }

        // Book slot
        function bookSlot(court, time) {
            console.log(`Booking Court ${court} at ${time}`);
            openNewBookingModal();
        }

        // Modal functions
        function openNewBookingModal() {
            document.getElementById('newBookingModal').classList.add('active');
        }

        function closeNewBookingModal() {
            document.getElementById('newBookingModal').classList.remove('active');
        }

        function viewBookingDetails(bookingId) {
            console.log('View booking:', bookingId);
            document.getElementById('bookingDetailsModal').classList.add('active');
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

        // Load page
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Booking Management Loaded');
        });
    </script>
@endsection
