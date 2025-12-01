@extends('layouts.front')

@section('css')
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
                           <div class="tournament-item" data-tournament-id="{{ $tournament->id }}">
                               <a href="{{ route('tournaments-detail', $tournament->id) }}" class="tournament-link">
                                    <div class="tournament-image">
                                         @php
                                             $media = $tournament->getFirstMedia('banner');
                                             $imageUrl = $media ? $media->getUrl() : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 250%22%3E%3Cdefs%3E%3ClinearGradient id=%22t' . $tournament->id . '%22 x1=%220%25%22 y1=%220%25%22 x2=%22100%25%22 y2=%22100%25%22%3E%3Cstop offset=%220%25%22 style=%22stop-color:%2300D9B5;stop-opacity:1%22 /%3E%3Cstop offset=%22100%25%22 style=%22stop-color:%230099CC;stop-opacity:1%22 /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill=%22url(%23t' . $tournament->id . ')%22 width=%22400%22 height=%22250%22/%3E%3Ctext x=%22200%22 y=%22125%22 font-family=%22Arial%22 font-size=%2224%22 fill=%22white%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 font-weight=%22bold%22%3E' . strtoupper(substr($tournament->name, 0, 20)) . '%3C/text%3E%3C/svg%3E';
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
                                                <span>{{ $tournament->athletes()->count() }} VĐV</span>
                                            </div>
                                        </div>

                                        <div class="tournament-footer">
                                             <div class="tournament-prize">
                                                 <span class="prize-label">Giải thưởng</span>
                                                 <span class="prize-amount">{{ $tournament->prizes ? number_format($tournament->prizes, 0, '.', ',') . ' VNĐ' : 'N/A' }}</span>
                                             </div>
                                             @if(!$tournament->user_registered)
                                                 <button class="btn btn-primary btn-sm tournament-register-btn" onclick="event.preventDefault(); openRegisterModal({{ $tournament->id }}, '{{ $tournament->name }}');">
                                                     Đăng ký ngay
                                                 </button>
                                             @else
                                                 <button class="btn btn-secondary btn-sm tournament-register-btn" disabled style="opacity: 0.6; cursor: not-allowed;">
                                                     Chờ xét duyệt
                                                 </button>
                                             @endif
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

    <!-- Registration Modal -->
    <div id="registerModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 2000; display: none; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
        <div style="background: white; border-radius: 20px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <!-- Modal Header with Gradient -->
            <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 30px 30px 40px; color: white; position: relative;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Đăng ký tham gia</h2>
                <p id="modalTournamentName" style="margin: 8px 0 0 0; opacity: 0.9; font-size: 0.95rem;"></p>
                <button onclick="closeRegisterModal()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.5rem; cursor: pointer; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
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
                        <label for="athlete_name" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Tên vận động viên <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="athlete_name" name="athlete_name" required 
                            value="{{ auth()->check() ? auth()->user()->name : '' }}"
                            placeholder="Nhập tên của bạn"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        <div id="athlete_name_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
                    </div>

                    <!-- Email Field -->
                    <div style="margin-bottom: 25px;">
                        <label for="email" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Email <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="email" id="email" name="email" required 
                            value="{{ auth()->check() ? auth()->user()->email : '' }}"
                            placeholder="Nhập email của bạn"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        <div id="email_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
                    </div>

                    <!-- Phone Field -->
                    <div style="margin-bottom: 30px;">
                        <label for="phone" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Số điện thoại <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" required 
                            value="{{ auth()->check() ? auth()->user()->phone : '' }}"
                            placeholder="Nhập số điện thoại của bạn"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        <div id="phone_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
                    </div>

                    <!-- Category Selection Field -->
                    <div style="margin-bottom: 30px;">
                        <label for="category_id" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Nội dung thi đấu <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="category_id" name="category_id" required 
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box; appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22%3E%3cpolyline points=%226 9 12 15 18 9%22%3E%3c/polyline%3E%3c/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 20px; padding-right: 40px;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                            <option value="">-- Chọn nội dung thi đấu --</option>
                        </select>
                        <div id="category_id_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
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
            border: 2px solid rgba(255,255,255,0.3);
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
                        const ageGroup = category.age_group && category.age_group !== 'open' ? ` (${category.age_group})` : '';
                        
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = `${category.category_name}${ageGroup} - ${category.current_participants}/${category.max_participants}${statusText}`;
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
            .then(response => response.json().then(data => ({status: response.status, data})))
            .then(({status, data}) => {
                if (data && data.success) {
                    alert('Đăng ký thành công! Vui lòng chờ xác nhận từ ban tổ chức.');
                    form.reset();
                    
                    // Find and update all register buttons for this tournament
                    const tournamentCards = document.querySelectorAll('[data-tournament-id="' + tournamentId + '"]');
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

        // Close modal when clicking outside
        document.getElementById('registerModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeRegisterModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeRegisterModal();
            }
        });
    </script>
@endsection
