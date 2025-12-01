@extends('layouts.front')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles-coaches.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/instructor-review.css') }}">
@endsection

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
                <a href="{{ route('instructors') }}">Giảng viên</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
                <span>{{ $instructor->name }}</span>
            </nav>
        </div>
    </section>

    <!-- Coach Profile Header -->
    <section class="coach-profile-header">
        <div class="container">
            <div class="profile-header-wrapper">
                <!-- Coach Avatar & Basic Info -->
                <div class="coach-avatar-section">
                    <div class="coach-avatar-large">
                        @if ($instructor->image)
                            <img src="{{ asset('storage/' . $instructor->image) }}" alt="{{ $instructor->name }}">
                        @else
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 400'%3E%3Cdefs%3E%3ClinearGradient id='gProfile' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23gProfile)' width='400' height='400'/%3E%3Ccircle cx='200' cy='150' r='80' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='200' cy='320' rx='100' ry='80' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E"
                                alt="{{ $instructor->name }}">
                        @endif
                        <div class="online-status">
                            <span class="status-dot"></span>
                            Online
                        </div>
                    </div>
                    <div class="coach-badges-vertical">
                        <span class="badge-item verified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                            </svg>
                            Đã xác minh
                        </span>
                        <span class="badge-item certified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2L15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2z" />
                            </svg>
                            Có chứng chỉ
                        </span>
                    </div>
                </div>

                <!-- Coach Info -->
                <div class="coach-info-section">
                    <div class="coach-header-top">
                        <h1 class="coach-profile-name">{{ $instructor->name }}</h1>
                        <div class="coach-rating-large">
                            <div class="stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="rating-number">{{ number_format($instructor->rating ?? 5, 1) }}</span>
                            <span class="rating-count">({{ $instructor->reviews_count ?? 0 }} đánh giá)</span>
                        </div>
                    </div>

                    <p class="coach-tagline">{{ $instructor->bio ?? 'Huấn luyện viên Pickleball chuyên nghiệp' }}</p>

                    <div class="coach-quick-info">
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                            </svg>
                            <span><strong>{{ $instructor->experience_years ?? 0 }}</strong> năm kinh nghiệm</span>
                        </div>
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>{{ $instructor->province->name ?? 'Hà Nội' }}</span>
                        </div>
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span><strong>{{ $instructor->student_count ?? 0 }}</strong> học viên</span>
                        </div>
                    </div>

                    <div class="coach-specialty-tags">
                        @php
                            $specialties = is_array($instructor->specialties) ? $instructor->specialties : json_decode($instructor->specialties ?? '[]', true);
                        @endphp
                        @forelse($specialties as $specialty)
                            <span class="specialty-tag">{{ $specialty }}</span>
                        @empty
                            <span class="specialty-tag">Dạy 1-1</span>
                            <span class="specialty-tag">Dạy nhóm</span>
                        @endforelse
                    </div>

                    <div class="coach-actions-header">
                        <button class="btn btn-primary btn-lg" id="contactBtn">
                             <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                 <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                             </svg>
                             Liên hệ ngay
                        </button>
                        <button class="btn btn-outline btn-icon btn-favorite" title="Yêu thích" data-instructor-id="{{ $instructor->id }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                        </button>
                        <button class="btn btn-outline btn-icon btn-share" title="Chia sẻ">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="18" cy="5" r="3" />
                                <circle cx="6" cy="12" r="3" />
                                <circle cx="18" cy="19" r="3" />
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="coach-stats-sidebar">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $instructor->student_count ?? 0 }}</span>
                            <span class="stat-label">Học viên</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $instructor->reviews_count ?? 0 }}</span>
                            <span class="stat-label">Đánh giá</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $instructor->total_hours ?? 0 }}+</span>
                            <span class="stat-label">Giờ dạy</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Coach Detail Content -->
    <section class="coach-detail-content section">
        <div class="container">
            <div class="detail-layout">
                <!-- Main Content -->
                <div class="detail-main">
                    <!-- About Section -->
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Giới thiệu
                        </h2>
                        <div class="about-content">
                            {!! nl2br($instructor->description ?? 'Giảng viên Pickleball chuyên nghiệp') !!}
                        </div>
                    </div>

                    <!-- Teaching Experience -->
                     <div class="detail-card">
                         <h2 class="detail-card-title">
                             <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                 <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                             </svg>
                             Kinh nghiệm giảng dạy
                         </h2>
                         <div class="experience-timeline">
                             @forelse($instructor->experiences as $experience)
                                 <div class="timeline-item">
                                     <div class="timeline-marker"></div>
                                     <div class="timeline-content">
                                         <h4>{{ $experience->title }}</h4>
                                         <span class="timeline-date">{{ $experience->start_year }} - {{ $experience->end_year ?? 'Hiện tại' }}</span>
                                         <p>{{ $experience->description }}</p>
                                     </div>
                                 </div>
                             @empty
                                 <p>Chưa có dữ liệu kinh nghiệm</p>
                             @endforelse
                         </div>
                     </div>

                    <!-- Certifications -->
                     <div class="detail-card">
                         <h2 class="detail-card-title">
                             <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                 <circle cx="12" cy="8" r="7" />
                                 <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" />
                             </svg>
                             Chứng chỉ & Thành tích
                         </h2>
                         <div class="certifications-grid">
                             @forelse($instructor->certifications as $cert)
                                 <div class="cert-item">
                                     <div class="cert-icon {{ $cert->is_award ? 'trophy' : '' }}">
                                         <svg viewBox="0 0 24 24" fill="currentColor">
                                             @if($cert->is_award)
                                                 <path d="M8 21h8m-4-4v4M6 4h12m-6 8a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-8 0h2a2 2 0 0 0 2-2V4H2v6a2 2 0 0 0 2 2zm16 0h2a2 2 0 0 0 2-2V4h-4v6a2 2 0 0 0 2 2z" />
                                             @else
                                                 <path d="M12 2L15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2z" />
                                             @endif
                                         </svg>
                                     </div>
                                     <div class="cert-info">
                                         <h4>{{ $cert->title }}</h4>
                                         <p>{{ $cert->issuer }}</p>
                                         <span class="cert-year">{{ $cert->year }}</span>
                                     </div>
                                 </div>
                             @empty
                                 <p>Chưa có dữ liệu chứng chỉ</p>
                             @endforelse
                         </div>
                     </div>

                    <!-- Teaching Methods -->
                     <div class="detail-card">
                         <h2 class="detail-card-title">
                             <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                 <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                 <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                             </svg>
                             Phương pháp giảng dạy
                         </h2>
                         <div class="methods-grid">
                             @forelse($instructor->teachingMethods as $method)
                                 <div class="method-item">
                                     <div class="method-icon">
                                         <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                             <circle cx="12" cy="12" r="10" />
                                             <path d="M12 16v-4M12 8h.01" />
                                         </svg>
                                     </div>
                                     <h4>{{ $method->title }}</h4>
                                     <p>{{ $method->description }}</p>
                                 </div>
                             @empty
                                 <p>Chưa có dữ liệu phương pháp giảng dạy</p>
                             @endforelse
                         </div>
                     </div>

                    <!-- Reviews -->
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            Đánh giá từ học viên
                            <span class="review-count">(89 đánh giá)</span>
                        </h2>

                        <!-- Rating Summary -->
                        <div class="rating-summary">
                            <div class="rating-overview">
                                <div class="rating-big">{{ number_format($instructor->rating ?? 5, 1) }}</div>
                                <div class="rating-stars">
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span>{{ $instructor->reviews_count ?? 0 }} đánh giá</span>
                                </div>
                            </div>
                            <div class="rating-bars">
                                @php
                                    $ratingStats = $instructor->reviews->groupBy('rating')->map->count();
                                    $totalReviews = $instructor->reviews_count ?? 1;
                                @endphp
                                @for($star = 5; $star >= 1; $star--)
                                    @php
                                        $count = $ratingStats[$star] ?? 0;
                                        $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                                    @endphp
                                    <div class="rating-bar-item">
                                        <span>{{ $star }} sao</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span>{{ $count }}</span>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Reviews List -->
                        <div class="reviews-list">
                            @forelse($instructor->reviews->take(3) as $review)
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="reviewer-avatar">
                                            @if($review->user && $review->user->avatar)
                                                <img src="{{ asset('storage/' . $review->user->avatar) }}" alt="{{ $review->user->name }}">
                                            @else
                                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%23FF8E53'/%3E%3Ctext x='50' y='55' font-size='40' text-anchor='middle' fill='white' dominant-baseline='middle'%3E{{ substr($review->user->name ?? 'U', 0, 2) }}%3C/text%3E%3C/svg%3E"
                                                    alt="{{ $review->user->name ?? 'User' }}">
                                            @endif
                                        </div>
                                        <div class="reviewer-info">
                                            <h4>{{ $review->user->name ?? 'Ẩn danh' }}</h4>
                                            <div class="review-meta">
                                                <div class="stars small">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg viewBox="0 0 24 24" fill="currentColor" style="opacity: {{ $i <= $review->rating ? 1 : 0.3 }}">
                                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                        </svg>
                                                    @endfor
                                                </div>
                                                <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="review-content">{{ $review->content }}</p>
                                    @if($review->tags && count($review->tags) > 0)
                                        <div class="review-tags">
                                            @foreach($review->tags as $tag)
                                                <span class="review-tag">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <!-- Edit/Delete buttons for own reviews -->
                                    @if(auth()->check() && auth()->user()->id === $review->user_id)
                                        <div class="review-actions" style="margin-top: 12px; display: flex; gap: 8px;">
                                            <button class="btn-edit-review btn-sm" data-review-id="{{ $review->id }}" data-rating="{{ $review->rating }}" data-content="{{ $review->content }}" data-tags="{{ implode(',', $review->tags ?? []) }}" style="padding: 6px 12px; font-size: 13px; background-color: #00D9B5; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 4px;">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                                Sửa
                                            </button>
                                            <button class="btn-delete-review btn-sm" data-review-id="{{ $review->id }}" style="padding: 6px 12px; font-size: 13px; background-color: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 4px;">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                </svg>
                                                Xóa
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p>Chưa có đánh giá nào</p>
                            @endforelse
                        </div>

                        <button class="btn btn-outline btn-block">Xem tất cả đánh giá</button>

                        <!-- Add Review Form -->
                        <div class="review-form-section" style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #e5e5e5;">
                            <h3 class="detail-card-title" style="margin-bottom: 20px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                                Để lại đánh giá của bạn
                            </h3>

                            <form id="reviewForm" class="review-form">
                                @csrf
                                <input type="hidden" name="instructor_id" value="{{ $instructor->id }}">

                                <!-- Rating Stars -->
                                <div class="form-group">
                                    <label>Chất lượng giảng dạy *</label>
                                    <div class="star-rating-input" id="starRating">
                                        @for ($i = 5; $i >= 1; $i--)
                                            <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}">
                                            <label for="star{{ $i }}">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                            </label>
                                        @endfor
                                    </div>
                                    <span id="ratingText" style="margin-top: 10px; font-size: 14px; color: #666;"></span>
                                </div>

                                <!-- Review Content -->
                                <div class="form-group">
                                    <label for="reviewContent">Nội dung đánh giá (tùy chọn)</label>
                                    <textarea id="reviewContent" name="content" class="form-control" 
                                        placeholder="Chia sẻ trải nghiệm của bạn với giảng viên này..." 
                                        rows="4" maxlength="1000"></textarea>
                                    <div style="font-size: 12px; color: #999; margin-top: 5px;">
                                        <span id="charCount">0</span>/1000 ký tự
                                    </div>
                                </div>

                                <!-- Tags/Keywords -->
                                <div class="form-group">
                                    <label>Điểm mạnh của giảng viên (tùy chọn)</label>
                                    <div class="tags-checkbox">
                                        <label class="tag-option">
                                            <input type="checkbox" name="tags" value="Thân thiện">
                                            <span>Thân thiện</span>
                                        </label>
                                        <label class="tag-option">
                                            <input type="checkbox" name="tags" value="Chuyên nghiệp">
                                            <span>Chuyên nghiệp</span>
                                        </label>
                                        <label class="tag-option">
                                            <input type="checkbox" name="tags" value="Kiên nhẫn">
                                            <span>Kiên nhẫn</span>
                                        </label>
                                        <label class="tag-option">
                                            <input type="checkbox" name="tags" value="Dễ hiểu">
                                            <span>Dễ hiểu</span>
                                        </label>
                                        <label class="tag-option">
                                            <input type="checkbox" name="tags" value="Truyền cảm hứng">
                                            <span>Truyền cảm hứng</span>
                                        </label>
                                        <label class="tag-option">
                                            <input type="checkbox" name="tags" value="Linh hoạt">
                                            <span>Linh hoạt</span>
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 20px;">
                                    Gửi đánh giá
                                </button>
                            </form>
                            </div>
                            </div>
                            </div>

                            <!-- Edit Review Modal -->
                            <div id="editReviewModal" class="modal" style="display: none;">
                            <div class="modal-overlay"></div>
                            <div class="modal-content" style="width: 95%; max-width: 600px;">
                            <div class="modal-header">
                             <h3>Sửa đánh giá</h3>
                             <button class="modal-close" id="closeEditModal">&times;</button>
                            </div>
                            <form id="editReviewForm" class="review-form" style="padding: 30px;">
                             @csrf
                             <input type="hidden" name="review_id" id="editReviewId">
                             <input type="hidden" name="_method" value="PUT">

                             <!-- Rating Stars -->
                             <div class="form-group">
                                 <label>Chất lượng giảng dạy *</label>
                                 <div class="star-rating-input" id="editStarRating">
                                     @for ($i = 5; $i >= 1; $i--)
                                         <input type="radio" name="rating" value="{{ $i }}" id="editStar{{ $i }}">
                                         <label for="editStar{{ $i }}">
                                             <svg viewBox="0 0 24 24" fill="currentColor">
                                                 <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                             </svg>
                                         </label>
                                     @endfor
                                 </div>
                                 <span id="editRatingText" style="margin-top: 10px; font-size: 14px; color: #666;"></span>
                             </div>

                             <!-- Review Content -->
                             <div class="form-group">
                                 <label for="editReviewContent">Nội dung đánh giá</label>
                                 <textarea id="editReviewContent" name="content" class="form-control" 
                                     placeholder="Chia sẻ trải nghiệm của bạn..." 
                                     rows="4" maxlength="1000"></textarea>
                                 <div style="font-size: 12px; color: #999; margin-top: 5px;">
                                     <span id="editCharCount">0</span>/1000 ký tự
                                 </div>
                             </div>

                             <!-- Tags -->
                             <div class="form-group">
                                 <label>Điểm mạnh của giảng viên</label>
                                 <div class="tags-checkbox">
                                     <label class="tag-option">
                                         <input type="checkbox" name="tags" value="Thân thiện">
                                         <span>Thân thiện</span>
                                     </label>
                                     <label class="tag-option">
                                         <input type="checkbox" name="tags" value="Chuyên nghiệp">
                                         <span>Chuyên nghiệp</span>
                                     </label>
                                     <label class="tag-option">
                                         <input type="checkbox" name="tags" value="Kiên nhẫn">
                                         <span>Kiên nhẫn</span>
                                     </label>
                                     <label class="tag-option">
                                         <input type="checkbox" name="tags" value="Dễ hiểu">
                                         <span>Dễ hiểu</span>
                                     </label>
                                     <label class="tag-option">
                                         <input type="checkbox" name="tags" value="Truyền cảm hứng">
                                         <span>Truyền cảm hứng</span>
                                     </label>
                                     <label class="tag-option">
                                         <input type="checkbox" name="tags" value="Linh hoạt">
                                         <span>Linh hoạt</span>
                                     </label>
                                 </div>
                             </div>

                             <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 20px;">
                                 Cập nhật đánh giá
                             </button>
                            </form>
                            </div>
                            </div>

                            <!-- Sidebar -->
                <div class="detail-sidebar">
                    <!-- Booking Card -->
                    <div class="sidebar-card booking-card">
                        <h3 class="sidebar-card-title">Đăng ký học</h3>
                        <div class="price-display">
                            <span class="price-label">Học phí từ</span>
                            <span
                                class="price-value">{{ number_format($instructor->price_per_session ?? 500000, 0, ',', '.') }}đ</span>
                            <span class="price-unit">/ buổi</span>
                        </div>
                        <div class="package-options">
                            @forelse($instructor->packages as $package)
                                <label class="package-option {{ $loop->first ? 'popular' : '' }}">
                                    <input type="radio" name="package" value="{{ $package->id }}" {{ $loop->first ? 'checked' : '' }}>
                                    <div class="package-content">
                                        <span class="package-name">{{ $package->name }}</span>
                                        <span class="package-price">{{ number_format($package->price, 0, ',', '.') }}đ {{ $package->discount_percent ? "(-" . $package->discount_percent . "%)" : '' }}</span>
                                    </div>
                                    @if($loop->first)
                                        <span class="popular-badge">Phổ biến</span>
                                    @endif
                                </label>
                            @empty
                                <p>Chưa có gói học</p>
                            @endforelse
                        </div>
                        <button class="btn btn-primary btn-lg btn-block" id="bookingBtn" data-instructor-id="{{ $instructor->id }}">Đặt lịch ngay</button>
                        <p class="booking-note">Liên hệ để được tư vấn và sắp xếp lịch phù hợp</p>
                    </div>

                    <!-- Contact Card -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">Liên hệ</h3>
                        <div class="contact-methods">
                            @if ($instructor->phone)
                                <a href="tel:{{ $instructor->phone }}" class="contact-method">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                    </svg>
                                    <span>{{ $instructor->phone }}</span>
                                </a>
                            @endif
                            @if ($instructor->zalo)
                                <a href="#" class="contact-method zalo">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 14.79c-.28.4-.85.77-1.58.77-.16 0-.33-.02-.5-.06-1.72-.42-3.46-1.51-4.91-3.06-1.45-1.56-2.39-3.38-2.65-5.13-.03-.17-.04-.34-.04-.5 0-.73.34-1.33.71-1.64.37-.32.88-.51 1.42-.51.12 0 .24.01.35.03.61.09 1.15.64 1.42 1.44l.59 1.76c.14.43.11.89-.08 1.28-.18.39-.51.7-.9.86l-.28.11c.12.28.29.56.52.84.48.57 1.08 1.12 1.76 1.64.28.21.55.38.82.5l.11-.28c.16-.39.47-.72.86-.9.39-.19.85-.22 1.28-.08l1.76.59c.8.27 1.35.81 1.44 1.42.02.11.03.23.03.35 0 .54-.19 1.05-.51 1.42z" />
                                    </svg>
                                    <span>Zalo: {{ $instructor->zalo }}</span>
                                </a>
                            @endif
                            @if ($instructor->email)
                                <a href="mailto:{{ $instructor->email }}" class="contact-method">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                        <polyline points="22,6 12,13 2,6" />
                                    </svg>
                                    <span>{{ $instructor->email }}</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">Khu vực dạy</h3>
                        <div class="location-list">
                            @forelse($instructor->locations as $location)
                                <div class="location-item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <div class="location-info">
                                        <strong>{{ $location->district }}, {{ $location->city }}</strong>
                                        <span>{{ $location->venues }}</span>
                                    </div>
                                </div>
                            @empty
                                <p>Chưa có khu vực dạy</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Schedule Card -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">Lịch dạy</h3>
                        <div class="schedule-list">
                            @forelse($instructor->schedules as $schedule)
                                <div class="schedule-item">
                                    <span class="day">{{ $schedule->days }}</span>
                                    <span class="time">{{ $schedule->time_slots }}</span>
                                </div>
                            @empty
                                <p>Chưa có lịch dạy</p>
                            @endforelse
                        </div>
                        <p class="schedule-note">* Lịch có thể thay đổi, vui lòng liên hệ trước</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Coaches -->
    <section class="similar-coaches section section-alt">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Giảng viên tương tự</h2>
                <p class="section-description">Khám phá thêm các huấn luyện viên khác trong khu vực của bạn</p>
            </div>
            <div class="coaches-grid small">
                @forelse($similarInstructors as $similar)
                    <div class="coach-card">
                        <div class="coach-image">
                            @if ($similar->image)
                                <img src="{{ asset('storage/' . $similar->image) }}" alt="{{ $similar->name }}">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='gs1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FF8E53;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23FE6B8B;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23gs1)' width='300' height='300'/%3E%3Ccircle cx='150' cy='120' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='150' cy='250' rx='80' ry='60' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E"
                                    alt="{{ $similar->name }}">
                            @endif
                            <div class="coach-rating-badge">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                </svg>
                                <span>{{ number_format($similar->rating ?? 5, 1) }}</span>
                            </div>
                        </div>
                        <div class="coach-content">
                            <h3 class="coach-name"><a
                                    href="{{ route('instructors.detail', $similar->id) }}">{{ $similar->name }}</a></h3>
                            <div class="coach-experience">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                                </svg>
                                <span>{{ $similar->experience_years ?? 0 }} năm kinh nghiệm</span>
                            </div>
                            <div class="coach-location">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <span>{{ $similar->province->name ?? 'Hà Nội' }}</span>
                            </div>
                            <div class="coach-actions">
                                <a href="{{ route('instructors.detail', $similar->id) }}"
                                    class="btn btn-primary btn-sm">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p>Không có giảng viên tương tự</p>
                @endforelse
            </div>
        </div>
    </section>
    <!-- Booking Modal -->
    <div id="bookingModal" class="modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Đặt lịch học</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <form id="bookingForm" class="booking-form">
                @csrf
                <input type="hidden" name="instructor_id" id="instructorId" value="{{ $instructor->id }}">
                
                <div class="form-group">
                    <label for="customerName">Tên của bạn *</label>
                    <input type="text" id="customerName" name="customer_name" required class="form-control" placeholder="Nhập tên của bạn">
                </div>

                <div class="form-group">
                    <label for="customerPhone">Số điện thoại *</label>
                    <input type="tel" id="customerPhone" name="customer_phone" required class="form-control" placeholder="Nhập số điện thoại">
                </div>

                <div class="form-group">
                    <label for="packageSelect">Chọn gói học *</label>
                    <select id="packageSelect" name="package_id" required class="form-control">
                        <option value="">-- Chọn gói học --</option>
                        @forelse($instructor->packages as $package)
                            <option value="{{ $package->id }}">{{ $package->name }} - {{ number_format($package->price, 0, ',', '.') }}đ</option>
                        @empty
                            <option value="" disabled>Không có gói học nào</option>
                        @endforelse
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Ghi chú (tuỳ chọn)</label>
                    <textarea id="notes" name="notes" class="form-control" placeholder="Nhập ghi chú hoặc yêu cầu đặc biệt..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Xác nhận đặt lịch</button>
            </form>
        </div>
    </div>

    <!-- Alert Message -->
    <div id="alertMessage" class="alert-message" style="display: none;">
        <div class="alert-content">
            <p id="alertText"></p>
            <button class="alert-close">&times;</button>
        </div>
    </div>

    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            position: relative;
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e5e5e5;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: #333;
        }

        .booking-form {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #00D9B5;
            box-shadow: 0 0 0 3px rgba(0, 217, 181, 0.1);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
            max-width: 400px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .alert-content {
            background: white;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .alert-content p {
            margin: 0;
            font-size: 14px;
        }

        .alert-content.success {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #15803d;
        }

        .alert-content.error {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .alert-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: inherit;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-favorite.active {
            background-color: #ff4458;
            color: white;
        }

        .btn-favorite.active svg {
            fill: currentColor;
            stroke: none;
        }

    </style>
@endsection
@section('js')
    <script>
        // Favorite button toggle for instructor
        const favoriteBtn = document.querySelector('.btn-favorite');
        const instructorId = favoriteBtn?.getAttribute('data-instructor-id');
        
        if (!instructorId) {
            console.error('Instructor ID not found');
        }
        
        // Check if instructor is already favorited on page load
        async function checkIsFavorited() {
            if (!instructorId) return;
            
            try {
                const response = await fetch(`/api/instructors/${instructorId}/is-favorited`);
                const data = await response.json();
                
                console.log('Favorite check response:', data);
                
                if (data.authenticated && data.favorited) {
                    favoriteBtn?.classList.add('active');
                }
            } catch (error) {
                console.error('Error checking favorite status:', error);
            }
        }
        
        // Initialize favorite status
        checkIsFavorited();
        
        // Handle favorite button click
        favoriteBtn?.addEventListener('click', async (e) => {
            e.preventDefault();
            
            if (!instructorId) {
                showAlert('Lỗi: Không tìm thấy ID giảng viên', 'error');
                return;
            }
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                if (!csrfToken) {
                    showAlert('Lỗi: CSRF token không tìm thấy', 'error');
                    return;
                }
                
                const response = await fetch(`/api/instructors/${instructorId}/toggle-favorite`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
                
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Toggle response:', data);
                console.log('data.authenticated:', data.authenticated);
                console.log('response.status === 401:', response.status === 401);
                
                if (response.status === 401 || !data.authenticated) {
                    console.log('Auth failed - showing alert');
                    showAlert('Vui lòng đăng nhập để lưu giảng viên yêu thích', 'error');
                    return;
                }
                
                if (data.success) {
                    if (data.favorited) {
                        favoriteBtn.classList.add('active');
                    } else {
                        favoriteBtn.classList.remove('active');
                    }
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
                showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            }
        });

        // Share button - copy link
        const shareBtn = document.querySelector('.btn-share');
        shareBtn?.addEventListener('click', async (e) => {
            e.preventDefault();
            
            try {
                const url = window.location.href;
                await navigator.clipboard.writeText(url);
                showAlert('Đã copy link giảng viên vào clipboard', 'success');
            } catch (error) {
                console.error('Error copying link:', error);
                
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = window.location.href;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                
                showAlert('Đã copy link giảng viên vào clipboard', 'success');
            }
        });

        // Package selection
        const packageOptions = document.querySelectorAll('.package-option input');
        packageOptions.forEach(option => {
            option.addEventListener('change', () => {
                document.querySelectorAll('.package-option').forEach(p => p.classList.remove('selected'));
                option.closest('.package-option').classList.add('selected');
            });
        });

        // Booking Modal Logic
        const bookingBtn = document.getElementById('bookingBtn');
        const bookingModal = document.getElementById('bookingModal');
        const closeModal = document.getElementById('closeModal');
        const bookingForm = document.getElementById('bookingForm');
        const modalOverlay = bookingModal?.querySelector('.modal-overlay');
        const alertMessage = document.getElementById('alertMessage');
        const alertText = document.getElementById('alertText');

        // Function to open booking modal
        function openBookingModal() {
            const instructorId = bookingBtn.getAttribute('data-instructor-id');
            document.getElementById('instructorId').value = instructorId;
            
            // Set first package as selected
            const selectedPackage = document.querySelector('.package-option input:checked');
            if (selectedPackage) {
                document.getElementById('packageSelect').value = selectedPackage.value;
            }
            
            bookingModal.style.display = 'flex';
        }

        // Open modal with booking button
        bookingBtn?.addEventListener('click', (e) => {
            openBookingModal();
        });

        // Open modal with contact button
        const contactBtn = document.getElementById('contactBtn');
        contactBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            openBookingModal();
        });

        // Close modal
        function closeBookingModal() {
            bookingModal.style.display = 'none';
            bookingForm.reset();
        }

        closeModal?.addEventListener('click', closeBookingModal);
        modalOverlay?.addEventListener('click', closeBookingModal);

        // Handle form submission
        bookingForm?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(bookingForm);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('{{ route("api.instructor-booking.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');
                    closeBookingModal();
                    bookingForm.reset();
                } else {
                    showAlert(result.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            }
        });

        // Show alert message
        function showAlert(message, type = 'success') {
            alertText.textContent = message;
            alertMessage.classList.remove('success', 'error');
            alertMessage.classList.add(type);
            alertMessage.style.display = 'block';

            setTimeout(() => {
                alertMessage.style.display = 'none';
            }, 4000);
        }

        // Close alert
        document.getElementById('alertMessage')?.querySelector('.alert-close')?.addEventListener('click', () => {
            alertMessage.style.display = 'none';
        });

        // Review Form Handler
        const reviewForm = document.getElementById('reviewForm');
        const reviewContent = document.getElementById('reviewContent');
        const charCount = document.getElementById('charCount');
        const ratingText = document.getElementById('ratingText');
        const starRatingInputs = document.querySelectorAll('.star-rating-input input[name="rating"]');

        // Update character count
        if (reviewContent) {
            reviewContent.addEventListener('input', (e) => {
                charCount.textContent = e.target.value.length;
            });
        }

        // Update rating text
        starRatingInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                const rating = e.target.value;
                const ratingLabels = {
                    5: 'Tuyệt vời!',
                    4: 'Rất tốt',
                    3: 'Bình thường',
                    2: 'Chưa tốt',
                    1: 'Không hài lòng'
                };
                ratingText.textContent = ratingLabels[rating] || '';
                ratingText.style.color = rating >= 4 ? '#22c55e' : rating >= 3 ? '#ffc107' : '#ef4444';
            });
        });

        // Handle form submission
        if (reviewForm) {
            reviewForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                // Validate rating
                const selectedRating = document.querySelector('.star-rating-input input[name="rating"]:checked');
                if (!selectedRating) {
                    showAlert('Vui lòng chọn số sao để đánh giá', 'error');
                    return;
                }

                // Collect form data
                const formData = new FormData(reviewForm);
                const instructorId = formData.get('instructor_id');
                const rating = formData.get('rating');
                const content = formData.get('content');
                const tagsArray = formData.getAll('tags');

                const data = {
                    instructor_id: instructorId,
                    rating: rating,
                    content: content || null,
                    tags: tagsArray.length > 0 ? tagsArray.join(',') : null,
                };

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    const response = await fetch('{{ route("api.instructor-review.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(data),
                    });

                    let result;
                    const contentType = response.headers.get('content-type');
                    
                    if (contentType && contentType.includes('application/json')) {
                        result = await response.json();
                    } else {
                        const text = await response.text();
                        console.error('Unexpected response:', text);
                        showAlert('Có lỗi xảy ra từ server. Vui lòng kiểm tra console.', 'error');
                        return;
                    }

                    if (response.status === 401) {
                        showAlert('Vui lòng đăng nhập để đánh giá giảng viên', 'error');
                        return;
                    }

                    if (result.success) {
                        showAlert(result.message || 'Đánh giá của bạn đã được gửi thành công!', 'success');
                        reviewForm.reset();
                        ratingText.textContent = '';
                        charCount.textContent = '0';
                        
                        // Reload page after 2 seconds to show new review
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showAlert(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
                }
            });
        }

        // Edit Review Modal
        const editReviewModal = document.getElementById('editReviewModal');
        const closeEditModal = document.getElementById('closeEditModal');
        const editReviewForm = document.getElementById('editReviewForm');
        const editModalOverlay = editReviewModal?.querySelector('.modal-overlay');

        // Close edit modal
        closeEditModal?.addEventListener('click', () => {
            editReviewModal.style.display = 'none';
            editReviewForm.reset();
        });
        editModalOverlay?.addEventListener('click', () => {
            editReviewModal.style.display = 'none';
            editReviewForm.reset();
        });

        // Edit button handlers
        document.querySelectorAll('.btn-edit-review').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const reviewId = btn.getAttribute('data-review-id');
                const rating = btn.getAttribute('data-rating');
                const content = btn.getAttribute('data-content');
                const tags = btn.getAttribute('data-tags');

                // Populate modal with current values
                document.getElementById('editReviewId').value = reviewId;
                document.getElementById('editReviewContent').value = content || '';
                document.getElementById('editCharCount').textContent = (content || '').length;

                // Set rating
                document.querySelector(`#editStar${rating}`).checked = true;
                const ratingLabels = {
                    5: 'Tuyệt vời!',
                    4: 'Rất tốt',
                    3: 'Bình thường',
                    2: 'Chưa tốt',
                    1: 'Không hài lòng'
                };
                document.getElementById('editRatingText').textContent = ratingLabels[rating] || '';
                document.getElementById('editRatingText').style.color = rating >= 4 ? '#22c55e' : rating >= 3 ? '#ffc107' : '#ef4444';

                // Set tags
                document.querySelectorAll('#editReviewForm input[name="tags"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                if (tags) {
                    const tagArray = tags.split(',').map(t => t.trim());
                    document.querySelectorAll('#editReviewForm input[name="tags"]').forEach(checkbox => {
                        if (tagArray.includes(checkbox.value)) {
                            checkbox.checked = true;
                        }
                    });
                }

                // Show modal
                editReviewModal.style.display = 'flex';
            });
        });

        // Edit form submission
        editReviewForm?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const reviewId = document.getElementById('editReviewId').value;
            const rating = document.querySelector('#editReviewForm input[name="rating"]:checked')?.value;
            const content = document.getElementById('editReviewContent').value;
            const tagsCheckboxes = document.querySelectorAll('#editReviewForm input[name="tags"]:checked');
            const tags = Array.from(tagsCheckboxes).map(cb => cb.value).join(',');

            if (!rating) {
                showAlert('Vui lòng chọn số sao', 'error');
                return;
            }

            const data = {
                rating: rating,
                content: content || null,
                tags: tags || null,
            };

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                const response = await fetch(`/api/instructor-review/${reviewId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(data),
                });

                let result;
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    const text = await response.text();
                    console.error('Unexpected response:', text);
                    showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
                    return;
                }

                if (result.success) {
                    showAlert('Đánh giá của bạn đã được cập nhật!', 'success');
                    editReviewModal.style.display = 'none';
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showAlert(result.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            }
        });

        // Delete button handlers
        document.querySelectorAll('.btn-delete-review').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                if (!confirm('Bạn có chắc muốn xóa đánh giá này?')) {
                    return;
                }

                const reviewId = btn.getAttribute('data-review-id');

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    const response = await fetch(`/api/instructor-review/${reviewId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    let result;
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        result = await response.json();
                    } else {
                        const text = await response.text();
                        console.error('Unexpected response:', text);
                        showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
                        return;
                    }

                    if (result.success) {
                        showAlert('Đánh giá đã được xóa!', 'success');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        showAlert(result.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
                }
            });
        });

        // Edit form character counter
        document.getElementById('editReviewContent')?.addEventListener('input', (e) => {
            document.getElementById('editCharCount').textContent = e.target.value.length;
        });

        // Edit form rating feedback
        document.querySelectorAll('#editStarRating input[name="rating"]').forEach(input => {
            input.addEventListener('change', (e) => {
                const rating = e.target.value;
                const ratingLabels = {
                    5: 'Tuyệt vời!',
                    4: 'Rất tốt',
                    3: 'Bình thường',
                    2: 'Chưa tốt',
                    1: 'Không hài lòng'
                };
                const editRatingText = document.getElementById('editRatingText');
                editRatingText.textContent = ratingLabels[rating] || '';
                editRatingText.style.color = rating >= 4 ? '#22c55e' : rating >= 3 ? '#ffc107' : '#ef4444';
            });
        });
        </script>
        @endsection
