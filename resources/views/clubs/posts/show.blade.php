@extends('layouts.front')

@section('title', Str::limit(strip_tags($post->content), 60) . ' - ' . $club->name . ' | Pickleball Vietnam')

@section('meta')
<meta name="description" content="{{ Str::limit(strip_tags($post->content), 160) }}">
<meta property="og:title" content="{{ Str::limit(strip_tags($post->content), 60) }} - {{ $club->name }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($post->content), 160) }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ route('clubs.posts.show', [$club, $post]) }}">
@if($post->media->where('type', 'image')->first())
<meta property="og:image" content="{{ asset('storage/' . $post->media->where('type', 'image')->first()->path) }}">
@endif
<meta name="twitter:card" content="summary_large_image">
@endsection

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles-club.css') }}?v=1">

<div class="single-post-container">
    {{-- Breadcrumb --}}
    <nav class="breadcrumb">
        <a href="{{ route('clubs.index') }}">CLB</a>
        <span class="separator">/</span>
        <a href="{{ route('clubs.show', $club) }}">{{ $club->name }}</a>
        <span class="separator">/</span>
        <span>Bài viết</span>
    </nav>

    <div class="single-post-layout">
        {{-- Main Post --}}
        <article class="post-card single-post {{ $post->is_pinned ? 'pinned' : '' }}">
            @if($post->is_pinned)
            <div class="post-pinned-badge">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
                </svg>
                Bài viết ghim
            </div>
            @endif

            <div class="post-header">
                @if($post->author->avatar)
                    <img src="{{ asset('storage/' . $post->author->avatar) }}" alt="{{ $post->author->name }}" class="post-avatar">
                @else
                    <div class="post-avatar post-avatar-placeholder">{{ strtoupper(substr($post->author->name, 0, 1)) }}</div>
                @endif

                <div class="post-author-info">
                    <div class="post-author">
                        <span class="author-name">{{ $post->author->name }}</span>
                        @php
                            $authorRole = $club->getMemberRole($post->author);
                        @endphp
                        @if($authorRole && in_array($authorRole, ['creator', 'admin', 'moderator']))
                            <span class="author-badge {{ $authorRole === 'moderator' ? 'moderator' : 'admin' }}">
                                @if($authorRole === 'creator') Chủ nhiệm
                                @elseif($authorRole === 'admin') Admin
                                @else Điều hành
                                @endif
                            </span>
                        @endif
                        @if($post->is_edited)
                            <span class="edited-badge">Đã chỉnh sửa</span>
                        @endif
                    </div>
                    <div class="post-meta">
                        <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                        <span class="dot">[DOT]</span>
                        <span class="post-visibility">
                            @if($post->visibility === 'public')
                            <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                            </svg>
                            Công khai
                            @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            Thành viên
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="post-content">
                {!! $post->content !!}
            </div>

            {{-- Media --}}
            @if($post->media->count() > 0)
            <div class="post-media">
                @php $firstMedia = $post->media->first(); @endphp

                @if($firstMedia->type === 'image')
                <div class="post-images-grid {{ $post->media->count() === 2 ? 'two-images' : ($post->media->count() === 3 ? 'three-images' : ($post->media->count() >= 4 ? 'four-plus-images' : 'one-image')) }}">
                    @foreach($post->media->take(4) as $index => $media)
                    <div class="post-image-item">
                        <img src="{{ asset('storage/' . $media->path) }}" alt="Ảnh {{ $index + 1 }}">
                        @if($index === 3 && $post->media->count() > 4)
                        <div class="more-images-overlay">
                            <span>+{{ $post->media->count() - 4 }}</span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @elseif($firstMedia->type === 'video')
                <div class="post-video">
                    <video controls>
                        <source src="{{ asset('storage/' . $firstMedia->path) }}" type="video/mp4">
                    </video>
                </div>
                @elseif($firstMedia->type === 'youtube')
                <div class="post-video">
                    <div class="video-wrapper">
                        @php
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $firstMedia->youtube_url, $matches);
                            $videoId = $matches[1] ?? '';
                        @endphp
                        <iframe src="https://www.youtube.com/embed/{{ $videoId }}?rel=0"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Stats --}}
            <div class="post-stats">
                <div class="reactions">
                    @if($post->total_reactions > 0)
                    <div class="reaction-icons">
                        @if(isset($post->reaction_counts['like']) && $post->reaction_counts['like'] > 0)
                            <span class="reaction like">&#128077;</span>
                        @endif
                        @if(isset($post->reaction_counts['love']) && $post->reaction_counts['love'] > 0)
                            <span class="reaction love">&#10084;&#65039;</span>
                        @endif
                        @if(isset($post->reaction_counts['fire']) && $post->reaction_counts['fire'] > 0)
                            <span class="reaction fire">&#128293;</span>
                        @endif
                    </div>
                    <span class="reaction-count">{{ $post->total_reactions }}</span>
                    @endif
                </div>
                <div class="engagement">
                    @if($post->comments_count > 0)
                    <span>{{ $post->comments_count }} bình luận</span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="post-actions">
                <button class="post-action share-btn" onclick="copyLink()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="18" cy="5" r="3"/>
                        <circle cx="6" cy="12" r="3"/>
                        <circle cx="18" cy="19" r="3"/>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                    <span>Chia sẻ</span>
                </button>
            </div>

            {{-- Comments --}}
            <div class="post-comments show">
                @foreach($post->comments as $comment)
                <div class="comment-item">
                    @if($comment->user->avatar)
                        <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="{{ $comment->user->name }}" class="comment-avatar">
                    @else
                        <div class="comment-avatar comment-avatar-placeholder">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</div>
                    @endif
                    <div class="comment-content-wrapper">
                        <div class="comment-bubble">
                            <span class="comment-author">{{ $comment->user->name }}</span>
                            <p class="comment-text">{{ $comment->content }}</p>
                        </div>
                        <div class="comment-meta">
                            <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>

                        {{-- Replies --}}
                        @if($comment->replies->count() > 0)
                        <div class="comment-replies">
                            @foreach($comment->replies as $reply)
                            <div class="comment-item reply">
                                @if($reply->user->avatar)
                                    <img src="{{ asset('storage/' . $reply->user->avatar) }}" alt="{{ $reply->user->name }}" class="comment-avatar small">
                                @else
                                    <div class="comment-avatar comment-avatar-placeholder small">{{ strtoupper(substr($reply->user->name, 0, 1)) }}</div>
                                @endif
                                <div class="comment-content-wrapper">
                                    <div class="comment-bubble">
                                        <span class="comment-author">{{ $reply->user->name }}</span>
                                        <p class="comment-text">{{ $reply->content }}</p>
                                    </div>
                                    <div class="comment-meta">
                                        <span class="comment-time">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </article>

        {{-- Back to club --}}
        <div class="back-to-club">
            <a href="{{ route('clubs.show', $club) }}" class="btn btn-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Quay lại {{ $club->name }}
            </a>
        </div>
    </div>
</div>

<style>
.single-post-container {
    max-width: 680px;
    margin: 100px auto 40px;
    padding: 0 16px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    font-size: 14px;
    color: #6b7280;
}

.breadcrumb a {
    color: var(--primary-color);
}

.breadcrumb .separator {
    color: #d1d5db;
}

.single-post-layout {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.single-post {
    border-radius: var(--radius-lg);
}

.back-to-club {
    text-align: center;
}

.back-to-club .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.back-to-club .btn svg {
    width: 18px;
    height: 18px;
}
</style>

<script>
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Đã sao chép liên kết!');
    });
}
</script>
@endsection
