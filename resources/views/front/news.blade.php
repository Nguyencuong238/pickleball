@extends('layouts.front')

@section('css')
<style>
    .news-container {
        padding: clamp(40px, 5vw, 80px) clamp(20px, 3vw, 40px);
        background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        min-height: 100vh;
    }

    .news-header {
        text-align: center;
        margin-top: 60px;
        margin-bottom: clamp(40px, 5vw, 60px);
        animation: slideDown 0.5s ease;
    }

    .news-header h1 {
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 15px;
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .news-header p {
        font-size: clamp(0.95rem, 2vw, 1.1rem);
        color: #6b7280;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .news-filters {
        display: flex;
        gap: clamp(10px, 2vw, 15px);
        margin-bottom: clamp(30px, 4vw, 50px);
        flex-wrap: wrap;
        justify-content: center;
    }

    .filter-btn {
        padding: clamp(8px, 1vw, 12px) clamp(16px, 2vw, 24px);
        border: 2px solid #e5e7eb;
        background: white;
        color: #6b7280;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        font-size: clamp(0.85rem, 1.5vw, 0.95rem);
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .filter-btn:hover,
    .filter-btn.active {
        border-color: #00D9B5;
        color: #00D9B5;
        background: linear-gradient(135deg, rgba(0, 217, 181, 0.1) 0%, rgba(0, 153, 204, 0.1) 100%);
    }

    .news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: clamp(20px, 3vw, 30px);
        margin-bottom: clamp(30px, 4vw, 50px);
    }

    .news-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }

    .news-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 217, 181, 0.2);
    }

    .news-card-image {
        position: relative;
        width: 100%;
        height: clamp(200px, 30vw, 250px);
        overflow: hidden;
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
    }

    .news-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .news-card:hover .news-card-image img {
        transform: scale(1.05);
    }

    .news-category-badge {
        position: absolute;
        top: clamp(12px, 2vw, 16px);
        right: clamp(12px, 2vw, 16px);
        background: rgba(0, 217, 181, 0.9);
        color: white;
        padding: clamp(6px, 1vw, 8px) clamp(12px, 2vw, 16px);
        border-radius: 20px;
        font-size: clamp(0.75rem, 1.2vw, 0.85rem);
        font-weight: 600;
        backdrop-filter: blur(10px);
    }

    .news-card-content {
        padding: clamp(20px, 3vw, 25px);
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .news-card-meta {
        display: flex;
        gap: clamp(10px, 2vw, 15px);
        margin-bottom: 12px;
        flex-wrap: wrap;
        font-size: clamp(0.8rem, 1.2vw, 0.85rem);
        color: #9ca3af;
    }

    .news-card-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .news-card-title {
        font-size: clamp(1rem, 2vw, 1.3rem);
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
        line-height: 1.4;
        word-break: break-word;
        flex-grow: 1;
    }

    .news-card-excerpt {
        color: #6b7280;
        font-size: clamp(0.9rem, 1.5vw, 0.95rem);
        line-height: 1.5;
        margin-bottom: 16px;
        flex-grow: 1;
    }

    .news-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
    }

    .read-more-link {
        color: #00D9B5;
        font-weight: 600;
        font-size: clamp(0.85rem, 1.2vw, 0.95rem);
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .read-more-link:hover {
        color: #0099CC;
        gap: 10px;
    }

    .read-more-link svg {
        width: 16px;
        height: 16px;
        transition: transform 0.3s ease;
    }

    .news-empty {
        text-align: center;
        padding: clamp(40px, 5vw, 80px) clamp(20px, 3vw, 40px);
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .news-empty i {
        font-size: clamp(3rem, 8vw, 4rem);
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .news-empty h3 {
        font-size: clamp(1.2rem, 3vw, 1.5rem);
        color: #6b7280;
        margin-bottom: 10px;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-bottom: 40px;
    }

    .pagination-btn {
        padding: clamp(8px, 1vw, 10px) clamp(12px, 2vw, 16px);
        border: 2px solid #e5e7eb;
        background: white;
        color: #6b7280;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: clamp(0.8rem, 1.2vw, 0.9rem);
        transition: all 0.3s ease;
    }

    .pagination-btn:hover,
    .pagination-btn.active {
        border-color: #00D9B5;
        color: white;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
    }

    .sidebar {
        display: none;
    }

    .featured-news {
        margin-bottom: clamp(30px, 4vw, 50px);
    }

    .featured-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }

    .featured-image {
        width: 100%;
        height: 100%;
        min-height: 350px;
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        overflow: hidden;
    }

    .featured-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .featured-content {
        padding: clamp(25px, 4vw, 40px);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .featured-badge {
        display: inline-block;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: clamp(0.8rem, 1.2vw, 0.9rem);
        font-weight: 600;
        margin-bottom: 15px;
        width: fit-content;
    }

    .featured-title {
        font-size: clamp(1.5rem, 4vw, 2.2rem);
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .featured-excerpt {
        color: #6b7280;
        font-size: clamp(0.95rem, 1.5vw, 1rem);
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .featured-meta {
        display: flex;
        gap: clamp(15px, 3vw, 25px);
        margin-bottom: 20px;
        flex-wrap: wrap;
        font-size: clamp(0.85rem, 1.2vw, 0.9rem);
        color: #9ca3af;
    }

    .featured-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .featured-btn {
        display: inline-block;
        padding: clamp(10px, 1.5vw, 14px) clamp(24px, 3vw, 32px);
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: clamp(0.9rem, 1.5vw, 1rem);
        transition: all 0.3s ease;
        width: fit-content;
    }

    .featured-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
        color: white;
    }

    @media (max-width: 1024px) {
        .featured-card {
            grid-template-columns: 1fr;
        }

        .featured-image {
            height: 300px;
        }

        .news-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .news-container {
            padding: clamp(20px, 3vw, 40px);
        }

        .news-header h1 {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
        }

        .news-grid {
            grid-template-columns: 1fr;
        }

        .featured-card {
            grid-template-columns: 1fr;
        }

        .featured-image {
            height: 250px;
        }

        .featured-content {
            padding: 20px;
        }

        .news-filters {
            gap: 8px;
        }
    }

    @media (max-width: 480px) {
        .news-header h1 {
            font-size: clamp(1.3rem, 3vw, 1.8rem);
        }

        .news-card-title {
            font-size: 1rem;
        }

        .featured-title {
            font-size: clamp(1.3rem, 3vw, 1.8rem);
        }

        .filter-btn {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('content')
<div class="news-container">
    <!-- Header -->
    <div class="news-header">
        <h1>📰 Tin Tức & Cập Nhật</h1>
        <p>Cập nhật tin tức mới nhất, kiến thức và xu hướng trong thế giới Pickleball</p>
    </div>

    @if($news->count() > 0)
        <!-- Featured News Article -->
        <div class="featured-news">
            @php
                $featured = $news->first();
            @endphp
            <div class="featured-card">
                <div class="featured-image">
                    @if($featured->image)
                        <img src="{{ asset('storage/' . $featured->image) }}" alt="{{ $featured->title }}">
                    @else
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 400'%3E%3Cdefs%3E%3ClinearGradient id='featured' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23featured)' width='600' height='400'/%3E%3Ctext x='300' y='200' font-family='Arial' font-size='32' fill='white' text-anchor='middle' dominant-baseline='middle'%3ENEWS IMAGE%3C/text%3E%3C/svg%3E" alt="{{ $featured->title }}">
                    @endif
                </div>
                <div class="featured-content">
                    <span class="featured-badge">🔥 Nổi Bật</span>
                    <h2 class="featured-title">{{ $featured->title }}</h2>
                    <p class="featured-excerpt">
                        {{ Str::limit(strip_tags($featured->content), 200) }}
                    </p>
                    <div class="featured-meta">
                        <span>📅 {{ $featured->created_at->format('d M Y') }}</span>
                        <span>✍️ {{ $featured->author ?? 'Admin' }}</span>
                    </div>
                    <a href="{{ route('news.show', $featured->slug) }}" class="featured-btn">Đọc Tiếp →</a>
                </div>
            </div>
        </div>

        <!-- News Grid -->
        <div class="news-grid" id="news-section">
            @forelse($news->skip(1) as $article)
                <div class="news-card">
                    <div class="news-card-image">
                        @if($article->image)
                            <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}">
                        @else
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 280'%3E%3Cdefs%3E%3ClinearGradient id='default' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23default)' width='400' height='280'/%3E%3Ctext x='200' y='140' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle'%3ENEWS%3C/text%3E%3C/svg%3E" alt="{{ $article->title }}">
                        @endif
                    </div>
                    <div class="news-card-content">
                        <div class="news-card-meta">
                            <span>📅 {{ $article->created_at->format('d M Y') }}</span>
                            <span>✍️ {{ $article->author ?? 'Admin' }}</span>
                        </div>
                        <h3 class="news-card-title">{{ $article->title }}</h3>
                        <p class="news-card-excerpt">{{ Str::limit(strip_tags($article->content), 100) }}</p>
                        <div class="news-card-footer">
                            <a href="{{ route('news.show', $article->slug) }}" class="read-more-link">Đọc Tiếp <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: #9ca3af; font-size: 1.1rem;">Chưa có bài viết nào.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($news->hasPages())
            <div class="pagination-container">
                @if($news->onFirstPage())
                    <button class="pagination-btn" disabled>←</button>
                @else
                    <a href="{{ $news->previousPageUrl() }}" class="pagination-btn">←</a>
                @endif

                @foreach($news->getUrlRange(1, $news->lastPage()) as $page => $url)
                    @if($page == $news->currentPage())
                        <button class="pagination-btn active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach

                @if($news->hasMorePages())
                    <a href="{{ $news->nextPageUrl() }}" class="pagination-btn">→</a>
                @else
                    <button class="pagination-btn" disabled>→</button>
                @endif
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="news-empty">
            <i class="fas fa-newspaper"></i>
            <h3>Chưa có bài viết nào</h3>
            <p>Vui lòng quay lại sau để xem những bài viết mới.</p>
        </div>
    @endif
</div>

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
