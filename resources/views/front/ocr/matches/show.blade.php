@extends('layouts.front')

@section('css')
<style>
    .page-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        padding: 2rem 0;
        color: white;
    }

    .page-breadcrumb {
        font-size: 0.875rem;
        opacity: 0.8;
        margin-bottom: 0.5rem;
    }

    .page-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .match-detail-section {
        padding: 2rem 0;
    }

    .match-status-banner {
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .status-pending { background: #fef3c7; color: #92400e; }
    .status-accepted { background: #dbeafe; color: #1e40af; }
    .status-in_progress { background: #e0e7ff; color: #3730a3; }
    .status-result_submitted { background: #fef9c3; color: #854d0e; }
    .status-confirmed { background: #dcfce7; color: #166534; }
    .status-disputed { background: #fee2e2; color: #991b1b; }
    .status-cancelled { background: #f1f5f9; color: #475569; }

    .status-text {
        font-weight: 600;
        font-size: 1rem;
    }

    .match-main-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .match-main-header {
        padding: 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .match-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 600;
        background: #e0f2fe;
        color: #0284c7;
    }

    .match-main-body {
        padding: 2rem;
    }

    .match-versus {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
    }

    .team-card {
        flex: 1;
        text-align: center;
        padding: 1.5rem;
        border-radius: 1rem;
        background: #f8fafc;
    }

    .team-card.winner {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
        border: 2px solid #10b981;
    }

    .team-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
    }

    .team-players {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .player-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .player-avatar-lg {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.25rem;
    }

    .player-info {
        text-align: left;
    }

    .player-name-lg {
        font-weight: 700;
        font-size: 1.125rem;
        color: #1e293b;
    }

    .player-name-lg a {
        color: inherit;
        text-decoration: none;
    }

    .player-name-lg a:hover {
        text-decoration: underline;
    }

    .player-elo-lg {
        font-size: 0.875rem;
        color: #64748b;
    }

    .versus-center {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .versus-text {
        font-weight: 800;
        color: #94a3b8;
        font-size: 1.25rem;
    }

    .score-display {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1e293b;
    }

    .win-probability {
        font-size: 0.75rem;
        color: #64748b;
        text-align: center;
    }

    .win-prob-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.25rem;
        display: flex;
    }

    .win-prob-challenger {
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
    }

    .win-prob-opponent {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }

    .match-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        padding: 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-icon {
        color: #64748b;
    }

    .info-label {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .info-value {
        font-weight: 600;
        color: #1e293b;
    }

    .action-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .action-card h3 {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #1e293b;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .evidence-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }

    .evidence-item {
        border-radius: 0.5rem;
        overflow: hidden;
        aspect-ratio: 1;
    }

    .evidence-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .evidence-item.video {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #1e293b;
        color: white;
    }

    .result-form {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.5rem;
    }

    .score-inputs {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .score-input-group {
        flex: 1;
        text-align: center;
    }

    .score-input-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #64748b;
    }

    .score-input-group input {
        width: 100%;
        padding: 0.75rem;
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
    }

    .score-input-group input:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .dispute-form textarea {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        resize: vertical;
        min-height: 100px;
    }

    .elo-change-display {
        padding: 1rem;
        border-radius: 0.75rem;
        text-align: center;
    }

    .elo-change-display.positive {
        background: #dcfce7;
        color: #166534;
    }

    .elo-change-display.negative {
        background: #fee2e2;
        color: #991b1b;
    }

    .elo-change-value {
        font-size: 2rem;
        font-weight: 800;
    }

    .elo-change-label {
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .match-versus {
            flex-direction: column;
        }

        .team-card {
            width: 100%;
        }

        .versus-center {
            padding: 1rem 0;
        }
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <p class="page-breadcrumb">
            <a href="{{ route('ocr.index') }}">OCR</a> /
            <a href="{{ route('ocr.matches.index') }}">Trận Đấu</a> /
            Chi Tiết
        </p>
        <h1 class="page-title">
            [GAME] Trận Đấu #{{ $match->id }}
            <span class="match-type-badge">
                {{ $match->match_type === 'singles' ? 'Đơn' : 'Đôi' }}
            </span>
        </h1>
    </div>
</section>

<section class="match-detail-section">
    <div class="container">
        {{-- Status Banner --}}
        <div class="match-status-banner status-{{ $match->status }}">
            <span class="status-text">
                @switch($match->status)
                        @case('pending')
                            [CLOCK] Đang chờ đối thủ chấp nhận lời thách đấu
                            @break
                        @case('accepted')
                            [CHECK] Trận đấu đã được chấp nhận - Sẵn sàng thi đấu!
                            @break
                        @case('in_progress')
                            [PLAY] Trận đấu đang diễn ra
                            @break
                        @case('result_submitted')
                            [ALERT] Kết quả đã được gửi - Chờ xác nhận từ đối thủ
                            @break
                        @case('confirmed')
                            [TROPHY] Trận đấu đã hoàn thành
                            @break
                        @case('disputed')
                            [WARNING] Trận đấu đang tranh chấp - Chờ xử lý từ admin
                            @break
                        @case('cancelled')
                            [X] Trận đấu đã bị hủy
                            @break
                    @endswitch
            </span>
        </div>

        <div class="row" style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
            <div style="flex: 2; min-width: 300px;">
                {{-- Main Match Card --}}
                <div class="match-main-card">
                    <div class="match-main-header">
                         <span class="match-type-badge">
                             {{ $match->match_type === 'singles' ? '[1v1] Trận Đơn' : '[2v2] Trận Đôi' }}
                         </span>
                         <span style="font-size: 0.875rem; color: #64748b;">
                             Tạo lúc: {{ $match->created_at->format('d/m/Y H:i') }}
                         </span>
                     </div>

                    <div class="match-main-body">
                        <div class="match-versus">
                            <div class="team-card {{ $match->winner_team === 'challenger' ? 'winner' : '' }}">
                                 <div class="team-label">
                                     Đội Thách Đấu
                                     @if($match->winner_team === 'challenger')
                                         [TROPHY] THẮNG
                                     @endif
                                 </div>
                                <div class="team-players">
                                    <div class="player-card">
                                        <div class="player-avatar-lg">
                                            {{ strtoupper(mb_substr($match->challenger->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="player-info">
                                            <div class="player-name-lg">
                                                <a href="{{ route('ocr.profile', $match->challenger) }}">
                                                    {{ $match->challenger->name ?? 'Unknown' }}
                                                </a>
                                            </div>
                                            <div class="player-elo-lg">
                                                Elo: {{ $match->challenger->elo_rating ?? '-' }}
                                                @if($match->challenger->elo_rank)
                                                    ({{ $match->challenger->elo_rank }})
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if($match->challengerPartner)
                                        <div class="player-card">
                                            <div class="player-avatar-lg">
                                                {{ strtoupper(mb_substr($match->challengerPartner->name, 0, 1)) }}
                                            </div>
                                            <div class="player-info">
                                                <div class="player-name-lg">
                                                    <a href="{{ route('ocr.profile', $match->challengerPartner) }}">
                                                        {{ $match->challengerPartner->name }}
                                                    </a>
                                                </div>
                                                <div class="player-elo-lg">
                                                    Elo: {{ $match->challengerPartner->elo_rating }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="versus-center">
                                <span class="versus-text">VS</span>
                                @if($match->challenger_score !== null && $match->opponent_score !== null)
                                    <div class="score-display">
                                        {{ $match->challenger_score }} - {{ $match->opponent_score }}
                                    </div>
                                @endif
                                @if($winProbability)
                                     <div class="win-probability">
                                         <div>Xác suất thắng</div>
                                         <div class="win-prob-bar" style="width: 120px;">
                                             <div class="win-prob-challenger" style="width: {{ $winProbability['challenger'] * 100 }}%"></div>
                                             <div class="win-prob-opponent" style="width: {{ $winProbability['opponent'] * 100 }}%"></div>
                                         </div>
                                         <div style="font-size: 0.7rem; margin-top: 0.25rem;">
                                             {{ round($winProbability['challenger'] * 100) }}% - {{ round($winProbability['opponent'] * 100) }}%
                                         </div>
                                     </div>
                                 @endif
                            </div>

                            <div class="team-card {{ $match->winner_team === 'opponent' ? 'winner' : '' }}">
                                 <div class="team-label">
                                     Đội Đối Thủ
                                     @if($match->winner_team === 'opponent')
                                         [TROPHY] THẮNG
                                     @endif
                                 </div>
                                <div class="team-players">
                                    <div class="player-card">
                                        <div class="player-avatar-lg">
                                            {{ strtoupper(mb_substr($match->opponent->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="player-info">
                                            <div class="player-name-lg">
                                                <a href="{{ route('ocr.profile', $match->opponent) }}">
                                                    {{ $match->opponent->name ?? 'Unknown' }}
                                                </a>
                                            </div>
                                            <div class="player-elo-lg">
                                                Elo: {{ $match->opponent->elo_rating ?? '-' }}
                                                @if($match->opponent->elo_rank)
                                                    ({{ $match->opponent->elo_rank }})
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if($match->opponentPartner)
                                        <div class="player-card">
                                            <div class="player-avatar-lg">
                                                {{ strtoupper(mb_substr($match->opponentPartner->name, 0, 1)) }}
                                            </div>
                                            <div class="player-info">
                                                <div class="player-name-lg">
                                                    <a href="{{ route('ocr.profile', $match->opponentPartner) }}">
                                                        {{ $match->opponentPartner->name }}
                                                    </a>
                                                </div>
                                                <div class="player-elo-lg">
                                                    Elo: {{ $match->opponentPartner->elo_rating }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="match-info-grid">
                        <div class="info-item">
                             <span class="info-icon">[CALENDAR]</span>
                             <div>
                                 <div class="info-label">Ngày Thi Đấu</div>
                                 <div class="info-value">{{ $match->scheduled_date?->format('d/m/Y') ?? 'Chưa xác định' }}</div>
                             </div>
                         </div>
                         <div class="info-item">
                             <span class="info-icon">[CLOCK]</span>
                             <div>
                                 <div class="info-label">Giờ</div>
                                 <div class="info-value">{{ $match->scheduled_time ?? 'Chưa xác định' }}</div>
                             </div>
                         </div>
                         <div class="info-item">
                             <span class="info-icon">[LOCATION]</span>
                             <div>
                                 <div class="info-label">Địa Điểm</div>
                                 <div class="info-value">{{ $match->location ?? 'Chưa xác định' }}</div>
                             </div>
                         </div>
                         @if($match->elo_change && $match->status === 'confirmed')
                             <div class="info-item">
                                 <span class="info-icon">[CHART]</span>
                                 <div>
                                     <div class="info-label">Elo Thay Đổi</div>
                                     <div class="info-value">+/- {{ $match->elo_change }}</div>
                                 </div>
                             </div>
                         @endif
                        </div>
                </div>

                {{-- Evidence --}}
                @if($match->media->isNotEmpty())
                    <div class="action-card">
                        <h3>[IMAGE] Bằng Chứng ({{ $match->media->count() }})</h3>
                        <div class="evidence-grid">
                            @foreach($match->media as $media)
                                @if(Str::startsWith($media->mime_type, 'image'))
                                    <a href="{{ $media->getUrl() }}" target="_blank" class="evidence-item">
                                        <img src="{{ $media->getUrl() }}" alt="Evidence">
                                    </a>
                                @else
                                    <a href="{{ $media->getUrl() }}" target="_blank" class="evidence-item video">
                                        [VIDEO] Xem Video
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div style="flex: 1; min-width: 280px;">
                {{-- Actions based on status and role --}}
                @php
                    $user = auth()->user();
                    $isChallenger = $match->challenger_id === $user->id;
                    $isOpponent = $match->opponent_id === $user->id;
                    $isChallengerTeam = $isChallenger || $match->challenger_partner_id === $user->id;
                    $isOpponentTeam = $isOpponent || $match->opponent_partner_id === $user->id;
                @endphp

                {{-- Pending: Opponent can accept/reject --}}
                @if($match->status === 'pending' && $isOpponent)
                    <div class="action-card">
                        <h3>[ACTION] Phản Hồi Thách Đấu</h3>
                        <div class="action-buttons">
                            <form action="{{ route('api.ocr.matches.accept', $match) }}" method="POST" style="flex: 1;">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="width: 100%;">
                                    Chấp Nhận
                                </button>
                            </form>
                            <form action="{{ route('api.ocr.matches.reject', $match) }}" method="POST" style="flex: 1;">
                                @csrf
                                <button type="submit" class="btn btn-outline" style="width: 100%;">
                                    Từ Chối
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Accepted: Either can mark as in progress --}}
                @if($match->status === 'accepted')
                    <div class="action-card">
                        <h3>[PLAY] Bắt Đầu Trận Đấu</h3>
                        <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 1rem;">
                            Khi cả hai đội đã sẵn sàng, nhấn bắt đầu.
                        </p>
                        <form action="{{ route('api.ocr.matches.start', $match) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                Bắt Đầu Trận Đấu
                            </button>
                        </form>
                    </div>
                @endif

                {{-- In Progress: Submit result --}}
                @if($match->status === 'in_progress')
                    <div class="action-card">
                        <h3>[SCORE] Gửi Kết Quả</h3>
                        <form action="{{ route('api.ocr.matches.result', $match) }}" method="POST" class="result-form">
                            @csrf
                            <div class="score-inputs">
                                <div class="score-input-group">
                                    <label>Đội Thách Đấu</label>
                                    <input type="number" name="challenger_score" min="0" max="99" required>
                                </div>
                                <span style="font-weight: 700; color: #94a3b8;">-</span>
                                <div class="score-input-group">
                                    <label>Đội Đối Thủ</label>
                                    <input type="number" name="opponent_score" min="0" max="99" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                Gửi Kết Quả
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Result Submitted: Confirm or Dispute --}}
                @if($match->status === 'result_submitted')
                    @if(($isChallengerTeam && $match->result_submitted_by !== $user->id) ||
                        ($isOpponentTeam && $match->result_submitted_by !== $user->id))
                        <div class="action-card">
                            <h3>[CHECK] Xác Nhận Kết Quả</h3>
                            <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 1rem;">
                                Kết quả: <strong>{{ $match->challenger_score }} - {{ $match->opponent_score }}</strong>
                            </p>
                            <div class="action-buttons">
                                <form action="{{ route('api.ocr.matches.confirm', $match) }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        Xác Nhận
                                    </button>
                                </form>
                            </div>

                            <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e2e8f0;">

                            <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Kết quả sai?</h4>
                            <form action="{{ route('api.ocr.matches.dispute', $match) }}" method="POST" class="dispute-form">
                                @csrf
                                <textarea name="reason" placeholder="Nhập lý do tranh chấp..." required></textarea>
                                <button type="submit" class="btn btn-outline" style="width: 100%; margin-top: 0.5rem;">
                                    Tranh Chấp
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="action-card">
                            <h3>[CLOCK] Đang Chờ Xác Nhận</h3>
                            <p style="font-size: 0.875rem; color: #64748b;">
                                Bạn đã gửi kết quả. Vui lòng chờ đối thủ xác nhận.
                                <br><br>
                                Kết quả sẽ tự động được xác nhận sau 24 giờ nếu không có phản hồi.
                            </p>
                        </div>
                    @endif
                @endif

                {{-- Confirmed: Show Elo change --}}
                @if($match->status === 'confirmed' && $match->elo_change)
                    @php
                        $userWon = ($isChallengerTeam && $match->winner_team === 'challenger') ||
                                   ($isOpponentTeam && $match->winner_team === 'opponent');
                    @endphp
                    <div class="action-card">
                        <h3>[CHART] Thay Đổi Elo Của Bạn</h3>
                        <div class="elo-change-display {{ $userWon ? 'positive' : 'negative' }}">
                            <div class="elo-change-value">
                                {{ $userWon ? '+' : '-' }}{{ $match->elo_change }}
                            </div>
                            <div class="elo-change-label">
                                {{ $userWon ? 'Tăng điểm Elo!' : 'Giảm điểm Elo' }}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Disputed --}}
                @if($match->status === 'disputed')
                    <div class="action-card">
                        <h3>[WARNING] Trận Đấu Đang Tranh Chấp</h3>
                        <p style="font-size: 0.875rem; color: #64748b;">
                            Lý do: <em>{{ $match->disputed_reason }}</em>
                        </p>
                        <p style="font-size: 0.875rem; color: #64748b; margin-top: 1rem;">
                            Admin sẽ xem xét và giải quyết tranh chấp này. Vui lòng chờ.
                        </p>
                    </div>
                @endif

                {{-- Upload Evidence --}}
                @if(in_array($match->status, ['in_progress', 'result_submitted']))
                    <div class="action-card">
                        <h3>[UPLOAD] Tải Lên Bằng Chứng</h3>
                        <form action="{{ route('api.ocr.matches.evidence', $match) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="evidence[]" multiple accept="image/*,video/*" style="margin-bottom: 1rem;">
                            <button type="submit" class="btn btn-outline" style="width: 100%;">
                                Tải Lên
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Back button --}}
                <a href="{{ route('ocr.matches.index') }}" class="btn btn-outline" style="width: 100%;">
                    Quay Lại Danh Sách
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
