@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles-courses.css') }}">
    <style>
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 0;
        }
        
        .stat-number {
            background: linear-gradient(135deg, #ff6b6b, #ff8787) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
            font-weight: 700 !important;
            font-size: 2.5rem !important;
        }
    </style>
@endsection

@section('content')
    <section class="page-header">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="page-header-content">
                <span class="section-badge">Video Khóa Học</span>
                <h1 class="page-title">Học <span class="gradient-text">Pickleball</span> Qua Video</h1>
                <p class="page-description">Khám phá thư viện video hướng dẫn từ cơ bản đến nâng cao, được biên soạn bởi các
                    huấn luyện viên chuyên nghiệp</p>
            </div>
        </div>
    </section>

    <!-- Stats Banner -->
    <section class="stats-banner">
        <div class="container">
            <div class="stats-wrapper">
                <div class="stat-item">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="23 7 16 12 23 17 23 7" />
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">{{ $stats['totalVideos'] }}+</span>
                        <span class="stat-label">Video bài giảng</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">{{ $stats['totalHours'] }}+</span>
                        <span class="stat-label">Giờ nội dung</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">{{ number_format($stats['totalUsers']) }}+</span>
                        <span class="stat-label">Học viên</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">{{ $stats['averageRating'] }}</span>
                        <span class="stat-label">Đánh giá trung bình</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="courses-filter">
        <div class="container">
            <form action="{{ route('course') }}" method="GET" id="filter-form">
                <div class="filter-wrapper">
                    <!-- Search -->
                    <div class="filter-search-box">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="M21 21l-4.35-4.35" />
                        </svg>
                        <input type="text" class="search-input" name="search" placeholder="Tìm kiếm video khóa học..." value="{{ $filters['search'] ?? '' }}">
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-group">
                        <label class="filter-label">Danh mục</label>
                        <select class="filter-select" name="category">
                            <option value="">Tất cả</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $filters['category'] == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Level Filter -->
                    <div class="filter-group">
                        <label class="filter-label">Trình độ</label>
                        <select class="filter-select" name="level">
                            <option value="">Tất cả</option>
                            @forelse($levels as $level)
                                <option value="{{ $level }}" {{ $filters['level'] == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @empty
                                <option value="">Không có dữ liệu</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Duration Filter -->
                    <div class="filter-group">
                        <label class="filter-label">Thời lượng</label>
                        <select class="filter-select" name="duration">
                            <option value="">Tất cả</option>
                            <option value="short" {{ $filters['duration'] == 'short' ? 'selected' : '' }}>Dưới 10 phút</option>
                            <option value="medium" {{ $filters['duration'] == 'medium' ? 'selected' : '' }}>10-30 phút</option>
                            <option value="long" {{ $filters['duration'] == 'long' ? 'selected' : '' }}>Trên 30 phút</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="filter-group">
                        <label class="filter-label">Sắp xếp</label>
                        <select class="filter-select" name="sort">
                            <option value="newest" {{ $filters['sort'] == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="popular" {{ $filters['sort'] == 'popular' ? 'selected' : '' }}>Phổ biến nhất</option>
                            <option value="rating" {{ $filters['sort'] == 'rating' ? 'selected' : '' }}>Đánh giá cao</option>
                            <option value="views" {{ $filters['sort'] == 'views' ? 'selected' : '' }}>Lượt xem</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Category Tabs -->
            {{-- <div class="category-tabs">
                <button class="category-tab active" data-category="all">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg>
                    Tất cả
                </button>
                <button class="category-tab" data-category="basic">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4M12 8h.01" />
                    </svg>
                    Cơ bản
                </button>
                <button class="category-tab" data-category="technique">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                    </svg>
                    Kỹ thuật
                </button>
                <button class="category-tab" data-category="strategy">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 2 7 12 12 22 7 12 2" />
                        <polyline points="2 17 12 22 22 17" />
                        <polyline points="2 12 12 17 22 12" />
                    </svg>
                    Chiến thuật
                </button>
                <button class="category-tab" data-category="serve">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v8M8 12h8" />
                    </svg>
                    Giao bóng
                </button>
                <button class="category-tab" data-category="doubles">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    Doubles
                </button>
                <button class="category-tab" data-category="fitness">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                    Thể lực
                </button>
            </div> --}}
        </div>
    </section>

    <!-- Featured Courses -->
    <section class="featured-courses section">
        <div class="container">
            <div class="section-header-inline">
                <h2 class="section-title-sm">🔥 Video nổi bật</h2>
            </div>

            <div class="featured-grid">
                @forelse($videos->take(3) as $index => $video)
                    @if ($index === 0)
                        <!-- Featured Video 1 -->
                        <div class="featured-video-card">
                            <div class="video-thumbnail large">
                                <img src="{{ $video->image ? asset('storage/' . $video->image) : 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 640 360%27%3E%3Crect fill=%27%23ccc%27 width=%27640%27 height=%27360%27/%3E%3C/svg%3E' }}"
                                    alt="{{ $video->name }}">
                                <div class="play-button">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <polygon points="5 3 19 12 5 21 5 3" />
                                    </svg>
                                </div>
                                <span class="video-duration">{{ $video->duration ?? '0:00' }}</span>
                                <span class="video-badge featured">Nổi bật</span>
                            </div>
                            <div class="video-info">
                                <span class="video-category">{{ $video->category->name ?? 'Chưa phân loại' }}</span>
                                <h3 class="video-title">
                                    <a href="{{ route('course.detail', $video->id) }}">{{ $video->name }}</a>
                                </h3>
                                <p class="video-description">{{ $video->description ?? '' }}</p>
                                <div class="video-meta">
                                    <div class="instructor">
                                        <img src="{{ $video->instructor && $video->instructor->image ? asset('storage/' . $video->instructor->image) : 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 40 40%27%3E%3Ccircle cx=%2720%27 cy=%2720%27 r=%2720%27 fill=%27%23ccc%27/%3E%3C/svg%3E' }}"
                                            alt="{{ $video->instructor->name ?? 'Coach' }}">
                                        <span>{{ $video->instructor->name ?? 'Coach' }}</span>
                                    </div>
                                    <div class="video-stats">
                                        <span class="views">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            {{ $video->views_count ? number_format($video->views_count) : '0' }}
                                        </span>
                                        <span class="rating">
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <polygon
                                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                            </svg>
                                            {{ $video->rating ?? '0' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Featured Video {{ $index + 1 }} -->
                        <div class="featured-video-card horizontal">
                            <div class="video-thumbnail">
                                <img src="{{ $video->image ? asset('storage/' . $video->image) : 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 320 180%27%3E%3Crect fill=%27%23ccc%27 width=%27320%27 height=%27180%27/%3E%3C/svg%3E' }}"
                                    alt="{{ $video->name }}">
                                <div class="play-button small">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <polygon points="5 3 19 12 5 21 5 3" />
                                    </svg>
                                </div>
                                <span class="video-duration">{{ $video->duration ?? '0:00' }}</span>
                            </div>
                            <div class="video-info">
                                <span class="video-category">{{ $video->category->name ?? 'Chưa phân loại' }}</span>
                                <h3 class="video-title">
                                    <a href="{{ route('course.detail', $video->id) }}">{{ $video->name }}</a>
                                </h3>
                                <div class="video-stats">
                                    <span
                                        class="views">{{ $video->views_count ? number_format($video->views_count) : '0' }}
                                        lượt xem</span>
                                    <span class="rating">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <polygon
                                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                        {{ $video->rating ?? '0' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <p>Không có video nổi bật nào</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- All Courses Grid -->
    <section class="courses-section section section-alt">
        <div class="container">
            <!-- Results Info -->
            <div class="results-info">
                <p class="results-count">Hiển thị <strong>{{ $videos->total() }}</strong> video khóa học</p>
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid" title="Xem dạng lưới">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                        </svg>
                    </button>
                    <button class="view-btn" data-view="list" title="Xem dạng danh sách">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="4" width="18" height="4" />
                            <rect x="3" y="10" width="18" height="4" />
                            <rect x="3" y="16" width="18" height="4" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Courses Grid -->
            <div class="courses-grid">
                @forelse($videos as $video)
                    <!-- Course Card -->
                    <div class="course-card">
                        <div class="video-thumbnail">
                            <img src="{{ $video->image ? asset('storage/' . $video->image) : 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 320 180%27%3E%3Crect fill=%27%23ccc%27 width=%27320%27 height=%27180%27/%3E%3C/svg%3E' }}"
                                alt="{{ $video->name }}">
                            <div class="play-button small">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <polygon points="5 3 19 12 5 21 5 3" />
                                </svg>
                            </div>
                            <span class="video-duration">{{ $video->duration ?? '0:00' }}</span>
                            <button class="bookmark-btn" title="Lưu video">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
                                </svg>
                            </button>
                        </div>
                        <div class="course-content">
                            <div class="course-meta">
                                <span class="course-category">{{ $video->category->name ?? 'Chưa phân loại' }}</span>
                                <span
                                    class="course-level {{ strtolower($video->level ?? 'beginner') }}">{{ ucfirst($video->level ?? 'Người mới') }}</span>
                            </div>
                            <h3 class="course-title">
                                <a href="{{ route('course.detail', $video->id) }}">{{ $video->name }}</a>
                            </h3>
                            <p class="course-excerpt">{{ $video->description ?? '' }}</p>
                            <div class="course-footer">
                                <div class="course-instructor">
                                    <img src="{{ $video->instructor && $video->instructor->image ? asset('storage/' . $video->instructor->image) : 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 32 32%27%3E%3Ccircle cx=%2716%27 cy=%2716%27 r=%2716%27 fill=%27%23ccc%27/%3E%3C/svg%3E' }}"
                                        alt="{{ $video->instructor->name ?? 'Coach' }}">
                                    <span>{{ $video->instructor->name ?? 'Coach' }}</span>
                                </div>
                                <div class="course-stats">
                                    <span
                                        class="views">{{ $video->views_count ? number_format($video->views_count) : '0' }}</span>
                                    <span class="rating">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <polygon
                                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                        {{ $video->rating ?? '0' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p>Không có video nào để hiển thị</p>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($videos->hasPages())
                <div style="text-align: center; margin-top: 40px;">
                    {{ $videos->links('pagination.custom') }}
                </div>
            @endif
        </div>
    </section>

    <!-- CTA Section -->
    <section class="courses-cta">
        <div class="container">
            <div class="cta-card gradient">
                <div class="cta-content">
                    <h2 class="cta-title">Muốn học có hệ thống hơn?</h2>
                    <p class="cta-description">Đăng ký gói Premium để truy cập toàn bộ video khóa học, nhận chứng chỉ và
                        được hỗ trợ trực tiếp từ giảng viên.</p>
                    <div class="cta-features">
                        <div class="cta-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            <span>150+ video độc quyền</span>
                        </div>
                        <div class="cta-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            <span>Chứng chỉ hoàn thành</span>
                        </div>
                        <div class="cta-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            <span>Hỗ trợ 1-1</span>
                        </div>
                    </div>
                    <button class="btn btn-white btn-lg">Xem gói Premium</button>
                </div>
                <div class="cta-image">
                    <svg viewBox="0 0 300 200" xmlns="http://www.w3.org/2000/svg">
                        <rect x="30" y="20" width="240" height="135" rx="10"
                            fill="rgba(255,255,255,0.15)" />
                        <polygon points="130,87.5 170,60 170,115" fill="rgba(255,255,255,0.9)" />
                        <circle cx="150" cy="87.5" r="40" fill="none" stroke="rgba(255,255,255,0.9)"
                            stroke-width="4" />
                        <rect x="60" y="170" width="180" height="10" rx="5"
                            fill="rgba(255,255,255,0.3)" />
                        <rect x="60" y="170" width="120" height="10" rx="5"
                            fill="rgba(255,255,255,0.6)" />
                    </svg>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        // Category tabs
        const categoryTabs = document.querySelectorAll('.category-tab');
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                categoryTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });

        // View toggle
        const viewBtns = document.querySelectorAll('.view-btn');
        const coursesGrid = document.querySelector('.courses-grid');

        viewBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                viewBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                if (btn.dataset.view === 'list') {
                    coursesGrid?.classList.add('list-view');
                } else {
                    coursesGrid?.classList.remove('list-view');
                }
            });
        });

        // Bookmark buttons
        const bookmarkBtns = document.querySelectorAll('.bookmark-btn');
        bookmarkBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                btn.classList.toggle('active');
            });
        });

        // Filter form submission with debounce for search
        const filterForm = document.getElementById('filter-form');
        const searchInput = document.querySelector('.search-input');
        const filterSelects = document.querySelectorAll('.filter-select');
        let searchTimeout;

        // Auto-submit on select change
        filterSelects.forEach(select => {
            select.addEventListener('change', () => {
                if (filterForm) filterForm.submit();
            });
        });

        // Debounce search input - submit after user stops typing
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (filterForm) filterForm.submit();
                }, 500); // Submit after 500ms of no typing
            });
        }
    </script>
@endsection
