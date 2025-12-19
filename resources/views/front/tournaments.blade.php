@extends('layouts.front')

@section('seo')
    <title>Giải Đấu Pickleball - OnePickleball</title>
    <meta name="description" content="Khám phá và đăng ký tham gia các giải đấu Pickleball chuyên nghiệp trên toàn quốc. Tìm kiếm giải đấu theo địa điểm, trình độ, và thời gian.">
    <meta name="keywords" content="giải đấu pickleball, đăng ký giải đấu, pickleball tournament, tìm giải đấu, giải pickleball">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Giải Đấu Pickleball - Tìm & Đăng Ký">
    <meta property="og:description" content="Khám phá và đăng ký tham gia các giải đấu Pickleball chuyên nghiệp trên toàn quốc">
    <meta property="og:image" content="{{ asset('assets/images/logo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="OnePickleball">
    <meta property="og:locale" content="vi_VN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Giải Đấu Pickleball - OnePickleball">
    <meta name="twitter:description" content="Khám phá các giải đấu Pickleball chuyên nghiệp">
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
                <span>Giải đấu</span>
            </div>
            <h1 class="page-title">Giải Đấu Pickleball</h1>
            <p class="page-description">Tìm và đăng ký tham gia các giải đấu Pickleball chuyên nghiệp và phong trào trên toàn
                quốc</p>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $totalTournaments }}</div>
                        <div class="stat-label">Giải đấu năm nay</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ number_format($totalAthletes, 0) }}</div>
                        <div class="stat-label">Vận động viên</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ number_format($totalPrizes / 1000000000, 1) }} tỷ</div>
                        <div class="stat-label">Tổng giải thưởng</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">📍</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $totalLocations }}</div>
                        <div class="stat-label">Tỉnh/Thành phố</div>
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
                    <form id="filterForm" method="GET" action="{{ route('tournaments') }}" class="filter-card">
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
                            <a href="{{ route('tournaments') }}" class="filter-reset">Xóa bộ lọc</a>
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
                                        placeholder="Tên giải đấu..." value="{{ $filters['search'] ?? '' }}">
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div class="filter-group">
                                <label class="filter-label">Trạng thái</label>
                                <div class="filter-options">
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="statuses[]" value="open"
                                            @if (in_array('open', $filters['statuses'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Đang mở đăng ký</span>
                                        <span class="filter-count">({{ $statusOpen }})</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="statuses[]" value="coming_soon"
                                            @if (in_array('coming_soon', $filters['statuses'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Sắp mở</span>
                                        <span class="filter-count">({{ $statusComingSoon }})</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="statuses[]" value="ongoing"
                                            @if (in_array('ongoing', $filters['statuses'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Đang diễn ra</span>
                                        <span class="filter-count">({{ $statusOngoing }})</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="statuses[]" value="ended"
                                            @if (in_array('ended', $filters['statuses'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Đã kết thúc</span>
                                        <span class="filter-count">({{ $statusEnded }})</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Location Filter -->
                            <div class="filter-group">
                                <label class="filter-label">Địa điểm</label>
                                <select name="location" class="filter-select">
                                    <option value="">Tất cả khu vực</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location }}"
                                            @if ($filters['location'] === $location) selected @endif>{{ $location }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Level Filter -->
                            <div class="filter-group">
                                <label class="filter-label">Trình độ</label>
                                <div class="filter-options">
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="ranks[]" value="beginner"
                                            @if (in_array('beginner', $filters['ranks'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Beginner</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="ranks[]" value="intermediate"
                                            @if (in_array('intermediate', $filters['ranks'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Intermediate</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="ranks[]" value="advanced"
                                            @if (in_array('advanced', $filters['ranks'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Advanced</span>
                                    </label>
                                    <label class="filter-checkbox">
                                        <input type="checkbox" name="ranks[]" value="professional"
                                            @if (in_array('professional', $filters['ranks'] ?? [])) checked @endif>
                                        <span class="checkbox-custom"></span>
                                        <span>Professional</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Date Range -->
                            <div class="filter-group">
                                <label class="filter-label">Thời gian</label>
                                <div class="date-range">
                                    <input type="date" name="start_date" class="filter-date"
                                        value="{{ $filters['start_date'] ?? '' }}">
                                    <input type="date" name="end_date" class="filter-date"
                                        value="{{ $filters['end_date'] ?? '' }}">
                                </div>
                            </div>

                            <!-- Prize Filter -->
                            <div class="filter-group">
                                <label class="filter-label">Giải thưởng</label>
                                <div class="filter-options">
                                    <label class="filter-radio">
                                        <input type="radio" name="prize_range" value=""
                                            @if (!($filters['prize_range'] ?? '')) checked @endif>
                                        <span class="radio-custom"></span>
                                        <span>Tất cả</span>
                                    </label>
                                    <label class="filter-radio">
                                        <input type="radio" name="prize_range" value="low"
                                            @if (($filters['prize_range'] ?? '') === 'low') checked @endif>
                                        <span class="radio-custom"></span>
                                        <span>Dưới 100 triệu</span>
                                    </label>
                                    <label class="filter-radio">
                                        <input type="radio" name="prize_range" value="mid"
                                            @if (($filters['prize_range'] ?? '') === 'mid') checked @endif>
                                        <span class="radio-custom"></span>
                                        <span>100 - 300 triệu</span>
                                    </label>
                                    <label class="filter-radio">
                                        <input type="radio" name="prize_range" value="high"
                                            @if (($filters['prize_range'] ?? '') === 'high') checked @endif>
                                        <span class="radio-custom"></span>
                                        <span>Trên 300 triệu</span>
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
                                    class="result-count">{{ $tournaments->total() }}</span> giải đấu</h2>
                        </div>
                        <div class="toolbar-right">
                            {{-- <div class="view-toggle">
                                <button class="view-btn active" data-view="grid" title="Grid view">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="3" y="3" width="7" height="7"/>
                                        <rect x="14" y="3" width="7" height="7"/>
                                        <rect x="14" y="14" width="7" height="7"/>
                                        <rect x="3" y="14" width="7" height="7"/>
                                    </svg>
                                </button>
                                <button class="view-btn" data-view="list" title="List view">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <line x1="8" y1="6" x2="21" y2="6"/>
                                        <line x1="8" y1="12" x2="21" y2="12"/>
                                        <line x1="8" y1="18" x2="21" y2="18"/>
                                        <line x1="3" y1="6" x2="3.01" y2="6"/>
                                        <line x1="3" y1="12" x2="3.01" y2="12"/>
                                        <line x1="3" y1="18" x2="3.01" y2="18"/>
                                    </svg>
                                </button>
                            </div> --}}
                            {{-- <select class="sort-select">
                                <option value="date-asc">Ngày diễn ra (gần nhất)</option>
                                <option value="date-desc">Ngày diễn ra (xa nhất)</option>
                                <option value="prize-desc">Giải thưởng (cao nhất)</option>
                                <option value="prize-asc">Giải thưởng (thấp nhất)</option>
                                <option value="name-asc">Tên A-Z</option>
                                <option value="name-desc">Tên Z-A</option>
                            </select> --}}
                        </div>
                    </div>

                    <!-- Tournaments Grid -->
                    <div class="tournaments-grid" id="tournamentsGrid">
                        @forelse($tournaments as $tournament)
                            <div class="tournament-item" data-tournament-id="{{ $tournament->id }}">
                                <a href="{{ route('tournaments-detail', $tournament->id) }}" class="tournament-link">
                                    <div class="tournament-image">
                                        @php
                                            $media = $tournament->getFirstMedia('banner');
                                            $imageUrl = $media
                                                ? $media->getUrl()
                                                : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 250%22%3E%3Cdefs%3E%3ClinearGradient id=%22t' .
                                                    $tournament->id .
                                                    '%22 x1=%220%25%22 y1=%220%25%22 x2=%22100%25%22 y2=%22100%25%22%3E%3Cstop offset=%220%25%22 style=%22stop-color:%2300D9B5;stop-opacity:1%22 /%3E%3Cstop offset=%22100%25%22 style=%22stop-color:%230099CC;stop-opacity:1%22 /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill=%22url(%23t' .
                                                    $tournament->id .
                                                    ')%22 width=%22400%22 height=%22250%22/%3E%3Ctext x=%22200%22 y=%22125%22 font-family=%22Arial%22 font-size=%2224%22 fill=%22white%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 font-weight=%22bold%22%3E' .
                                                    strtoupper(substr($tournament->name, 0, 20)) .
                                                    '%3C/text%3E%3C/svg%3E';
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="{{ $tournament->name }}">
                                        <div class="tournament-badges">
                                            <span class="badge badge-status status-open">Đang mở</span>
                                        </div>
                                        <div class="tournament-overlay">
                                            <span class="overlay-text">Xem chi tiết →</span>
                                        </div>
                                    </div>
                                    <div class="tournament-content">
                                        <div class="tournament-date-badge">
                                            <div class="date-icon">📅</div>
                                            <div class="date-text">
                                                <span class="date-day">{{ $tournament->start_date->format('d-m') }}</span>
                                                <span
                                                    class="date-month">{{ $tournament->start_date->format('M Y') }}</span>
                                            </div>
                                        </div>
                                        <h3 class="tournament-title">{{ $tournament->name }}</h3>
                                        {{-- <p class="tournament-excerpt">{{ substr($tournament->description, 0, 80) }}...</p> --}}

                                        <div class="tournament-meta">
                                            <div class="meta-item">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                                    <circle cx="12" cy="10" r="3" />
                                                </svg>
                                                <span>{{ $tournament->location ?? 'N/A' }}</span>
                                            </div>
                                            <div class="meta-item">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                                    <circle cx="9" cy="7" r="4" />
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                                </svg>
                                                <span>{{ $tournament->athletes()->count() }} VĐV</span>
                                            </div>
                                        </div>

                                        <div class="tournament-footer">
                                            <div class="tournament-prize">
                                                <span class="prize-label">Giải thưởng</span>
                                                <span
                                                    class="prize-amount">{{ $tournament->prizes ? number_format($tournament->prizes, 0, '.', ',') . ' VNĐ' : 'N/A' }}</span>
                                            </div>
                                            <a href="{{ route('tournaments-detail', $tournament->id) }}" class="btn btn-primary btn-sm tournament-register-btn" style="text-decoration: none; display: inline-block;">
                                                Xem ngay
                                            </a>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div
                                style="grid-column: 1/-1; padding: 3rem; text-align: center; color: var(--text-secondary);">
                                <p>Không có giải đấu nào</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if ($tournaments->hasPages())
                        {{ $tournaments->links('pagination.custom') }}
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-banner section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Không tìm thấy giải đấu phù hợp?</h2>
                <p class="cta-description">Đăng ký nhận thông báo về các giải đấu mới và ưu đãi đặc biệt</p>
                <button class="btn btn-primary btn-lg">Đăng ký nhận thông báo</button>
            </div>
        </div>
    </section>

    <!-- Detail Modal -->
    <div id="detailModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 2000; display: none; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
        <div style="background: white; border-radius: 20px; width: 90%; max-width: 700px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: auto; animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); max-height: 100vh; scrollbar-width: none;">
            <!-- Tournament Image -->
            <div style="width: 100%; height: 300px; overflow: hidden; border-radius: 20px 20px 0 0;">
                <img id="detailImage" src="" alt="" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 30px 30px 40px; color: white; position: relative;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Chi tiết giải đấu</h2>
                <button onclick="closeDetailModal()"
                    style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.5rem; cursor: pointer; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    ✕
                </button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 40px 30px; overflow-y: auto; max-height: 70vh;">
                <div style="display: grid; gap: 25px;">
                    <!-- Thông tin cơ bản -->
                    <div>
                        <h3 style="margin: 0 0 15px 0; color: #1f2937; font-size: 1.1rem; font-weight: 600;">Thông tin cơ bản</h3>
                        <div style="display: grid; gap: 12px;">
                            <div style="display: grid; grid-template-columns: 150px 1fr; gap: 20px;">
                                <span style="font-weight: 600; color: #6b7280;">Tên giải:</span>
                                <span id="detailName" style="color: #1f2937;"></span>
                            </div>
                            <div style="display: grid; grid-template-columns: 150px 1fr; gap: 20px;">
                                <span style="font-weight: 600; color: #6b7280;">Địa điểm:</span>
                                <span id="detailLocation" style="color: #1f2937;"></span>
                            </div>
                            <div style="display: grid; grid-template-columns: 150px 1fr; gap: 20px;">
                                <span style="font-weight: 600; color: #6b7280;">Thời gian:</span>
                                <span id="detailDate" style="color: #1f2937;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin tham gia -->
                    <div style="border-top: 1px solid #e5e7eb; padding-top: 25px;">
                        <h3 style="margin: 0 0 15px 0; color: #1f2937; font-size: 1.1rem; font-weight: 600;">Thông tin tham gia</h3>
                        <div style="display: grid; gap: 12px;">
                            <div style="display: grid; grid-template-columns: 150px 1fr; gap: 20px;">
                                <span style="font-weight: 600; color: #6b7280;">Số VĐV tối đa:</span>
                                <span id="detailMaxParticipants" style="color: #1f2937;"></span>
                            </div>
                            <div style="display: grid; grid-template-columns: 150px 1fr; gap: 20px;">
                                <span style="font-weight: 600; color: #6b7280;">Lệ phí đăng ký:</span>
                                <span id="detailPrice" style="color: #1f2937;"></span>
                            </div>
                            <div style="display: grid; grid-template-columns: 150px 1fr; gap: 20px;">
                                <span style="font-weight: 600; color: #6b7280;">Hạn đăng ký:</span>
                                <span id="detailDeadline" style="color: #1f2937;"></span>
                            </div>
                            <div style="display: grid; grid-template-columns: 150px 1fr; gap: 20px;">
                                <span style="font-weight: 600; color: #6b7280;">Đã đăng ký:</span>
                                <div>
                                    <div style="margin-bottom: 8px; color: #1f2937;" id="detailRegistered"></div>
                                    <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                        <div id="detailProgressBar" style="height: 100%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin giải thưởng -->
                    <div style="border-top: 1px solid #e5e7eb; padding-top: 25px;">
                        <h3 style="margin: 0 0 15px 0; color: #1f2937; font-size: 1.1rem; font-weight: 600;">Giải thưởng</h3>
                        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 20px;">
                            <span style="font-weight: 600; color: #6b7280;">Tổng giải:</span>
                            <span id="detailPrizes" style="color: #1f2937; font-size: 1.1rem; font-weight: 600;"></span>
                        </div>
                    </div>

                    <!-- Thông tin liên hệ -->
                    <div id="detailContactSection" style="border-top: 1px solid #e5e7eb; padding-top: 25px; display: none;">
                        <h3 style="margin: 0 0 15px 0; color: #1f2937; font-size: 1.1rem; font-weight: 600;">Liên hệ</h3>
                        <div style="display: grid; gap: 12px;" id="detailContactList"></div>
                    </div>

                    <!-- Mô tả -->
                    <div id="detailDescriptionSection" style="border-top: 1px solid #e5e7eb; padding-top: 25px; display: none;">
                        <h3 style="margin: 0 0 15px 0; color: #1f2937; font-size: 1.1rem; font-weight: 600;">Mô tả</h3>
                        <p id="detailDescription" style="margin: 0; color: #1f2937; line-height: 1.6;"></p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 0 30px 30px; display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeDetailModal()"
                    style="padding: 12px 28px; border: 2px solid #e5e7eb; background: white; color: #6b7280; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.95rem;"
                    onmouseover="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb'"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='white'">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden tournaments data for modal population -->
    <script type="application/json" id="tournamentsData">
        @php
            $tournamentsData = [];
            foreach($tournaments as $tournament) {
                $media = $tournament->getFirstMedia('banner');
                $imageUrl = $media ? $media->getUrl() : '';
                $tournamentsData[$tournament->id] = [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'location' => $tournament->location,
                    'start_date' => $tournament->start_date ? $tournament->start_date->format('d/m/Y') : 'N/A',
                    'end_date' => $tournament->end_date ? $tournament->end_date->format('d/m/Y') : 'N/A',
                    'registration_deadline' => $tournament->registration_deadline ? $tournament->registration_deadline->format('d/m/Y H:i') : 'N/A',
                    'max_participants' => $tournament->max_participants,
                    'price' => $tournament->price ?? 0,
                    'prizes' => $tournament->prizes ?? 0,
                    'athletes_count' => $tournament->athletes()->count(),
                    'image_url' => $imageUrl,
                    'description' => $tournament->description ?? '',
                    'organizer_email' => $tournament->organizer_email ?? '',
                    'organizer_hotline' => $tournament->organizer_hotline ?? ''
                ];
            }
            echo json_encode($tournamentsData);
        @endphp
    </script>

    <!-- Registration Modal -->
    <div id="registerModal"
        style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 2000; display: none; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
        <div
            style="background: white; border-radius: 20px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <!-- Modal Header with Gradient -->
            <div
                style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 30px 30px 40px; color: white; position: relative;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Đăng ký tham gia</h2>
                <p id="modalTournamentName" style="margin: 8px 0 0 0; opacity: 0.9; font-size: 0.95rem;"></p>
                <button onclick="closeRegisterModal()"
                    style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.5rem; cursor: pointer; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    ✕
                </button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 40px 30px;">
                <form id="registerForm">
                    @csrf
                    <input type="hidden" id="tournament_id" name="tournament_id">

                    <!-- Athlete Name Field -->
                    <div style="margin-bottom: 25px;">
                        <label for="athlete_name"
                            style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Tên vận động viên <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="athlete_name" name="athlete_name" required
                            value="{{ auth()->check() ? auth()->user()->name : '' }}" placeholder="Nhập tên của bạn"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        <div id="athlete_name_error"
                            style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
                    </div>

                    <!-- Email Field -->
                    <div style="margin-bottom: 25px;">
                        <label for="email"
                            style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Email <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                            value="{{ auth()->check() ? auth()->user()->email : '' }}" placeholder="Nhập email của bạn"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        <div id="email_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;">
                        </div>
                    </div>

                    <!-- Phone Field -->
                    <div style="margin-bottom: 30px;">
                        <label for="phone"
                            style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Số điện thoại <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" required
                            value="{{ auth()->check() ? auth()->user()->phone : '' }}"
                            placeholder="Nhập số điện thoại của bạn"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        <div id="phone_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;">
                        </div>
                    </div>

                    <!-- Category Selection Field -->
                    <div style="margin-bottom: 30px;">
                        <label for="category_id"
                            style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Nội dung thi đấu <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="category_id" name="category_id" required
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box; appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22%3E%3cpolyline points=%226 9 12 15 18 9%22%3E%3c/polyline%3E%3c/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 20px; padding-right: 40px;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                            <option value="">-- Chọn nội dung thi đấu --</option>
                        </select>
                        <div id="category_id_error"
                            style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 0 30px 30px; display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeRegisterModal()"
                    style="padding: 12px 28px; border: 2px solid #e5e7eb; background: white; color: #6b7280; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.95rem;"
                    onmouseover="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb'"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='white'">
                    Hủy
                </button>
                <button type="button" id="submitRegisterBtn" onclick="submitRegisterForm()"
                    style="padding: 12px 32px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.95rem; box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);"
                    onmouseover="this.style.boxShadow='0 6px 25px rgba(236, 72, 153, 0.4)'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.boxShadow='0 4px 15px rgba(236, 72, 153, 0.3)'; this.style.transform='translateY(0)'">
                    Đăng ký
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }
    </style>
@endsection

@section('js')
    <script src="{{ asset('assets/js/tournaments.js') }}"></script>
    <script>
        function openDetailModal(tournamentId) {
            const tournamentsData = JSON.parse(document.getElementById('tournamentsData').textContent);
            const tournament = tournamentsData[tournamentId];
            
            if (!tournament) {
                alert('Không thể tải thông tin giải đấu');
                return;
            }
            
            // Populate basic info
            document.getElementById('detailName').textContent = tournament.name;
            document.getElementById('detailLocation').textContent = tournament.location;
            document.getElementById('detailDate').textContent = tournament.start_date + ' - ' + tournament.end_date;
            
            // Populate participation info
            document.getElementById('detailMaxParticipants').textContent = tournament.max_participants + ' người';
            document.getElementById('detailPrice').textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(tournament.price);
            document.getElementById('detailDeadline').textContent = tournament.registration_deadline;
            
            // Populate registered count and progress
            const athletes = tournament.athletes_count || 0;
            const percentage = tournament.max_participants > 0 ? Math.round((athletes / tournament.max_participants) * 100) : 0;
            document.getElementById('detailRegistered').textContent = athletes + '/' + tournament.max_participants + ' (' + percentage + '%)';
            document.getElementById('detailProgressBar').style.width = percentage + '%';
            
            // Populate prizes
            document.getElementById('detailPrizes').textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(tournament.prizes);
            
            // Populate image
            const imgElement = document.getElementById('detailImage');
            if (tournament.image_url) {
                imgElement.src = tournament.image_url;
            } else {
                imgElement.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 700 400%22%3E%3Crect fill=%2200D9B5%22 width=%22700%22 height=%22400%22/%3E%3C/svg%3E';
            }
            imgElement.alt = tournament.name;
            
            // Populate contact info if exists
            const contactSection = document.getElementById('detailContactSection');
            const contactList = document.getElementById('detailContactList');
            if (tournament.organizer_email || tournament.organizer_hotline) {
                contactSection.style.display = 'block';
                contactList.innerHTML = '';
                
                if (tournament.organizer_email) {
                    const emailDiv = document.createElement('div');
                    emailDiv.style.cssText = 'display: grid; grid-template-columns: 150px 1fr; gap: 20px;';
                    emailDiv.innerHTML = '<span style="font-weight: 600; color: #6b7280;">Email:</span><span style="color: #1f2937;">' + tournament.organizer_email + '</span>';
                    contactList.appendChild(emailDiv);
                }
                
                if (tournament.organizer_hotline) {
                    const hotlineDiv = document.createElement('div');
                    hotlineDiv.style.cssText = 'display: grid; grid-template-columns: 150px 1fr; gap: 20px;';
                    hotlineDiv.innerHTML = '<span style="font-weight: 600; color: #6b7280;">Hotline:</span><span style="color: #1f2937;">' + tournament.organizer_hotline + '</span>';
                    contactList.appendChild(hotlineDiv);
                }
            } else {
                contactSection.style.display = 'none';
            }
            
            // Populate description if exists
            const descriptionSection = document.getElementById('detailDescriptionSection');
            if (tournament.description) {
                descriptionSection.style.display = 'block';
                document.getElementById('detailDescription').textContent = tournament.description;
            } else {
                descriptionSection.style.display = 'none';
            }
            
            // Show modal
            const modal = document.getElementById('detailModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function openRegisterModal(tournamentId, tournamentName) {
            document.getElementById('tournament_id').value = tournamentId;
            document.getElementById('modalTournamentName').textContent = tournamentName;

            // Load categories for this tournament
            const categorySelect = document.getElementById('category_id');
            categorySelect.innerHTML = '<option value="">-- Chọn nội dung thi đấu --</option>';

            fetch('/api/tournament/' + tournamentId + '/categories', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.categories && data.categories.length > 0) {
                        data.categories.forEach(category => {
                            const isAvailable = ['open', 'ongoing'].includes(category.status) &&
                                category.current_participants < category.max_participants;
                            const statusText = category.status === 'closed' ? ' (Đóng)' :
                                category.current_participants >= category.max_participants ? ' (Hết chỗ)' : '';
                            const ageGroup = category.age_group && category.age_group !== 'open' ?
                                ` (${category.age_group})` : '';

                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent =
                                `${category.category_name}${ageGroup} - ${category.current_participants}/${category.max_participants}${statusText}`;
                            option.disabled = !isAvailable;
                            categorySelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = '-- Không có nội dung nào --';
                        option.disabled = true;
                        categorySelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = '-- Lỗi khi tải dữ liệu --';
                    option.disabled = true;
                    categorySelect.appendChild(option);
                });

            const modal = document.getElementById('registerModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeRegisterModal() {
            const modal = document.getElementById('registerModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('registerForm').reset();
            document.getElementById('athlete_name_error').style.display = 'none';
            document.getElementById('email_error').style.display = 'none';
            document.getElementById('phone_error').style.display = 'none';
            document.getElementById('category_id_error').style.display = 'none';
        }

        function submitRegisterForm() {
            const form = document.getElementById('registerForm');

            // Clear previous errors
            document.getElementById('athlete_name_error').style.display = 'none';
            document.getElementById('email_error').style.display = 'none';
            document.getElementById('phone_error').style.display = 'none';
            document.getElementById('category_id_error').style.display = 'none';

            const athleteNameEl = document.getElementById('athlete_name');
            const emailEl = document.getElementById('email');
            const phoneEl = document.getElementById('phone');
            const categoryEl = document.getElementById('category_id');
            const tournamentIdEl = document.getElementById('tournament_id');

            const athleteName = athleteNameEl.value.trim();
            const email = emailEl.value.trim();
            const phone = phoneEl.value.trim();
            const categoryId = categoryEl.value.trim();
            const tournamentId = tournamentIdEl.value;

            let hasError = false;

            if (!athleteName) {
                document.getElementById('athlete_name_error').textContent = 'Vui lòng nhập tên vận động viên';
                document.getElementById('athlete_name_error').style.display = 'block';
                hasError = true;
            }
            if (!email) {
                document.getElementById('email_error').textContent = 'Vui lòng nhập email';
                document.getElementById('email_error').style.display = 'block';
                hasError = true;
            }
            if (!phone) {
                document.getElementById('phone_error').textContent = 'Vui lòng nhập số điện thoại';
                document.getElementById('phone_error').style.display = 'block';
                hasError = true;
            }
            if (!categoryId) {
                document.getElementById('category_id_error').textContent = 'Vui lòng chọn nội dung thi đấu';
                document.getElementById('category_id_error').style.display = 'block';
                hasError = true;
            }

            if (hasError) {
                return;
            }

            const btn = document.getElementById('submitRegisterBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div>Đang xử lý...';

            const payload = {
                athlete_name: athleteName,
                email: email,
                phone: phone,
                category_id: categoryId,
                tournament_id: tournamentId
            };

            fetch('/tournament/' + tournamentId + '/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json().then(data => ({
                    status: response.status,
                    data
                })))
                .then(({
                    status,
                    data
                }) => {
                    if (data && data.success) {
                        alert('Đăng ký thành công! Vui lòng chờ xác nhận từ ban tổ chức.');
                        form.reset();

                        // Find and update all register buttons for this tournament
                        const tournamentCards = document.querySelectorAll('[data-tournament-id="' + tournamentId +
                        '"]');
                        tournamentCards.forEach(card => {
                            const registerBtn = card.querySelector('.tournament-register-btn');
                            if (registerBtn) {
                                registerBtn.textContent = 'Chờ xét duyệt';
                                registerBtn.className = 'btn btn-secondary btn-sm';
                                registerBtn.disabled = true;
                                registerBtn.style.opacity = '0.6';
                                registerBtn.style.cursor = 'not-allowed';
                                registerBtn.removeAttribute('onclick');
                            }
                        });

                        closeRegisterModal();
                    } else {
                        const errorMsg = data?.message || 'Đã xảy ra lỗi. Vui lòng thử lại.';
                        alert(errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi. Vui lòng thử lại.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Đăng ký';
                });
        }

        // Close detail modal when clicking outside
        document.getElementById('detailModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeDetailModal();
            }
        });

        // Close register modal when clicking outside
        document.getElementById('registerModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeRegisterModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const detailModal = document.getElementById('detailModal');
                const registerModal = document.getElementById('registerModal');
                if (detailModal.style.display === 'flex') {
                    closeDetailModal();
                } else if (registerModal.style.display === 'flex') {
                    closeRegisterModal();
                }
            }
        });

        // Toggle filter body on arrow button click
        document.addEventListener('DOMContentLoaded', function() {
            const arrowToggle = document.querySelector('.btn-arrow-toggle');
            const filterBody = document.querySelector('.filter-body');

            if (arrowToggle && filterBody) {
                arrowToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Toggle the filter body visibility
                    filterBody.classList.toggle('hidden');

                    // Toggle arrow direction (if you want visual feedback)
                    const arrowUp = arrowToggle.querySelector('.arrow-up');
                    const arrowDown = arrowToggle.querySelector('.arrow-down');

                    arrowUp.classList.toggle('d-none');
                        arrowDown.classList.toggle('d-none');

                    // Smooth scroll to top of filter card on mobile
                    if (window.innerWidth <= 1024) {
                        const filterCard = arrowToggle.closest('.filter-card');
                        if (filterCard) {
                            filterCard.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            }
        });
    </script>
@endsection
