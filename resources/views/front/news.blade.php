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

        .simple-filter-section {
            background: #f8f9fa;
            border: 0;
            margin-top: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }

        section.simple-page-header {
            position: relative;
            padding: calc(80px + 4rem) 0 3rem;
            background: radial-gradient(circle at 20% 50%, rgba(0, 217, 181, 0.1) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(0, 153, 204, 0.1) 0%, transparent 50%);
            overflow: hidden;
        }

        .page-header-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(0, 217, 181, 0.1) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(0, 153, 204, 0.1) 0%, transparent 50%);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
        }
    </style>
@endsection

@section('content')
    <!-- Simple Page Header -->
    <section class="simple-page-header">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="breadcrumb">
                <a href="/">Trang chủ</a>
                <span class="separator">/</span>
                <span>Tin tức</span>
            </div>
            <h1 class="simple-page-title">Tin Tức Pickleball</h1>
            <p class="simple-page-subtitle">Cập nhật tin tức và sự kiện mới nhất từ cộng đồng</p>

            <!-- News Filter Simple -->
            <section class="simple-filter-section">
                <div class="container">
                    <form method="GET" action="{{ route('news') }}" id="newsFilterForm">
                        <div class="simple-filter-wrapper">
                            <!-- Category Filter Tags -->
                            <div class="filter-tags">
                                @if ($news->count())
                                    <button type="submit" name="category" value=""
                                        class="filter-tag {{ !($filters['category'] ?? null) ? 'active' : '' }}">
                                        Tất cả
                                    </button>
                                @endif
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
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="20"
                                        height="20">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.35-4.35" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
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
                                {!! Str::words(strip_tags($item->content), 20) !!}
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
            @if ($news->hasPages())
                {{ $news->links('pagination.custom') }}
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
