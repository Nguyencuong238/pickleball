<section class="hero" id="home">
    <div class="hero-bg">
        <div class="hero-grid"></div>
        <div class="hero-orb1"></div>
        <div class="hero-orb2"></div>
    </div>
    <div class="section-inner" style="display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1;min-height:calc(70vh - 70px);padding-top:0;">
        <div class="hero-content">
            <div class="hero-badge">
                <div class="badge-dot"></div>
                Nền tảng Pickleball #1 Việt Nam
            </div>
            <h1 class="hero-title">
                <span class="line1">Kết nối cộng đồng</span>
                <span class="line2">Pickleball Việt Nam</span>
            </h1>
            <p class="hero-desc">Tìm sân, đăng ký giải đấu, kết nối huấn luyện viên và giao lưu cùng hàng ngàn người chơi trên toàn quốc — tất cả trên một nền tảng duy nhất.</p>
            <div class="hero-actions">
                <a class="btn-primary btn-large" href="{{ route('register') }}">Bắt đầu ngay</a>
                <a class="btn-outline-large" href="{{ route('tournaments') }}">Xem giải đấu</a>
            </div>
        </div>

        <!-- Hero Visual (decorative court card) -->
        <div class="hero-visual">
            <div class="hp-court-card" style="position:relative;padding:20px;">
                <div class="float-card card-top">
                    <div class="float-icon blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="float-text">
                        <span>Giải mới</span>
                        <strong>HCMC Open 2025</strong>
                    </div>
                </div>
                <div class="court-illustration">
                    <!-- Court boundary -->
                    <div class="court-boundary">
                        <!-- Left baseline -->
                        <div class="court-line court-baseline-left"></div>
                        <!-- Right baseline -->
                        <div class="court-line court-baseline-right"></div>
                        <!-- Top sideline -->
                        <div class="court-line court-sideline-top"></div>
                        <!-- Bottom sideline -->
                        <div class="court-line court-sideline-bottom"></div>
                        <!-- Net -->
                        <div class="court-line court-net-center"></div>
                        <!-- Left kitchen line -->
                        <div class="court-line court-kitchen-line-left"></div>
                        <!-- Right kitchen line -->
                        <div class="court-line court-kitchen-line-right"></div>
                        <!-- Left center service line -->
                        <div class="court-line court-centerline-left"></div>
                        <!-- Right center service line -->
                        <div class="court-line court-centerline-right"></div>
                        <!-- Net posts -->
                        <div class="court-net-post court-net-post-top"></div>
                        <div class="court-net-post court-net-post-bottom"></div>
                    </div>
                    <!-- Ball -->
                    <div class="court-ball"></div>
                    <!-- Players -->
                    <div class="court-player court-player-left">
                        <div class="player-body"></div>
                        <div class="player-paddle"></div>
                    </div>
                    <div class="court-player court-player-right">
                        <div class="player-body"></div>
                        <div class="player-paddle"></div>
                    </div>
                </div>
                <div style="margin-top:14px;">
                    <div class="live-chip">
                        <div class="live-dot"></div> LIVE
                    </div>
                    <div class="card-score-row">
                        <div class="score-match" style="display:flex;align-items:center;gap:12px;flex:1;">
                            <div style="text-align:center;">
                                <div class="team-name">Tuấn Anh</div>
                                <div class="score win">11</div>
                            </div>
                            <div class="vs-badge">VS</div>
                            <div style="text-align:center;">
                                <div class="team-name">Minh Khoa</div>
                                <div class="score">7</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="float-card card-bottom">
                    <div class="float-icon green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="float-text">
                        <span>Gần bạn nhất</span>
                        <strong>Sân Phú Mỹ Hưng</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
