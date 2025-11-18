@extends('layouts.front')

@section('css')
@endsection

@section('content')
<section class="page-header">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="breadcrumb">
                <a href="index.html">Trang chủ</a>
                <span class="separator">/</span>
                <span>Giải đấu</span>
            </div>
            <h1 class="page-title">Giải Đấu Pickleball</h1>
            <p class="page-description">Tìm và đăng ký tham gia các giải đấu Pickleball chuyên nghiệp và phong trào trên toàn quốc</p>
            
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
                                    <line x1="4" y1="21" x2="4" y2="14"/>
                                    <line x1="4" y1="10" x2="4" y2="3"/>
                                    <line x1="12" y1="21" x2="12" y2="12"/>
                                    <line x1="12" y1="8" x2="12" y2="3"/>
                                    <line x1="20" y1="21" x2="20" y2="16"/>
                                    <line x1="20" y1="12" x2="20" y2="3"/>
                                    <line x1="1" y1="14" x2="7" y2="14"/>
                                    <line x1="9" y1="8" x2="15" y2="8"/>
                                    <line x1="17" y1="16" x2="23" y2="16"/>
                                </svg>
                                Bộ lọc
                            </h3>
                            <a href="{{ route('tournaments') }}" class="filter-reset">Xóa bộ lọc</a>
                        </div>

                        <!-- Search -->
                        <div class="filter-group">
                            <label class="filter-label">Tìm kiếm</label>
                            <div class="search-input-wrapper">
                                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="m21 21-4.35-4.35"/>
                                </svg>
                                <input type="text" name="search" class="filter-search" placeholder="Tên giải đấu..." value="{{ $filters['search'] ?? '' }}">
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Trạng thái</label>
                            <div class="filter-options">
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="statuses[]" value="open" @if(in_array('open', $filters['statuses'] ?? [])) checked @endif>
                                    <span class="checkbox-custom"></span>
                                    <span>Đang mở đăng ký</span>
                                    <span class="filter-count">({{ $statusOpen }})</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="statuses[]" value="coming_soon" @if(in_array('coming_soon', $filters['statuses'] ?? [])) checked @endif>
                                    <span class="checkbox-custom"></span>
                                    <span>Sắp mở</span>
                                    <span class="filter-count">({{ $statusComingSoon }})</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="statuses[]" value="ongoing" @if(in_array('ongoing', $filters['statuses'] ?? [])) checked @endif>
                                    <span class="checkbox-custom"></span>
                                    <span>Đang diễn ra</span>
                                    <span class="filter-count">({{ $statusOngoing }})</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="statuses[]" value="ended" @if(in_array('ended', $filters['statuses'] ?? [])) checked @endif>
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
                                @foreach($locations as $location)
                                    <option value="{{ $location }}" @if($filters['location'] === $location) selected @endif>{{ $location }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Level Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Trình độ</label>
                            <div class="filter-options">
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="ranks[]" value="beginner" @if(in_array('beginner', $filters['ranks'] ?? [])) checked @endif>
                                    <span class="checkbox-custom"></span>
                                    <span>Beginner</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="ranks[]" value="intermediate" @if(in_array('intermediate', $filters['ranks'] ?? [])) checked @endif>
                                    <span class="checkbox-custom"></span>
                                    <span>Intermediate</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="ranks[]" value="advanced" @if(in_array('advanced', $filters['ranks'] ?? [])) checked @endif>
                                    <span class="checkbox-custom"></span>
                                    <span>Advanced</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" name="ranks[]" value="professional" @if(in_array('professional', $filters['ranks'] ?? [])) checked @endif>
                                    <span class="checkbox-custom"></span>
                                    <span>Professional</span>
                                </label>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="filter-group">
                            <label class="filter-label">Thời gian</label>
                            <div class="date-range" style="display: block">
                                <input type="date" name="start_date" class="filter-date" value="{{ $filters['start_date'] ?? '' }}">
                                <input type="date" name="end_date" class="filter-date" value="{{ $filters['end_date'] ?? '' }}">
                            </div>
                        </div>

                        <!-- Prize Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Giải thưởng</label>
                            <div class="filter-options">
                                <label class="filter-radio">
                                    <input type="radio" name="prize_range" value="" @if(!($filters['prize_range'] ?? '')) checked @endif>
                                    <span class="radio-custom"></span>
                                    <span>Tất cả</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="prize_range" value="low" @if(($filters['prize_range'] ?? '') === 'low') checked @endif>
                                    <span class="radio-custom"></span>
                                    <span>Dưới 100 triệu</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="prize_range" value="mid" @if(($filters['prize_range'] ?? '') === 'mid') checked @endif>
                                    <span class="radio-custom"></span>
                                    <span>100 - 300 triệu</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="prize_range" value="high" @if(($filters['prize_range'] ?? '') === 'high') checked @endif>
                                    <span class="radio-custom"></span>
                                    <span>Trên 300 triệu</span>
                                </label>
                            </div>
                        </div>

                        <!-- Apply Filters Button -->
                        <button type="submit" class="btn btn-primary btn-block filter-apply">
                            Áp dụng bộ lọc
                        </button>
                    </form>

                    <!-- Featured Banner -->
                    <div class="sidebar-banner">
                        <div class="banner-content">
                            <span class="banner-badge">🎉 Đặc biệt</span>
                            <h4 class="banner-title">Vietnam National Championship</h4>
                            <p class="banner-text">Giải vô địch quốc gia - Đăng ký sớm để nhận ưu đãi!</p>
                            <a href="tournament-detail.html" class="btn btn-white btn-sm">Xem chi tiết</a>
                        </div>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <div class="tournaments-main">
                    <!-- Toolbar -->
                    <div class="tournaments-toolbar">
                        <div class="toolbar-left">
                            <h2 class="toolbar-title">Tìm thấy <span class="result-count">{{ $tournaments->total() }}</span> giải đấu</h2>
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
                            <div class="tournament-item">
                                <a href="{{ route('tournaments-detail', $tournament->id) }}" class="tournament-link">
                                    <div class="tournament-image">
                                        @if($tournament->image)
                                            <img src="{{ asset('storage/' . $tournament->image) }}" alt="{{ $tournament->name }}">
                                        @else
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='t{{ $tournament->id }}' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23t{{ $tournament->id }})' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle' font-weight='bold'%3E{{ strtoupper(substr($tournament->name, 0, 20)) }}%3C/text%3E%3C/svg%3E" alt="{{ $tournament->name }}">
                                        @endif
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
                                                <span class="date-month">{{ $tournament->start_date->format('M Y') }}</span>
                                            </div>
                                        </div>
                                        <h3 class="tournament-title">{{ $tournament->name }}</h3>
                                        <p class="tournament-excerpt">{{ substr($tournament->description, 0, 80) }}...</p>
                                        
                                        <div class="tournament-meta">
                                            <div class="meta-item">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                    <circle cx="12" cy="10" r="3"/>
                                                </svg>
                                                <span>{{ $tournament->location ?? 'N/A' }}</span>
                                            </div>
                                            <div class="meta-item">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                    <circle cx="9" cy="7" r="4"/>
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                                </svg>
                                                <span>{{ $tournament->athleteCount() }} VĐV</span>
                                            </div>
                                        </div>

                                        <div class="tournament-footer">
                                            <div class="tournament-prize">
                                                <span class="prize-label">Giải thưởng</span>
                                                <span class="prize-amount">{{ $tournament->prizes ? number_format($tournament->prizes, 0, '.', ',') . ' VNĐ' : 'N/A' }}</span>
                                            </div>
                                            <button class="btn btn-primary btn-sm" onclick="event.preventDefault(); alert('Đăng ký giải đấu!');">
                                                Đăng ký ngay
                                            </button>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div style="grid-column: 1/-1; padding: 3rem; text-align: center; color: var(--text-secondary);">
                                <p>Không có giải đấu nào</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        @if($tournaments->onFirstPage())
                            <button class="pagination-btn pagination-prev" disabled>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="15 18 9 12 15 6"/>
                                </svg>
                                Trước
                            </button>
                        @else
                            <a href="{{ $tournaments->previousPageUrl() . '&' . http_build_query(array_filter($filters)) }}" class="pagination-btn pagination-prev">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="15 18 9 12 15 6"/>
                                </svg>
                                Trước
                            </a>
                        @endif
                        
                        <div class="pagination-numbers">
                            @for($i = 1; $i <= $tournaments->lastPage(); $i++)
                                @if($i == $tournaments->currentPage())
                                    <button class="pagination-number active">{{ $i }}</button>
                                @elseif($i <= 3 || $i > $tournaments->lastPage() - 2)
                                    <a href="{{ $tournaments->url($i) . '&' . http_build_query(array_filter($filters)) }}" class="pagination-number">{{ $i }}</a>
                                @elseif($i == 4 && $tournaments->lastPage() > 6)
                                    <span class="pagination-dots">...</span>
                                @endif
                            @endfor
                        </div>
                        
                        @if($tournaments->hasMorePages())
                            <a href="{{ $tournaments->nextPageUrl() . '&' . http_build_query(array_filter($filters)) }}" class="pagination-btn pagination-next">
                                Sau
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </a>
                        @else
                            <button class="pagination-btn pagination-next" disabled>
                                Sau
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </button>
                        @endif
                    </div>
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
@endsection

@section('js')
    <script src="{{ asset('assets/js/tournaments.js') }}"></script>
@endsection
