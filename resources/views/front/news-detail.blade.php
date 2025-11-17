@extends('layouts.front')

@section('css')
<style>
    .news-detail-container {
        padding: clamp(40px, 5vw, 80px) clamp(20px, 3vw, 40px);
        background: #f8fafc;
        min-height: 100vh;
    }

    .news-detail-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .breadcrumb-nav {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        font-size: 0.9rem;
    }

    .breadcrumb-nav a {
        color: #00D9B5;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .breadcrumb-nav a:hover {
        color: #0099CC;
    }

    .breadcrumb-nav span {
        color: #9ca3af;
    }

    .article-header {
        background: white;
        padding: clamp(30px, 4vw, 50px);
        border-radius: 15px;
        margin-bottom: clamp(30px, 4vw, 50px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .article-title {
        font-size: clamp(1.8rem, 5vw, 2.8rem);
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .article-meta {
        display: flex;
        gap: clamp(15px, 3vw, 30px);
        flex-wrap: wrap;
        color: #9ca3af;
        font-size: clamp(0.9rem, 1.5vw, 1rem);
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 20px;
    }

    .article-meta span {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .article-image {
        width: 100%;
        height: clamp(300px, 50vw, 500px);
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: clamp(30px, 4vw, 50px);
    }

    .article-content {
        background: white;
        padding: clamp(30px, 4vw, 50px);
        border-radius: 15px;
        margin-bottom: clamp(30px, 4vw, 50px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        line-height: 1.8;
        font-size: clamp(0.95rem, 1.5vw, 1.05rem);
        color: #475569;
    }

    .article-content p {
        margin-bottom: 20px;
    }

    .article-content h2 {
        font-size: clamp(1.5rem, 3vw, 1.8rem);
        color: #1f2937;
        margin: 30px 0 20px 0;
        font-weight: 700;
    }

    .article-content h3 {
        font-size: clamp(1.2rem, 2.5vw, 1.5rem);
        color: #374151;
        margin: 25px 0 15px 0;
        font-weight: 600;
    }

    .article-content ul,
    .article-content ol {
        margin: 20px 0 20px 20px;
    }

    .article-content li {
        margin-bottom: 10px;
    }

    .article-content strong {
        color: #1f2937;
        font-weight: 600;
    }

    .article-content em {
        color: #00D9B5;
    }

    .article-footer {
        background: white;
        padding: clamp(20px, 3vw, 30px);
        border-radius: 15px;
        border-top: 3px solid #00D9B5;
        margin-bottom: clamp(30px, 4vw, 50px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .author-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .author-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
    }

    .author-details h4 {
        margin: 0;
        color: #1f2937;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .author-details p {
        margin: 5px 0 0 0;
        color: #9ca3af;
        font-size: 0.85rem;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #00D9B5;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        gap: 12px;
        color: #0099CC;
    }

    .related-section {
        margin-top: clamp(40px, 5vw, 60px);
    }

    .related-title {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 800;
        color: #1f2937;
        margin-bottom: clamp(20px, 3vw, 30px);
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: clamp(15px, 2vw, 25px);
    }

    .related-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .related-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 217, 181, 0.15);
    }

    .related-card-image {
        width: 100%;
        height: 180px;
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        overflow: hidden;
    }

    .related-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .related-card-content {
        padding: clamp(15px, 2vw, 20px);
    }

    .related-card-title {
        font-size: clamp(0.95rem, 1.5vw, 1.1rem);
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .related-card-date {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    @media (max-width: 768px) {
        .news-detail-container {
            padding: clamp(20px, 3vw, 30px);
        }

        .article-header {
            padding: 20px;
        }

        .article-content {
            padding: 20px;
        }

        .article-meta {
            flex-direction: column;
            gap: 10px;
        }

        .related-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .article-title {
            font-size: 1.5rem;
        }

        .article-image {
            height: 200px;
        }

        .breadcrumb-nav {
            font-size: 0.8rem;
        }
    }
</style>
@endsection

@section('content')
<div class="news-detail-container">
    <div class="news-detail-wrapper">
        <!-- Back Link -->
        <a href="{{ route('news') }}" class="back-link">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg>
            Quay lại Tin tức
        </a>

        <!-- Article Header -->
        <div class="article-header">
            <h1 class="article-title">{{ $article->title }}</h1>
            <div class="article-meta">
                <span>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    {{ $article->created_at->format('d M Y') }}
                </span>
                <span>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    {{ $article->author ?? 'Admin' }}
                </span>
            </div>
        </div>

        <!-- Article Image -->
        @if($article->image)
            <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}" class="article-image">
        @else
            <div style="width: 100%; height: clamp(300px, 50vw, 500px); background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%); border-radius: 12px; margin-bottom: clamp(30px, 4vw, 50px); display: flex; align-items: center; justify-content: center;">
                <span style="color: white; font-size: 1.2rem;">No Image</span>
            </div>
        @endif

        <!-- Article Content -->
        <div class="article-content">
            {!! $article->content !!}
        </div>

        <!-- Article Footer -->
        <div class="article-footer">
            <div class="author-info">
                <div class="author-avatar">{{ strtoupper(substr($article->author ?? 'A', 0, 1)) }}</div>
                <div class="author-details">
                    <h4>{{ $article->author ?? 'Admin' }}</h4>
                    <p>Tác giả bài viết</p>
                </div>
            </div>
        </div>

        <!-- Related News -->
        @if($relatedNews->count() > 0)
            <div class="related-section">
                <h2 class="related-title">Bài Viết Liên Quan</h2>
                <div class="related-grid">
                    @foreach($relatedNews as $news)
                        <a href="{{ route('news.show', $news->slug) }}" class="related-card">
                            <div class="related-card-image">
                                @if($news->image)
                                    <img src="{{ asset('storage/' . $news->image) }}" alt="{{ $news->title }}">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 280" style="width: 100%; height: 100%;">
                                        <defs>
                                            <linearGradient id="placeholder" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" style="stop-color:#00D9B5;stop-opacity:1" />
                                                <stop offset="100%" style="stop-color:#0099CC;stop-opacity:1" />
                                            </linearGradient>
                                        </defs>
                                        <rect fill="url(#placeholder)" width="400" height="280"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="related-card-content">
                                <h3 class="related-card-title">{{ Str::limit($news->title, 50) }}</h3>
                                <p class="related-card-date">{{ $news->created_at->format('d M Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
