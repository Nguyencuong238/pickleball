@extends('layouts.front')

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

    .matches-section {
        padding: 2rem 0;
    }

    .filter-tabs {
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

    .match-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 1rem;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .match-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .match-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .match-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        background: #e0f2fe;
        color: #0284c7;
    }

    .match-status {
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-pending { background: #fef3c7; color: #d97706; }
    .status-accepted { background: #dbeafe; color: #2563eb; }
    .status-in_progress { background: #e0e7ff; color: #4f46e5; }
    .status-result_submitted { background: #fef9c3; color: #ca8a04; }
    .status-confirmed { background: #dcfce7; color: #16a34a; }
    .status-disputed { background: #fee2e2; color: #dc2626; }
    .status-cancelled { background: #f1f5f9; color: #64748b; }

    .match-card-body {
        padding: 1.5rem;
    }

    .match-teams {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .team {
        flex: 1;
        text-align: center;
    }

    .team-label {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .team-players {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .team-player {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .player-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .player-name {
        font-weight: 600;
        color: #1e293b;
    }

    .player-elo {
        font-size: 0.75rem;
        color: #64748b;
    }

    .match-vs {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .vs-text {
        font-weight: 700;
        color: #94a3b8;
        font-size: 0.875rem;
    }

    .match-score {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
    }

    .match-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .match-meta {
        display: flex;
        gap: 1.5rem;
        font-size: 0.875rem;
        color: #64748b;
    }

    .match-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .elo-change {
        font-weight: 700;
    }

    .elo-change.positive { color: #16a34a; }
    .elo-change.negative { color: #dc2626; }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #64748b;
        margin-bottom: 1.5rem;
    }

    .pagination-wrapper {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .match-teams {
            flex-direction: column;
        }

        .match-vs {
            margin: 1rem 0;
        }

        .match-card-footer {
            flex-direction: column;
            gap: 1rem;
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
                     <a href="{{ route('ocr.index') }}">OCR</a> / Quản Lý Trận Đấu
                 </p>
                 <h1 class="page-title">🏓 Trận Đấu Của Tôi</h1>
            </div>
            <a href="{{ route('ocr.matches.create') }}" class="btn btn-primary">
                + Tạo Trận Đấu Mới
            </a>
        </div>
    </div>
</section>

<section class="matches-section">
    <div class="container">
        <div class="filter-tabs">
             <a href="{{ route('ocr.matches.index') }}" class="filter-tab {{ !$status ? 'active' : '' }}">
                 Tất Cả
             </a>
             <a href="{{ route('ocr.matches.index', ['status' => 'pending']) }}"
                class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">
                 Chờ Xác Nhận
             </a>
             <a href="{{ route('ocr.matches.index', ['status' => 'accepted']) }}"
                class="filter-tab {{ $status === 'accepted' ? 'active' : '' }}">
                 Đã Chấp Nhận
             </a>
             <a href="{{ route('ocr.matches.index', ['status' => 'in_progress']) }}"
                class="filter-tab {{ $status === 'in_progress' ? 'active' : '' }}">
                 Đang Diễn Ra
             </a>
             <a href="{{ route('ocr.matches.index', ['status' => 'result_submitted']) }}"
                class="filter-tab {{ $status === 'result_submitted' ? 'active' : '' }}">
                 Chờ Xác Nhận Kết Quả
             </a>
             <a href="{{ route('ocr.matches.index', ['status' => 'confirmed']) }}"
                class="filter-tab {{ $status === 'confirmed' ? 'active' : '' }}">
                 Hoàn Thành
             </a>
             <a href="{{ route('ocr.matches.index', ['status' => 'disputed']) }}"
                class="filter-tab {{ $status === 'disputed' ? 'active' : '' }}">
                 Tranh Chấp
             </a>
         </div>

        @if($matches->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">🏓</div>
                <h3>Chưa Có Trận Đấu Nào</h3>
                <p>
                    @if($status)
                        Không có trận đấu nào với trạng thái "{{ ucfirst(str_replace('_', ' ', $status)) }}"
                    @else
                        Bạn chưa tham gia trận đấu nào. Hãy tạo trận đấu mới để bắt đầu!
                    @endif
                </p>
                <a href="{{ route('ocr.matches.create') }}" class="btn btn-primary">
                    Tạo Trận Đấu Đầu Tiên
                </a>
            </div>
        @else
            @foreach($matches as $match)
                @php
                    $isChallenger = $match->challenger_id === auth()->id() || $match->challenger_partner_id === auth()->id();
                    $eloChange = $match->elo_change ?? 0;
                    $userWon = ($isChallenger && $match->winner_team === 'challenger') || (!$isChallenger && $match->winner_team === 'opponent');
                @endphp
                <a href="{{ route('ocr.matches.show', $match) }}" style="text-decoration: none;">
                    <div class="match-card">
                        <div class="match-card-header">
                            <span class="match-type-badge">
                                 {{ $match->match_type === 'singles' ? '[1v1] Đơn' : '[2v2] Đôi' }}
                             </span>
                            <span class="match-status status-{{ $match->status }}">
                                {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                            </span>
                        </div>
                        <div class="match-card-body">
                             <div class="match-teams">
                                 <div class="team">
                                     <div class="team-label">Đội Thách Đấu</div>
                                    <div class="team-players">
                                        <div class="team-player">
                                            <div class="player-avatar">
                                                {{ strtoupper(mb_substr($match->challenger->name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="player-name">{{ $match->challenger->name ?? 'Unknown' }}</div>
                                                <div class="player-elo">Elo: {{ $match->challenger->elo_rating ?? '-' }}</div>
                                            </div>
                                        </div>
                                        @if($match->challengerPartner)
                                            <div class="team-player">
                                                <div class="player-avatar">
                                                    {{ strtoupper(mb_substr($match->challengerPartner->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="player-name">{{ $match->challengerPartner->name }}</div>
                                                    <div class="player-elo">Elo: {{ $match->challengerPartner->elo_rating }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="match-vs">
                                    <span class="vs-text">VS</span>
                                    @if($match->challenger_score !== null && $match->opponent_score !== null)
                                        <div class="match-score">
                                            {{ $match->challenger_score }} - {{ $match->opponent_score }}
                                        </div>
                                    @endif
                                </div>

                                <div class="team">
                                     <div class="team-label">Đội Đối Thủ</div>
                                    <div class="team-players">
                                        <div class="team-player">
                                            <div class="player-avatar">
                                                {{ strtoupper(mb_substr($match->opponent->name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="player-name">{{ $match->opponent->name ?? 'Unknown' }}</div>
                                                <div class="player-elo">Elo: {{ $match->opponent->elo_rating ?? '-' }}</div>
                                            </div>
                                        </div>
                                        @if($match->opponentPartner)
                                            <div class="team-player">
                                                <div class="player-avatar">
                                                    {{ strtoupper(mb_substr($match->opponentPartner->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="player-name">{{ $match->opponentPartner->name }}</div>
                                                    <div class="player-elo">Elo: {{ $match->opponentPartner->elo_rating }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="match-card-footer">
                            <div class="match-meta">
                                <span class="match-meta-item">
                                    📅 {{ $match->scheduled_date?->format('d/m/Y') ?? 'TBD' }}
                                </span>
                                @if($match->scheduled_time)
                                    <span class="match-meta-item">
                                        🕐 {{ $match->scheduled_time }}
                                    </span>
                                @endif
                                @if($match->location)
                                    <span class="match-meta-item">
                                        📍 {{ Str::limit($match->location, 30) }}
                                    </span>
                                @endif
                            </div>
                            @if($match->status === 'confirmed' && $eloChange)
                                <span class="elo-change {{ $userWon ? 'positive' : 'negative' }}">
                                    {{ $userWon ? '+' : '-' }}{{ $eloChange }} Elo
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach

            <div class="pagination-wrapper">
                {{ $matches->appends(['status' => $status])->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
