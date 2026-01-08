@extends('layouts.front')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-club.css') }}?v=1">
<style>
</style>

<section class="club-cover">
        <div class="cover-image">
            <svg viewBox="0 0 1200 300" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
                <defs>
                    <linearGradient id="coverGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#00D9B5;stop-opacity:1" />
                        <stop offset="50%" style="stop-color:#0099CC;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#006699;stop-opacity:1" />
                    </linearGradient>
                    <pattern id="courtPattern" patternUnits="userSpaceOnUse" width="100" height="100">
                        <rect fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2" width="100" height="100"/>
                        <line x1="50" y1="0" x2="50" y2="100" stroke="rgba(255,255,255,0.1)" stroke-width="2"/>
                        <line x1="0" y1="50" x2="100" y2="50" stroke="rgba(255,255,255,0.1)" stroke-width="2"/>
                    </pattern>
                </defs>
                <rect fill="url(#coverGrad)" width="1200" height="300"/>
                <rect fill="url(#courtPattern)" width="1200" height="300"/>
                <circle cx="200" cy="150" r="80" fill="rgba(255,255,255,0.1)"/>
                <circle cx="1000" cy="100" r="120" fill="rgba(255,255,255,0.05)"/>
                <circle cx="600" cy="250" r="60" fill="rgba(255,255,255,0.08)"/>
            </svg>
        </div>
        <div class="container">
            <div class="club-header-info">
                <div class="club-avatar">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 120'%3E%3Ccircle cx='60' cy='60' r='60' fill='%23fff'/%3E%3Ccircle cx='60' cy='60' r='55' fill='%2300D9B5'/%3E%3Ctext x='60' y='72' font-size='36' font-weight='bold' text-anchor='middle' fill='white'%3ESGP%3C/text%3E%3C/svg%3E" alt="Saigon Pickleball Club">
                    <span class="verified-badge">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                        </svg>
                    </span>
                </div>
                <div class="club-title-section">
                    <h1 class="club-name">Saigon Pickleball Club</h1>
                    <p class="club-tagline">Kết nối đam mê - Nâng tầm kỹ năng</p>
                    <div class="club-stats-row">
                        <span class="club-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <strong>256</strong> thành viên
                        </span>
                        <span class="club-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <strong>48</strong> sự kiện
                        </span>
                        <span class="club-stat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Quận 2, TP.HCM
                        </span>
                    </div>
                </div>
                <div class="club-actions">
                    <button class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                        Tham gia CLB
                    </button>
                    <button class="btn btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        Liên hệ
                    </button>
                    <button class="btn btn-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="18" cy="5" r="3"/>
                            <circle cx="6" cy="12" r="3"/>
                            <circle cx="18" cy="19" r="3"/>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Club Navigation Tabs -->
    <section class="club-nav-tabs">
        <div class="container">
            <div class="tabs-wrapper">
                <button class="tab-btn active" data-tab="timeline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="17" y1="10" x2="3" y2="10"/>
                        <line x1="21" y1="6" x2="3" y2="6"/>
                        <line x1="21" y1="14" x2="3" y2="14"/>
                        <line x1="17" y1="18" x2="3" y2="18"/>
                    </svg>
                    Bảng tin
                </button>
                <button class="tab-btn" data-tab="about">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    Giới thiệu
                </button>
                <button class="tab-btn" data-tab="events">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Sự kiện
                </button>
                <button class="tab-btn" data-tab="photos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    Ảnh
                </button>
                <button class="tab-btn" data-tab="members">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Thành viên
                </button>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="club-main">
        <div class="container">
            <div class="club-layout">
                <!-- Left Column - Timeline -->
                <div class="timeline-column">
                    <!-- Create Post Card -->
                    <div class="create-post-card">
                        <div class="create-post-header">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%2300D9B5'/%3E%3Ctext x='20' y='26' font-size='16' text-anchor='middle' fill='white'%3EBạn%3C/text%3E%3C/svg%3E" alt="Your avatar" class="user-avatar">
                            <button class="create-post-input" id="openPostModal">
                                Bạn đang nghĩ gì về Pickleball?
                            </button>
                        </div>
                        <div class="create-post-actions">
                            <button class="post-action-btn" data-type="photo">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                                <span>Ảnh</span>
                            </button>
                            <button class="post-action-btn" data-type="video">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="23 7 16 12 23 17 23 7"/>
                                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                                </svg>
                                <span>Video</span>
                            </button>
                            <button class="post-action-btn" data-type="event">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>Sự kiện</span>
                            </button>
                        </div>
                    </div>

                    <!-- Timeline Posts -->
                    <div class="timeline-posts">
                        <!-- Pinned Post -->
                        <article class="post-card pinned">
                            <div class="post-pinned-badge">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
                                </svg>
                                Bài viết ghim
                            </div>
                            <div class="post-header">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%2300D9B5'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3ESGP%3C/text%3E%3C/svg%3E" alt="Saigon Pickleball Club" class="post-avatar">
                                <div class="post-author-info">
                                    <div class="post-author">
                                        <span class="author-name">Saigon Pickleball Club</span>
                                        <span class="verified-icon">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="post-meta">
                                        <span class="post-time">2 giờ trước</span>
                                        <span class="dot">•</span>
                                        <span class="post-visibility">
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                            </svg>
                                            Công khai
                                        </span>
                                    </div>
                                </div>
                                <button class="post-menu-btn">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="12" cy="19" r="2"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="post-content">
                                <p>🎉 <strong>THÔNG BÁO QUAN TRỌNG!</strong></p>
                                <p>CLB Saigon Pickleball chính thức mở đăng ký tham gia <strong>Giải đấu Mùa Xuân 2025</strong>! 🏆</p>
                                <p>📅 Thời gian: 15-16/02/2025<br>
                                📍 Địa điểm: Sân Rạch Chiếc, Quận 2<br>
                                💰 Lệ phí: 200.000đ/người</p>
                                <p>Hạng đấu: Singles & Doubles (Nam/Nữ/Mix)<br>
                                Giải thưởng hấp dẫn + Cúp + Huy chương 🥇🥈🥉</p>
                                <p>👉 Đăng ký ngay: Link trong comment<br>
                                ⏰ Hạn đăng ký: 10/02/2025</p>
                                <p>#SaigonPickleball #PickleballVietnam #GiaiDauMuaXuan2025</p>
                            </div>
                            <div class="post-image">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 400'%3E%3Cdefs%3E%3ClinearGradient id='postImg1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23postImg1)' width='800' height='400'/%3E%3Ctext x='400' y='180' font-size='48' font-weight='bold' text-anchor='middle' fill='white'%3EGIẢI ĐẤU%3C/text%3E%3Ctext x='400' y='240' font-size='36' text-anchor='middle' fill='white'%3EMÙA XUÂN 2025%3C/text%3E%3Ctext x='400' y='300' font-size='24' text-anchor='middle' fill='rgba(255,255,255,0.8)'%3E15-16/02/2025 • Sân Rạch Chiếc%3C/text%3E%3C/svg%3E" alt="Giải đấu Mùa Xuân 2025">
                            </div>
                            <div class="post-stats">
                                <div class="reactions">
                                    <div class="reaction-icons">
                                        <span class="reaction like">👍</span>
                                        <span class="reaction love">❤️</span>
                                        <span class="reaction fire">🔥</span>
                                    </div>
                                    <span class="reaction-count">234</span>
                                </div>
                                <div class="engagement">
                                    <span>56 bình luận</span>
                                    <span>23 chia sẻ</span>
                                </div>
                            </div>
                            <div class="post-actions">
                                <button class="post-action like-btn" data-post-id="1">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                                    </svg>
                                    <span>Thích</span>
                                </button>
                                <button class="post-action comment-btn" data-post-id="1">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                    <span>Bình luận</span>
                                </button>
                                <button class="post-action share-btn" data-post-id="1">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="18" cy="5" r="3"/>
                                        <circle cx="6" cy="12" r="3"/>
                                        <circle cx="18" cy="19" r="3"/>
                                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                                    </svg>
                                    <span>Chia sẻ</span>
                                </button>
                            </div>
                            <!-- Comments Section -->
                            <div class="post-comments" id="comments-1">
                                <div class="comment-item">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 36 36'%3E%3Ccircle cx='18' cy='18' r='18' fill='%23FF8E53'/%3E%3Ctext x='18' y='23' font-size='14' text-anchor='middle' fill='white'%3ETM%3C/text%3E%3C/svg%3E" alt="User" class="comment-avatar">
                                    <div class="comment-bubble">
                                        <span class="comment-author">Trần Minh</span>
                                        <p class="comment-text">Đăng ký ngay! Năm ngoái giải rất vui 🎉</p>
                                        <div class="comment-meta">
                                            <span class="comment-time">1 giờ trước</span>
                                            <button class="comment-action-btn">Thích</button>
                                            <button class="comment-action-btn">Trả lời</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="comment-form-inline">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 36 36'%3E%3Ccircle cx='18' cy='18' r='18' fill='%2300D9B5'/%3E%3Ctext x='18' y='23' font-size='14' text-anchor='middle' fill='white'%3EBạn%3C/text%3E%3C/svg%3E" alt="Your avatar" class="comment-avatar">
                                    <div class="comment-input-wrapper">
                                        <input type="text" placeholder="Viết bình luận..." class="comment-input">
                                        <div class="comment-input-actions">
                                            <button class="input-action-btn" title="Thêm ảnh">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <polyline points="21 15 16 10 5 21"/>
                                                </svg>
                                            </button>
                                            <button class="input-action-btn" title="Thêm emoji">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                                                    <line x1="9" y1="9" x2="9.01" y2="9"/>
                                                    <line x1="15" y1="9" x2="15.01" y2="9"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <!-- Regular Post with Photo -->
                        <article class="post-card">
                            <div class="post-header">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%239D84B7'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3ENL%3C/text%3E%3C/svg%3E" alt="Ngọc Linh" class="post-avatar">
                                <div class="post-author-info">
                                    <div class="post-author">
                                        <span class="author-name">Ngọc Linh</span>
                                        <span class="author-badge member">Thành viên</span>
                                    </div>
                                    <div class="post-meta">
                                        <span class="post-time">5 giờ trước</span>
                                        <span class="dot">•</span>
                                        <span class="post-visibility">
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                            </svg>
                                            Công khai
                                        </span>
                                    </div>
                                </div>
                                <button class="post-menu-btn">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="12" cy="19" r="2"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="post-content">
                                <p>Buổi tập hôm nay thật tuyệt vời! 🎾 Cảm ơn anh chị em trong CLB đã hướng dẫn nhiệt tình. Từ một người mới biết chơi, giờ đã có thể rally được 20 quả rồi 💪</p>
                                <p>Ai rảnh cuối tuần này ra sân tập chung không? 😊</p>
                            </div>
                            <div class="post-images-grid two-images">
                                <div class="post-image-item">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Cdefs%3E%3ClinearGradient id='img2a' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FF8E53;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23FE6B8B;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23img2a)' width='400' height='300'/%3E%3Ctext x='200' y='160' font-size='24' text-anchor='middle' fill='white'%3E🎾 Buổi tập%3C/text%3E%3C/svg%3E" alt="Training session">
                                </div>
                                <div class="post-image-item">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Cdefs%3E%3ClinearGradient id='img2b' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300B89A;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23img2b)' width='400' height='300'/%3E%3Ctext x='200' y='160' font-size='24' text-anchor='middle' fill='white'%3E🏸 Sân đẹp%3C/text%3E%3C/svg%3E" alt="Court">
                                </div>
                            </div>
                            <div class="post-stats">
                                <div class="reactions">
                                    <div class="reaction-icons">
                                        <span class="reaction like">👍</span>
                                        <span class="reaction love">❤️</span>
                                    </div>
                                    <span class="reaction-count">87</span>
                                </div>
                                <div class="engagement">
                                    <span>23 bình luận</span>
                                    <span>5 chia sẻ</span>
                                </div>
                            </div>
                            <div class="post-actions">
                                <button class="post-action like-btn" data-post-id="2">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                                    </svg>
                                    <span>Thích</span>
                                </button>
                                <button class="post-action comment-btn" data-post-id="2">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                    <span>Bình luận</span>
                                </button>
                                <button class="post-action share-btn" data-post-id="2">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="18" cy="5" r="3"/>
                                        <circle cx="6" cy="12" r="3"/>
                                        <circle cx="18" cy="19" r="3"/>
                                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                                    </svg>
                                    <span>Chia sẻ</span>
                                </button>
                            </div>
                        </article>

                        <!-- Post with Video -->
                        <article class="post-card">
                            <div class="post-header">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%230099CC'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3ENH%3C/text%3E%3C/svg%3E" alt="Coach Nguyễn Hùng" class="post-avatar">
                                <div class="post-author-info">
                                    <div class="post-author">
                                        <span class="author-name">Coach Nguyễn Hùng</span>
                                        <span class="author-badge coach">HLV</span>
                                    </div>
                                    <div class="post-meta">
                                        <span class="post-time">Hôm qua lúc 18:30</span>
                                        <span class="dot">•</span>
                                        <span class="post-visibility">
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                            </svg>
                                            Công khai
                                        </span>
                                    </div>
                                </div>
                                <button class="post-menu-btn">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="12" cy="19" r="2"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="post-content">
                                <p>📹 <strong>Video hướng dẫn mới!</strong></p>
                                <p>Hôm nay mình chia sẻ về kỹ thuật Third Shot Drop - một trong những cú đánh quan trọng nhất trong Pickleball. Đây là kỹ thuật giúp team của bạn tiến lên vùng Kitchen một cách an toàn.</p>
                                <p>Các bạn xem video và comment câu hỏi nhé! 👇</p>
                            </div>
                            <div class="post-video">
                                <div class="video-wrapper">
                                    <iframe 
                                        src="https://www.youtube.com/embed/fTbCBsXCzKI?rel=0" 
                                        title="Third Shot Drop Tutorial"
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                            <div class="post-stats">
                                <div class="reactions">
                                    <div class="reaction-icons">
                                        <span class="reaction like">👍</span>
                                        <span class="reaction love">❤️</span>
                                        <span class="reaction fire">🔥</span>
                                    </div>
                                    <span class="reaction-count">156</span>
                                </div>
                                <div class="engagement">
                                    <span>42 bình luận</span>
                                    <span>31 chia sẻ</span>
                                </div>
                            </div>
                            <div class="post-actions">
                                <button class="post-action like-btn" data-post-id="3">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                                    </svg>
                                    <span>Thích</span>
                                </button>
                                <button class="post-action comment-btn" data-post-id="3">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                    <span>Bình luận</span>
                                </button>
                                <button class="post-action share-btn" data-post-id="3">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="18" cy="5" r="3"/>
                                        <circle cx="6" cy="12" r="3"/>
                                        <circle cx="18" cy="19" r="3"/>
                                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                                    </svg>
                                    <span>Chia sẻ</span>
                                </button>
                            </div>
                        </article>

                        <!-- Text Only Post -->
                        <article class="post-card">
                            <div class="post-header">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%23FFD93D'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='%23333'%3EPH%3C/text%3E%3C/svg%3E" alt="Phạm Hoàng" class="post-avatar">
                                <div class="post-author-info">
                                    <div class="post-author">
                                        <span class="author-name">Phạm Hoàng</span>
                                        <span class="author-badge admin">Admin</span>
                                    </div>
                                    <div class="post-meta">
                                        <span class="post-time">2 ngày trước</span>
                                        <span class="dot">•</span>
                                        <span class="post-visibility">
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                            </svg>
                                            Công khai
                                        </span>
                                    </div>
                                </div>
                                <button class="post-menu-btn">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="2"/>
                                        <circle cx="12" cy="12" r="2"/>
                                        <circle cx="12" cy="19" r="2"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="post-content">
                                <p>📢 <strong>Nhắc nhở các thành viên:</strong></p>
                                <p>Lịch tập cố định hàng tuần của CLB:</p>
                                <p>🗓️ Thứ 3, Thứ 5, Thứ 7: 17:00 - 20:00<br>
                                🗓️ Chủ nhật: 07:00 - 11:00<br>
                                📍 Sân Rạch Chiếc, Quận 2</p>
                                <p>Các bạn mới muốn tham gia vui lòng đăng ký trước qua form để ban điều hành sắp xếp nhé!</p>
                                <p>Mọi thắc mắc inbox page hoặc liên hệ hotline: 0901 234 567 ☎️</p>
                            </div>
                            <div class="post-stats">
                                <div class="reactions">
                                    <div class="reaction-icons">
                                        <span class="reaction like">👍</span>
                                    </div>
                                    <span class="reaction-count">45</span>
                                </div>
                                <div class="engagement">
                                    <span>12 bình luận</span>
                                </div>
                            </div>
                            <div class="post-actions">
                                <button class="post-action like-btn" data-post-id="4">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                                    </svg>
                                    <span>Thích</span>
                                </button>
                                <button class="post-action comment-btn" data-post-id="4">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                    <span>Bình luận</span>
                                </button>
                                <button class="post-action share-btn" data-post-id="4">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="18" cy="5" r="3"/>
                                        <circle cx="6" cy="12" r="3"/>
                                        <circle cx="18" cy="19" r="3"/>
                                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                                    </svg>
                                    <span>Chia sẻ</span>
                                </button>
                            </div>
                        </article>

                        <!-- Load More -->
                        <button class="load-more-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 4 23 10 17 10"/>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                            </svg>
                            Xem thêm bài viết
                        </button>
                    </div>
                </div>

                <!-- Right Column - Sidebar -->
                <div class="sidebar-column">
                    <!-- Activity Area Card -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Khu vực hoạt động
                        </h3>
                        <div class="activity-areas">
                            <div class="area-item">
                                <div class="area-icon primary">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                        <polyline points="9 22 9 12 15 12 15 22"/>
                                    </svg>
                                </div>
                                <div class="area-info">
                                    <h4>Sân Rạch Chiếc</h4>
                                    <p>Quận 2, TP.HCM</p>
                                    <span class="area-tag main">Sân chính</span>
                                </div>
                            </div>
                            <div class="area-item">
                                <div class="area-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                        <polyline points="9 22 9 12 15 12 15 22"/>
                                    </svg>
                                </div>
                                <div class="area-info">
                                    <h4>Sân Thủ Đức</h4>
                                    <p>Thủ Đức, TP.HCM</p>
                                </div>
                            </div>
                            <div class="area-item">
                                <div class="area-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                        <polyline points="9 22 9 12 15 12 15 22"/>
                                    </svg>
                                </div>
                                <div class="area-info">
                                    <h4>Sân Phú Mỹ Hưng</h4>
                                    <p>Quận 7, TP.HCM</p>
                                </div>
                            </div>
                        </div>
                        <a href="#" class="sidebar-link">Xem tất cả sân →</a>
                    </div>

                    <!-- Management Team Card -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            Ban điều hành
                        </h3>
                        <div class="management-team">
                            <div class="team-member">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%23FFD93D'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='%23333'%3EPH%3C/text%3E%3C/svg%3E" alt="Phạm Hoàng" class="member-avatar">
                                <div class="member-info">
                                    <span class="member-name">Phạm Hoàng</span>
                                    <span class="member-role president">Chủ nhiệm CLB</span>
                                </div>
                                <button class="member-action" title="Nhắn tin">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="team-member">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%230099CC'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3ENH%3C/text%3E%3C/svg%3E" alt="Nguyễn Hùng" class="member-avatar">
                                <div class="member-info">
                                    <span class="member-name">Nguyễn Hùng</span>
                                    <span class="member-role coach">Huấn luyện viên</span>
                                </div>
                                <button class="member-action" title="Nhắn tin">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="team-member">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%23FF8E53'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3ETM%3C/text%3E%3C/svg%3E" alt="Trần Mai" class="member-avatar">
                                <div class="member-info">
                                    <span class="member-name">Trần Thị Mai</span>
                                    <span class="member-role secretary">Thư ký</span>
                                </div>
                                <button class="member-action" title="Nhắn tin">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="team-member">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%2300D9B5'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3ELT%3C/text%3E%3C/svg%3E" alt="Lê Tuấn" class="member-avatar">
                                <div class="member-info">
                                    <span class="member-name">Lê Minh Tuấn</span>
                                    <span class="member-role treasurer">Thủ quỹ</span>
                                </div>
                                <button class="member-action" title="Nhắn tin">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Members Card -->
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <h3 class="sidebar-card-title">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                Thành viên
                            </h3>
                            <span class="member-count">256 thành viên</span>
                        </div>
                        <div class="members-grid">
                            <a href="#" class="member-avatar-item" title="Ngọc Linh">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%239D84B7'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3ENL%3C/text%3E%3C/svg%3E" alt="Ngọc Linh">
                            </a>
                            <a href="#" class="member-avatar-item" title="Minh Khoa">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%236C63FF'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3EMK%3C/text%3E%3C/svg%3E" alt="Minh Khoa">
                            </a>
                            <a href="#" class="member-avatar-item" title="Hương Giang">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23FE6B8B'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3EHG%3C/text%3E%3C/svg%3E" alt="Hương Giang">
                            </a>
                            <a href="#" class="member-avatar-item" title="Đức Anh">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%2300B89A'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3EDA%3C/text%3E%3C/svg%3E" alt="Đức Anh">
                            </a>
                            <a href="#" class="member-avatar-item" title="Thanh Tâm">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23FF6B6B'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3ETT%3C/text%3E%3C/svg%3E" alt="Thanh Tâm">
                            </a>
                            <a href="#" class="member-avatar-item" title="Quốc Bảo">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%230099CC'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3EQB%3C/text%3E%3C/svg%3E" alt="Quốc Bảo">
                            </a>
                            <a href="#" class="member-avatar-item" title="Yến Nhi">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23FFD93D'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='%23333'%3EYN%3C/text%3E%3C/svg%3E" alt="Yến Nhi">
                            </a>
                            <a href="#" class="member-avatar-item" title="Hoàng Long">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23FF8E53'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3EHL%3C/text%3E%3C/svg%3E" alt="Hoàng Long">
                            </a>
                            <a href="#" class="member-avatar-item more" title="Xem thêm">
                                <span>+248</span>
                            </a>
                        </div>
                        <a href="#" class="sidebar-link">Xem tất cả thành viên →</a>
                    </div>

                    <!-- Upcoming Events Card -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Sự kiện sắp tới
                        </h3>
                        <div class="upcoming-events">
                            <a href="#" class="event-item">
                                <div class="event-date">
                                    <span class="day">15</span>
                                    <span class="month">Th2</span>
                                </div>
                                <div class="event-info">
                                    <h4>Giải đấu Mùa Xuân 2025</h4>
                                    <p>Sân Rạch Chiếc, Q2</p>
                                    <span class="event-interested">56 người quan tâm</span>
                                </div>
                            </a>
                            <a href="#" class="event-item">
                                <div class="event-date">
                                    <span class="day">20</span>
                                    <span class="month">Th2</span>
                                </div>
                                <div class="event-info">
                                    <h4>Buổi giao lưu CLB Đà Nẵng</h4>
                                    <p>Sân Thủ Đức</p>
                                    <span class="event-interested">32 người quan tâm</span>
                                </div>
                            </a>
                            <a href="#" class="event-item">
                                <div class="event-date">
                                    <span class="day">01</span>
                                    <span class="month">Th3</span>
                                </div>
                                <div class="event-info">
                                    <h4>Workshop: Kỹ thuật nâng cao</h4>
                                    <p>Sân Phú Mỹ Hưng, Q7</p>
                                    <span class="event-interested">28 người quan tâm</span>
                                </div>
                            </a>
                        </div>
                        <a href="#" class="sidebar-link">Xem tất cả sự kiện →</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Create Post Modal -->
    <div class="modal-overlay" id="postModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tạo bài viết</h3>
                <button class="modal-close" id="closePostModal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-author">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%2300D9B5'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3EBạn%3C/text%3E%3C/svg%3E" alt="Your avatar" class="modal-avatar">
                    <div class="modal-author-info">
                        <span class="modal-author-name">Tên của bạn</span>
                        <button class="visibility-selector">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                            </svg>
                            Công khai
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <textarea class="post-textarea" placeholder="Bạn đang nghĩ gì về Pickleball?" rows="5"></textarea>
                <div class="media-preview" id="mediaPreview"></div>
            </div>
            <div class="modal-add-to-post">
                <span>Thêm vào bài viết</span>
                <div class="add-options">
                    <button class="add-option-btn" data-type="photo" title="Thêm ảnh">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </button>
                    <button class="add-option-btn" data-type="video" title="Thêm video">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="23 7 16 12 23 17 23 7"/>
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                        </svg>
                    </button>
                    <button class="add-option-btn" data-type="emoji" title="Thêm emoji">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                            <line x1="9" y1="9" x2="9.01" y2="9"/>
                            <line x1="15" y1="9" x2="15.01" y2="9"/>
                        </svg>
                    </button>
                    <button class="add-option-btn" data-type="location" title="Thêm vị trí">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary btn-block" id="submitPost">Đăng bài</button>
            </div>
        </div>
    </div>

<script>
        // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
    // Post Modal
    const postModal = document.getElementById('postModal');
    const openPostModalBtn = document.getElementById('openPostModal');
    const closePostModalBtn = document.getElementById('closePostModal');
    const postActionBtns = document.querySelectorAll('.post-action-btn');
    function openModal() {
        postModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        postModal.classList.remove('active');
        document.body.style.overflow = '';
    }
    openPostModalBtn?.addEventListener('click', openModal);
    closePostModalBtn?.addEventListener('click', closeModal);
    postModal?.addEventListener('click', (e) => {
        if (e.target === postModal) closeModal();
    });
    postActionBtns.forEach(btn => {
        btn.addEventListener('click', openModal);
    });
    // Like buttons
    const likeBtns = document.querySelectorAll('.like-btn');
    likeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('active');
        });
    });
    // Comment toggle
    const commentBtns = document.querySelectorAll('.comment-btn');
    commentBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const postId = btn.dataset.postId;
            const commentsSection = document.getElementById(`comments-${postId}`);
            if (commentsSection) {
                commentsSection.classList.toggle('show');
            }
        });
    });
</script>

@endsection
