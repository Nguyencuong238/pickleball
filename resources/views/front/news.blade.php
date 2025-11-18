@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles-news-simple.css') }}">
    <style>
        .simple-search-input {
            padding: 16px 24px;
        }

        .search-submit {
            position: absolute;
            top: 50%;
            right: 0;
            transform: translate(-50%, -50%);
        }
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
            <form method="GET" action="{{ route('news') }}" id="newsFilterForm">
                <div class="simple-filter-wrapper">
                    <!-- Category Filter Tags -->
                    <div class="filter-tags">
                        <button type="submit" name="category" value=""
                            class="filter-tag {{ !($filters['category'] ?? null) ? 'active' : '' }}">
                            Tất cả
                        </button>
                        @foreach ($categories as $category)
                            <button type="submit" name="category" value="{{ $category->slug }}"
                                class="filter-tag {{ ($filters['category'] ?? null) === $category->slug ? 'active' : '' }}">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Search Box -->
                    <div class="simple-search-box">
                        <input type="text" placeholder="Tìm kiếm..." class="simple-search-input" name="search"
                            value="{{ $filters['search'] ?? '' }}">
                        <button type="submit" class="search-submit" title="Tìm kiếm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="20" height="20">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.35-4.35" />
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Featured Article -->
    <section class="section">
        <div class="container">
            @if ($featuredNews)
                <div class="featured-article-simple">
                    <div class="featured-article-image">
                        @php
                            $featureImage = $featuredNews->getFirstMediaUrl('featured_image');
                            $defaultImage =
                                "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 450'%3E%3Cdefs%3E%3ClinearGradient id='feat1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5'/%3E%3Cstop offset='100%25' style='stop-color:%230099CC'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23feat1)' width='800' height='450'/%3E%3Ctext x='400' y='225' font-family='Arial' font-size='32' fill='white' text-anchor='middle' dominant-baseline='middle'%3EFeatured News%3C/text%3E%3C/svg%3E";
                        @endphp
                        <img src="{{ $featureImage ?: $defaultImage }}" alt="{{ $featuredNews->title }}">
                        <span class="article-badge featured">Nổi bật</span>
                    </div>
                    <div class="featured-article-content">
                        <div class="article-meta-simple">
                            @if ($featuredNews->category)
                                <span class="article-category">{{ $featuredNews->category->name }}</span>
                            @endif
                            <span class="article-date">{{ $featuredNews->created_at->format('d \T\h\á\n\g m, Y') }}</span>
                            <span class="article-read-time">{{ ceil(str_word_count($featuredNews->content) / 200) }} phút
                                đọc</span>
                        </div>
                        <h2 class="featured-article-title">
                            <a href="{{ route('news.show', $featuredNews->slug) }}">{{ $featuredNews->title }}</a>
                        </h2>
                        <p class="featured-article-excerpt">
                            {{ Str::words(strip_tags($featuredNews->content), 20) }}
                        </p>
                        <a href="{{ route('news.show', $featuredNews->slug) }}" class="btn btn-primary">Đọc ngay</a>
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
                                $categoryClass = ['event', 'technique', 'community', 'international', 'tournament'];
                            @endphp
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}">
                            @if ($item->category)
                                <span
                                    class="article-badge {{ $categoryClass[$loop->index % 5] }}">{{ $item->category->name }}</span>
                            @endif
                        </div>
                        <div class="simple-news-content">
                            <div class="article-meta-simple">
                                <span class="article-date">{{ $item->created_at->format('d \T\h\á\n\g m, Y') }}</span>
                                {{-- <span class="article-views">{{ rand(600, 3000) }} lượt xem</span> --}}
                            </div>
                            <h3 class="simple-news-title">
                                <a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a>
                            </h3>
                            <p class="simple-news-excerpt">
                                {{ Str::words(strip_tags($item->content), 20) }}
                            </p>
                            <a href="{{ route('news.show', $item->slug) }}" class="simple-read-more">Đọc thêm →</a>
                        </div>
                    </article>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <p style="font-size: 16px; color: #666;">Không có bài viết nào</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="pagination">
                @if ($news->onFirstPage())
                    <button class="pagination-btn pagination-prev" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                        Trước
                    </button>
                @else
                    <a href="{{ $news->previousPageUrl() . '&' . http_build_query(array_filter($filters)) }}"
                        class="pagination-btn pagination-prev">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                        Trước
                    </a>
                @endif

                <div class="pagination-numbers">
                    @for ($i = 1; $i <= $news->lastPage(); $i++)
                        @if ($i == $news->currentPage())
                            <button class="pagination-number active">{{ $i }}</button>
                        @elseif($i <= 3 || $i > $news->lastPage() - 2)
                            <a href="{{ $news->url($i) . '&' . http_build_query(array_filter($filters)) }}"
                                class="pagination-number">{{ $i }}</a>
                        @elseif($i == 4 && $news->lastPage() > 6)
                            <span class="pagination-dots">...</span>
                        @endif
                    @endfor
                </div>

                @if ($news->hasMorePages())
                    <a href="{{ $news->nextPageUrl() . '&' . http_build_query(array_filter($filters)) }}"
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
            anchor.addEventListener('click', function(e) {
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
