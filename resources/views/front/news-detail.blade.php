@extends('layouts.front')

@php
$articleImage = $article->getFirstMediaUrl('featured_image') ?? asset('assets/images/logo.png');
@endphp

@section('seo')
    <title>{{ $article->title }} | Tin Tức Pickleball - OnePickleball</title>
    <meta name="description" content="{{ substr(strip_tags($article->content), 0, 160) ?: $article->title }}">
    <meta name="keywords" content="{{ $article->category ? $article->category->name . ', ' : '' }}pickleball, tin tức, {{ $article->author }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('news.show', $article->slug) }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $article->title }}">
    <meta property="og:description" content="{{ substr(strip_tags($article->content), 0, 160) ?: $article->title }}">
    <meta property="og:image" content="{{ $articleImage }}">
    <meta property="og:url" content="{{ route('news.show', $article->slug) }}">
    <meta property="og:site_name" content="OnePickleball">
    <meta property="og:locale" content="vi_VN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $article->title }}">
    <meta name="twitter:description" content="{{ substr(strip_tags($article->content), 0, 160) ?: $article->title }}">
    <meta name="twitter:image" content="{{ $articleImage }}">
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/styles-news-simple.css') }}">
<style>
    .simple-article-image img {
        width: auto;
        max-width: 100%;
    }
</style>
@endsection

@section('content')
<!-- Article Container -->
    <article class="simple-article">
        <!-- Breadcrumb -->
        <div class="container">
            <nav class="simple-breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('news') }}">Tin tức</a>
                <span>/</span>
                <span>{{ $article->title }}</span>
            </nav>
        </div>

        <!-- Article Header -->
        <div class="simple-article-header">
            <div class="container container-narrow">
                <div class="article-meta-simple">
                    @if($article->category)
                    <span class="article-category">{{ $article->category->name }}</span>
                    @endif
                    <span class="article-date">{{ $article->created_at->format('d \T\h\á\n\g m, Y') }}</span>
                    {{-- <span class="article-read-time">{{ ceil(str_word_count($article->content) / 200) }} phút đọc</span> --}}
                </div>
                
                <h1 class="simple-article-title">
                    {{ $article->title }}
                </h1>
                
                <div class="article-author-simple">
                    <div class="author-avatar-simple">
                        <svg viewBox="0 0 48 48">
                            <circle cx="24" cy="24" r="24" fill="#00D9B5"/>
                            <text x="24" y="30" font-family="Inter" font-size="16" fill="white" text-anchor="middle">{{ strtoupper(substr($article->author, 0, 2)) }}</text>
                        </svg>
                    </div>
                    <div class="author-info-simple">
                        <div class="author-name">{{ $article->author }}</div>
                        <div class="author-role">Biên tập viên</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Image -->
        <div class="container">
        <div class="simple-article-image">
            <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}">
        </div>
        </div>

        <!-- Article Content -->
        <div class="container container-narrow">
            <div class="simple-article-content">
                <div>{!! $article->content !!}</div>

                <div class="simple-share">
                    <h4>Chia sẻ bài viết</h4>
                    <div class="share-buttons-simple">
                        <a class="share-btn-simple facebook" target="_blank" rel="noopener noreferrer" 
                            href="https://www.facebook.com/sharer/sharer.php?u={{url()->current()}}">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path>
                            </svg>
                            Facebook
                        </a>
                        <a class="share-btn-simple twitter"  target="_blank" rel="noopener noreferrer"
                            href="https://twitter.com/intent/tweet?text={{$article->title}}&url={{url()->current()}}">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"></path>
                            </svg>
                            Twitter
                        </a>
                        {{-- <a class="share-btn-simple zalo">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 14.79c-.28.4-.85.77-1.58.77-.16 0-.33-.02-.5-.06-1.72-.42-3.46-1.51-4.91-3.06-1.45-1.56-2.39-3.38-2.65-5.13-.03-.17-.04-.34-.04-.5 0-.73.34-1.33.71-1.64.37-.32.88-.51 1.42-.51.12 0 .24.01.35.03.61.09 1.15.64 1.42 1.44l.59 1.76c.14.43.11.89-.08 1.28-.18.39-.51.7-.9.86l-.28.11c.12.28.29.56.52.84.48.57 1.08 1.12 1.76 1.64.28.21.55.38.82.5l.11-.28c.16-.39.47-.72.86-.9.39-.19.85-.22 1.28-.08l1.76.59c.8.27 1.35.81 1.44 1.42.02.11.03.23.03.35 0 .54-.19 1.05-.51 1.42z"></path>
                            </svg>
                            Zalo
                        </a> --}}
                    </div>
                </div>
            </div>

            <!-- Related Articles -->
            <div class="related-articles-simple">
                <h3>Bài viết liên quan</h3>
                <div class="related-grid-simple">
                    @forelse($relatedNews as $item)
                    <a href="{{ route('news.show', $item->slug) }}" class="related-article-simple">
                        
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}">
                        <div class="related-content-simple">
                            @if($item->category)
                            <span class="related-category">{{ $item->category->name }}</span>
                            @endif
                            <h4>{{ $item->title }}</h4>
                        </div>
                    </a>
                    @empty
                    <p style="grid-column: 1 / -1; text-align: center; color: #999;">Không có bài viết liên quan</p>
                    @endforelse
                </div>
            </div>
        </div>
    </article>
@endsection
