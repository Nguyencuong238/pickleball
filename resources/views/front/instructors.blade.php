@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles-coaches.css') }}">
    <style>
        .pagination-btn{
            padding: 15px;
        }

        /* Filter Section Styles */
        .filter-wrapper {
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-search-container {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .filter-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: var(--text-light);
        }

        .search-input {
            padding-left: 44px;
            width: 100%;
            height: 60px;
            box-sizing: border-box;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 0;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .filter-select {
            height: 60px;
            box-sizing: border-box;
        }
    </style>
@endsection

@section('content')
<section class="page-header">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="page-header-content">
                <span class="section-badge">Giảng Viên</span>
                <h1 class="page-title">Tìm <span class="gradient-text">Giảng Viên</span> Pickleball</h1>
                <p class="page-description">Kết nối với các huấn luyện viên chuyên nghiệp, nhiều năm kinh nghiệm, sẵn sàng đồng hành cùng bạn trên hành trình chinh phục Pickleball</p>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="coaches-filter">
        <div class="container">
            <div class="filter-wrapper">
                <div class="filter-search-container">
                    <svg class="filter-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" class="search-input" placeholder="Tìm kiếm giảng viên...">
                </div>

                <div class="filter-group">
                    <label class="filter-label">Thành phố</label>
                    <select class="filter-select">
                        <option value="">Tất cả</option>
                        @foreach(\App\Models\Province::all() as $province)
                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Kinh nghiệm</label>
                    <select class="filter-select">
                        <option value="">Tất cả</option>
                        <option value="1-3">1-3 năm</option>
                        <option value="3-5">3-5 năm</option>
                        <option value="5+">Trên 5 năm</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Sắp xếp</label>
                    <select class="filter-select">
                        <option value="rating">Đánh giá cao nhất</option>
                        <option value="experience">Nhiều kinh nghiệm</option>
                        <option value="newest">Mới nhất</option>
                    </select>
                </div>
            </div>

            <!-- Quick Filter Tags -->
            {{-- <div class="filter-tags">
                <button class="filter-tag active">Tất cả</button>
                <button class="filter-tag">⭐ Được đánh giá cao</button>
                <button class="filter-tag">🏆 Có chứng chỉ</button>
                <button class="filter-tag">👨‍👩‍👧‍👦 Dạy nhóm</button>
                <button class="filter-tag">🎯 Dạy 1-1</button>
                <button class="filter-tag">🌟 Giảng viên mới</button>
            </div> --}}
        </div>
    </section>

    <!-- Coaches Grid -->
    <section class="coaches-section section">
        <div class="container">
            <!-- Results Info -->
            <div class="results-info">
                <p class="results-count">Tìm thấy <strong>{{ $instructors->total() }}</strong> giảng viên</p>
                {{-- <div class="view-toggle">
                    <button class="view-btn active" data-view="grid" title="Xem dạng lưới">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                        </svg>
                    </button>
                    <button class="view-btn" data-view="list" title="Xem dạng danh sách">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="4" width="18" height="4"/>
                            <rect x="3" y="10" width="18" height="4"/>
                            <rect x="3" y="16" width="18" height="4"/>
                        </svg>
                    </button>
                </div> --}}
            </div>

            <!-- Coaches Grid -->
            <div class="coaches-grid">
                @forelse($instructors as $instructor)
                <!-- Coach Card -->
                <div class="coach-card">
                    <div class="coach-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='g1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g1)' width='300' height='300'/%3E%3Ccircle cx='150' cy='120' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='150' cy='250' rx='80' ry='60' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E" alt="Nguyễn Văn Hùng">
                        <span class="coach-badge verified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                            </svg>
                            Đã xác minh
                        </span>
                        <div class="coach-rating-badge">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>4.9</span>
                        </div>
                    </div>
                    <div class="coach-content">
                        <h3 class="coach-name">
                            <a href="{{ route('instructors.detail', $instructor->id) }}">{{ $instructor->name }}</a>
                        </h3>
                        <div class="coach-experience">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            <span>{{ $instructor->experience }} năm kinh nghiệm</span>
                        </div>
                        <div class="coach-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>{{ $instructor->ward }}, {{ $instructor->province->name ?? 'N/A' }}</span>
                        </div>
                        <div class="coach-tags">
                            <span class="tag">Dạy 1-1</span>
                            <span class="tag">Dạy nhóm</span>
                            <span class="tag">Nâng cao</span>
                        </div>
                        <div class="coach-stats">
                            <div class="stat">
                                <strong>156</strong>
                                <span>Học viên</span>
                            </div>
                            <div class="stat">
                                <strong>89</strong>
                                <span>Đánh giá</span>
                            </div>
                        </div>
                        <div class="coach-actions">
                            <a href="{{ route('instructors.detail', $instructor->id) }}" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            <button class="btn btn-outline btn-sm btn-favorite" title="Yêu thích">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    </div>
                    @empty
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                    <p style="font-size: 18px; color: #1e293b;">Chưa có giảng viên nào</p>
                    </div>
                    @endforelse
            </div>

            <!-- Pagination -->
            <div class="pagination">
                @if($instructors->onFirstPage())
                    <span class="pagination-btn disabled">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $instructors->previousPageUrl() }}" class="pagination-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </a>
                @endif

                @foreach($instructors->getUrlRange(1, $instructors->lastPage()) as $page => $url)
                    @if($page == $instructors->currentPage())
                        <a href="{{ $url }}" class="pagination-btn active">{{ $page }}</a>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach

                @if($instructors->hasMorePages())
                    <a href="{{ $instructors->nextPageUrl() }}" class="pagination-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                @else
                    <span class="pagination-btn disabled">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </section>

    <!-- Become a Coach CTA -->
    <section class="become-coach-cta">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2 class="cta-title">Bạn là huấn luyện viên Pickleball?</h2>
                    <p class="cta-description">Đăng ký trở thành giảng viên trên onePickleball.vn và kết nối với hàng ngàn học viên tiềm năng trên toàn quốc.</p>
                    <div class="cta-features">
                        <div class="cta-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span>Miễn phí đăng ký</span>
                        </div>
                        <div class="cta-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <span>Tiếp cận học viên</span>
                        </div>
                        <div class="cta-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            <span>Thu nhập linh hoạt</span>
                        </div>
                    </div>
                    <button class="btn btn-white btn-lg">Đăng ký ngay</button>
                </div>
                <div class="cta-image">
                    <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="ctaGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:rgba(255,255,255,0.2)"/>
                                <stop offset="100%" style="stop-color:rgba(255,255,255,0.05)"/>
                            </linearGradient>
                        </defs>
                        <circle cx="200" cy="150" r="120" fill="url(#ctaGrad)"/>
                        <circle cx="200" cy="150" r="80" fill="rgba(255,255,255,0.15)"/>
                        <path d="M160 150 L240 150 M200 110 L200 190" stroke="rgba(255,255,255,0.5)" stroke-width="8" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-input');
    const citySelect = document.querySelectorAll('.filter-select')[0];
    const experienceSelect = document.querySelectorAll('.filter-select')[1];
    const sortSelect = document.querySelectorAll('.filter-select')[2];
    const filterTags = document.querySelectorAll('.filter-tag');
    const coachesGrid = document.querySelector('.coaches-grid');

    // Get current URL params
    const urlParams = new URLSearchParams(window.location.search);
    
    // Initialize filters from URL
    if (urlParams.has('search')) {
        searchInput.value = urlParams.get('search');
    }
    if (urlParams.has('city')) {
        citySelect.value = urlParams.get('city');
    }
    if (urlParams.has('experience')) {
        experienceSelect.value = urlParams.get('experience');
    }
    if (urlParams.has('sort')) {
        sortSelect.value = urlParams.get('sort');
    }

    // Apply filters function
    function applyFilters() {
        const params = new URLSearchParams();
        
        const search = searchInput.value.trim();
        const city = citySelect.value;
        const experience = experienceSelect.value;
        const sort = sortSelect.value;

        if (search) params.append('search', search);
        if (city) params.append('city', city);
        if (experience) params.append('experience', experience);
        if (sort) params.append('sort', sort);

        // Redirect with new params
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.location.href = newUrl;
    }

    // Add event listeners
    searchInput.addEventListener('input', function() {
        clearTimeout(window.filterTimeout);
        window.filterTimeout = setTimeout(applyFilters, 500);
    });

    citySelect.addEventListener('change', applyFilters);
    experienceSelect.addEventListener('change', applyFilters);
    sortSelect.addEventListener('change', applyFilters);

    // Filter tag functionality
    filterTags.forEach(tag => {
        tag.addEventListener('click', function() {
            filterTags.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            // You can add more logic here for tag-based filtering
        });
    });
});
</script>
 <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        mobileMenuToggle?.addEventListener('click', () => {
            navMenu?.classList.toggle('active');
        });

        // Filter tags toggle
        const filterTags = document.querySelectorAll('.filter-tag');
        filterTags.forEach(tag => {
            tag.addEventListener('click', () => {
                filterTags.forEach(t => t.classList.remove('active'));
                tag.classList.add('active');
            });
        });

        // View toggle
        const viewBtns = document.querySelectorAll('.view-btn');
        const coachesGrid = document.querySelector('.coaches-grid');
        
        viewBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                viewBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                if (btn.dataset.view === 'list') {
                    coachesGrid?.classList.add('list-view');
                } else {
                    coachesGrid?.classList.remove('list-view');
                }
            });
        });

        // Favorite button toggle
        const favoriteBtns = document.querySelectorAll('.btn-favorite');
        favoriteBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                btn.classList.toggle('active');
            });
        });
    </script>
@endsection
