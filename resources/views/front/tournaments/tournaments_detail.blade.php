@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tournament-detail.css') }}">
@endsection

@section('content')
    <section class="tournament-hero">
        <div class="hero-background">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1920 600'%3E%3Cdefs%3E%3ClinearGradient id='hero-grad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23hero-grad)' width='1920' height='600'/%3E%3Ctext x='960' y='300' font-family='Arial' font-size='72' fill='white' text-anchor='middle' dominant-baseline='middle' font-weight='bold'%3EHCM OPEN 2025%3C/text%3E%3C/svg%3E" alt="Tournament Banner">
            <div class="hero-overlay"></div>
        </div>
        
        <div class="container">
            <div class="breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span class="separator">/</span>
                <a href="{{ route('tournaments') }}">Giải đấu</a>
                <span class="separator">/</span>
                <span>{{ $tournament->name }}</span>
            </div>
            
            <div class="hero-content">
                <div class="hero-badges">
                    @if($tournament->status == 1)
                        <span class="hero-badge badge-featured">⭐ Featured</span>
                    @endif
                    @if($tournament->registration_deadline > now())
                        <span class="hero-badge badge-open">✓ Đang mở đăng ký</span>
                    @else
                        <span class="hero-badge badge-closed">✗ Đã đóng đăng ký</span>
                    @endif
                </div>
                
                <h1 class="hero-title">{{ $tournament->name }}</h1>
                <p class="hero-subtitle">{{ $tournament->description }}</p>
                
                <div class="hero-meta">
                    <div class="hero-meta-item">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span>{{ $tournament->start_date->format('d/m/Y') }} - {{ $tournament->end_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="hero-meta-item">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>{{ $tournament->location }}</span>
                    </div>
                    <div class="hero-meta-item">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>{{ $tournament->max_participants }} vận động viên</span>
                    </div>
                </div>
                
                <div class="hero-actions">
                    @if(!$registered)
                        <button class="btn btn-primary btn-lg tournament-detail-register-btn" onclick="openRegisterModal()">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="8.5" cy="7" r="4"/>
                                <line x1="20" y1="8" x2="20" y2="14"/>
                                <line x1="23" y1="11" x2="17" y2="11"/>
                            </svg>
                            Đăng ký tham gia
                        </button>
                    @else
                        <button class="btn btn-secondary btn-lg tournament-detail-register-btn" disabled style="opacity: 0.6; cursor: not-allowed;">
                            Chờ xét duyệt
                        </button>
                    @endif
                    <button class="btn btn-secondary btn-lg">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                            <line x1="4" y1="22" x2="4" y2="15"/>
                        </svg>
                        Lưu giải đấu
                    </button>
                    <button class="btn btn-white btn-lg">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="18" cy="5" r="3"/>
                            <circle cx="6" cy="12" r="3"/>
                            <circle cx="18" cy="19" r="3"/>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                        </svg>
                        Chia sẻ
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Tournament Stats Bar -->
     <section class="stats-bar">
         <div class="container">
             <div class="stats-grid">
                 <div class="stat-card">
                     <div class="stat-icon">🏆</div>
                     <div class="stat-info">
                         <div class="stat-value">{{ number_format($tournament->prizes, 0, ',', '.') }} VNĐ</div>
                         <div class="stat-label">Tổng giải thưởng</div>
                     </div>
                 </div>
                 <div class="stat-card">
                     <div class="stat-icon">👥</div>
                     <div class="stat-info">
                         <div class="stat-value">{{ $tournament->max_participants }}</div>
                         <div class="stat-label">Số vận động viên</div>
                     </div>
                 </div>
                 <div class="stat-card">
                     <div class="stat-icon">🎯</div>
                     <div class="stat-info">
                         <div class="stat-value">8</div>
                         <div class="stat-label">Số sân thi đấu</div>
                     </div>
                 </div>
                 <div class="stat-card">
                     <div class="stat-icon">📅</div>
                     <div class="stat-info">
                         @php
                             $duration = $tournament->start_date->diffInDays($tournament->end_date) + 1;
                         @endphp
                         <div class="stat-value">{{ $duration }} ngày</div>
                         <div class="stat-label">Thời gian diễn ra</div>
                     </div>
                 </div>
                 <div class="stat-card">
                     <div class="stat-icon">⏰</div>
                     <div class="stat-info">
                         @php
                             $daysRemaining = max(0, now()->diffInDays($tournament->registration_deadline));
                         @endphp
                         <div class="stat-value">{{ $daysRemaining }} ngày</div>
                         <div class="stat-label">Còn lại để đăng ký</div>
                     </div>
                 </div>
             </div>
         </div>
     </section>

    <!-- Main Content -->
    <section class="tournament-detail-section section">
        <div class="container">
            <div class="detail-layout">
                @include('front.tournaments.tabs-section')
                
                <!-- Sidebar -->
                <aside class="detail-sidebar">
                    <!-- Registration Card -->
                    <div class="sidebar-card registration-card">
                        <div class="card-header">
                            <h3>Đăng ký tham gia</h3>
                            <span class="urgency-badge">⏰ Còn 15 ngày</span>
                        </div>
                        
                        <div class="price-section">
                            <div class="price-item">
                                <span class="price-label">Lệ phí đăng ký</span>
                                <span class="price-value">{{ number_format($tournament->price, 0, ',', '.') }} VNĐ</span>
                            </div>
                        </div>

                        @php
                            $athletes = $tournament->athletes()->count();
                            $percentage = $tournament->max_participants > 0 ? round(($athletes / $tournament->max_participants) * 100) : 0;
                        @endphp
                        <div class="registration-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="progress-text">
                                <span>{{ $athletes }}/{{ $tournament->max_participants }} VĐV đã đăng ký</span>
                                <span>{{ $percentage }}%</span>
                            </div>
                        </div>
                        @if(!$registered)
                            <button class="btn btn-primary btn-block btn-lg tournament-detail-register-btn" onclick="openRegisterModal()">
                                Đăng ký ngay
                            </button>
                        @else
                            <button class="btn btn-secondary btn-lg btn-block tournament-detail-register-btn" disabled style="opacity: 0.6; cursor: not-allowed;">
                                Chờ xét duyệt
                            </button>
                        @endif
                        
                        @if($tournament->registration_benefits)
                            <div class="registration-benefits">
                                <h4>Quyền lợi khi đăng ký:</h4>
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    @php
                                        // Handle both newline and "/" separators
                                        $benefitText = str_replace('/', "\n", $tournament->registration_benefits);
                                        $benefits = array_filter(array_map('trim', explode("\n", $benefitText)));
                                    @endphp
                                    @foreach($benefits as $benefit)
                                        <li style="padding: 6px 0; color: #1f2937; font-weight: 500;">✓ {{ preg_replace('/^✓\s*/', '', $benefit) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Contact Card -->
                    <div class="sidebar-card contact-card">
                        <h3 class="card-title">Thông tin liên hệ</h3>
                        <div class="contact-list">
                            @if($tournament->organizer_email)
                                <div class="contact-item">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <div>
                                        <div class="contact-label">Email</div>
                                        <div class="contact-value">{{ $tournament->organizer_email }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($tournament->organizer_hotline)
                                <div class="contact-item">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                                    </svg>
                                    <div>
                                        <div class="contact-label">Hotline</div>
                                        <div class="contact-value">{{ $tournament->organizer_hotline }}</div>
                                    </div>
                                </div>
                            @endif
                            @if($tournament->social_information)
                                <div class="contact-item">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 6v6l4 2"/>
                                    </svg>
                                    <div>
                                        <div class="contact-label">Mạng xã hội</div>
                                        <div class="contact-value">{!! nl2br(e($tournament->social_information)) !!}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Share Card -->
                    <div class="sidebar-card share-card">
                        <h3 class="card-title">Chia sẻ giải đấu</h3>
                        <div class="share-buttons">
                            <button class="share-btn facebook">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </button>
                            <button class="share-btn zalo">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 14.79c-.28.4-.85.77-1.58.77-.16 0-.33-.02-.5-.06-1.72-.42-3.46-1.51-4.91-3.06-1.45-1.56-2.39-3.38-2.65-5.13-.03-.17-.04-.34-.04-.5 0-.73.34-1.33.71-1.64.37-.32.88-.51 1.42-.51.12 0 .24.01.35.03.61.09 1.15.64 1.42 1.44l.59 1.76c.14.43.11.89-.08 1.28-.18.39-.51.7-.9.86l-.28.11c.12.28.29.56.52.84.48.57 1.08 1.12 1.76 1.64.28.21.55.38.82.5l.11-.28c.16-.39.47-.72.86-.9.39-.19.85-.22 1.28-.08l1.76.59c.8.27 1.35.81 1.44 1.42.02.11.03.23.03.35 0 .54-.19 1.05-.51 1.42z"/>
                                </svg>
                            </button>
                            <button class="share-btn copy">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                    <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Related Tournaments -->
                    <div class="sidebar-card related-card">
                        <h3 class="card-title">Giải đấu liên quan</h3>
                        <div class="related-list">
                            <a href="#" class="related-item">
                                <div class="related-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 80'%3E%3Crect fill='%23FF6B6B' width='100' height='80'/%3E%3C/svg%3E" alt="">
                                </div>
                                <div class="related-content">
                                    <h4>Hà Nội Masters</h4>
                                    <p>22-24 Tháng 12</p>
                                </div>
                            </a>
                            <a href="#" class="related-item">
                                <div class="related-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 80'%3E%3Crect fill='%234ECDC4' width='100' height='80'/%3E%3C/svg%3E" alt="">
                                </div>
                                <div class="related-content">
                                    <h4>Đà Nẵng Beach</h4>
                                    <p>05-07 Tháng 1</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- Registration Modal -->
    <div id="registerModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 2000; display: none; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
        <div style="background: white; border-radius: 20px; width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <!-- Modal Header with Gradient -->
            <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 30px 30px 40px; color: white; position: relative;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Đăng ký tham gia</h2>
                <p style="margin: 8px 0 0 0; opacity: 0.9; font-size: 0.95rem;">{{ $tournament->name }}</p>
                <button onclick="closeRegisterModal()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.5rem; cursor: pointer; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    ✕
                </button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 40px 30px;">
                <form id="registerForm">
                    @csrf
                    
                    <!-- Athlete Name Field -->
                    <div style="margin-bottom: 25px;">
                        <label for="athlete_name" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Tên vận động viên <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="athlete_name" name="athlete_name" required 
                            value="{{ auth()->check() ? auth()->user()->name : '' }}"
                            placeholder="Nhập tên của bạn"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        <div id="athlete_name_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
                    </div>

                    <!-- Email Field -->
                    <div style="margin-bottom: 25px;">
                        <label for="email" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Email <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="email" id="email" name="email" required 
                            value="{{ auth()->check() ? auth()->user()->email : '' }}"
                            placeholder="Nhập email của bạn"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        <div id="email_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
                    </div>

                    <!-- Phone Field -->
                    <div style="margin-bottom: 30px;">
                        <label for="phone" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Số điện thoại <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" required 
                            value="{{ auth()->check() ? auth()->user()->phone : '' }}"
                            placeholder="Nhập số điện thoại của bạn"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                        <div id="phone_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
                    </div>

                    <!-- Category Selection Field -->
                    <div style="margin-bottom: 30px;">
                        <label for="category_id" style="display: block; font-weight: 600; color: #1f2937; margin-bottom: 10px; font-size: 0.95rem;">
                            Nội dung thi đấu <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="category_id" name="category_id" required 
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 0.95rem; font-family: inherit; transition: all 0.3s ease; box-sizing: border-box; appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22currentColor%22 stroke-width=%222%22%3E%3cpolyline points=%226 9 12 15 18 9%22%3E%3c/polyline%3E%3c/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 20px; padding-right: 40px;"
                            onmouseover="this.style.borderColor='#d1d5db'"
                            onfocus="this.style.borderColor='var(--primary-color)'; this.style.boxShadow='0 0 0 3px rgba(236, 72, 153, 0.1)'"
                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'">
                            <option value="">-- Chọn nội dung thi đấu --</option>
                            @if($tournament->categories)
                                @foreach($tournament->categories as $category)
                                    @php
                                        $athleteCount = $category->athletes()->count();
                                        $isAvailable = in_array($category->status, ['open', 'ongoing']) && $athleteCount < $category->max_participants;
                                        $statusText = '';
                                        if ($category->status === 'closed') {
                                            $statusText = ' (Đóng)';
                                        } elseif ($athleteCount >= $category->max_participants) {
                                            $statusText = ' (Hết chỗ)';
                                        }
                                    @endphp
                                    <option value="{{ $category->id }}" @if(!$isAvailable) disabled @endif>
                                        {{ $category->category_name }} 
                                        @if($category->age_group && $category->age_group !== 'open')
                                            ({{ $category->age_group }})
                                        @endif
                                        - {{ $athleteCount }}/{{ $category->max_participants }}{{ $statusText }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div id="category_id_error" style="color: #ef4444; font-size: 0.85rem; margin-top: 6px; display: none;"></div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 0 30px 30px; display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeRegisterModal()" 
                    style="padding: 12px 28px; border: 2px solid #e5e7eb; background: white; color: #6b7280; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.95rem;"
                    onmouseover="this.style.borderColor='#d1d5db'; this.style.background='#f9fafb'"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='white'">
                    Hủy
                </button>
                <button type="button" id="submitRegisterBtn" onclick="submitRegisterForm()"
                    style="padding: 12px 32px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 0.95rem; box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);"
                    onmouseover="this.style.boxShadow='0 6px 25px rgba(236, 72, 153, 0.4)'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.boxShadow='0 4px 15px rgba(236, 72, 153, 0.3)'; this.style.transform='translateY(0)'">
                    Đăng ký
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }
    </style>
@endsection

@section('js')
    <script src="{{ asset('assets/js/tournament-detail.js') }}"></script>
    <script>
        function openRegisterModal() {
            const modal = document.getElementById('registerModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeRegisterModal() {
            const modal = document.getElementById('registerModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('registerForm').reset();
            document.getElementById('athlete_name_error').style.display = 'none';
            document.getElementById('email_error').style.display = 'none';
            document.getElementById('phone_error').style.display = 'none';
            document.getElementById('category_id_error').style.display = 'none';
        }

        function submitRegisterForm() {
            console.log('submitRegisterForm called!');
            const form = document.getElementById('registerForm');
            
            // Clear previous errors
            document.getElementById('athlete_name_error').style.display = 'none';
            document.getElementById('email_error').style.display = 'none';
            document.getElementById('phone_error').style.display = 'none';
            document.getElementById('category_id_error').style.display = 'none';
            
            const athleteNameEl = document.getElementById('athlete_name');
            const emailEl = document.getElementById('email');
            const phoneEl = document.getElementById('phone');
            const categoryEl = document.getElementById('category_id');
            
            console.log('Form elements found:', {athleteNameEl, emailEl, phoneEl, categoryEl});
            
            const athleteName = athleteNameEl.value.trim();
            const email = emailEl.value.trim();
            const phone = phoneEl.value.trim();
            const categoryId = categoryEl.value.trim();
            
            console.log('Form values:', {athleteName, email, phone, categoryId});
            
            let hasError = false;
            
            if (!athleteName) {
                document.getElementById('athlete_name_error').textContent = 'Vui lòng nhập tên vận động viên';
                document.getElementById('athlete_name_error').style.display = 'block';
                hasError = true;
            }
            if (!email) {
                document.getElementById('email_error').textContent = 'Vui lòng nhập email';
                document.getElementById('email_error').style.display = 'block';
                hasError = true;
            }
            if (!phone) {
                document.getElementById('phone_error').textContent = 'Vui lòng nhập số điện thoại';
                document.getElementById('phone_error').style.display = 'block';
                hasError = true;
            }
            if (!categoryId) {
                document.getElementById('category_id_error').textContent = 'Vui lòng chọn nội dung thi đấu';
                document.getElementById('category_id_error').style.display = 'block';
                hasError = true;
            }
            
            console.log('Validation errors:', hasError);
            if (hasError) {
                console.log('Form has errors, returning');
                return;
            }
            
            const btn = document.getElementById('submitRegisterBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div>Đang xử lý...';
            
            const payload = {
                athlete_name: athleteName,
                email: email,
                phone: phone,
                category_id: categoryId,
                tournament_id: {{ $tournament->id }}
            };
            
            console.log('Submitting registration:', payload);
            
            fetch('{{ route("tournament.register", $tournament->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json().then(data => ({status: response.status, data}));
            })
            .then(({status, data}) => {
                console.log('Response status:', status);
                console.log('Response data:', data);
                if (data && data.success) {
                    console.log('Registration successful!');
                    alert('Đăng ký thành công! Vui lòng chờ xác nhận từ ban tổ chức.');
                    form.reset();
                    
                    // Update all registration buttons
                    console.log('Looking for registration buttons...');
                    
                    // Find all buttons with class tournament-detail-register-btn
                    const allButtons = document.querySelectorAll('.tournament-detail-register-btn');
                    console.log('Buttons found:', allButtons.length);
                    
                    allButtons.forEach((btn, index) => {
                        console.log(`Updating button ${index + 1}...`);
                        
                        // Clear the onclick handler
                        btn.removeAttribute('onclick');
                        btn.onclick = null;
                        
                        // Replace button content completely
                        btn.textContent = 'Chờ xét duyệt';
                        btn.className = 'btn btn-secondary btn-block btn-lg tournament-detail-register-btn';
                        
                        // Disable the button
                        btn.disabled = true;
                        btn.style.opacity = '0.6';
                        btn.style.cursor = 'not-allowed';
                        btn.style.pointerEvents = 'none';
                        
                        console.log(`Button ${index + 1} updated:`, btn);
                    });
                    
                    closeRegisterModal();
                } else {
                    const errorMsg = data?.message || 'Đã xảy ra lỗi. Vui lòng thử lại.';
                    console.error('Registration failed:', errorMsg);
                    alert(errorMsg);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi. Vui lòng thử lại.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Đăng ký';
            });
        }

        // Close modal when clicking outside
        document.getElementById('registerModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeRegisterModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeRegisterModal();
            }
        });
    </script>
@endsection
