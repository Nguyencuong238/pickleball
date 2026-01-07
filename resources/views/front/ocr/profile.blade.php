@extends('layouts.front')

@section('css')
<style>
    .profile-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        padding: 9rem 0;
        color: white;
    }

    .profile-header-content {
        display: flex;
        align-items: flex-start;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 3rem;
        border: 4px solid rgba(255, 255, 255, 0.2);
    }

    .profile-info {
        flex: 1;
    }

    .profile-name {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .profile-rank-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .rank-bronze { background: #fef3c7; color: #b45309; }
    .rank-silver { background: #f1f5f9; color: #475569; }
    .rank-gold { background: #fef3c7; color: #d97706; }
    .rank-platinum { background: #e0f2fe; color: #0284c7; }
    .rank-diamond { background: #ede9fe; color: #7c3aed; }
    .rank-master { background: #fce7f3; color: #db2777; }
    .rank-grandmaster { background: #fee2e2; color: #dc2626; }

    .profile-stats {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .profile-stat {
        text-align: center;
    }

    .profile-stat-value {
        font-size: 1.75rem;
        font-weight: 800;
    }

    .profile-stat-label {
        font-size: 0.75rem;
        opacity: 0.8;
        text-transform: uppercase;
    }

    .profile-section {
        padding: 2rem 0;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-header {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 700;
        color: #1e293b;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Badges */
    .badges-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .badge-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .badge-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
    }

    .badge-name {
        font-size: 0.75rem;
        font-weight: 600;
        color: #1e293b;
    }

    .badge-progress {
        margin-top: 1.5rem;
    }

    .progress-item {
        margin-bottom: 1rem;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .progress-name {
        font-weight: 600;
        color: #1e293b;
    }

    .progress-count {
        color: #64748b;
    }

    .progress-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #00D9B5, #0099CC);
        border-radius: 4px;
        transition: width 0.3s;
    }

    /* Elo History */
    .elo-history-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .elo-history-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .elo-history-item:last-child {
        border-bottom: none;
    }

    .elo-change {
        font-weight: 700;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }

    .elo-change.positive {
        background: #dcfce7;
        color: #166534;
    }

    .elo-change.negative {
        background: #fee2e2;
        color: #991b1b;
    }

    .elo-reason {
        font-size: 0.875rem;
        color: #64748b;
    }

    .elo-date {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    /* Recent Matches */
    .match-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .match-item:last-child {
        border-bottom: none;
    }

    .match-players {
        font-size: 0.875rem;
    }

    .match-players .vs {
        color: #64748b;
        margin: 0 0.25rem;
    }

    .match-result {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .match-score {
        font-weight: 700;
        color: #1e293b;
    }

    .match-outcome {
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .outcome-win {
        background: #dcfce7;
        color: #166534;
    }

    .outcome-loss {
        background: #fee2e2;
        color: #991b1b;
    }

    .empty-message {
        text-align: center;
        padding: 2rem;
        color: #94a3b8;
    }

    @media (max-width: 768px) {
        .profile-header-content {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .profile-stats {
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<section class="profile-header">
    <div class="container">
        <div class="profile-header-content">
            <div class="profile-avatar">
                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>
            <div class="profile-info">
                <h1 class="profile-name">{{ $user->name }}</h1>
                @if($user->elo_rank)
                    <span class="profile-rank-badge rank-{{ strtolower($user->elo_rank) }}">
                        {{ $user->elo_rank }}
                    </span>
                @endif

                {{-- Verification Status Badge --}}
                <div class="verification-status" style="margin-bottom: 0.75rem;">
                    @if($user->is_elo_verified)
                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; background: #dcfce7; color: #166534; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                            ✓ Đã Xác Minh
                        </span>
                    @elseif($user->elo_is_provisional)
                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; background: #fef3c7; color: #92400e; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                            ⏳ Chưa Xác Minh
                        </span>
                        @auth
                            @if(auth()->id() === $user->id)
                                @if($user->canRequestVerification())
                                    <a href="{{ route('opr-verification.create') }}"
                                       style="display: inline-flex; align-items: center; gap: 0.25rem; background: linear-gradient(135deg, #00D9B5, #0099CC); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; margin-left: 0.5rem;">
                                        Gửi Xác Minh
                                    </a>
                                @elseif($user->hasPendingVerificationRequest())
                                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; background: #dbeafe; color: #1e40af; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600; margin-left: 0.5rem;">
                                        Đang Chờ Duyệt
                                    </span>
                                @endif
                            @endif
                        @endauth
                    @endif
                </div>

                <div class="profile-stats">
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ number_format($user->total_oprs, 0) }}</div>
                        <div class="profile-stat-label">OPRS</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">#{{ $globalRank }}</div>
                        <div class="profile-stat-label">Thứ Hạng</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $user->elo_rating }}</div>
                        <div class="profile-stat-label">Điểm Elo</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $user->total_ocr_matches }}</div>
                        <div class="profile-stat-label">Trận Đấu</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $user->ocr_wins }}</div>
                        <div class="profile-stat-label">Thắng</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $user->ocr_losses }}</div>
                        <div class="profile-stat-label">Thua</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">
                            {{ $user->total_ocr_matches > 0 ? round(($user->ocr_wins / $user->total_ocr_matches) * 100) : 0 }}%
                        </div>
                        <div class="profile-stat-label">Tỷ Lệ Thắng</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="profile-section">
    <div class="container">
        {{-- OPRS Section --}}
        <div class="content-grid" style="margin-bottom: 2rem;">
            <x-oprs.score-card :user="$user" :breakdown="$oprsBreakdown" />
            <x-oprs.breakdown-chart :breakdown="$oprsBreakdown" />
        </div>

        {{-- Quick Actions for OPRS --}}
        @auth
            @if(auth()->id() === $user->id)
            <div class="oprs-actions" style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                <!-- <a href="{{ route('ocr.challenges.index') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    🎯 Trung Tâm Thử Thách
                </a> -->
                <a href="{{ route('ocr.community.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    👥 Cộng Đồng
                </a>
            </div>
            @endif
        @endauth

        <div class="content-grid">
            {{-- Badges --}}
            <div class="card">
                <div class="card-header">🏅 Huy Hiệu ({{ $user->badges->count() }})</div>
                <div class="card-body">
                    @if($user->badges->isEmpty())
                        <div class="empty-message">Chưa có huy hiệu nào</div>
                    @else
                        <div class="badges-grid">
                            @foreach($user->badges as $badge)
                                @php
                                    $badgeInfo = \App\Models\UserBadge::getBadgeInfo($badge->badge_type) ?? [];
                                @endphp
                                <div class="badge-item" title="{{ $badgeInfo['description'] ?? '' }}">
                                    <div class="badge-icon">{{ $badgeInfo['icon'] ?? '🏅' }}</div>
                                    <div class="badge-name">{{ $badgeInfo['name'] ?? $badge->badge_type }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($badgeProgress))
                         <div class="badge-progress">
                             <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; color: #64748b;">
                                 Tiến Trình
                             </h4>
                            @foreach($badgeProgress as $type => $progress)
                                @if(isset($progress['current']) && isset($progress['target']) && $progress['current'] < $progress['target'])
                                    <div class="progress-item">
                                        <div class="progress-header">
                                            <span class="progress-name">{{ $progress['name'] ?? $type }}</span>
                                            <span class="progress-count">{{ $progress['current'] }}/{{ $progress['target'] }}</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: {{ min(100, ($progress['current'] / $progress['target']) * 100) }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Elo History --}}
            <div class="card">
                <div class="card-header">📊 Lịch Sử Elo</div>
                <div class="card-body">
                    @if($eloHistory->isEmpty())
                        <div class="empty-message">Chưa có lịch sử Elo</div>
                    @else
                        <div class="elo-history-list">
                            @foreach($eloHistory as $history)
                                <div class="elo-history-item">
                                    <div>
                                        <div class="elo-reason">{{ $history->reason }}</div>
                                        <div class="elo-date">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <span class="elo-change {{ $history->change > 0 ? 'positive' : 'negative' }}">
                                        {{ $history->change > 0 ? '+' : '' }}{{ $history->change }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Matches --}}
            <div class="card" style="grid-column: 1 / -1;">
                <div class="card-header">🏓 Trận Đấu Gần Đây</div>
                <div class="card-body">
                    @if($recentMatches->isEmpty())
                        <div class="empty-message">Chưa có trận đấu nào</div>
                    @else
                        @foreach($recentMatches as $match)
                            @php
                                $isChallenger = $match->challenger_id === $user->id || $match->challenger_partner_id === $user->id;
                                $userWon = ($isChallenger && $match->winner_team === 'challenger') ||
                                           (!$isChallenger && $match->winner_team === 'opponent');
                            @endphp
                            <div class="match-item">
                                <div class="match-players">
                                    <span>{{ $match->challenger->name ?? 'Unknown' }}</span>
                                    <span class="vs">vs</span>
                                    <span>{{ $match->opponent->name ?? 'Unknown' }}</span>
                                </div>
                                <div class="match-result">
                                    <span class="match-score">{{ $match->challenger_score }} - {{ $match->opponent_score }}</span>
                                    <span class="match-outcome {{ $userWon ? 'outcome-win' : 'outcome-loss' }}">
                                        {{ $userWon ? 'Thắng' : 'Thua' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('ocr.leaderboard') }}" class="btn btn-outline">
                Xem Bảng Xếp Hạng
            </a>
            @auth
                @if(auth()->id() !== $user->id)
                    <a href="{{ route('ocr.matches.create') }}?opponent={{ $user->id }}" class="btn btn-primary">
                        Thách Đấu
                    </a>
                @endif
            @endauth
        </div>
    </div>
</section>
@endsection
