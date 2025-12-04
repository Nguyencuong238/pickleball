@extends('layouts.front')

@section('css')
    <style>
        form.social-filter-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        select.form-control {
            background: #fff;
        }
        .filter-group label {
            margin-bottom: 0.5rem;
            display: block;
        }

        @media (max-width: 768px) {
            form.social-filter-bar {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            form.social-filter-bar {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $levels = [
            'beginner' => 'Người mới',
            'intermediate' => 'Trung cấp',
            'advanced' => 'Nâng cao',
        ];
    @endphp
    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Thi Đấu Social</h1>
            <p class="page-description">Tham gia cộng đồng, tìm đối thủ và nâng cao kỹ năng Pickleball</p>

            {{-- <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $totalParticipants }}+</div>
                        <div class="stat-label">Thành viên</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">🎾</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $totalSocials }}</div>
                        <div class="stat-label">Buổi thi đấu</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">📍</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $totalStadiums }}</div>
                        <div class="stat-label">Địa điểm</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <div class="stat-number">4.9</div>
                        <div class="stat-label">Đánh giá</div>
                    </div>
                </div>
            </div> --}}
            
            <form method="GET" action="{{ route('social') }}" class="social-filter-bar">
                <div class="filter-group">
                    {{-- <label>Tìm kiếm</label> --}}
                    <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..."
                        value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="filter-group">
                    {{-- <label>Sân</label> --}}
                    <select class="form-control" name="stadium_id">
                        <option value="">-- Sân --</option>
                        @foreach ($stadiums as $stadium)
                            <option value="{{ $stadium->id }}"
                                {{ ($filters['stadium_id'] ?? '') == $stadium->id ? 'selected' : '' }}>
                                {{ $stadium->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    {{-- <label>Đối tượng</label> --}}
                    <select class="form-control" name="object">
                        <option value="">-- Đối tượng --</option>
                        @foreach ($levels as $key => $value)
                            <option value="{{ $key }}" {{ ($filters['object'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    {{-- <label>Ngày</label> --}}
                    @php
                           $daysofweek = [
                            '2' => 'Thứ 2',
                            '3' => 'Thứ 3',
                            '4' => 'Thứ 4',
                            '5' => 'Thứ 5',
                            '6' => 'Thứ 6',
                            '7' => 'Thứ 7',
                            '1' => 'CN',
                        ]; 
                        @endphp
                    <select class="form-control" name="date">
                        <option value="">-- Ngày --</option>
                        @foreach ($daysofweek as $key => $value)
                            <option value="{{ $key }}" {{ ($filters['date'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="form-control btn btn-primary">Tìm kiếm</button>
                </div>
            </form>
        </div>
    </section>

    <section class="social-section section">
        <div class="container">
            <div class="social-grid">
                @forelse ($socials as $social)
                    @php
                        $daysMap = [
                            '2' => 'T2',
                            '3' => 'T3',
                            '4' => 'T4',
                            '5' => 'T5',
                            '6' => 'T6',
                            '7' => 'T7',
                            '1' => 'CN',
                        ];
                        $dayLabel = '';
                        if (count($social->days_of_week) < 7 && count($social->days_of_week) > 0) {
                            $days = [];
                            foreach ($social->days_of_week as $value) {
                                $days[] = $daysMap[$value];
                            }
                            $dayLabel = implode(', ', $days);
                        } else {
                            $dayLabel = 'Thứ 2 - CN';
                        }
                    @endphp
                    <div class="social-card">
                        <div class="social-header">
                            <div class="social-day">
                                <span class="day-name">{{ $dayLabel }}</span>
                                <span class="day-date">
                                    {{ substr($social->start_time, 0, 5) }} - {{ substr($social->end_time, 0, 5) }}
                                </span>
                            </div>
                            <span class="social-level level-beginner">{{ $levels[$social->object] ?? 'N/A' }}</span>
                        </div>
                        <h3 class="social-title">{{ $social->name }}</h3>
                        <p class="social-description">
                            {{ $social->description ? Str::limit($social->description, 50) : 'Không có mô tả' }}</p>
                        <div class="social-info">
                            <div class="info-row">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <span>{{ $social->stadium->name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                </svg>
                                <span>0/{{ $social->max_participants ?? 'N/A' }} người</span>
                            </div>
                            <div class="info-row">
                                <span
                                    class="price">{{ $social->fee ? number_format($social->fee, 0, ',', '.') . 'đ/người' : 'Miễn phí' }}</span>
                                </div>
                                </div>
                                @auth
                                @if ($social->user_joined)
                                <button class="btn btn-primary btn-block" disabled style="opacity: 0.6;">Đã tham gia</button>
                                @else
                                <button class="btn btn-primary btn-block" onclick="joinSocial({{ $social->id }}, this)">Tham gia ngay</button>
                                @endif
                                @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-block">Đăng nhập để tham gia</a>
                                @endauth
                                </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <p style="color: var(--text-secondary);">Không có buổi thi đấu nào</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($socials->hasPages())
                {{ $socials->links('pagination.custom') }}
            @endif
        </div>
    </section>
@endsection

@section('js')
    <script>
        function joinSocial(socialId, button) {
            fetch(`/social/${socialId}/join`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Change button to "Đã tham gia" and disable it
                    button.textContent = 'Đã tham gia';
                    button.disabled = true;
                    button.style.opacity = '0.6';
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                toastr.error('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        }
    </script>
@endsection
