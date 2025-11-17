@extends('layouts.front')

@section('css')
    
@endsection

@section('content')
    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Giờ Đi Đấu Social</h1>
            <p class="page-description">Tham gia cộng đồng, tìm đối thủ và nâng cao kỹ năng Pickleball</p>
            
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-number">2,500+</div>
                        <div class="stat-label">Thành viên</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">🎾</div>
                    <div class="stat-content">
                        <div class="stat-number">150+</div>
                        <div class="stat-label">Buổi/tháng</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">📍</div>
                    <div class="stat-content">
                        <div class="stat-number">25</div>
                        <div class="stat-label">Địa điểm</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <div class="stat-number">4.9</div>
                        <div class="stat-label">Đánh giá</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="social-section section">
        <div class="container">
            <div class="social-filter-bar">
                <div class="filter-group">
                    <label>Khu vực</label>
                    <select class="form-control">
                        <option>Tất cả</option>
                        <option>TP.HCM</option>
                        <option>Hà Nội</option>
                        <option>Đà Nẵng</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Trình độ</label>
                    <select class="form-control">
                        <option>Tất cả</option>
                        <option>Beginner</option>
                        <option>Intermediate</option>
                        <option>Advanced</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Ngày</label>
                    <select class="form-control">
                        <option>Tuần này</option>
                        <option>Tuần sau</option>
                        <option>Tháng này</option>
                    </select>
                </div>
            </div>

            <div class="social-grid">
                <div class="social-card">
                    <div class="social-header">
                        <div class="social-day">
                            <span class="day-name">Thứ Hai</span>
                            <span class="day-date">18:00 - 21:00</span>
                        </div>
                        <span class="social-level level-beginner">Beginner</span>
                    </div>
                    <h3 class="social-title">Monday Social Play</h3>
                    <p class="social-description">Buổi chơi dành cho người mới bắt đầu</p>
                    <div class="social-info">
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Sân Rạch Chiếc, Q2</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            <span>12/20 người</span>
                        </div>
                        <div class="info-row">
                            <span class="price">50.000đ/người</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block">Tham gia ngay</button>
                </div>

                <div class="social-card">
                    <div class="social-header">
                        <div class="social-day">
                            <span class="day-name">Thứ Tư</span>
                            <span class="day-date">19:00 - 22:00</span>
                        </div>
                        <span class="social-level level-intermediate">Intermediate</span>
                    </div>
                    <h3 class="social-title">Wednesday Mix & Match</h3>
                    <p class="social-description">Đấu xoay vòng với nhiều đối thủ</p>
                    <div class="social-info">
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Thảo Điền Sports Club</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            <span>18/24 người</span>
                        </div>
                        <div class="info-row">
                            <span class="price">80.000đ/người</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block">Tham gia ngay</button>
                </div>

                <div class="social-card">
                    <div class="social-header">
                        <div class="social-day">
                            <span class="day-name">Thứ Sáu</span>
                            <span class="day-date">18:30 - 21:30</span>
                        </div>
                        <span class="social-level level-advanced">Advanced</span>
                    </div>
                    <h3 class="social-title">Friday Night Showdown</h3>
                    <p class="social-description">Buổi chơi mức độ cao cho tay vợt giỏi</p>
                    <div class="social-info">
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Cầu Giấy Arena, Hà Nội</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            <span>14/16 người</span>
                        </div>
                        <div class="info-row">
                            <span class="price">100.000đ/người</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block">Tham gia ngay</button>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')

@endsection