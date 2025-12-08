@extends('layouts.front')

@section('css')
<style>
    .matches-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        padding: 60px 20px 80px;
        margin-top: 100px;
    }

    .matches-header {
        max-width: 1200px;
        margin: 0 auto 50px;
        text-align: center;
        color: white;
    }

    .matches-header h1 {
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: 800;
    }

    .matches-header p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 30px;
    }

    .filter-tabs {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 40px;
    }

    .filter-btn {
        padding: 12px 28px;
        border: 2px solid white;
        background: transparent;
        color: white;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .filter-btn:hover {
        background: white;
        color: #00D9B5;
        transform: translateY(-2px);
    }

    .filter-btn.active {
        background: white;
        color: #00D9B5;
    }

    .matches-wrapper {
        max-width: 1200px;
        margin: 0 auto;
    }

    .matches-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }

    .match-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border-left: 5px solid #FFFFFF;
        position: relative;
        overflow: hidden;
    }

    .match-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0, 217, 181, 0.05) 0%, transparent 100%);
        pointer-events: none;
    }

    .match-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    .match-card.pending {
        border-left-color: #FFD93D;
    }

    .match-card.accepted {
        border-left-color: #00D9B5;
    }

    .match-card.in_progress {
        border-left-color: #FF6B6B;
    }

    .match-card.confirmed {
        border-left-color: #95E1D3;
    }

    .match-card.disputed {
        border-left-color: #FF6B6B;
    }

    .match-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: rgba(255, 217, 61, 0.1);
        color: #FFD93D;
    }

    .status-accepted {
        background: rgba(0, 217, 181, 0.1);
        color: #00D9B5;
    }

    .status-in_progress {
        background: rgba(255, 107, 107, 0.1);
        color: #FF6B6B;
    }

    .status-confirmed {
        background: rgba(149, 225, 211, 0.1);
        color: #95E1D3;
    }

    .status-disputed {
        background: rgba(255, 107, 107, 0.1);
        color: #FF6B6B;
    }

    .match-info {
        position: relative;
        z-index: 1;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .match-type {
        display: inline-block;
        background: #f1f5f9;
        color: #0099CC;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .match-players {
        position: relative;
        z-index: 1;
        margin-bottom: 20px;
    }

    .player-team {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 0;
    }

    .player-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00D9B5 0%, #0099CC 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .player-info {
        flex: 1;
    }

    .player-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .vs-separator {
        text-align: center;
        font-weight: 700;
        color: #0099CC;
        font-size: 0.85rem;
        margin: 12px 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .match-score {
        display: flex;
        justify-content: space-around;
        align-items: center;
        margin: 12px 0;
        padding: 12px;
        background: #f8fafc;
        border-radius: 8px;
    }

    .score-item {
        text-align: center;
    }

    .score-number {
        font-size: 1.8rem;
        font-weight: 800;
        color: #0099CC;
    }

    .match-details {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 12px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
    }

    .detail-item {
        flex: 1;
        text-align: center;
        font-size: 0.8rem;
    }

    .detail-label {
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .detail-value {
        color: #1e293b;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: white;
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.8;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 10px;
    }

    .empty-state p {
        opacity: 0.9;
        margin-bottom: 30px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 40px;
    }

    .pagination a,
    .pagination span {
        padding: 10px 14px;
        border-radius: 8px;
        background: white;
        color: #0099CC;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .pagination a:hover {
        background: #0099CC;
        color: white;
    }

    .pagination .active {
        background: #0099CC;
        color: white;
    }

    @media (max-width: 768px) {
        .matches-container {
            padding: 40px 15px 60px;
        }

        .matches-header h1 {
            font-size: 1.8rem;
        }

        .matches-grid {
            grid-template-columns: 1fr;
        }

        .filter-tabs {
            gap: 8px;
        }

        .filter-btn {
            padding: 10px 20px;
            font-size: 0.9rem;
        }
    }
</style>
@endsection

@section('content')
<div class="matches-container">
    <div class="matches-header">
        <h1>🏸 Trận Đấu OCR</h1>
        <p>Xem tất cả trận đấu OCR, từ chờ xác nhận đến hoàn tất</p>

        <div class="filter-tabs">
            <a href="{{ route('ocr.ocr-matches') }}" class="filter-btn {{ $filter === 'all' ? 'active' : '' }}">
                📊 Tất Cả
            </a>
            <a href="{{ route('ocr.ocr-matches', ['filter' => 'in_progress']) }}" class="filter-btn {{ $filter === 'in_progress' ? 'active' : '' }}">
                🔥 Đang Diễn Ra
            </a>
            <a href="{{ route('ocr.ocr-matches', ['filter' => 'pending']) }}" class="filter-btn {{ $filter === 'pending' ? 'active' : '' }}">
                ⏳ Chờ Xác Nhận
            </a>
            <a href="{{ route('ocr.ocr-matches', ['filter' => 'confirmed']) }}" class="filter-btn {{ $filter === 'confirmed' ? 'active' : '' }}">
                ✅ Hoàn Tất
            </a>
            <a href="{{ route('ocr.ocr-matches', ['filter' => 'disputed']) }}" class="filter-btn {{ $filter === 'disputed' ? 'active' : '' }}">
                ⚠️ Tranh Chấp
            </a>
        </div>
    </div>

    <div class="matches-wrapper">
        @if($matches->count() > 0)
            <div class="matches-grid">
                @foreach($matches as $match)
                    <div class="match-card {{ $match->status }}">
                        <!-- Status Badge -->
                        @if($match->status === 'in_progress')
                            <span class="match-status status-in_progress">⚡ Đang Diễn Ra</span>
                        @elseif($match->status === 'pending')
                            <span class="match-status status-pending">⏳ Chờ Xác Nhận</span>
                        @elseif($match->status === 'accepted')
                            <span class="match-status status-accepted">✓ Đã Chấp Nhận</span>
                        @elseif($match->status === 'confirmed')
                            <span class="match-status status-confirmed">✓ Hoàn Tất</span>
                        @elseif($match->status === 'disputed')
                            <span class="match-status status-disputed">⚠️ Tranh Chấp</span>
                        @else
                            <span class="match-status">{{ ucfirst($match->status) }}</span>
                        @endif

                        <!-- Match Type -->
                        <div class="match-info">
                            <span class="match-type">
                                {{ $match->match_type === 'singles' ? '👤 Đơn' : '👥 Đôi' }}
                            </span>
                        </div>

                        <!-- Players -->
                        <div class="match-players">
                            <!-- Challenger Team -->
                            <div class="player-team">
                                <div class="player-avatar">{{ substr($match->challenger?->name ?? 'A', 0, 1) }}</div>
                                <div class="player-info">
                                    <div class="player-name">{{ $match->challenger?->name ?? 'Cầu thủ 1' }}</div>
                                </div>
                            </div>

                            @if($match->challenger_partner_id)
                                <div class="player-team" style="margin-left: 20px;">
                                    <div class="player-avatar">{{ substr($match->challengerPartner?->name ?? 'B', 0, 1) }}</div>
                                    <div class="player-info">
                                        <div class="player-name">{{ $match->challengerPartner?->name ?? 'Cầu thủ 2' }}</div>
                                    </div>
                                </div>
                            @endif

                            <div class="vs-separator">VS</div>

                            <!-- Opponent Team -->
                            <div class="player-team">
                                <div class="player-avatar">{{ substr($match->opponent?->name ?? 'C', 0, 1) }}</div>
                                <div class="player-info">
                                    <div class="player-name">{{ $match->opponent?->name ?? 'Cầu thủ 3' }}</div>
                                </div>
                            </div>

                            @if($match->opponent_partner_id)
                                <div class="player-team" style="margin-left: 20px;">
                                    <div class="player-avatar">{{ substr($match->opponentPartner?->name ?? 'D', 0, 1) }}</div>
                                    <div class="player-info">
                                        <div class="player-name">{{ $match->opponentPartner?->name ?? 'Cầu thủ 4' }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Score for confirmed matches -->
                        @if($match->status === 'confirmed' && $match->challenger_score !== null)
                            <div class="match-score">
                                <div class="score-item">
                                    <div class="score-number">{{ $match->challenger_score }}</div>
                                </div>
                                <div style="color: #94a3b8; font-weight: bold;">-</div>
                                <div class="score-item">
                                    <div class="score-number">{{ $match->opponent_score }}</div>
                                </div>
                            </div>
                        @endif

                        <!-- Match Details -->
                        <div class="match-details">
                            <div class="detail-item">
                                <div class="detail-label">Ngày</div>
                                <div class="detail-value">
                                    {{ $match->scheduled_date ? $match->scheduled_date->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Giờ</div>
                                <div class="detail-value">
                                    {{ $match->scheduled_time ?? '-' }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Địa Điểm</div>
                                <div class="detail-value">
                                    {{ $match->location ? substr($match->location, 0, 10) : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($matches->hasPages())
                <div style="display: flex; justify-content: center; gap: 8px; margin-top: 40px; flex-wrap: wrap;">
                    @if($matches->onFirstPage())
                    @else
                        <a href="{{ $matches->previousPageUrl() }}" style="padding: 10px 14px; border-radius: 8px; background: white; color: #0099CC; text-decoration: none; font-weight: 600;">← Trước</a>
                    @endif

                    @foreach($matches->getUrlRange(1, $matches->lastPage()) as $page => $url)
                        @if($page == $matches->currentPage())
                            <span style="padding: 10px 14px; border-radius: 8px; background: white; color: #0099CC; font-weight: 600;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" style="padding: 10px 14px; border-radius: 8px; background: white; color: #0099CC; text-decoration: none; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#0099CC'; this.style.color='white';" onmouseout="this.style.backgroundColor='white'; this.style.color='#0099CC';">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($matches->hasMorePages())
                        <a href="{{ $matches->nextPageUrl() }}" style="padding: 10px 14px; border-radius: 8px; background: white; color: #0099CC; text-decoration: none; font-weight: 600;">Sau →</a>
                    @endif
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>Không Có Trận Đấu</h3>
                <p>
                    @if($filter === 'in_progress')
                        Hiện không có trận đấu nào đang diễn ra.
                    @elseif($filter === 'pending')
                        Không có trận đấu nào đang chờ xác nhận.
                    @elseif($filter === 'confirmed')
                        Chưa có trận đấu nào hoàn tất.
                    @elseif($filter === 'disputed')
                        Không có tranh chấp nào.
                    @else
                        Chưa có trận đấu OCR nào.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
