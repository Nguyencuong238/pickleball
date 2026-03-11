@extends('layouts.front')

@section('seo')
    <title>Giải Đấu Leagues - OnePickleball</title>
    <meta name="description" content="Khám phá và đăng ký tham gia các giải đấu (Leagues) Pickleball chuyên nghiệp trên toàn quốc. Tìm kiếm giải đấu theo địa điểm, trình độ, và thời gian.">
    <meta name="keywords" content="leagues pickleball, đăng ký leagues, pickleball leagues, tìm leagues, giải leagues pickleball">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Leagues Pickleball - Tìm & Đăng Ký">
    <meta property="og:description" content="Khám phá và đăng ký tham gia các giải đấu Leagues Pickleball chuyên nghiệp trên toàn quốc">
    <meta property="og:image" content="{{ asset('assets/images/logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="OnePickleball">
    <meta property="og:locale" content="vi_VN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Leagues Pickleball - OnePickleball">
    <meta name="twitter:description" content="Khám phá các giải đấu Leagues Pickleball chuyên nghiệp">
    <meta name="twitter:image" content="{{ asset('assets/images/logo.png') }}">
@endsection

@section('css')
    <style>
        .filter-body {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            max-height: 1000px;
            overflow: hidden;
        }

        .filter-body.hidden {
            max-height: 0;
            opacity: 0;
            visibility: hidden;
        }

        .btn-arrow-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-arrow-toggle:hover svg {
            transform: scale(1.1);
        }

        .btn-arrow-toggle svg {
            transition: transform 0.3s ease;
        }
        .d-none {
            display: none;
        }
    </style>
@endsection

