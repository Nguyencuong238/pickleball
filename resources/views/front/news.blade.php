@extends('layouts.front')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/styles-news-simple.css') }}">
<style>
    
</style>
@endsection

@section('content')

    <!-- Simple Page Header -->
    <section class="simple-page-header">
        <div class="container">
            <h1 class="simple-page-title">Tin tức Pickleball</h1>
            <p class="simple-page-subtitle">Cập nhật tin tức và sự kiện mới nhất từ cộng đồng</p>
        </div>
    </section>

    <!-- News Filter Simple -->
    <section class="simple-filter-section">
        <div class="container">
            <div class="simple-filter-wrapper">
                <div class="filter-tags">
                    <button class="filter-tag active" data-category="">Tất cả</button>
                    @forelse ($categories as $category)
                        <button class="filter-tag" data-category="{{ $category }}">{{ $category }}</button>
                    @empty
                        <button class="filter-tag">Giải đấu</button>
                        <button class="filter-tag">Sự kiện</button>
                        <button class="filter-tag">Kỹ thuật</button>
                        <button class="filter-tag">Cộng đồng</button>
                        <button class="filter-tag">Quốc tế</button>
                    @endforelse
                </div>
                
                <div class="simple-search-box">
                    <input type="text" placeholder="Tìm kiếm..." class="simple-search-input">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Article -->
    <section class="section">
        <div class="container">
            @if($featuredNews)
            <div class="featured-article-simple">
                <div class="featured-article-image">
                    @php
                        $featureImage = $featuredNews->getFirstMediaUrl('featured_image');
                        $defaultImage = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 450'%3E%3Cdefs%3E%3ClinearGradient id='feat1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5'/%3E%3Cstop offset='100%25' style='stop-color:%230099CC'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23feat1)' width='800' height='450'/%3E%3Ctext x='400' y='225' font-family='Arial' font-size='32' fill='white' text-anchor='middle' dominant-baseline='middle'%3EFeatured News%3C/text%3E%3C/svg%3E";
                    @endphp
                    <img src="{{ $featureImage ?: $defaultImage }}" alt="{{ $featuredNews->title }}">
                    <span class="article-badge featured">Nổi bật</span>
                </div>
                <div class="featured-article-content">
                    <div class="article-meta-simple">
                        <span class="article-category">{{ $featuredNews->category }}</span>
                        <span class="article-date">{{ $featuredNews->created_at->format('d \T\h\á\n\g m, Y') }}</span>
                        <span class="article-read-time">{{ ceil(str_word_count($featuredNews->content) / 200) }} phút đọc</span>
                    </div>
                    <h2 class="featured-article-title">
                        <a href="{{ route('front.news-detail', $featuredNews->slug) }}">{{ $featuredNews->title }}</a>
                    </h2>
                    <p class="featured-article-excerpt">
                        {{ Str::limit($featuredNews->content, 200) }}
                    </p>
                    <a href="{{ route('front.news-detail', $featuredNews->slug) }}" class="btn btn-primary">Đọc ngay</a>
                </div>
            </div>
            @endif
        </div>
    </section>

    <!-- News Grid Simple -->
    <section class="section section-alt">
        <div class="container">
            <div class="simple-news-grid">
                @forelse($news as $item)
                <!-- News Item {{ $loop->iteration }} -->
                <article class="simple-news-card">
                    <div class="simple-news-image">
                        @php
                            $newsImage = $item->getFirstMediaUrl('featured_image');
                            $categoryColors = [
                                'Giải đấu' => '#FF8E53-#FF6B6B',
                                'Sự kiện' => '#FF8E53-#FF6B6B',
                                'Kỹ thuật' => '#9D84B7-#FF8E53',
                                'Cộng đồng' => '#00D9B5-#0099CC',
                                'Quốc tế' => '#FFD93D-#FF8E53',
                            ];
                            $colors = $categoryColors[$item->category] ?? '#00D9B5-#0099CC';
                            list($color1, $color2) = explode('-', $colors);
                            $defaultImage = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='n" . $loop->iteration . "' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23" . ltrim($color1, '#') . "'/%3E%3Cstop offset='100%25' style='stop-color:%23" . ltrim($color2, '#') . "'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23n" . $loop->iteration . ")' width='400' height='250'/%3E%3C/svg%3E";
                        @endphp
                        <img src="{{ $newsImage ?: $defaultImage }}" alt="{{ $item->title }}">
                        <span class="article-badge {{ strtolower(str_replace(' ', '_', $item->category)) }}">{{ $item->category }}</span>
                    </div>
                    <div class="simple-news-content">
                        <div class="article-meta-simple">
                            <span class="article-date">{{ $item->created_at->format('d \T\h\á\n\g m, Y') }}</span>
                            {{-- <span class="article-views">{{ rand(600, 3000) }} lượt xem</span> --}}
                        </div>
                        <h3 class="simple-news-title">
                            <a href="{{ route('front.news-detail', $item->slug) }}">{{ $item->title }}</a>
                        </h3>
                        <p class="simple-news-excerpt">
                            {{ Str::limit($item->content, 150) }}
                        </p>
                        <a href="{{ route('front.news-detail', $item->slug) }}" class="simple-read-more">Đọc thêm →</a>
                    </div>
                </article>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p style="font-size: 16px; color: #666;">Không có bài viết nào</p>
                </div>
                @endforelse
            </div>

            <!-- Simple Pagination -->
            @if($news->hasPages())
            <div class="simple-pagination">
                @if ($news->onFirstPage())
                    <button class="pagination-btn" disabled>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </button>
                @else
                    <a href="{{ $news->previousPageUrl() }}" class="pagination-btn">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </a>
                @endif

                @foreach ($news->getUrlRange(1, $news->lastPage()) as $page => $url)
                    @if ($page == $news->currentPage())
                        <button class="pagination-btn active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($news->hasMorePages())
                    <a href="{{ $news->nextPageUrl() }}" class="pagination-btn">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                @else
                    <button class="pagination-btn" disabled>
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                @endif
            </div>
            @endif
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter-simple section">
        <div class="container">
            <div class="newsletter-box">
                <div class="newsletter-content-simple">
                    <h2>Nhận tin tức mới nhất</h2>
                    <p>Đăng ký để nhận thông báo về giải đấu, sự kiện và bài viết mới</p>
                </div>
                <div class="newsletter-form-simple">
                    <input type="email" placeholder="Email của bạn" class="newsletter-input-simple">
                    <button class="btn btn-primary">Đăng ký</button>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('js')
<script>
    // Smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endsection
