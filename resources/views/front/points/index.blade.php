@extends('layouts.front')

@section('title', 'Kiếm Điểm')

@section('css')
<style>
    .points-header {
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        color: white;
    }
    .points-balance {
        font-size: 2rem;
        font-weight: 700;
    }
    .task-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 12px;
        overflow: hidden;
    }
    .task-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .task-card.disabled {
        opacity: 0.7;
    }
    .challenge-banner {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .category-header {
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    .points-badge {
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
        border-radius: 20px;
    }
    .social-status-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1rem;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
    }
    .quick-links {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .quick-link {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.2s;
    }
    .quick-link:hover {
        transform: translateY(-1px);
    }
</style>
@endsection

@section('content')
<div class="container py-4 page-content">
    {{-- Header with Balance --}}
    <div class="points-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 text-white">Kiếm Điểm OnePickleball</h1>
                <p class="mb-0 opacity-75">Hoàn thành nhiệm vụ để nhận điểm thưởng</p>
            </div>
            <div class="text-end">
                <div class="points-balance">{{ number_format($balance) }}</div>
                <small class="opacity-75">Điểm hiện tại</small>
                @if($pendingCount > 0)
                    <br><small class="opacity-75">{{ $pendingCount }} yêu cầu đang chờ duyệt</small>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="quick-links mb-4">
        <a href="{{ route('user.points.history') }}" class="quick-link bg-white border">📜 Lịch sử điểm</a>
        <a href="{{ route('user.points.submissions') }}" class="quick-link bg-white border">📄 Các yêu cầu của tôi</a>
        <a href="{{ route('user.wallet.index') }}" class="quick-link bg-white border">💰 Ví điểm</a>
    </div>

    {{-- Special Challenges Banner --}}
    @if($challenges->count() > 0)
        <div class="challenge-banner text-dark">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-danger">🔥 HOT</span>
                <h5 class="mb-0 fw-bold">Thách Thức Đặc Biệt</h5>
            </div>
            @foreach($challenges as $challenge)
                <div class="d-flex justify-content-between align-items-center bg-white rounded p-2 mb-2">
                    <div>
                        <strong>{{ $challenge->title }}</strong>
                        @if($challenge->description)
                            <br><small class="text-muted">{{ Str::limit($challenge->description, 80) }}</small>
                        @endif
                        <br><small class="text-muted">Kết thúc: {{ $challenge->end_date->format('d/m/Y') }}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-danger points-badge">+{{ $challenge->points }} điểm</span>
                        @if(!$challenge->hasReachedLimit() && $specialChallengeTask)
                            <br><a href="{{ route('user.points.submit-form', $specialChallengeTask) }}" class="btn btn-sm btn-dark mt-1">Tham gia</a>
                        @elseif($challenge->hasReachedLimit())
                            <br><span class="badge bg-secondary mt-1">Hết chỗ</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Task Categories --}}
    @php
        $tasksByCategory = $tasks->groupBy(fn($t) => $t['task']->category);
        $categoryLabels = [
            'daily' => ['label' => 'Nhiệm Vụ Hàng Ngày', 'icon' => '📅'],
            'social' => ['label' => 'Nhiệm Vụ Mạng Xã Hội', 'icon' => '📣'],
            'event' => ['label' => 'Sự Kiện & Workshop', 'icon' => '🎟️'],
            'tournament' => ['label' => 'Giải Đấu & Cộng Đồng', 'icon' => '🏆'],
        ];
    @endphp

    @foreach($categoryLabels as $category => $info)
        @if($tasksByCategory->has($category))
            <div class="mb-4">
                <div class="category-header">
                    <h5 class="mb-0">{{ $info['icon'] }} {{ $info['label'] }}</h5>
                </div>
                <div class="row g-3">
                    @foreach($tasksByCategory[$category] as $item)
                        @php $task = $item['task']; @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="card task-card h-100 {{ $item['can_earn'] ? '' : 'disabled' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">{{ $task->name }}</h6>
                                        <span class="badge bg-success points-badge">+{{ $task->points }}</span>
                                    </div>
                                    <p class="card-text small text-muted mb-2">{{ $task->description }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-secondary me-1">{{ ['once' => '1 lần', 'unlimited' => 'Vô hạn', 'daily' => 'Hàng ngày', 'weekly' => 'Hàng tuần', 'monthly' => 'Hàng tháng'][$task->frequency] ?? ucfirst($task->frequency) }}</span>
                                            @if($task->requires_approval)
                                                <span class="badge bg-info">Cần duyệt</span>
                                            @endif
                                        </div>
                                        <div>
                                            @if($item['can_earn'])
                                                @if($task->requires_approval)
                                                    <a href="{{ route('user.points.submit-form', $task) }}" class="btn btn-sm btn-primary">Gửi yêu cầu</a>
                                                @else
                                                    @php $actionUrl = $task->getActionUrl(); @endphp
                                                    @if($actionUrl)
                                                        <a href="{{ $actionUrl }}" class="btn btn-sm btn-success">{{ $task->getActionLabel() }}</a>
                                                    @else
                                                        <span class="badge bg-primary">Tự động</span>
                                                    @endif
                                                @endif
                                            @else
                                                <small class="text-danger">{{ $item['reason'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    {{-- Social Verification Status --}}
    <div class="social-status-card mt-4">
        <h5 class="mb-3">🔗 Trạng Thái Xác Minh Mạng Xã Hội</h5>
        <div class="row text-center">
            <div class="col-md-4 mb-2">
                @if($socialStatus['facebook'])
                    <span class="status-badge bg-success text-white">✅ Facebook đã xác minh</span>
                @else
                    <span class="status-badge bg-secondary text-white">Facebook chưa xác minh</span>
                @endif
            </div>
            <div class="col-md-4 mb-2">
                @if($socialStatus['youtube'])
                    <span class="status-badge bg-success text-white">✅ YouTube đã xác minh</span>
                @else
                    <span class="status-badge bg-secondary text-white">YouTube chưa xác minh</span>
                @endif
            </div>
            <div class="col-md-4 mb-2">
                @if($socialStatus['tiktok'])
                    <span class="status-badge bg-success text-white">✅ TikTok đã xác minh</span>
                @else
                    <span class="status-badge bg-secondary text-white">TikTok chưa xác minh</span>
                @endif
            </div>
        </div>
        <p class="small text-muted text-center mt-2 mb-0">
            Xác minh tài khoản mạng xã hội để nhận điểm từ các nhiệm vụ liên quan.
        </p>
    </div>
</div>
@endsection
