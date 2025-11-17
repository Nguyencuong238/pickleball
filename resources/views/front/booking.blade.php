@extends('layouts.front')

@section('css')
@endsection

@section('content')
    <section class="booking-section">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.html">Trang chủ</a> / 
                <a href="courts.html">Sân thi đấu</a> / 
                <a href="court-detail.html">Pickleball Rạch Chiếc</a> / 
                <span>Đặt sân</span>
            </div>

            <h1 class="page-title">Đặt Sân Pickleball</h1>
            
            <div class="booking-layout">
                <div class="booking-form-section">
                    <div class="step-indicator">
                        <div class="step active">
                            <div class="step-number">1</div>
                            <div class="step-label">Chọn thời gian</div>
                        </div>
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-label">Thông tin</div>
                        </div>
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-label">Thanh toán</div>
                        </div>
                    </div>

                    <div class="booking-card">
                        <h2>Chọn ngày & giờ</h2>
                        <div class="form-group">
                            <label>Ngày đặt sân</label>
                            <input type="date" class="form-control" min="2025-11-12">
                        </div>
                        
                        <div class="time-slots">
                            <h3>Chọn giờ</h3>
                            <div class="slots-grid">
                                <button class="slot-btn">05:00 - 06:00<span>150k</span></button>
                                <button class="slot-btn">06:00 - 07:00<span>150k</span></button>
                                <button class="slot-btn">07:00 - 08:00<span>150k</span></button>
                                <button class="slot-btn disabled">08:00 - 09:00<span>Đã đặt</span></button>
                                <button class="slot-btn">09:00 - 10:00<span>150k</span></button>
                                <button class="slot-btn">10:00 - 11:00<span>150k</span></button>
                                <button class="slot-btn">11:00 - 12:00<span>200k</span></button>
                                <button class="slot-btn">18:00 - 19:00<span>300k</span></button>
                                <button class="slot-btn">19:00 - 20:00<span>300k</span></button>
                                <button class="slot-btn">20:00 - 21:00<span>300k</span></button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Chọn sân</label>
                            <select class="form-control">
                                <option>Sân 1</option>
                                <option>Sân 2</option>
                                <option>Sân 3</option>
                                <option>Sân 4</option>
                            </select>
                        </div>

                        <h2>Thông tin người đặt</h2>
                        <div class="form-group">
                            <label>Họ tên *</label>
                            <input type="text" class="form-control" placeholder="Nguyễn Văn A">
                        </div>
                        <div class="form-group">
                            <label>Số điện thoại *</label>
                            <input type="tel" class="form-control" placeholder="0901234567">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" placeholder="email@example.com">
                        </div>
                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea class="form-control" rows="3" placeholder="Ghi chú thêm..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="booking-summary">
                    <div class="summary-card">
                        <h3>Chi tiết đặt sân</h3>
                        <div class="summary-item">
                            <span>Sân:</span>
                            <strong>Pickleball Rạch Chiếc</strong>
                        </div>
                        <div class="summary-item">
                            <span>Ngày:</span>
                            <strong>12/11/2025</strong>
                        </div>
                        <div class="summary-item">
                            <span>Giờ:</span>
                            <strong>Chưa chọn</strong>
                        </div>
                        <div class="summary-item">
                            <span>Sân số:</span>
                            <strong>Sân 1</strong>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-item">
                            <span>Tạm tính:</span>
                            <strong>0đ</strong>
                        </div>
                        <div class="summary-item">
                            <span>Phí dịch vụ:</span>
                            <strong>0đ</strong>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-total">
                            <span>Tổng cộng:</span>
                            <strong>0đ</strong>
                        </div>
                        <button class="btn btn-primary btn-block btn-lg">Tiếp tục thanh toán</button>
                        <p class="payment-note">🔒 Thanh toán an toàn với VNPay, Momo, Banking</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
