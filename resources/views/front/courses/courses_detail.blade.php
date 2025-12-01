@extends('layouts.front')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles-courses.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
                <a href="{{ route('course') }}">Khóa học</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
                <a href="{{ route('course', ['category' => $video->category_id]) }}">{{ $video->category->name }}</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
                <span>{{ $video->name }}</span>
            </nav>
        </div>
    </section>

    <!-- Video Player Section -->
    <section class="video-player-section">
        <div class="container">
            <div class="video-layout">
                <!-- Main Video -->
                <div class="video-main">
                    <div class="video-player-wrapper">
                        <!-- YouTube Embed -->
                        <div class="youtube-container">
                            @php
                                // Extract YouTube ID from video_link
                                $youtubeId = $video->video_link;
                                if (strpos($video->video_link, 'youtube.com') !== false) {
                                    preg_match('/v=([a-zA-Z0-9_-]+)/', $video->video_link, $matches);
                                    $youtubeId = $matches[1] ?? $video->video_link;
                                } elseif (strpos($video->video_link, 'youtu.be') !== false) {
                                    preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $video->video_link, $matches);
                                    $youtubeId = $matches[1] ?? $video->video_link;
                                }
                            @endphp
                            <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}?rel=0&modestbranding=1"
                                title="{{ $video->name }}" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>

                    <!-- Video Info -->
                    <div class="video-detail-info">
                        <div class="video-badges-row">
                            <span class="video-category-badge">{{ $video->category->name }}</span>
                            <span class="video-level-badge beginner">{{ $video->level ?? 'Người mới' }}</span>
                            <span class="video-duration-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                {{ $video->duration ?? '25:30' }}
                            </span>
                        </div>

                        <h1 class="video-detail-title">{{ $video->name }}</h1>

                        <div class="video-detail-meta">
                            <div class="meta-left">
                                @if ($video->instructor)
                                    <div class="instructor-info">
                                        @php
                                            $name = $video->instructor->name ?? '';
                                            $initials = '';
                                            foreach (explode(' ', trim($name)) as $word) {
                                                if (!empty($word)) {
                                                    $initials .= strtoupper(mb_substr($word, 0, 1));
                                                }
                                            }
                                            $initials = $initials ?: 'I';
                                            $colors = ['#FF8E53', '#00D9B5', '#9D84B7', '#FFC93C', '#FF6B6B'];
                                            $colorIndex = (ord($initials[0]) + strlen($initials)) % count($colors);
                                            $bgColor = $colors[$colorIndex];
                                        @endphp
                                        @if ($video->instructor->image)
                                            <img src="{{ asset('storage/' . $video->instructor->image) }}" alt="{{ $video->instructor->name }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                        @else
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='{{ urlencode($bgColor) }}'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3E{{ $initials }}%3C/text%3E%3C/svg%3E"
                                                alt="{{ $video->instructor->name }}">
                                        @endif
                                        <div class="instructor-text">
                                            <span class="instructor-name">Coach {{ $video->instructor->name }}</span>
                                            <span class="instructor-title">{{ $video->instructor->experience ?? 'Huấn luyện viên chuyên nghiệp' }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="instructor-info">
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%23CCCCCC'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3E%3F%3C/text%3E%3C/svg%3E"
                                            alt="Coach">
                                        <div class="instructor-text">
                                            <span class="instructor-name">Chưa có giảng viên</span>
                                            <span class="instructor-title">Chưa cập nhật</span>
                                        </div>
                                    </div>
                                @endif
                                <div class="video-stats-detail">
                                     <span class="stat">
                                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                              <circle cx="12" cy="12" r="3" />
                                          </svg>
                                          @php
                                              $viewsCount = $video->views_count ?? 0;
                                              if ($viewsCount >= 1000000) {
                                                  $displayViews = number_format($viewsCount / 1000000, 1) . 'M';
                                              } elseif ($viewsCount >= 1000) {
                                                  $displayViews = number_format($viewsCount / 1000, 1) . 'K';
                                              } else {
                                                  $displayViews = $viewsCount;
                                              }
                                          @endphp
                                          {{ $displayViews }} lượt xem
                                      </span>
                                      <span class="stat">
                                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                              <line x1="16" y1="2" x2="16" y2="6" />
                                              <line x1="8" y1="2" x2="8" y2="6" />
                                              <line x1="3" y1="10" x2="21" y2="10" />
                                          </svg>
                                          {{ $video->created_at->format('d/m/Y') }}
                                      </span>
                                  </div>
                            </div>
                            <div class="meta-right">
                                <div class="rating-display">
                                     @php
                                         $rating = $video->rating ?? 5;
                                         $ratingCount = $video->rating_count ?? 0;
                                         $fullStars = floor($rating);
                                         $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                     @endphp
                                     <div class="stars">
                                         @for ($i = 1; $i <= 5; $i++)
                                             <svg viewBox="0 0 24 24" fill="{{ $i <= $fullStars || ($i == ceil($rating) && $hasHalfStar) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1">
                                                 <path
                                                     d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                             </svg>
                                         @endfor
                                     </div>
                                     <span class="rating-text">{{ number_format($rating, 1) }} ({{ $ratingCount }} đánh giá)</span>
                                  </div>
                            </div>
                        </div>

                        <div class="video-actions">
                            <button class="action-btn like" id="likeVideoBtn" data-video-id="{{ $video->id }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                </svg>
                                <span class="like-count">{{ $video->likes()->count() }}</span>
                            </button>
                            {{-- <button class="action-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
                                </svg>
                                <span>Lưu</span>
                            </button> --}}
                            <button class="action-btn" id="shareVideoBtn" data-video-url="{{ url()->current() }}" data-video-title="{{ $video->name }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="18" cy="5" r="3" />
                                    <circle cx="6" cy="12" r="3" />
                                    <circle cx="18" cy="19" r="3" />
                                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                                </svg>
                                <span>Chia sẻ</span>
                            </button>
                            {{-- <button class="action-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                <span>Tải xuống</span>
                            </button> --}}
                        </div>
                    </div>

                    <!-- Video Description -->
                    <div class="video-description-card">
                        <h3 class="card-title">Mô tả video</h3>
                        <div class="description-content" id="descriptionContent">
                            {!! nl2br(e($video->description)) !!}
                        </div>
                        <button class="expand-btn" id="expandBtn">
                            <span>Xem thêm</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                    </div>

                    <!-- Chapters -->
                    @if ($video->chapters && count($video->chapters) > 0)
                        <div class="video-chapters-card">
                            <h3 class="card-title">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="8" y1="6" x2="21" y2="6" />
                                    <line x1="8" y1="12" x2="21" y2="12" />
                                    <line x1="8" y1="18" x2="21" y2="18" />
                                    <line x1="3" y1="6" x2="3.01" y2="6" />
                                    <line x1="3" y1="12" x2="3.01" y2="12" />
                                    <line x1="3" y1="18" x2="3.01" y2="18" />
                                </svg>
                                Chương trong video
                            </h3>
                            <div class="chapters-list">
                                @foreach ($video->chapters as $index => $chapter)
                                    <button class="chapter-item {{ $index === 0 ? 'active' : '' }}" data-time="{{ $chapter['time'] ?? 0 }}">
                                        <span class="chapter-time">{{ $chapter['start_time'] ?? '00:00' }}</span>
                                        <span class="chapter-title">{{ $chapter['title'] ?? '' }}</span>
                                        <span class="chapter-duration">{{ $chapter['duration'] ?? '' }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Comments Section -->
                    <div class="comments-section">
                        <h3 class="card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            Bình luận (<span id="commentCount">{{ $video->allComments()->count() }}</span>)
                        </h3>

                        @auth
                            <!-- Comment Form -->
                            <div class="comment-form">
                                @php
                                    $user = Auth::user();
                                    $name = $user->name ?? '';
                                    $initials = '';
                                    foreach (explode(' ', trim($name)) as $word) {
                                        if (!empty($word)) {
                                            $initials .= strtoupper(mb_substr($word, 0, 1));
                                        }
                                    }
                                    $initials = $initials ?: 'U';
                                    $colors = ['#FF8E53', '#00D9B5', '#9D84B7', '#FFC93C', '#FF6B6B'];
                                    $colorIndex = (ord($initials[0]) + strlen($initials)) % count($colors);
                                    $bgColor = $colors[$colorIndex];
                                @endphp
                                @if ($user->image)
                                    <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}" class="comment-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                @else
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='{{ urlencode($bgColor) }}'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3E{{ $initials }}%3C/text%3E%3C/svg%3E" alt="{{ $user->name }}" class="comment-avatar">
                                @endif
                                <div class="comment-input-wrapper">
                                    <textarea placeholder="Viết bình luận..." class="comment-input" id="commentInput" rows="2"></textarea>
                                    <button class="btn btn-primary btn-sm" id="submitCommentBtn" data-video-id="{{ $video->id }}">Gửi</button>
                                </div>
                            </div>
                        @endauth

                        <!-- Comments List -->
                        <div class="comments-list" id="commentsList">
                            @forelse ($video->comments()->orderByDesc('created_at')->get() as $comment)
                                <div class="comment-item" data-comment-id="{{ $comment->id }}">
                                    @php
                                        $name = $comment->user->name ?? '';
                                        $initials = '';
                                        foreach (explode(' ', trim($name)) as $word) {
                                            if (!empty($word)) {
                                                $initials .= strtoupper(mb_substr($word, 0, 1));
                                            }
                                        }
                                        $initials = $initials ?: 'U';
                                        $colors = ['#FF8E53', '#00D9B5', '#9D84B7', '#FFC93C', '#FF6B6B'];
                                        $colorIndex = (ord($initials[0]) + strlen($initials)) % count($colors);
                                        $bgColor = $colors[$colorIndex];
                                    @endphp
                                    @if ($comment->user->image)
                                        <img src="{{ asset('storage/' . $comment->user->image) }}" alt="{{ $comment->user->name }}" class="comment-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    @else
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='{{ urlencode($bgColor) }}'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3E{{ $initials }}%3C/text%3E%3C/svg%3E" alt="{{ $comment->user->name }}" class="comment-avatar">
                                    @endif
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <span class="comment-author">{{ $comment->user->name }}</span>
                                            @if ($comment->user_id === $video->instructor_id)
                                                <span class="author-badge">Tác giả</span>
                                            @endif
                                            <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="comment-text">{{ $comment->content }}</p>
                                        <div class="comment-actions">
                                            <button class="comment-action like-comment-btn" data-comment-id="{{ $comment->id }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                                </svg>
                                                <span class="comment-like-count">{{ $comment->likes_count }}</span>
                                            </button>
                                            @auth
                                                <button class="comment-action reply-btn" data-comment-id="{{ $comment->id }}">Trả lời</button>
                                                @if (Auth::user()->id === $comment->user_id)
                                                    <button class="comment-action delete-comment-btn" data-comment-id="{{ $comment->id }}" style="color: #e74c3c;">Xóa</button>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                </div>

                                <!-- Replies -->
                                @if ($comment->replies()->count() > 0)
                                    <div class="comment-replies" id="replies-{{ $comment->id }}">
                                        @foreach ($comment->replies()->orderByDesc('created_at')->get() as $reply)
                                            <div class="comment-item reply" data-comment-id="{{ $reply->id }}">
                                                @php
                                                    $rName = $reply->user->name ?? '';
                                                    $rInitials = '';
                                                    foreach (explode(' ', trim($rName)) as $word) {
                                                        if (!empty($word)) {
                                                            $rInitials .= strtoupper(mb_substr($word, 0, 1));
                                                        }
                                                    }
                                                    $rInitials = $rInitials ?: 'U';
                                                    $rColorIndex = (ord($rInitials[0]) + strlen($rInitials)) % count($colors);
                                                    $rBgColor = $colors[$rColorIndex];
                                                @endphp
                                                @if ($reply->user->image)
                                                    <img src="{{ asset('storage/' . $reply->user->image) }}" alt="{{ $reply->user->name }}" class="comment-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                                @else
                                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='{{ urlencode($rBgColor) }}'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3E{{ $rInitials }}%3C/text%3E%3C/svg%3E" alt="{{ $reply->user->name }}" class="comment-avatar">
                                                @endif
                                                <div class="comment-content">
                                                    <div class="comment-header">
                                                        <span class="comment-author">{{ $reply->user->name }}</span>
                                                        @if ($reply->user_id === $video->instructor_id)
                                                            <span class="author-badge">Tác giả</span>
                                                        @endif
                                                        <span class="comment-date">{{ $reply->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <p class="comment-text">{{ $reply->content }}</p>
                                                    <div class="comment-actions">
                                                        <button class="comment-action like-comment-btn" data-comment-id="{{ $reply->id }}">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                                            </svg>
                                                            <span class="comment-like-count">{{ $reply->likes_count }}</span>
                                                        </button>
                                                        @auth
                                                            @if (Auth::user()->id === $reply->user_id)
                                                                <button class="comment-action delete-comment-btn" data-comment-id="{{ $reply->id }}" style="color: #e74c3c;">Xóa</button>
                                                            @endif
                                                        @endauth
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @empty
                                <p style="text-align: center; color: #999; padding: 20px 0;">Chưa có bình luận. Hãy là người đầu tiên!</p>
                            @endforelse
                        </div>

                        @if ($video->allComments()->count() > 5)
                            <button class="btn btn-outline btn-block" id="loadMoreComments">Xem thêm bình luận</button>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="video-sidebar">
                    <!-- Instructor Card -->
                    <div class="sidebar-card instructor-card">
                         <h3 class="sidebar-card-title">Giảng viên</h3>
                         @if ($video->instructor)
                             @php
                                 $name = $video->instructor->name ?? '';
                                 $initials = '';
                                 foreach (explode(' ', trim($name)) as $word) {
                                     if (!empty($word)) {
                                         $initials .= strtoupper(mb_substr($word, 0, 1));
                                     }
                                 }
                                 $initials = $initials ?: 'I';
                                 $colors = ['#FF8E53', '#00D9B5', '#9D84B7', '#FFC93C', '#FF6B6B'];
                                 $colorIndex = (ord($initials[0]) + strlen($initials)) % count($colors);
                                 $bgColor = $colors[$colorIndex];
                             @endphp
                             <div class="instructor-profile">
                                 @if ($video->instructor->image)
                                     <img src="{{ asset('storage/' . $video->instructor->image) }}" alt="{{ $video->instructor->name }}" class="instructor-avatar-lg" style="object-fit: cover;">
                                 @else
                                     <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 80 80'%3E%3Ccircle cx='40' cy='40' r='40' fill='{{ urlencode($bgColor) }}'/%3E%3Ctext x='40' y='48' font-size='28' text-anchor='middle' fill='white'%3E{{ $initials }}%3C/text%3E%3C/svg%3E"
                                          alt="{{ $video->instructor->name }}" class="instructor-avatar-lg">
                                 @endif
                                 <div class="instructor-details">
                                     <h4>Coach {{ $video->instructor->name }}</h4>
                                     <p>{{ $video->instructor->experience ?? 'Huấn luyện viên chuyên nghiệp' }}</p>
                                     <div class="instructor-stats">
                                         <span>
                                             <svg viewBox="0 0 24 24" fill="currentColor">
                                                 <polygon
                                                     points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                             </svg>
                                             {{ number_format($video->instructor->rating ?? 5, 1) }}
                                         </span>
                                         <span>{{ $video->instructor->student_count ?? 0 }} học viên</span>
                                         <span>{{ $video->instructor->reviews_count ?? 0 }} đánh giá</span>
                                     </div>
                                 </div>
                             </div>
                             <a href="#" class="btn btn-outline btn-sm btn-block">Xem hồ sơ</a>
                         @else
                             <div class="instructor-profile">
                                 <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 80 80'%3E%3Ccircle cx='40' cy='40' r='40' fill='%23CCCCCC'/%3E%3Ctext x='40' y='48' font-size='28' text-anchor='middle' fill='white'%3E%3F%3C/text%3E%3C/svg%3E"
                                     alt="Không có giảng viên" class="instructor-avatar-lg">
                                 <div class="instructor-details">
                                     <h4>Chưa có giảng viên</h4>
                                     <p>Đang cập nhật thông tin</p>
                                     <div class="instructor-stats">
                                         <span>Chưa có dữ liệu</span>
                                     </div>
                                 </div>
                             </div>
                         @endif
                     </div>

                    <!-- Related Videos -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">Video liên quan</h3>
                        <div class="related-videos-list">
                            @forelse ($relatedVideos as $relatedVideo)
                                <a href="{{ route('course.detail', $relatedVideo->id) }}" class="related-video-item">
                                    <div class="related-thumbnail">
                                        <img src="{{ $relatedVideo->image ? asset('storage/' . $relatedVideo->image) : 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 160 90%27%3E%3Cdefs%3E%3ClinearGradient id=%27rv1%27 x1=%270%25%27 y1=%270%25%27 x2=%27100%25%27 y2=%27100%25%27%3E%3Cstop offset=%270%25%27 style=%27stop-color:%23FF8E53;stop-opacity:1%27 /%3E%3Cstop offset=%27100%25%27 style=%27stop-color:%23FE6B8B;stop-opacity:1%27 /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill=%27url(%23rv1)%27 width=%27160%27 height=%2790%27/%3E%3Cpolygon points=%2770,45 90,32 90,58%27 fill=%27rgba(255,255,255,0.9)%27/%3E%3C/svg%3E' }}" alt="{{ $relatedVideo->name }}">
                                        <span class="duration">{{ $relatedVideo->duration ?? '0:00' }}</span>
                                    </div>
                                    <div class="related-info">
                                        <h4>{{ $relatedVideo->name }}</h4>
                                        <span class="related-meta">{{ $relatedVideo->views_count ? number_format($relatedVideo->views_count) : '0' }} lượt xem • {{ $relatedVideo->created_at->diffForHumans() }}</span>
                                    </div>
                                </a>
                            @empty
                                <p style="text-align: center; color: #999; padding: 20px 0;">Không có video liên quan</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Course Series -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">Khóa học cùng series</h3>
                        <div class="series-info">
                            <span class="series-name">{{ $video->category->name }}</span>
                            <span class="series-progress">Video 1/{{ count($relatedVideos) + 1 }}</span>
                        </div>
                        <div class="series-list">
                            <a href="#" class="series-item active">
                                <span class="series-number">1</span>
                                <span class="series-title">{{ $video->name }}</span>
                                <span class="series-check">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                                    </svg>
                                </span>
                            </a>
                            @foreach ($relatedVideos as $index => $relatedVideo)
                                <a href="{{ route('course.detail', $relatedVideo->id) }}" class="series-item">
                                    <span class="series-number">{{ $index + 2 }}</span>
                                    <span class="series-title">{{ $relatedVideo->name }}</span>
                                </a>
                            @endforeach
                        </div>
                        <button class="btn btn-primary btn-sm btn-block">Mở khóa toàn bộ</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        // Expand description
        const expandBtn = document.getElementById('expandBtn');
        const descriptionContent = document.getElementById('descriptionContent');

        expandBtn?.addEventListener('click', () => {
            descriptionContent.classList.toggle('expanded');
            const isExpanded = descriptionContent.classList.contains('expanded');
            expandBtn.querySelector('span').textContent = isExpanded ? 'Thu gọn' : 'Xem thêm';
            expandBtn.querySelector('svg').style.transform = isExpanded ? 'rotate(180deg)' : 'rotate(0)';
        });

        // Chapter click
        const chapterItems = document.querySelectorAll('.chapter-item');
        chapterItems.forEach(item => {
            item.addEventListener('click', () => {
                chapterItems.forEach(c => c.classList.remove('active'));
                item.classList.add('active');
            });
        });

        // Like Video
        const likeVideoBtn = document.getElementById('likeVideoBtn');
        if (likeVideoBtn) {
            likeVideoBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch(`/api/videos/${likeVideoBtn.dataset.videoId}/like`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        likeVideoBtn.classList.toggle('active');
                        document.querySelector('.like-count').textContent = data.likes_count;
                    } else if (data.message) {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra');
                }
            });
        }

        // Submit Comment
        const submitCommentBtn = document.getElementById('submitCommentBtn');
        if (submitCommentBtn) {
            submitCommentBtn.addEventListener('click', async () => {
                const commentInput = document.getElementById('commentInput');
                const content = commentInput.value.trim();
                
                if (!content) {
                    alert('Vui lòng nhập nội dung bình luận');
                    return;
                }

                try {
                    const response = await fetch(`/api/videos/${submitCommentBtn.dataset.videoId}/comments`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ content })
                    });
                    const data = await response.json();
                    if (data.success) {
                        commentInput.value = '';
                        location.reload();
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        }

        // Like Comment
        document.querySelectorAll('.like-comment-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                try {
                    const response = await fetch(`/api/comments/${btn.dataset.commentId}/like`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        btn.classList.toggle('active');
                        btn.querySelector('.comment-like-count').textContent = data.likes_count;
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });

        // Delete Comment
        document.querySelectorAll('.delete-comment-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn chắc chắn muốn xóa bình luận này?')) return;
                
                try {
                    const response = await fetch(`/api/comments/${btn.dataset.commentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        location.reload();
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });

        // Reply to Comment
        document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const commentId = btn.dataset.commentId;
                const repliesContainer = document.getElementById(`replies-${commentId}`);
                const commentItem = btn.closest('.comment-item');
                
                // Check if reply form already exists
                if (commentItem.nextElementSibling?.classList.contains('reply-form')) {
                    commentItem.nextElementSibling.remove();
                    return;
                }
                
                const replyForm = document.createElement('div');
                replyForm.className = 'reply-form';
                replyForm.innerHTML = `
                    <div class="comment-form" style="margin-left: 48px; margin-top: 10px;">
                        <div class="comment-input-wrapper" style="display: flex; gap: 10px;">
                            <textarea placeholder="Viết trả lời..." class="comment-input reply-input" rows="2" style="flex: 1;"></textarea>
                            <div style="display: flex; gap: 5px; flex-direction: column;">
                                <button class="btn btn-primary btn-sm submit-reply-btn" data-comment-id="${commentId}">Gửi</button>
                                <button class="btn btn-outline btn-sm cancel-reply-btn">Hủy</button>
                            </div>
                        </div>
                    </div>
                `;
                
                commentItem.after(replyForm);
                replyForm.querySelector('.reply-input').focus();
                
                // Cancel button
                replyForm.querySelector('.cancel-reply-btn').addEventListener('click', () => {
                    replyForm.remove();
                });
                
                // Submit reply
                replyForm.querySelector('.submit-reply-btn').addEventListener('click', async () => {
                    const content = replyForm.querySelector('.reply-input').value.trim();
                    if (!content) {
                        alert('Vui lòng nhập nội dung trả lời');
                        return;
                    }
                    
                    try {
                        const videoId = document.querySelector('[data-video-id]')?.dataset.videoId || '{{ $video->id }}';
                        const response = await fetch(`/api/videos/${videoId}/comments`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ content, parent_id: commentId })
                        });
                        const data = await response.json();
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Có lỗi xảy ra');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi gửi trả lời');
                    }
                });
            });
        });

        // Share Video
        const shareVideoBtn = document.getElementById('shareVideoBtn');
        if (shareVideoBtn) {
            shareVideoBtn.addEventListener('click', async () => {
                const videoUrl = shareVideoBtn.dataset.videoUrl;
                const videoTitle = shareVideoBtn.dataset.videoTitle;
                
                // Check if Web Share API is available
                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: videoTitle,
                            url: videoUrl
                        });
                    } catch (error) {
                        console.log('Share cancelled:', error);
                    }
                } else {
                    // Fallback: Show share options
                    const shareText = `${videoTitle}\n${videoUrl}`;
                    const encodedText = encodeURIComponent(shareText);
                    const encodedUrl = encodeURIComponent(videoUrl);
                    
                    const shareOptions = `
                        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 9999; min-width: 300px;">
                            <h3 style="margin-top: 0; text-align: center;">Chia sẻ video</h3>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-bottom: 20px;">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}" target="_blank" style="padding: 8px 15px; background: #1877F2; color: white; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedText}" target="_blank" style="padding: 8px 15px; background: #1DA1F2; color: white; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 002.856-3.51 10 10 0 01-2.856 1.1 4.993 4.993 0 00-8.668 4.56 14.16 14.16 0 01-10.29-5.16 4.993 4.993 0 001.548 6.66 4.991 4.991 0 01-2.26-.616v.06a4.993 4.993 0 003.996 4.897 4.993 4.993 0 01-2.252.085 4.994 4.994 0 004.644 3.461 10.01 10.01 0 01-6.177 2.13c-.397 0-.79-.023-1.177-.067a14.16 14.16 0 007.666 2.245c9.2 0 14.207-7.617 14.207-14.207 0-.216-.005-.433-.015-.649a10.016 10.016 0 002.457-2.548z"/></svg>
                                    Twitter
                                </a>
                                <a href="https://api.whatsapp.com/send?text=${encodedText}" target="_blank" style="padding: 8px 15px; background: #25D366; color: white; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-9.746 9.798c0 2.734.732 5.41 2.124 7.758L.855 23.87a.5.5 0 00.632.632l7.314-1.92a9.87 9.87 0 007.764 2.087h.005A9.87 9.87 0 0023.477 12.02 9.86 9.86 0 0012.051 2.98"/></svg>
                                    WhatsApp
                                </a>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Sao chép link:</label>
                                <div style="display: flex; gap: 5px;">
                                    <input type="text" value="${videoUrl}" readonly style="flex: 1; padding: 8px 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 12px;">
                                    <button id="copyLinkBtn" style="padding: 8px 15px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer;">Sao chép</button>
                                </div>
                            </div>
                            <button id="closeShareModal" style="width: 100%; padding: 10px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; font-weight: 500;">Đóng</button>
                        </div>
                        <div id="shareOverlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9998;"></div>
                    `;
                    
                    const shareContainer = document.createElement('div');
                    shareContainer.innerHTML = shareOptions;
                    document.body.appendChild(shareContainer);
                    
                    // Copy link functionality
                    document.getElementById('copyLinkBtn').addEventListener('click', () => {
                        const input = shareContainer.querySelector('input[type="text"]');
                        input.select();
                        document.execCommand('copy');
                        document.getElementById('copyLinkBtn').textContent = 'Đã sao chép!';
                        setTimeout(() => {
                            document.getElementById('copyLinkBtn').textContent = 'Sao chép';
                        }, 2000);
                    });
                    
                    // Close modal
                    document.getElementById('closeShareModal').addEventListener('click', () => {
                        shareContainer.remove();
                    });
                    
                    document.getElementById('shareOverlay').addEventListener('click', () => {
                        shareContainer.remove();
                    });
                }
            });
        }
    </script>
@endsection
