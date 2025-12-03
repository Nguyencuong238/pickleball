@extends('layouts.front')

@section('css')
<style>
    .ocr-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        padding: 4rem 0 3rem;
        color: white;
    }

    .ocr-hero-content {
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
    }

    .ocr-hero-badge {
        display: inline-block;
        background: rgba(0, 217, 181, 0.2);
        color: var(--primary-color);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .ocr-hero-title {
        font-size: clamp(2rem, 5vw, 3rem);
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .ocr-hero-description {
        font-size: 1.125rem;
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 2rem;
    }

    .ocr-hero-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .ocr-section {
        padding: 3rem 0;
    }

    .ocr-section-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .leaderboard-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .leaderboard-header {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        padding: 1.25rem;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .leaderboard-header h3 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }

    .leaderboard-table {
        width: 100%;
    }

    .leaderboard-table th,
    .leaderboard-table td {
        padding: 1rem;
        text-align: left;
    }

    .leaderboard-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #64748b;
        font-size: 0.875rem;
    }

    .leaderboard-table tr:not(:last-child) td {
        border-bottom: 1px solid #e2e8f0;
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .rank-1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; }
    .rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); color: white; }
    .rank-3 { background: linear-gradient(135deg, #d97706, #b45309); color: white; }
    .rank-default { background: #f1f5f9; color: #64748b; }

    .player-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .player-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .player-name {
        font-weight: 600;
        color: #1e293b;
    }

    .player-rank-title {
        font-size: 0.75rem;
        color: #64748b;
    }

    .elo-rating {
        font-weight: 700;
        color: #1e293b;
    }

    .win-rate {
        color: #10b981;
        font-weight: 600;
    }

    .recent-matches-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .recent-matches-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        padding: 1.25rem;
        color: white;
    }

    .recent-matches-header h3 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }

    .match-item {
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e2e8f0;
    }

    .match-item:last-child {
        border-bottom: none;
    }

    .match-players {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .match-vs {
        font-weight: 600;
        color: #64748b;
        font-size: 0.875rem;
    }

    .match-score {
        font-weight: 700;
        font-size: 1.125rem;
        color: #1e293b;
    }

    .match-date {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .user-rank-card {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        color: white;
        text-align: center;
    }

    .user-rank-card h4 {
        font-size: 0.875rem;
        font-weight: 600;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }

    .user-rank-position {
        font-size: 3rem;
        font-weight: 800;
        line-height: 1;
    }

    .user-rank-label {
        font-size: 0.875rem;
        opacity: 0.8;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        text-align: center;
    }

    .stat-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
    }

    .stat-card-value {
        font-size: 2rem;
        font-weight: 800;
        color: #1e293b;
    }

    .stat-card-label {
        font-size: 0.875rem;
        color: #64748b;
    }

    .cta-section {
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        padding: 3rem;
        border-radius: 1rem;
        color: white;
        text-align: center;
    }

    .cta-section h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .cta-section p {
        opacity: 0.9;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .leaderboard-table th:nth-child(4),
        .leaderboard-table td:nth-child(4) {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<section class="ocr-hero">
    <div class="container">
        <div class="ocr-hero-content">
            <span class="ocr-hero-badge">[TROPHY] OnePickleball Championship Ranking</span>
            <h1 class="ocr-hero-title">Xep Hang Elo Pickleball</h1>
            <p class="ocr-hero-description">
                Tu to chuc tran dau, tich luy diem Elo, nhan huy hieu va leo hang trong cong dong Pickleball Viet Nam
            </p>
            <div class="ocr-hero-actions">
                @auth
                    <a href="{{ route('ocr.matches.create') }}" class="btn btn-primary btn-lg">Tao Tran Dau Moi</a>
                    <a href="{{ route('ocr.matches.index') }}" class="btn btn-secondary btn-lg">Quan Ly Tran Dau</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Dang Nhap De Tham Gia</a>
                @endauth
                <a href="{{ route('ocr.leaderboard') }}" class="btn btn-outline color-white">Xem Bang Xep Hang</a>
            </div>
        </div>
    </div>
</section>

<section class="ocr-section">
    <div class="container">
        @auth
            @if($userRank)
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.1);">
                            [RANK]
                        </div>
                        <div class="stat-card-value">#{{ $userRank }}</div>
                        <div class="stat-card-label">Thu Hang Cua Ban</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon" style="background: rgba(59, 130, 246, 0.1);">
                            [STAR]
                        </div>
                        <div class="stat-card-value">{{ auth()->user()->elo_rating }}</div>
                        <div class="stat-card-label">Diem Elo</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon" style="background: rgba(251, 191, 36, 0.1);">
                            [GAME]
                        </div>
                        <div class="stat-card-value">{{ auth()->user()->total_ocr_matches }}</div>
                        <div class="stat-card-label">Tong So Tran</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.1);">
                            [WIN]
                        </div>
                        <div class="stat-card-value">
                            {{ auth()->user()->total_ocr_matches > 0 ? round((auth()->user()->ocr_wins / auth()->user()->total_ocr_matches) * 100) : 0 }}%
                        </div>
                        <div class="stat-card-label">Ti Le Thang</div>
                    </div>
                </div>
            @endif
        @endauth

        <div class="row" style="display: flex; flex-wrap: wrap; gap: 2rem;">
            <div style="flex: 2; min-width: 300px;">
                <div class="leaderboard-card">
                    <div class="leaderboard-header">
                        <h3>[TROPHY] Top 10 Bang Xep Hang</h3>
                        <a href="{{ route('ocr.leaderboard') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white;">
                            Xem Tat Ca
                        </a>
                    </div>
                    <table class="leaderboard-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nguoi Choi</th>
                                <th>Elo</th>
                                <th>Ti Le Thang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPlayers as $index => $player)
                                <tr>
                                    <td>
                                        <span class="rank-badge {{ $index < 3 ? 'rank-' . ($index + 1) : 'rank-default' }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="player-info">
                                            <div class="player-avatar">
                                                {{ strtoupper(mb_substr($player->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="player-name">
                                                    <a href="{{ route('ocr.profile', $player) }}" style="color: inherit; text-decoration: none;">
                                                        {{ $player->name }}
                                                    </a>
                                                </div>
                                                <div class="player-rank-title">{{ $player->elo_rank ?? 'Unranked' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="elo-rating">{{ $player->elo_rating }}</span>
                                    </td>
                                    <td>
                                        <span class="win-rate">
                                            {{ $player->total_ocr_matches > 0 ? round(($player->ocr_wins / $player->total_ocr_matches) * 100) : 0 }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                        Chua co du lieu xep hang. Hay la nguoi dau tien!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="flex: 1; min-width: 280px;">
                <div class="recent-matches-card">
                    <div class="recent-matches-header">
                        <h3>[GAME] Tran Dau Gan Day</h3>
                    </div>
                    <div>
                        @forelse($recentMatches as $match)
                            <div class="match-item">
                                <div class="match-players">
                                    <span>{{ $match->challenger->name ?? 'Unknown' }}</span>
                                    <span class="match-vs">vs</span>
                                    <span>{{ $match->opponent->name ?? 'Unknown' }}</span>
                                </div>
                                <div style="text-align: right;">
                                    <div class="match-score">{{ $match->challenger_score }} - {{ $match->opponent_score }}</div>
                                    <div class="match-date">{{ $match->confirmed_at?->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <div style="padding: 2rem; text-align: center; color: #94a3b8;">
                                Chua co tran dau nao duoc hoan thanh
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @guest
            <div class="cta-section" style="margin-top: 2rem;">
                <h3>San Sang Tham Gia Cuoc Dua?</h3>
                <p>Dang ky tai khoan de bat dau thi dau, tich luy diem Elo va chinh phuc bang xep hang!</p>
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg" style="background: white; color: #0099CC;">
                    Dang Ky Ngay
                </a>
            </div>
        @endguest
    </div>
</section>
@endsection
