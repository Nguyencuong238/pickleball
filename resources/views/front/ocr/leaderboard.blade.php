@extends('layouts.front')

@section('css')
<style>
    .page-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        padding: 3rem 0;
        color: white;
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

    .leaderboard-section {
        padding: 2rem 0;
    }

    .user-rank-banner {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        color: white;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .user-rank-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-rank-position {
        font-size: 2.5rem;
        font-weight: 800;
    }

    .user-rank-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .user-rank-stats {
        display: flex;
        gap: 2rem;
    }

    .user-stat {
        text-align: center;
    }

    .user-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .user-stat-label {
        font-size: 0.75rem;
        opacity: 0.8;
    }

    .filter-section {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        color: #64748b;
        background: #f1f5f9;
        transition: all 0.2s;
    }

    .filter-tab:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .filter-tab.active {
        background: var(--primary-color);
        color: white;
    }

    .leaderboard-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .leaderboard-table {
        width: 100%;
    }

    .leaderboard-table th,
    .leaderboard-table td {
        padding: 1rem 1.5rem;
        text-align: left;
    }

    .leaderboard-table th {
        background: #f8fafc;
        font-weight: 600;
        color: #64748b;
        font-size: 0.875rem;
    }

    .leaderboard-table tr {
        border-bottom: 1px solid #e2e8f0;
    }

    .leaderboard-table tr:last-child {
        border-bottom: none;
    }

    .leaderboard-table tbody tr:hover {
        background: #f8fafc;
    }

    .leaderboard-table tbody tr.current-user {
        background: rgba(0, 217, 181, 0.1);
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-weight: 700;
        font-size: 1rem;
    }

    .rank-1 {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
        box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
    }
    .rank-2 {
        background: linear-gradient(135deg, #94a3b8, #64748b);
        color: white;
    }
    .rank-3 {
        background: linear-gradient(135deg, #d97706, #b45309);
        color: white;
    }
    .rank-default {
        background: #f1f5f9;
        color: #64748b;
    }

    .player-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .player-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.125rem;
    }

    .player-details {
        flex: 1;
    }

    .player-name {
        font-weight: 700;
        color: #1e293b;
        font-size: 1rem;
    }

    .player-name a {
        color: inherit;
        text-decoration: none;
    }

    .player-name a:hover {
        text-decoration: underline;
    }

    .player-rank-title {
        display: inline-block;
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: 0.25rem;
    }

    .rank-bronze { background: #fef3c7; color: #b45309; }
    .rank-silver { background: #f1f5f9; color: #475569; }
    .rank-gold { background: #fef3c7; color: #d97706; }
    .rank-platinum { background: #e0f2fe; color: #0284c7; }
    .rank-diamond { background: #ede9fe; color: #7c3aed; }
    .rank-master { background: #fce7f3; color: #db2777; }
    .rank-grandmaster { background: #fee2e2; color: #dc2626; }

    .elo-rating {
        font-weight: 700;
        font-size: 1.125rem;
        color: #1e293b;
    }

    .stats-cell {
        display: flex;
        gap: 1.5rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-weight: 700;
        color: #1e293b;
    }

    .stat-value.wins { color: #10b981; }
    .stat-value.losses { color: #ef4444; }

    .stat-label {
        font-size: 0.7rem;
        color: #94a3b8;
        text-transform: uppercase;
    }

    .win-rate {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .win-rate-bar {
        width: 60px;
        height: 8px;
        background: #fee2e2;
        border-radius: 4px;
        overflow: hidden;
    }

    .win-rate-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #34d399);
        border-radius: 4px;
    }

    .win-rate-text {
        font-weight: 600;
        color: #10b981;
        font-size: 0.875rem;
    }

    .pagination-wrapper {
        padding: 1.5rem;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .leaderboard-table th:nth-child(4),
        .leaderboard-table td:nth-child(4),
        .leaderboard-table th:nth-child(5),
        .leaderboard-table td:nth-child(5) {
            display: none;
        }

        .user-rank-banner {
            flex-direction: column;
            text-align: center;
        }

        .user-rank-info {
            flex-direction: column;
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
                     <a href="{{ route('ocr.index') }}">OCR</a> / Bảng Xếp Hạng
                 </p>
                 <h1 class="page-title">[TROPHY] Bảng Xếp Hạng Elo</h1>
            </div>
            @auth
                <a href="{{ route('ocr.matches.create') }}" class="btn btn-primary">
                    + Tạo Trận Đấu
                </a>
            @endauth
        </div>
    </div>
</section>

<section class="leaderboard-section">
    <div class="container">
        @auth
            @if($userRank)
                <div class="user-rank-banner">
                    <div class="user-rank-info">
                         <div>
                             <div class="user-rank-label">Thứ Hạng Của Bạn</div>
                             <div class="user-rank-position">#{{ $userRank }}</div>
                         </div>
                     </div>
                     <div class="user-rank-stats">
                         <div class="user-stat">
                             <div class="user-stat-value">{{ auth()->user()->elo_rating }}</div>
                             <div class="user-stat-label">Điểm Elo</div>
                         </div>
                         <div class="user-stat">
                             <div class="user-stat-value">{{ auth()->user()->ocr_wins }}</div>
                             <div class="user-stat-label">Thắng</div>
                         </div>
                         <div class="user-stat">
                             <div class="user-stat-value">{{ auth()->user()->ocr_losses }}</div>
                             <div class="user-stat-label">Thua</div>
                         </div>
                         <div class="user-stat">
                             <div class="user-stat-value">
                                 {{ auth()->user()->total_ocr_matches > 0 ? round((auth()->user()->ocr_wins / auth()->user()->total_ocr_matches) * 100) : 0 }}%
                             </div>
                             <div class="user-stat-label">Tỷ Lệ</div>
                         </div>
                     </div>
                </div>
            @endif
        @endauth

        <div class="filter-section">
            <a href="{{ route('ocr.leaderboard') }}" class="filter-tab {{ !$rank ? 'active' : '' }}">
                Tất Cả
            </a>
            @foreach($ranks as $r)
                <a href="{{ route('ocr.leaderboard', ['rank' => strtolower($r)]) }}"
                   class="filter-tab {{ strtolower($rank ?? '') === strtolower($r) ? 'active' : '' }}">
                    {{ $r }}
                </a>
            @endforeach
        </div>

        <div class="leaderboard-card">
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Hạng</th>
                        <th>Người Chơi</th>
                        <th style="width: 100px;">Elo</th>
                        <th style="width: 180px;">Thống Kê</th>
                        <th style="width: 160px;">Tỷ Lệ Thắng</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $startRank = ($players->currentPage() - 1) * $players->perPage();
                    @endphp
                    @forelse($players as $index => $player)
                        @php
                            $currentRank = $startRank + $index + 1;
                            $isCurrentUser = auth()->check() && auth()->id() === $player->id;
                            $winRate = $player->total_ocr_matches > 0
                                ? round(($player->ocr_wins / $player->total_ocr_matches) * 100)
                                : 0;
                        @endphp
                        <tr class="{{ $isCurrentUser ? 'current-user' : '' }}">
                            <td>
                                <span class="rank-badge {{ $currentRank <= 3 ? 'rank-' . $currentRank : 'rank-default' }}">
                                    {{ $currentRank }}
                                </span>
                            </td>
                            <td>
                                <div class="player-info">
                                    <div class="player-avatar">
                                        {{ strtoupper(mb_substr($player->name, 0, 1)) }}
                                    </div>
                                    <div class="player-details">
                                        <div class="player-name">
                                            <a href="{{ route('ocr.profile', $player) }}">
                                                {{ $player->name }}
                                                @if($isCurrentUser)
                                                    (Bạn)
                                                @endif
                                            </a>
                                        </div>
                                        @if($player->elo_rank)
                                            <span class="player-rank-title rank-{{ strtolower($player->elo_rank) }}">
                                                {{ $player->elo_rank }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="elo-rating">{{ $player->elo_rating }}</span>
                            </td>
                            <td>
                                <div class="stats-cell">
                                    <div class="stat-item">
                                        <div class="stat-value">{{ $player->total_ocr_matches }}</div>
                                        <div class="stat-label">Trận</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value wins">{{ $player->ocr_wins }}</div>
                                        <div class="stat-label">Thắng</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value losses">{{ $player->ocr_losses }}</div>
                                        <div class="stat-label">Thua</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="win-rate">
                                    <div class="win-rate-bar">
                                        <div class="win-rate-fill" style="width: {{ $winRate }}%"></div>
                                    </div>
                                    <span class="win-rate-text">{{ $winRate }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                             <td colspan="5" style="text-align: center; padding: 3rem; color: #94a3b8;">
                                 @if($rank)
                                     Chưa có người chơi nào ở hạng {{ ucfirst($rank) }}
                                 @else
                                     Chưa có dữ liệu xếp hạng
                                 @endif
                             </td>
                         </tr>
                    @endforelse
                </tbody>
            </table>

            @if($players->hasPages())
                <div class="pagination-wrapper">
                    {{ $players->appends(['rank' => $rank])->links() }}
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