@section('content')
    <section class="page-header">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="breadcrumb">
                <a href="/">Trang chủ</a>
                <span class="separator">/</span>
                <span>Giải đấu Round Robin</span>
            </div>
            <h1 class="page-title">Giải đấu Round Robin</h1>
            <p class="page-description">Tìm và đăng ký tham gia các giải đấu Round Robin chuyên nghiệp và phong trào trên toàn quốc</p>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $totalLeagues }}</div>
                        <div class="stat-label">Tổng số League</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">🔥</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $activeLeagues }}</div>
                        <div class="stat-label">League đang hoạt động</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $statusOpen }}</div>
                        <div class="stat-label">Đang mở đăng ký</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">⚡</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $statusOngoing }}</div>
                        <div class="stat-label">Đang diễn ra</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="tournaments-section section">
        <div class="container">
            <div class="tournaments-layout">
                <!-- Sidebar Filters -->
                <aside class="tournaments-sidebar">
                    <form id="filterForm" method="GET" action="{{ route('leagues.index') }}" class="filter-card">
                        <div class="filter-header">
                            <h3 class="filter-title">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <line x1="4" y1="21" x2="4" y2="14" />
                                    <line x1="4" y1="10" x2="4" y2="3" />
                                    <line x1="12" y1="21" x2="12" y2="12" />
                                    <line x1="12" y1="8" x2="12" y2="3" />
                                    <line x1="20" y1="21" x2="20" y2="16" />
                                    <line x1="20" y1="12" x2="20" y2="3" />
                                    <line x1="1" y1="14" x2="7" y2="14" />
                                    <line x1="9" y1="8" x2="15" y2="8" />
                                    <line x1="17" y1="16" x2="23" y2="16" />
                                </svg>
                                Bộ lọc
                                <span class="btn-arrow-toggle">
                                    <svg class="arrow-up" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24">
                                        <path d="M6 15l6-6 6 6" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <svg class="arrow-down d-none" width="20" height="20" fill="#475569"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            </h3>
                            <a href="{{ route('leagues.index') }}" class="filter-reset">Xóa bộ lọc</a>
                        </div>
                        <div class="filter-body">
                            <!-- Search -->
                            <div class="filter-group">
                                <label class="filter-label">Tìm kiếm</label>
                                <div class="search-input-wrapper">
                                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.35-4.35" />
                                    </svg>
                                    <input type="text" name="search" class="filter-search"
                                        placeholder="Tên League..." value="{{ $filters['search'] ?? '' }}">
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="filter-group">
                                <label class="filter-label">Trạng thái</label>
                                <div class="filter-options">
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="statuses[]" value="registration"
                                            @if (in_array('registration', $filters['statuses'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Đang mở đăng ký</span>
                                        <span class="filter-count">({{ $statusOpen }})</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="statuses[]" value="active"
                                            @if (in_array('active', $filters['statuses'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Đang diễn ra</span>
                                        <span class="filter-count">({{ $statusOngoing }})</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="statuses[]" value="completed"
                                            @if (in_array('completed', $filters['statuses'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Đã kết thúc</span>
                                        <span class="filter-count">({{ $statusCompleted }})</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Apply Filters Button -->
                        <button type="submit" class="btn btn-primary btn-block filter-apply">
                            Áp dụng bộ lọc
                        </button>
                    </form>

                </aside>

                <!-- Main Content Area -->
                <div class="tournaments-main">
                    <!-- Toolbar -->
                    <div class="tournaments-toolbar">
                        <div class="toolbar-left">
                            <h2 class="toolbar-title">Tìm thấy <span
                                    class="result-count">{{ $leagues->total() }}</span> league</h2>
                        </div>
                    </div>

                    <!-- Tournaments Grid -->
                    <div class="tournaments-grid" id="tournamentsGrid">
                        @forelse($leagues as $league)
                            <div class="tournament-item" data-tournament-id="{{ $league->id }}">
                                <a href="{{ route('leagues.register', $league->slug) }}" class="tournament-link">
                                    <div class="tournament-image">
                                        @php
                                            $imageUrl = $league->logo ? asset('storage/' . $league->logo) : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 250%22%3E%3Cdefs%3E%3ClinearGradient id=%22t' .
                                                    $league->id .
                                                    '%22 x1=%220%25%22 y1=%220%25%22 x2=%22100%25%22 y2=%22100%25%22%3E%3Cstop offset=%220%25%22 style=%22stop-color:%2300D9B5;stop-opacity:1%22 /%3E%3Cstop offset=%22100%25%22 style=%22stop-color:%230099CC;stop-opacity:1%22 /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill=%22url(%23t' .
                                                    $league->id .
                                                    ')%22 width=%22400%22 height=%22250%22/%3E%3Ctext x=%22200%22 y=%22125%22 font-family=%22Arial%22 font-size=%2224%22 fill=%22white%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 font-weight=%22bold%22%3E' .
                                                    strtoupper(substr($league->name, 0, 20)) .
                                                    '%3C/text%3E%3C/svg%3E';
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="{{ $league->name }}">
                                        @if($league->status === 'registration')
                                            <div class="tournament-badges">
                                                <span class="badge badge-status" style="background: rgba(16, 185, 129, 0.9); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">🕒 Đang mở đăng ký</span>
                                            </div>
                                        @elseif($league->status === 'active')
                                            <div class="tournament-badges">
                                                <span class="badge badge-status" style="background: rgba(245, 158, 11, 0.9); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">🔥 Đang diễn ra</span>
                                            </div>
                                        @elseif($league->status === 'completed')
                                            <div class="tournament-badges">
                                                <span class="badge badge-status" style="background: rgba(107, 114, 128, 0.9); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">✅ Đã kết thúc</span>
                                            </div>
                                        @endif
                                        <div class="tournament-overlay">
                                            <span class="overlay-text">Đăng ký tham gia →</span>
                                        </div>
                                    </div>
                                    <div class="tournament-content">
                                        <div class="tournament-date-badge">
                                            <div class="date-icon">📅</div>
                                            <div class="date-text">
                                                @if($league->start_date)
                                                    <span class="date-day">{{ $league->start_date->format('d-m') }}</span>
                                                    <span class="date-month">{{ $league->start_date->format('M Y') }}</span>
                                                @else
                                                    <span class="date-day">N/A</span>
                                                    <span class="date-month">N/A</span>
                                                @endif
                                            </div>
                                        </div>
                                        <h3 class="tournament-title" style="margin-bottom: 5px;">{{ $league->name }}</h3>
                                        @if($league->season_name)
                                            <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 10px; font-weight: 500;">
                                                <i class="fas fa-medal" style="margin-right: 5px; color: var(--primary-color);"></i>{{ $league->season_name }}
                                            </p>
                                        @endif

                                        <div class="tournament-meta">
                                            <div class="meta-item">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                    <circle cx="9" cy="7" r="4" />
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                                </svg>
                                                <span>{{ $league->required_players_per_registration }} VĐV / đội</span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="far fa-clock" style="margin-right: 6px;"></i>
                                                @if($league->registration_deadline)
                                                <span>Hạn: {{ $league->registration_deadline->format('d/m/Y') }}</span>
                                                @else
                                                <span>N/A</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="tournament-footer" style="border-top: none">
                                            <div class="tournament-prize">
                                                <span class="prize-label">Lệ phí</span>
                                                <span class="prize-amount">{{ $league->registration_fee ? number_format($league->registration_fee, 0, '.', ',') . ' VNĐ' : 'Miễn phí' }}</span>
                                            </div>
                                            <a href="{{ route('leagues.register', $league->slug) }}" class="btn btn-primary btn-sm tournament-register-btn" style="text-decoration: none; display: inline-block;">
                                                Đăng ký ngay
                                            </a>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div style="grid-column: 1/-1; padding: 3rem; text-align: center; color: var(--text-secondary);">
                                <p>Không có league nào phù hợp với tìm kiếm của bạn</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if ($leagues->hasPages())
                        <div style="margin-top: 30px;">
                            {{ $leagues->links('pagination.custom') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-banner section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Không tìm thấy league phù hợp?</h2>
                <p class="cta-description">Đăng ký nhận thông báo về các leagues mới và ưu đãi đặc biệt</p>
                <button class="btn btn-primary btn-lg">Đăng ký nhận thông báo</button>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter toggle functionality
            const toggleBtns = document.querySelectorAll('.btn-arrow-toggle');
            
            toggleBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const filterHeader = this.closest('.filter-header');
                    const filterBody = filterHeader.nextElementSibling;
                    const arrowUp = this.querySelector('.arrow-up');
                    const arrowDown = this.querySelector('.arrow-down');
                    
                    if (filterBody.classList.contains('hidden')) {
                        filterBody.classList.remove('hidden');
                        arrowUp.classList.remove('d-none');
                        arrowDown.classList.add('d-none');
                    } else {
                        filterBody.classList.add('hidden');
                        arrowUp.classList.add('d-none');
                        arrowDown.classList.remove('d-none');
                    }
                });
            });
        });
    </script>
@endsection
