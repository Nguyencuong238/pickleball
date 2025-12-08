@extends('layouts.front')

@section('title', 'Trung Tâm Thử Thách - OPRS')

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

    .challenge-section {
        padding: 2rem 0;
    }

    .stats-banner {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
        color: #86efac;
    }

    .stats-secondary .stats-note {
        font-size: 0.75rem;
        opacity: 0.7;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .challenges-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .challenge-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .challenge-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .challenge-card.disabled {
        opacity: 0.6;
    }

    .challenge-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .challenge-info {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .challenge-icon {
        font-size: 1.5rem;
    }

    .challenge-name {
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 0.25rem 0;
    }

    .challenge-desc {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
    }

    .challenge-points {
        background: #dcfce7;
        color: #166534;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .challenge-action {
        margin-top: 1rem;
    }

    .challenge-action .btn {
        width: 100%;
        display: block;
        text-align: center;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }

    .challenge-action .btn-primary {
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
        color: white;
    }

    .challenge-action .btn-primary:hover {
        background: linear-gradient(90deg, #2563eb, #1e40af);
    }

    .challenge-unavailable {
        text-align: center;
        padding: 0.75rem;
        background: #f1f5f9;
        border-radius: 0.5rem;
        color: #64748b;
        font-size: 0.875rem;
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

    .history-status {
        font-size: 1.25rem;
    }

    .history-status.passed {
        color: #22c55e;
    }

    .history-status.failed {
        color: #ef4444;
    }

    .history-name {
        font-weight: 500;
        color: #1e293b;
    }

    .history-score {
        font-size: 0.875rem;
        color: #64748b;
    }

    .history-result {
        text-align: right;
    }

    .history-points {
        font-weight: 600;
        color: #22c55e;
    }

    .history-points.zero {
        color: #94a3b8;
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

        .challenges-grid {
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
                    Trung Tâm Thử Thách
                </p>
                <h1 class="page-title">🎯 Trung Tâm Thử Thách</h1>
            </div>
            <a href="{{ route('ocr.profile', auth()->user()) }}" class="btn btn-outline" style="color: white">
                ← Quay Lại Hồ Sơ
            </a>
        </div>
    </div>
</section>

<section class="challenge-section">
    <div class="container">
        {{-- Stats Banner --}}
        <div class="stats-banner">
            <div class="stats-main">
                <h3>Điểm Thử Thách Của Bạn</h3>
                <div class="stats-value">{{ number_format($user->challenge_score, 0) }}</div>
                <div class="stats-contrib">Đóng góp {{ number_format($user->challenge_score * 0.2, 2) }} vào OPRS</div>
            </div>
            <div class="stats-secondary">
                <div class="stats-label">Tổng Thử Thách Đã Vượt Qua</div>
                <div class="stats-count">{{ $stats['passed'] }}</div>
                <div class="stats-note">trong {{ $stats['total'] }} lần thử</div>
            </div>
        </div>

        {{-- Available Challenges --}}
        <h2 class="section-title">Thử Thách Có Sẵn</h2>
        <div class="challenges-grid">
            @foreach($availableChallenges as $type => $challenge)
            <div class="challenge-card {{ !$challenge['available'] ? 'disabled' : '' }}">
                <div class="challenge-header">
                    <div class="challenge-info">
                        <span class="challenge-icon">{{ $challenge['info']['icon'] ?? '⭐' }}</span>
                        <div>
                            <h3 class="challenge-name">{{ $challenge['info']['name'] }}</h3>
                            <p class="challenge-desc">{{ $challenge['info']['description'] }}</p>
                        </div>
                    </div>
                    <span class="challenge-points">
                        +{{ is_array($challenge['info']['points']) ? ($challenge['info']['points']['min'] . '-' . $challenge['info']['points']['max']) : $challenge['info']['points'] }} pts
                    </span>
                </div>

                <div class="challenge-action">
                    @if($challenge['available'])
                        <a href="{{ route('ocr.challenges.submit', $type) }}" class="btn btn-primary">
                            Bắt Đầu Thử Thách
                        </a>
                    @else
                        <div class="challenge-unavailable">
                            {{ $challenge['reason'] ?? 'Chưa khả dụng' }}
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Recent Challenge History --}}
        <div class="history-card">
            <div class="history-header">Thử Thách Gần Đây</div>
            <div class="history-list">
                @forelse($history as $result)
                <div class="history-item">
                    <div class="history-info">
                        <span class="history-status {{ $result->passed ? 'passed' : 'failed' }}">
                            {{ $result->passed ? '✅' : '❌' }}
                        </span>
                        <div>
                            <div class="history-name">{{ \App\Models\ChallengeResult::getChallengeInfo($result->challenge_type)['name'] }}</div>
                            <div class="history-score">Điểm: {{ $result->score }}</div>
                        </div>
                    </div>
                    <div class="history-result">
                        @if($result->passed)
                            <div class="history-points">+{{ number_format($result->points_earned, 0) }} điểm</div>
                        @else
                            <div class="history-points zero">Chưa vượt qua</div>
                        @endif
                        <div class="history-date">{{ $result->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="empty-message">
                    <div class="empty-icon">🎯</div>
                    <p>Chưa có thử thách nào. Hãy bắt đầu thử thách đầu tiên!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
