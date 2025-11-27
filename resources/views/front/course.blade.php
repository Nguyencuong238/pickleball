@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles-coaches.css') }}">
    <style>
        /* .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 0;
        } */
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
            <form method="GET" action="{{ route('course') }}" class="filter-wrapper">
                <!-- Search -->
                <div class="filter-search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" name="search" class="search-input" placeholder="Tìm kiếm khóa học..." value="{{ $filters['search'] ?? '' }}">
                </div>

                <!-- Category Filter -->
                <div class="filter-group" style="margin-bottom: 40px">
                    <label class="filter-label">Danh mục</label>
                    <select name="category" class="filter-select">
                        <option value="">---</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ ($filters['category'] ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Tìm kiếm</button>
            </form>
        </div>
    </section>

    <!-- Coaches Grid -->
    <section class="coaches-section section">
        <div class="container">
            <!-- Results Info -->
            <div class="results-info">
                <p class="results-count">Tìm thấy <strong>{{ $videos->total() }}</strong> khóa học</p>
            </div>

            @if($videos->count() > 0)
            <!-- Videos Grid -->
            <div class="coaches-grid">
                @foreach($videos as $video)
                <!-- Video Card -->
                <div class="coach-card">
                    <div class="coach-image">
                        @if($video->image)
                            <img src="{{ asset('storage/' . $video->image) }}" alt="{{ $video->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='g1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g1)' width='300' height='300'/%3E%3C/svg%3E" alt="{{ $video->name }}">
                        @endif
                        @if($video->category)
                        <span class="coach-badge verified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                            </svg>
                            {{ $video->category->name }}
                        </span>
                        @endif
                    </div>
                    <div class="coach-content">
                         <a href="{{ route('course.detail', $video->id) }}" style="text-decoration: none; color: inherit;">
                             <h3 class="coach-name">
                                 <span>{{ $video->name }}</span>
                             </h3>
                             <div class="coach-experience">
                                 <p style="color: #666; line-height: 1.5;">{{ Str::limit($video->description, 80) }}</p>
                             </div>
                         </a>
                         <div class="coach-actions">
                             <a href="{{ route('course.detail', $video->id) }}" class="btn btn-primary btn-sm">Xem chi tiết</a>
                             <button class="btn btn-outline btn-sm btn-favorite" title="Yêu thích">
                                 <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                     <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                 </svg>
                             </button>
                         </div>
                     </div>
                </div>
                @endforeach

            </div>

            @else
            <div style="text-align: center; padding: 40px 20px;">
                <p style="font-size: 16px; color: #666;">Không tìm thấy khóa học nào</p>
            </div>
            @endif

            <!-- Pagination -->
            @if($videos->count() > 0)
            <div class="pagination">
                @if ($videos->onFirstPage())
                    <button class="pagination-btn pagination-prev" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                        Trước
                    </button>
                @else
                    <a href="{{ $videos->previousPageUrl() . '&' . http_build_query(array_filter($filters)) }}"
                        class="pagination-btn pagination-prev">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                        Trước
                    </a>
                @endif

                <div class="pagination-numbers">
                    @for ($i = 1; $i <= $videos->lastPage(); $i++)
                        @if ($i == $videos->currentPage())
                            <button class="pagination-number active">{{ $i }}</button>
                        @elseif($i <= 3 || $i > $videos->lastPage() - 2)
                            <a href="{{ $videos->url($i) . '&' . http_build_query(array_filter($filters)) }}"
                                class="pagination-number">{{ $i }}</a>
                        @elseif($i == 4 && $videos->lastPage() > 6)
                            <span class="pagination-dots">...</span>
                        @endif
                    @endfor
                </div>

                @if ($videos->hasMorePages())
                    <a href="{{ $videos->nextPageUrl() . '&' . http_build_query(array_filter($filters)) }}"
                        class="pagination-btn pagination-next">
                        Sau
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </a>
                @else
                    <button class="pagination-btn pagination-next" disabled>
                        Sau
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </button>
                @endif
            </div>
            @endif
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

        // Auto-submit form when category changes
        const categorySelect = document.querySelector('select[name="category"]');
        const filterForm = document.querySelector('form');
        
        if (categorySelect && filterForm) {
            categorySelect.addEventListener('change', () => {
                filterForm.submit();
            });
        }
        </script>
        @endsection
