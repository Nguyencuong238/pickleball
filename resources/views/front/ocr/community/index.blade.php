@extends('layouts.front')

@section('title', 'Cộng Đồng - OPRS')

@section('css')
<style>
    .page-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        padding: 3rem 0;
        color: white;
        margin-top: 100px;
    }

    .page-header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

    .page-breadcrumb {
        font-size: 0.875rem;
        opacity: 0.8;
    }

    .page-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .page-breadcrumb a:hover {
        text-decoration: underline;
    }

    .community-section {
        padding: 2rem 0;
    }

    .stats-banner {
        background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        color: white;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .stats-main h3 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 0.25rem 0;
        opacity: 0.9;
    }

    .stats-main .stats-value {
        font-size: 2rem;
        font-weight: 700;
    }

    .stats-main .stats-contrib {
        font-size: 0.875rem;
        opacity: 0.8;
        margin-top: 0.25rem;
    }

    .stats-secondary {
        text-align: right;
    }

    .stats-secondary .stats-label {
        font-size: 0.875rem;
        opacity: 0.8;
    }

    .stats-secondary .stats-count {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .activities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .activity-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .activity-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .activity-card.disabled {
        opacity: 0.6;
    }

    .activity-header {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .activity-icon {
        font-size: 1.5rem;
    }

    .activity-name {
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 0.25rem 0;
    }

    .activity-points {
        font-size: 0.875rem;
        color: #a855f7;
        font-weight: 500;
    }

    .activity-desc {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 1rem;
    }

    .activity-action .btn {
        width: 100%;
        display: block;
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }

    .activity-action .btn-purple {
        background: linear-gradient(90deg, #a855f7, #7c3aed);
        color: white;
    }

    .activity-action .btn-purple:hover {
        background: linear-gradient(90deg, #9333ea, #6d28d9);
    }

    .activity-unavailable {
        text-align: center;
        padding: 0.75rem;
        background: #f1f5f9;
        border-radius: 0.5rem;
        color: #64748b;
        font-size: 0.875rem;
    }

    .weekly-progress {
        text-align: center;
    }

    .weekly-count {
        font-size: 1.125rem;
        font-weight: 700;
        color: #a855f7;
        margin-bottom: 0.5rem;
    }

    .weekly-bar {
        width: 100%;
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }

    .weekly-fill {
        height: 100%;
        background: linear-gradient(90deg, #a855f7, #7c3aed);
        border-radius: 4px;
        transition: width 0.3s;
    }

    .weekly-complete {
        color: #22c55e;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .history-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .history-header {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 700;
        color: #1e293b;
    }

    .history-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .history-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .history-item:last-child {
        border-bottom: none;
    }

    .history-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .history-icon {
        font-size: 1.25rem;
        color: #a855f7;
    }

    .history-name {
        font-weight: 500;
        color: #1e293b;
    }

    .history-meta {
        font-size: 0.875rem;
        color: #64748b;
    }

    .history-result {
        text-align: right;
    }

    .history-points {
        font-weight: 600;
        color: #a855f7;
    }

    .history-date {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .empty-message {
        text-align: center;
        padding: 3rem;
        color: #94a3b8;
    }

    .empty-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .stats-banner {
            flex-direction: column;
            text-align: center;
        }

        .stats-secondary {
            text-align: center;
        }

        .activities-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <div>
                <p class="page-breadcrumb">
                    <a href="{{ route('ocr.index') }}">OCR</a> /
                    <a href="{{ route('ocr.profile', auth()->user()) }}">Hồ Sơ</a> /
                    Cộng Đồng
                </p>
                <h1 class="page-title">👥 Cộng Đồng</h1>
            </div>
            <a href="{{ route('ocr.profile', auth()->user()) }}" class="btn btn-outline" style="color: white">
                ← Quay Lại Hồ Sơ
            </a>
        </div>
    </div>
</section>

<section class="community-section">
    <div class="container">
        {{-- Stats Banner --}}
        <div class="stats-banner">
            <div class="stats-main">
                <h3>Điểm Cộng Đồng Của Bạn</h3>
                <div class="stats-value">{{ number_format($user->community_score, 0) }}</div>
                <div class="stats-contrib">Đóng góp {{ number_format($user->community_score * 0.1, 2) }} vào OPRS</div>
            </div>
            <div class="stats-secondary">
                <div class="stats-label">Hoạt Động Tháng Này</div>
                <div class="stats-count">{{ $stats['recent_count'] }}</div>
            </div>
        </div>

        {{-- Activity Options --}}
        <h2 class="section-title">Kiếm Điểm Cộng Đồng</h2>
        <div class="activities-grid">
            {{-- Check-in --}}
            <div class="activity-card">
                <div class="activity-header">
                    <span class="activity-icon">📍</span>
                    <div>
                        <h3 class="activity-name">Check-in</h3>
                        <span class="activity-points">+2 điểm/lần check-in</span>
                    </div>
                </div>
                <p class="activity-desc">Check-in tại sân OnePickleball hoặc đối tác</p>
                <div class="activity-action">
                    <a href="{{ route('ocr.community.checkin') }}" class="btn btn-purple">
                        Check-in Ngay
                    </a>
                </div>
            </div>

            {{-- Referral --}}
            <div class="activity-card">
                <div class="activity-header">
                    <span class="activity-icon">👤➕</span>
                    <div>
                        <h3 class="activity-name">Giới Thiệu Bạn Bè</h3>
                        <span class="activity-points">+10 điểm/lần giới thiệu</span>
                    </div>
                </div>
                <p class="activity-desc">Giới thiệu bạn bè đăng ký OnePickleball</p>
                <div class="activity-action">
                    <button onclick="copyReferralLink()" class="btn btn-purple">
                        Sao Chép Link Giới Thiệu
                    </button>
                </div>
            </div>

            {{-- Weekly 5 Matches --}}
            <div class="activity-card">
                <div class="activity-header">
                    <span class="activity-icon">📅</span>
                    <div>
                        <h3 class="activity-name">5 Trận Tuần</h3>
                        <span class="activity-points">+5 điểm (tự động)</span>
                    </div>
                </div>
                <p class="activity-desc">Hoàn thành 5 trận trong tuần</p>
                <div class="weekly-progress">
                    <div class="weekly-count">{{ $weeklyMatchCount }}/5</div>
                    <div class="weekly-bar">
                        <div class="weekly-fill" style="width: {{ min(100, ($weeklyMatchCount/5)*100) }}%"></div>
                    </div>
                    @if($weeklyMatchCount >= 5)
                        <span class="weekly-complete">✅ Đã hoàn thành tuần này!</span>
                    @endif
                </div>
            </div>

            {{-- Events --}}
            <div class="activity-card">
                <div class="activity-header">
                    <span class="activity-icon">🎉</span>
                    <div>
                        <h3 class="activity-name">Tham Gia Sự Kiện</h3>
                        <span class="activity-points">+5 điểm/sự kiện</span>
                    </div>
                </div>
                <p class="activity-desc">Tham gia workshop, clinic, sự kiện cộng đồng</p>
                <div class="activity-action">
                    <a href="{{ route('ocr.community.index') }}" class="btn btn-purple">
                        Xem Sự Kiện
                    </a>
                </div>
            </div>

            {{-- Monthly Challenge --}}
            <div class="activity-card {{ !$availableActivities['monthly_challenge']['available'] ? 'disabled' : '' }}">
                <div class="activity-header">
                    <span class="activity-icon">🏆</span>
                    <div>
                        <h3 class="activity-name">Thử Thách Tháng</h3>
                        <span class="activity-points">+15 điểm</span>
                    </div>
                </div>
                <p class="activity-desc">Hoàn thành nhiệm vụ đặc biệt hàng tháng</p>
                <div class="activity-action">
                    <div class="activity-unavailable">
                        {{ $availableActivities['monthly_challenge']['reason'] ?? 'Sắp ra mắt' }}
                    </div>
                </div>
            </div>

            {{-- Social: Join Group OnePickleball --}}
            <div class="activity-card {{ !$availableActivities['join_group']['available'] ? 'disabled' : '' }}">
                <div class="activity-header">
                    <span class="activity-icon">👥</span>
                    <div>
                        <h3 class="activity-name">Join Group OnePickleball</h3>
                        <span class="activity-points">+5 điểm</span>
                    </div>
                </div>
                <p class="activity-desc">Tham gia nhóm cộng đồng OnePickleball</p>
                <div class="activity-action">
                    @if($availableActivities['join_group']['available'])
                        <button onclick="recordSocialActivity('join_group')" class="btn btn-purple">
                            Tham Gia Nhóm
                        </button>
                    @else
                        <div class="activity-unavailable">
                            {{ $availableActivities['join_group']['reason'] ?? 'Sắp ra mắt' }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Social: Follow FB Channel --}}
            <div class="activity-card {{ !$availableActivities['follow_fb']['available'] ? 'disabled' : '' }}">
                <div class="activity-header">
                    <span class="activity-icon">📘</span>
                    <div>
                        <h3 class="activity-name">Follow Kênh Facebook</h3>
                        <span class="activity-points">+5 điểm</span>
                    </div>
                </div>
                <p class="activity-desc">Theo dõi trang Facebook chính thức</p>
                <div class="activity-action">
                    @if($availableActivities['follow_fb']['available'])
                        <button onclick="recordSocialActivity('follow_fb')" class="btn btn-purple">
                            Follow Ngay
                        </button>
                    @else
                        <div class="activity-unavailable">
                            {{ $availableActivities['follow_fb']['reason'] ?? 'Sắp ra mắt' }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Social: Follow Youtube Channel --}}
            <div class="activity-card {{ !$availableActivities['follow_youtube']['available'] ? 'disabled' : '' }}">
                <div class="activity-header">
                    <span class="activity-icon">▶️</span>
                    <div>
                        <h3 class="activity-name">Follow Kênh Youtube</h3>
                        <span class="activity-points">+5 điểm</span>
                    </div>
                </div>
                <p class="activity-desc">Đăng ký kênh Youtube OnePickleball</p>
                <div class="activity-action">
                    @if($availableActivities['follow_youtube']['available'])
                        <button onclick="recordSocialActivity('follow_youtube')" class="btn btn-purple">
                            Đăng Ký Kênh
                        </button>
                    @else
                        <div class="activity-unavailable">
                            {{ $availableActivities['follow_youtube']['reason'] ?? 'Sắp ra mắt' }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Social: Follow TikTok Channel --}}
            <div class="activity-card {{ !$availableActivities['follow_tiktok']['available'] ? 'disabled' : '' }}">
                <div class="activity-header">
                    <span class="activity-icon">🎵</span>
                    <div>
                        <h3 class="activity-name">Follow Kênh TikTok</h3>
                        <span class="activity-points">+5 điểm</span>
                    </div>
                </div>
                <p class="activity-desc">Theo dõi TikTok OnePickleball</p>
                <div class="activity-action">
                    @if($availableActivities['follow_tiktok']['available'])
                        <button onclick="recordSocialActivity('follow_tiktok')" class="btn btn-purple">
                            Follow TikTok
                        </button>
                    @else
                        <div class="activity-unavailable">
                            {{ $availableActivities['follow_tiktok']['reason'] ?? 'Sắp ra mắt' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="history-card">
            <div class="history-header">Hoạt Động Gần Đây</div>
            <div class="history-list">
                @forelse($history as $activity)
                <div class="history-item">
                    <div class="history-info">
                        <span class="history-icon">{{ \App\Models\CommunityActivity::getActivityInfo($activity->activity_type)['icon'] ?? '⭐' }}</span>
                        <div>
                            <div class="history-name">{{ \App\Models\CommunityActivity::getActivityInfo($activity->activity_type)['name'] }}</div>
                            @if($activity->metadata)
                                <div class="history-meta">
                                    {{ $activity->metadata['stadium_name'] ?? $activity->metadata['event_name'] ?? $activity->metadata['referred_user_name'] ?? '' }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="history-result">
                        <div class="history-points">+{{ number_format($activity->points_earned, 0) }} điểm</div>
                        <div class="history-date">{{ $activity->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="empty-message">
                    <div class="empty-icon">👥</div>
                    <p>Chưa có hoạt động nào. Hãy bắt đầu kiếm điểm cộng đồng!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
<script>
function copyReferralLink() {
    const link = '{{ url('/register?ref=' . auth()->user()->id) }}';
    navigator.clipboard.writeText(link).then(() => {
        toastr.success('Đã sao chép link giới thiệu!');
    }).catch(() => {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = link;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        toastr.success('Đã sao chép link giới thiệu!');
    });
}

function recordSocialActivity(activityType) {
    // Open social link in new tab
    const socialLinks = {
        'join_group': 'https://www.facebook.com/groups/onepickleball',
        'follow_fb': 'https://www.facebook.com/search/top?q=onepickleball',
        'follow_youtube': 'https://www.youtube.com/@OnePickleballvn',
        'follow_tiktok': 'https://www.tiktok.com/@onepickleball1',
    };

    if (socialLinks[activityType]) {
        window.open(socialLinks[activityType], '_blank');
    }

    // Record the activity via API
    fetch('{{ route("ocr.community.social-activity") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            activity_type: activityType,
        }),
    })
    .then(response => {
        // Log response for debugging
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then(text => {
            console.log('Response text:', text);
            return text ? JSON.parse(text) : {};
        });
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            toastr.success(data.message);
            // Reload page to update available activities
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            toastr.error(data.error || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Có lỗi xảy ra: ' + error.message);
    });
}
</script>
@endsection
