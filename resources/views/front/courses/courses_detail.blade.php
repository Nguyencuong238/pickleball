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
                            <span class="video-level-badge beginner">Người mới</span>
                            <span class="video-duration-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                25:30
                            </span>
                        </div>

                        <h1 class="video-detail-title">{{ $video->name }}</h1>

                        <div class="video-detail-meta">
                            <div class="meta-left">
                                <div class="instructor-info">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Ccircle cx='24' cy='24' r='24' fill='%2300D9B5'/%3E%3Ctext x='24' y='30' font-size='18' text-anchor='middle' fill='white'%3ENH%3C/text%3E%3C/svg%3E"
                                        alt="Coach">
                                    <div class="instructor-text">
                                        <span class="instructor-name">Coach Nguyễn Văn Hùng</span>
                                        <span class="instructor-title">Huấn luyện viên chuyên nghiệp</span>
                                    </div>
                                </div>
                                <div class="video-stats-detail">
                                    <span class="stat">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        12,543 lượt xem
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
                                    <div class="stars">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                    </div>
                                    <span class="rating-text">4.9 (234 đánh giá)</span>
                                </div>
                            </div>
                        </div>

                        <div class="video-actions">
                            <button class="action-btn like">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                </svg>
                                <span>1,234</span>
                            </button>
                            <button class="action-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
                                </svg>
                                <span>Lưu</span>
                            </button>
                            <button class="action-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="18" cy="5" r="3" />
                                    <circle cx="6" cy="12" r="3" />
                                    <circle cx="18" cy="19" r="3" />
                                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                                </svg>
                                <span>Chia sẻ</span>
                            </button>
                            <button class="action-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                <span>Tải xuống</span>
                            </button>
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
                            <button class="chapter-item active" data-time="0">
                                <span class="chapter-time">00:00</span>
                                <span class="chapter-title">Giới thiệu về Pickleball</span>
                                <span class="chapter-duration">3:00</span>
                            </button>
                            <button class="chapter-item" data-time="180">
                                <span class="chapter-time">03:00</span>
                                <span class="chapter-title">Giới thiệu dụng cụ</span>
                                <span class="chapter-duration">3:30</span>
                            </button>
                            <button class="chapter-item" data-time="390">
                                <span class="chapter-time">06:30</span>
                                <span class="chapter-title">Luật chơi cơ bản</span>
                                <span class="chapter-duration">3:30</span>
                            </button>
                            <button class="chapter-item" data-time="600">
                                <span class="chapter-time">10:00</span>
                                <span class="chapter-title">Cách cầm vợt đúng chuẩn</span>
                                <span class="chapter-duration">4:00</span>
                            </button>
                            <button class="chapter-item" data-time="840">
                                <span class="chapter-time">14:00</span>
                                <span class="chapter-title">Tư thế đứng và di chuyển</span>
                                <span class="chapter-duration">4:00</span>
                            </button>
                            <button class="chapter-item" data-time="1080">
                                <span class="chapter-time">18:00</span>
                                <span class="chapter-title">Kỹ thuật giao bóng (Serve)</span>
                                <span class="chapter-duration">4:00</span>
                            </button>
                            <button class="chapter-item" data-time="1320">
                                <span class="chapter-time">22:00</span>
                                <span class="chapter-title">Luyện tập và tổng kết</span>
                                <span class="chapter-duration">3:30</span>
                            </button>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="comments-section">
                        <h3 class="card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            Bình luận (89)
                        </h3>

                        <!-- Comment Form -->
                        <div class="comment-form">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23E5E5E5'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='%23999'%3EU%3C/text%3E%3C/svg%3E"
                                alt="User" class="comment-avatar">
                            <div class="comment-input-wrapper">
                                <input type="text" placeholder="Viết bình luận..." class="comment-input">
                                <button class="btn btn-primary btn-sm">Gửi</button>
                            </div>
                        </div>

                        <!-- Comments List -->
                        <div class="comments-list">
                            <div class="comment-item">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23FF8E53'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3ETM%3C/text%3E%3C/svg%3E"
                                    alt="Trần Minh" class="comment-avatar">
                                <div class="comment-content">
                                    <div class="comment-header">
                                        <span class="comment-author">Trần Minh</span>
                                        <span class="comment-date">2 ngày trước</span>
                                    </div>
                                    <p class="comment-text">Video rất hay và dễ hiểu! Mình mới bắt đầu chơi pickleball được
                                        1 tuần, nhờ video này mà đã biết cách cầm vợt đúng rồi. Cảm ơn coach!</p>
                                    <div class="comment-actions">
                                        <button class="comment-action">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path
                                                    d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                            </svg>
                                            24
                                        </button>
                                        <button class="comment-action">Trả lời</button>
                                    </div>
                                </div>
                            </div>

                            <div class="comment-item">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%2300D9B5'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3ENL%3C/text%3E%3C/svg%3E"
                                    alt="Ngọc Linh" class="comment-avatar">
                                <div class="comment-content">
                                    <div class="comment-header">
                                        <span class="comment-author">Ngọc Linh</span>
                                        <span class="comment-date">5 ngày trước</span>
                                    </div>
                                    <p class="comment-text">Coach giải thích luật chơi rất chi tiết, đặc biệt là phần
                                        Kitchen zone. Mình hay bị lỗi phần này, giờ đã hiểu rồi 👍</p>
                                    <div class="comment-actions">
                                        <button class="comment-action">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path
                                                    d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                            </svg>
                                            18
                                        </button>
                                        <button class="comment-action">Trả lời</button>
                                    </div>

                                    <!-- Reply -->
                                    <div class="comment-replies">
                                        <div class="comment-item reply">
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%2300D9B5'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3ENH%3C/text%3E%3C/svg%3E"
                                                alt="Coach Nguyễn Hùng" class="comment-avatar">
                                            <div class="comment-content">
                                                <div class="comment-header">
                                                    <span class="comment-author">Coach Nguyễn Hùng</span>
                                                    <span class="author-badge">Tác giả</span>
                                                    <span class="comment-date">4 ngày trước</span>
                                                </div>
                                                <p class="comment-text">Cảm ơn bạn đã xem! Kitchen zone là phần nhiều người
                                                    hay nhầm lẫn nhất. Mình sẽ làm thêm video chi tiết về luật Kitchen nhé!
                                                </p>
                                                <div class="comment-actions">
                                                    <button class="comment-action">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2">
                                                            <path
                                                                d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                                        </svg>
                                                        32
                                                    </button>
                                                    <button class="comment-action">Trả lời</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="comment-item">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%239D84B7'/%3E%3Ctext x='20' y='25' font-size='14' text-anchor='middle' fill='white'%3ELH%3C/text%3E%3C/svg%3E"
                                    alt="Lê Hoàng" class="comment-avatar">
                                <div class="comment-content">
                                    <div class="comment-header">
                                        <span class="comment-author">Lê Hoàng</span>
                                        <span class="comment-date">1 tuần trước</span>
                                    </div>
                                    <p class="comment-text">Xin hỏi coach, khi nào sẽ có video về kỹ thuật Dink ạ? Mình
                                        thấy đây là kỹ thuật rất quan trọng trong Doubles.</p>
                                    <div class="comment-actions">
                                        <button class="comment-action">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path
                                                    d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
                                            </svg>
                                            12
                                        </button>
                                        <button class="comment-action">Trả lời</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-outline btn-block">Xem thêm bình luận</button>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="video-sidebar">
                    <!-- Instructor Card -->
                    <div class="sidebar-card instructor-card">
                        <h3 class="sidebar-card-title">Giảng viên</h3>
                        <div class="instructor-profile">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 80 80'%3E%3Ccircle cx='40' cy='40' r='40' fill='%2300D9B5'/%3E%3Ctext x='40' y='48' font-size='28' text-anchor='middle' fill='white'%3ENH%3C/text%3E%3C/svg%3E"
                                alt="Coach Nguyễn Hùng" class="instructor-avatar-lg">
                            <div class="instructor-details">
                                <h4>Coach Nguyễn Văn Hùng</h4>
                                <p>8 năm kinh nghiệm</p>
                                <div class="instructor-stats">
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <polygon
                                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                        4.9
                                    </span>
                                    <span>32 video</span>
                                    <span>156 học viên</span>
                                </div>
                            </div>
                        </div>
                        <a href="coach-detail.html" class="btn btn-outline btn-sm btn-block">Xem hồ sơ</a>
                    </div>

                    <!-- Related Videos -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">Video liên quan</h3>
                        <div class="related-videos-list">
                            @forelse ($relatedVideos as $relatedVideo)
                                <a href="{{ route('course.detail', $relatedVideo->id) }}" class="related-video-item">
                                    <div class="related-thumbnail">
                                        @if ($relatedVideo->image)
                                            <img src="{{ asset($relatedVideo->image) }}" alt="{{ $relatedVideo->name }}">
                                        @else
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 160 90'%3E%3Cdefs%3E%3ClinearGradient id='rv1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FF8E53;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23FE6B8B;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23rv1)' width='160' height='90'/%3E%3Cpolygon points='70,45 90,32 90,58' fill='rgba(255,255,255,0.9)'/%3E%3C/svg%3E"
                                                alt="{{ $relatedVideo->name }}">
                                        @endif
                                        <span class="duration">18:45</span>
                                    </div>
                                    <div class="related-info">
                                        <h4>{{ $relatedVideo->name }}</h4>
                                        <span class="related-meta">8.2K lượt xem • {{ $relatedVideo->created_at->diffForHumans() }}</span>
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
                // In real implementation, this would seek the video to the timestamp
            });
        });

        // Like button
        const likeBtn = document.querySelector('.action-btn.like');
        likeBtn?.addEventListener('click', () => {
            likeBtn.classList.toggle('active');
        });
    </script>
@endsection
