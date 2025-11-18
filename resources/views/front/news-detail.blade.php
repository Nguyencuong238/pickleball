@extends('layouts.front')

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
                <a href="{{ route('front.news') }}">Tin tức</a>
                <span>/</span>
                <span>{{ $article->title }}</span>
            </nav>
        </div>

        <!-- Article Header -->
        <div class="simple-article-header">
            <div class="container container-narrow">
                <div class="article-meta-simple">
                    <span class="article-category">{{ $article->category }}</span>
                    <span class="article-date">{{ $article->created_at->format('d \T\h\á\n\g m, Y') }}</span>
                    <span class="article-read-time">{{ ceil(str_word_count($article->content) / 200) }} phút đọc</span>
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
        <div class="simple-article-image">
            @php
                $featuredImage = $article->getFirstMediaUrl('featured_image');
                $defaultImage = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 450'%3E%3Cdefs%3E%3ClinearGradient id='feat' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5'/%3E%3Cstop offset='100%25' style='stop-color:%230099CC'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23feat)' width='800' height='450'/%3E%3C/svg%3E";
            @endphp
            <img src="{{ $featuredImage ?: $defaultImage }}" alt="{{ $article->title }}">
        </div>

        <!-- Article Content -->
        <div class="container container-narrow">
            <div class="simple-article-content">
                {!! $article->content !!}
            </div>

            <!-- Related Articles -->
            <div class="related-articles-simple">
                <h3>Bài viết liên quan</h3>
                <div class="related-grid-simple">
                    @forelse($relatedNews as $item)
                    <a href="{{ route('front.news-detail', $item->slug) }}" class="related-article-simple">
                        @php
                            $relatedImage = $item->getFirstMediaUrl('featured_image');
                            $relatedDefault = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 200'%3E%3Crect fill='%2300D9B5' width='300' height='200'/%3E%3C/svg%3E";
                        @endphp
                        <img src="{{ $relatedImage ?: $relatedDefault }}" alt="{{ $item->title }}">
                        <div class="related-content-simple">
                            <span class="related-category">{{ $item->category }}</span>
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
