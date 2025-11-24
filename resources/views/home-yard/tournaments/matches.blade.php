@extends('layouts.homeyard')
<style>
    /* Page-specific styles */
    .match-card {
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1rem;
        border: 2px solid var(--border-color);
        transition: all var(--transition);
    }

    .match-card:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-md);
    }

    .match-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .match-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .match-id {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .match-time {
        font-size: 0.75rem;
        color: var(--text-light);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .match-body {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 1.5rem;
        align-items: center;
    }

    .player-side {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .player-card-mini {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-light);
        border-radius: var(--radius-md);
    }

    .player-card-mini.winner {
        background: rgba(74, 222, 128, 0.1);
        border: 2px solid var(--accent-green);
    }

    .player-avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.75rem;
    }

    .player-name-sm {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .match-score {
        text-align: center;
    }

    .score-display {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .score-sets {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .match-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px solid var(--border-color);
    }

    .match-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .match-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .court-badge {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .match-actions {
        display: flex;
        gap: 0.5rem;
    }

    .tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--border-color);
        overflow-x: auto;
    }

    .tab {
        padding: 0.75rem 1.5rem;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all var(--transition);
        font-weight: 600;
        white-space: nowrap;
    }

    .tab:hover {
        color: var(--primary-color);
    }

    .tab.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .schedule-grid {
        display: grid;
        gap: 1rem;
    }

    .live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        background: var(--accent-red);
        color: white;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        animation: blink 1.5s infinite;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0;
        }
    }

    .calendar-view {
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
    }

    .calendar-header-full {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .calendar-title-full {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .calendar-nav-full {
        display: flex;
        gap: 0.5rem;
    }

    .calendar-grid-full {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1rem;
    }

    .calendar-day-full {
        aspect-ratio: 1;
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        cursor: pointer;
        transition: all var(--transition);
    }

    .calendar-day-full:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-md);
    }

    .calendar-day-full.has-matches {
        background: rgba(0, 217, 181, 0.05);
        border-color: var(--primary-color);
    }

    .calendar-day-full.today {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: transparent;
    }

    .day-number {
        font-weight: 700;
        font-size: 1.125rem;
    }

    .day-matches {
        font-size: 0.625rem;
        color: var(--text-light);
    }

    .calendar-day-full.today .day-matches {
        color: rgba(255, 255, 255, 0.9);
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-mini-card {
        background: var(--bg-light);
        padding: 1rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-mini-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-mini-icon.success {
        background: rgba(74, 222, 128, 0.1);
    }

    .stat-mini-icon.warning {
        background: rgba(255, 211, 61, 0.1);
    }

    .stat-mini-icon.danger {
        background: rgba(255, 107, 107, 0.1);
    }

    .stat-mini-icon.info {
        background: rgba(0, 153, 204, 0.1);
    }

    .stat-mini-content {
        flex: 1;
    }

    .stat-mini-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }

    .stat-mini-value-lg {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* Pagination Styles */
    .custom-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        list-style: none;
        padding: 0;
        margin: 0;
        flex-wrap: wrap;
    }

    .custom-pagination li {
        display: inline-block;
    }

    .custom-pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0.5rem 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        color: var(--text-primary);
        text-decoration: none;
        transition: all var(--transition);
        font-weight: 500;
        font-size: 0.875rem;
    }

    .custom-pagination a.page-link {
        cursor: pointer;
    }

    .custom-pagination a.page-link:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        background: rgba(0, 217, 181, 0.1);
    }

    .custom-pagination li.active .page-link {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .custom-pagination li.disabled .page-link {
        color: var(--text-light);
        cursor: not-allowed;
        opacity: 0.6;
    }
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Header -->
            <div class="top-header">
                <div class="header-left">
                    <h1>Quản Lý Trận Đấu</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="overview.html" class="breadcrumb-link">Trang chủ</a>
                        </span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-item">Trận Đấu</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" placeholder="Tìm kiếm trận đấu...">
                    </div>
                    <div class="header-notifications">
                        <button class="notification-btn">
                            🔔
                            <span class="notification-badge">5</span>
                        </button>
                    </div>
                    <div class="header-user">
                        <div class="user-avatar">AD</div>
                        <div class="user-info">
                            <div class="user-name">Admin User</div>
                            <div class="user-role">Quản trị viên</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="stats-row fade-in">
                <div class="stat-mini-card">
                    <div class="stat-mini-icon success">✅</div>
                    <div class="stat-mini-content">
                        <div class="stat-mini-label">Đã hoàn thành</div>
                        <div class="stat-mini-value-lg">{{ $stats['completed'] }}</div>
                    </div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-icon danger">🔴</div>
                    <div class="stat-mini-content">
                        <div class="stat-mini-label">Đang diễn ra</div>
                        <div class="stat-mini-value-lg">{{ $stats['live'] }}</div>
                    </div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-icon warning">⏰</div>
                    <div class="stat-mini-content">
                        <div class="stat-mini-label">Sắp tới</div>
                        <div class="stat-mini-value-lg">{{ $stats['upcoming'] }}</div>
                    </div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-icon info">📊</div>
                    <div class="stat-mini-content">
                        <div class="stat-mini-label">Tổng cộng</div>
                        <div class="stat-mini-value-lg">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="tabs">
                        <button class="tab active" onclick="switchTab('live')">🔴 Trực Tiếp</button>
                        <button class="tab" onclick="switchTab('upcoming')">⏰ Sắp Tới</button>
                        <button class="tab" onclick="switchTab('completed')">✅ Đã Kết Thúc</button>
                        <button class="tab" onclick="switchTab('calendar')">📅 Lịch Thi Đấu</button>
                    </div>

                    <!-- Live Matches Tab -->
                    <div class="tab-content active" id="live">
                        <div class="schedule-grid">
                            @if ($liveMatches->count() > 0)
                                @foreach ($liveMatches as $match)
                                    <div class="match-card">
                                        <div class="match-header">
                                            <div class="match-info">
                                                <span class="match-id">Trận #{{ $match->match_number ?? $match->id }}</span>
                                                <span class="live-indicator">
                                                    <span class="live-dot"></span>
                                                    LIVE
                                                </span>
                                            </div>
                                            <div class="match-time">
                                                🕐 Bắt đầu: {{ $match->actual_start_time?->format('H:i') ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="match-body">
                                            <div class="player-side">
                                                <div class="player-card-mini">
                                                    <div class="player-avatar-sm">
                                                        {{ strtoupper(substr($match->athlete1_name ?? 'N/A', 0, 2)) }}</div>
                                                    <div class="player-name-sm">{{ $match->athlete1_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="match-score">
                                                <div class="score-display">{{ $match->athlete1_score }} -
                                                    {{ $match->athlete2_score }}</div>
                                                <div class="score-sets">
                                                    @php
                                                        $setScores = $match->set_scores ? json_decode($match->set_scores, true) : [];
                                                        $setsDisplay = [];
                                                        
                                                        if (is_array($setScores) && count($setScores) > 0) {
                                                            foreach ($setScores as $index => $set) {
                                                                $setsDisplay[] = 'Set ' . ($index + 1) . ': ' . $set['athlete1_score'] . '-' . $set['athlete2_score'];
                                                            }
                                                            // Add current set if there's score
                                                            if ($match->athlete1_score > 0 || $match->athlete2_score > 0) {
                                                                $setsDisplay[] = 'Set ' . (count($setScores) + 1) . ': Đang chơi';
                                                            }
                                                            echo implode(' • ', $setsDisplay);
                                                        } else {
                                                            // No sets completed yet
                                                            if ($match->athlete1_score > 0 || $match->athlete2_score > 0) {
                                                                echo 'Set 1: Đang chơi';
                                                            } else {
                                                                echo 'Chưa bắt đầu';
                                                            }
                                                        }
                                                    @endphp
                                                </div>
                                            </div>
                                            <div class="player-side">
                                                <div class="player-card-mini">
                                                    <div class="player-avatar-sm">
                                                        {{ strtoupper(substr($match->athlete2_name ?? 'N/A', 0, 2)) }}
                                                    </div>
                                                    <div class="player-name-sm">{{ $match->athlete2_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="match-footer">
                                            <div class="match-meta">
                                                <span class="match-meta-item">🏆 {{ $match->round?->name ?? 'N/A' }}</span>
                                                <span class="match-meta-item">👤
                                                    {{ $match->category?->name ?? 'N/A' }}</span>
                                                @if ($match->court)
                                                    <span class="court-badge">{{ $match->court->name }}</span>
                                                @endif
                                            </div>
                                            <div class="match-actions">
                                                <button class="btn btn-primary btn-sm" onclick="openUpdateScoreModal({{ $match->tournament_id }}, {{ $match->id }})">📊 Cập nhật điểm</button>
                                                <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p style="text-align: center; padding: 2rem; color: var(--text-secondary);">Không có trận
                                    đấu nào đang diễn ra</p>
                            @endif
                        </div>
                        <!-- Pagination for Live Matches -->
                        @if ($liveMatches->hasPages())
                            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                {{ $liveMatches->links('pagination::custom') }}
                            </div>
                        @endif
                    </div>

                    <!-- Upcoming Matches Tab -->
                    <div class="tab-content" id="upcoming">
                        <div class="schedule-grid">
                            @if ($upcomingMatches->count() > 0)
                                @foreach ($upcomingMatches as $match)
                                    <div class="match-card">
                                        <div class="match-header">
                                            <div class="match-info">
                                                <span class="match-id">Trận
                                                    #{{ $match->match_number ?? $match->id }}</span>
                                                <span class="badge badge-warning">Sắp tới</span>
                                            </div>
                                            <div class="match-time">
                                                🕐 {{ $match->match_time?->format('H:i') ?? 'N/A' }} • 📅
                                                {{ $match->match_date?->format('d/m/Y') ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="match-body">
                                            <div class="player-side">
                                                <div class="player-card-mini">
                                                    <div class="player-avatar-sm">
                                                        {{ strtoupper(substr($match->athlete1_name ?? 'N/A', 0, 2)) }}
                                                    </div>
                                                    <div class="player-name-sm">{{ $match->athlete1_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="match-score">
                                                <div class="score-display"
                                                    style="font-size: 1.5rem; color: var(--text-light);">VS</div>
                                            </div>
                                            <div class="player-side">
                                                <div class="player-card-mini">
                                                    <div class="player-avatar-sm">
                                                        {{ strtoupper(substr($match->athlete2_name ?? 'N/A', 0, 2)) }}
                                                    </div>
                                                    <div class="player-name-sm">{{ $match->athlete2_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="match-footer">
                                            <div class="match-meta">
                                                <span class="match-meta-item">🏆
                                                    {{ $match->round?->name ?? 'N/A' }}</span>
                                                <span class="match-meta-item">👤
                                                    {{ $match->category?->name ?? 'N/A' }}</span>
                                                @if ($match->court)
                                                    <span class="court-badge">{{ $match->court->name }}</span>
                                                @endif
                                            </div>
                                            <div class="match-actions">
                                                <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                            </div>
                                            </div>
                                            </div>
                                            @endforeach
                                            @else
                                            <p style="text-align: center; padding: 2rem; color: var(--text-secondary);">Không có trận
                                                đấu nào sắp tới</p>
                                            @endif
                        </div>
                        <!-- Pagination for Upcoming Matches -->
                        @if ($upcomingMatches->hasPages())
                            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                {{ $upcomingMatches->links('pagination::custom') }}
                            </div>
                        @endif
                    </div>

                    <!-- Completed Matches Tab -->
                    <div class="tab-content" id="completed">
                        <div class="schedule-grid">
                            @if ($completedMatches->count() > 0)
                                @foreach ($completedMatches as $match)
                                    <div class="match-card">
                                        <div class="match-header">
                                            <div class="match-info">
                                                <span class="match-id">Trận
                                                    #{{ $match->match_number ?? $match->id }}</span>
                                                <span class="badge badge-success">Đã kết thúc</span>
                                            </div>
                                            <div class="match-time">
                                                🕐
                                                {{ $match->actual_start_time?->format('H:i') ?? ($match->match_time?->format('H:i') ?? 'N/A') }}
                                                • 📅 {{ $match->match_date?->format('d/m/Y') ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="match-body">
                                            <div class="player-side">
                                                <div
                                                    class="player-card-mini {{ $match->winner_id == $match->athlete1_id ? 'winner' : '' }}">
                                                    <div class="player-avatar-sm">
                                                        {{ strtoupper(substr($match->athlete1_name ?? 'N/A', 0, 2)) }}
                                                    </div>
                                                    <div class="player-name-sm">{{ $match->athlete1_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="match-score">
                                                <div class="score-display">{{ $match->athlete1_score }} -
                                                    {{ $match->athlete2_score }}</div>
                                                <div class="score-sets">{{ $match->final_score ?? 'N/A' }}</div>
                                            </div>
                                            <div class="player-side">
                                                <div
                                                    class="player-card-mini {{ $match->winner_id == $match->athlete2_id ? 'winner' : '' }}">
                                                    <div class="player-avatar-sm">
                                                        {{ strtoupper(substr($match->athlete2_name ?? 'N/A', 0, 2)) }}
                                                    </div>
                                                    <div class="player-name-sm">{{ $match->athlete2_name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="match-footer">
                                            <div class="match-meta">
                                                <span class="match-meta-item">🏆
                                                    {{ $match->round?->name ?? 'N/A' }}</span>
                                                <span class="match-meta-item">👤
                                                    {{ $match->category?->name ?? 'N/A' }}</span>
                                                @if ($match->court)
                                                    <span class="court-badge">{{ $match->court->name }}</span>
                                                @endif
                                            </div>
                                            <div class="match-actions">
                                                <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                            </div>
                                            </div>
                                            </div>
                                            @endforeach
                                            @else
                                            <p style="text-align: center; padding: 2rem; color: var(--text-secondary);">Không có trận
                                            đấu nào đã kết thúc</p>
                                            @endif
                        </div>
                        <!-- Pagination for Completed Matches -->
                        @if ($completedMatches->hasPages())
                            <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                {{ $completedMatches->links('pagination::custom') }}
                            </div>
                        @endif
                    </div>

                    <!-- Calendar Tab -->
                    <div class="tab-content" id="calendar">
                        <div class="calendar-view">
                            <div class="calendar-header-full">
                                <h3 class="calendar-title-full">Tháng 1, 2025</h3>
                                <div class="calendar-nav-full">
                                    <button class="btn btn-secondary btn-sm">‹ Tháng trước</button>
                                    <button class="btn btn-secondary btn-sm">Tháng sau ›</button>
                                </div>
                            </div>
                            <div class="calendar-grid-full">
                                <div class="calendar-day-full" style="opacity: 0.5;">
                                    <div class="day-number">29</div>
                                </div>
                                <div class="calendar-day-full" style="opacity: 0.5;">
                                    <div class="day-number">30</div>
                                </div>
                                <div class="calendar-day-full" style="opacity: 0.5;">
                                    <div class="day-number">31</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">1</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">2</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">3</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">4</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">5</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">6</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">7</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">8</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">9</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">10</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">11</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">12</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">13</div>
                                    <div class="day-matches">8 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">14</div>
                                    <div class="day-matches">12 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">15</div>
                                    <div class="day-matches">10 trận</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">16</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">17</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">18</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">19</div>
                                    <div class="day-matches">6 trận</div>
                                </div>
                                <div class="calendar-day-full today has-matches">
                                    <div class="day-number">20</div>
                                    <div class="day-matches">15 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">21</div>
                                    <div class="day-matches">14 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">22</div>
                                    <div class="day-matches">11 trận</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">23</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">24</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">25</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">26</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">27</div>
                                    <div class="day-matches">9 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">28</div>
                                    <div class="day-matches">7 trận</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">29</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">30</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">31</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Modal Cập Nhật Điểm Trận Đấu -->
    <div id="updateScoreModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: var(--radius-lg); padding: 2rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; font-size: 1.5rem; color: var(--text-primary);">Cập Nhật Điểm Trận Đấu</h2>
                <button onclick="closeUpdateScoreModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
            </div>
            
            <form id="updateScoreForm">
                @csrf
                <input type="hidden" id="tournamentId" name="tournament_id">
                <input type="hidden" id="matchId" name="match_id">
                
                <!-- Completed Sets Display -->
                <div id="completedSetsContainer" style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(0, 217, 181, 0.1); border-radius: var(--radius-md); display: none;">
                    <h4 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 0.875rem;">Các Set đã hoàn thành</h4>
                    <div id="completedSetsList" style="display: flex; gap: 0.5rem; flex-wrap: wrap;"></div>
                </div>

                <!-- Athlete 1 Info -->
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md);">
                    <h3 id="athlete1Name" style="margin: 0 0 1rem 0; color: var(--text-primary);"></h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-secondary);">Điểm Set Hiện Tại</label>
                            <input type="number" id="athlete1Score" name="athlete1_score" min="0" max="21" style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: var(--radius-md); font-size: 1rem;">
                        </div>
                    </div>
                </div>
                
                <!-- Athlete 2 Info -->
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md);">
                    <h3 id="athlete2Name" style="margin: 0 0 1rem 0; color: var(--text-primary);"></h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-secondary);">Điểm Set Hiện Tại</label>
                            <input type="number" id="athlete2Score" name="athlete2_score" min="0" max="21" style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: var(--radius-md); font-size: 1rem;">
                        </div>
                    </div>
                </div>
                
                <!-- Match Status -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-secondary);">Trạng Thái Trận Đấu</label>
                    <select id="matchStatus" name="status" style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: var(--radius-md); font-size: 1rem;">
                        <option value="in_progress">Đang diễn ra</option>
                        <option value="completed">Kết thúc</option>
                    </select>
                </div>
                
                <!-- Final Score (for completed matches) -->
                <div id="finalScoreContainer" style="margin-bottom: 1.5rem; display: none;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-secondary);">Tỷ Số Cuối Cùng (ví dụ: 11-7, 11-8 hoặc 11-9)</label>
                    <input type="text" id="finalScore" name="final_score" placeholder="11-7, 11-8" style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: var(--radius-md); font-size: 1rem;">
                </div>
                
                <!-- Actions -->
                <div style="display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap;">
                    <button type="button" onclick="closeUpdateScoreModal()" class="btn btn-ghost" style="flex: 1; min-width: 100px;">Hủy</button>
                    <button type="button" onclick="submitMatchScore('update')" class="btn btn-secondary" style="flex: 1; min-width: 120px;">💾 Cập nhật Điểm</button>
                    <button type="button" onclick="submitMatchScore('end_set')" class="btn btn-secondary" style="flex: 1; min-width: 120px;">✅ Kết thúc Set</button>
                    <button type="button" onclick="submitMatchScore('end_match')" class="btn btn-primary" style="flex: 1; min-width: 120px;">🏁 Kết thúc Trận</button>
                </div>
            </form>
        </div>
    </div>

@endsection
@section('js')
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        }

        // Mobile menu toggle
        if (window.innerWidth <= 1024) {
            toggleSidebar();
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth <= 1024) {
                document.getElementById('sidebar').classList.add('collapsed');
                document.getElementById('mainContent').classList.add('sidebar-collapsed');
            }
        });

        // Tab switching
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked tab
            event.target.classList.add('active');

            // Save current tab to localStorage
            localStorage.setItem('currentMatchTab', tabName);
        }

        // Restore tab from URL parameter, default to 'live'
         function restoreTabState() {
             // Check URL for tab parameter
             const urlParams = new URLSearchParams(window.location.search);
             let tabName = urlParams.get('tab') || 'live'; // Always default to 'live'

             // Switch to the tab
             const tabContent = document.getElementById(tabName);
             if (tabContent) {
                 document.querySelectorAll('.tab-content').forEach(content => {
                     content.classList.remove('active');
                 });
                 document.querySelectorAll('.tab').forEach(tab => {
                     tab.classList.remove('active');
                 });

                 tabContent.classList.add('active');

                 // Find and activate the corresponding tab button
                 const tabButtons = document.querySelectorAll('.tab');
                 tabButtons.forEach(btn => {
                     if (btn.textContent.includes(getTabLabel(tabName))) {
                         btn.classList.add('active');
                     }
                 });
             }
         }

        // Helper function to get tab label from tab name
        function getTabLabel(tabName) {
            const labels = {
                'live': 'LIVE',
                'upcoming': 'Sắp Tới',
                'completed': 'Đã Kết Thúc',
                'calendar': 'Lịch'
            };
            return labels[tabName] || '';
        }

        // Update pagination links to include tab parameter
        function updatePaginationLinks() {
            const currentTab = localStorage.getItem('currentMatchTab') || 'live';
            const paginationLinks = document.querySelectorAll('.custom-pagination a');
            paginationLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href) {
                    // Add or update tab parameter
                    const url = new URL(href, window.location.origin);
                    url.searchParams.set('tab', currentTab);
                    link.setAttribute('href', url.toString());
                }
            });
        }

        // Modal functions for updating match score
         function openUpdateScoreModal(tournamentId, matchId) {
             // Fetch match data via AJAX
             fetch(`/homeyard/tournaments/${tournamentId}/matches/${matchId}`, {
                 headers: {
                     'X-Requested-With': 'XMLHttpRequest'
                 }
             })
             .then(response => {
                 if (!response.ok) {
                     throw new Error(`HTTP error! status: ${response.status}`);
                 }
                 return response.json();
             })
             .then(data => {
                 if (data.success) {
                     const match = data.match;
                     document.getElementById('matchId').value = matchId;
                     document.getElementById('tournamentId').value = tournamentId;
                     document.getElementById('athlete1Name').textContent = match.athlete1_name;
                     document.getElementById('athlete2Name').textContent = match.athlete2_name;
                     document.getElementById('athlete1Score').value = match.athlete1_score || 0;
                     document.getElementById('athlete2Score').value = match.athlete2_score || 0;
                     document.getElementById('matchStatus').value = match.status || 'in_progress';
                     document.getElementById('finalScore').value = match.final_score || '';
                     
                     // Display completed sets if available
                     displayCompletedSets(match.set_scores);
                     
                     // Show/hide final score field based on status
                     toggleFinalScoreField();
                     
                     // Show modal
                     const modal = document.getElementById('updateScoreModal');
                     modal.style.display = 'flex';
                 } else {
                     alert('Lỗi: ' + (data.message || 'Không thể tải dữ liệu'));
                 }
             })
             .catch(error => {
                 console.error('Error:', error);
                 alert('Lỗi: Không thể tải dữ liệu trận đấu. ' + error.message);
             });
         }

         // Display completed sets
         function displayCompletedSets(setScoresJson) {
             const container = document.getElementById('completedSetsContainer');
             const list = document.getElementById('completedSetsList');
             
             list.innerHTML = ''; // Clear previous content

             if (!setScoresJson) {
                 container.style.display = 'none';
                 return;
             }

             let setScores = [];
             try {
                 setScores = typeof setScoresJson === 'string' ? JSON.parse(setScoresJson) : setScoresJson;
             } catch (e) {
                 container.style.display = 'none';
                 return;
             }

             if (!Array.isArray(setScores) || setScores.length === 0) {
                 container.style.display = 'none';
                 return;
             }

             // Create badge for each set
             setScores.forEach((set, index) => {
                 const badge = document.createElement('div');
                 badge.style.cssText = 'background: var(--primary-color); color: white; padding: 0.5rem 1rem; border-radius: var(--radius-full); font-size: 0.875rem; font-weight: 600;';
                 badge.textContent = `Set ${index + 1}: ${set.athlete1_score}-${set.athlete2_score}`;
                 list.appendChild(badge);
             });

             container.style.display = 'block';
         }

         function closeUpdateScoreModal() {
             const modal = document.getElementById('updateScoreModal');
             modal.style.display = 'none';
         }

         function toggleFinalScoreField() {
             const status = document.getElementById('matchStatus').value;
             const container = document.getElementById('finalScoreContainer');
             if (status === 'completed') {
                 container.style.display = 'block';
             } else {
                 container.style.display = 'none';
             }
         }

         // Handle score submission based on action type
         function submitMatchScore(actionType) {
             const tournamentId = document.getElementById('tournamentId').value;
             const matchId = document.getElementById('matchId').value;
             
             if (!tournamentId || !matchId) {
                 alert('Lỗi: Không tìm thấy thông tin giải đấu hoặc trận đấu');
                 return;
             }

             // Nếu là kết thúc trận, cần hiển thị field cuối cùng và yêu cầu nhập
             if (actionType === 'end_match') {
                 const finalScore = document.getElementById('finalScore').value.trim();
                 if (!finalScore) {
                     // Hiển thị field finalScore
                     document.getElementById('finalScoreContainer').style.display = 'block';
                     document.getElementById('finalScore').focus();
                     alert('Vui lòng nhập tỷ số cuối cùng\n\nVí dụ:\n- 11-7 (1 set)\n- 11-7, 11-8 (2 set)\n- 11-7, 9-11, 11-9 (3 set)');
                     return;
                 }
                 
                 // Validate format
                 const sets = finalScore.split(',');
                 let isValid = true;
                 for (let set of sets) {
                     const scores = set.trim().split('-');
                     if (scores.length !== 2 || !scores[0].trim() || !scores[1].trim()) {
                         isValid = false;
                         break;
                     }
                     const score1 = parseInt(scores[0].trim());
                     const score2 = parseInt(scores[1].trim());
                     if (isNaN(score1) || isNaN(score2)) {
                         isValid = false;
                         break;
                     }
                 }
                 
                 if (!isValid) {
                     alert('Format tỷ số không hợp lệ\n\nVí dụ đúng:\n- 11-7\n- 11-7, 11-8\n- 11-7, 9-11, 11-9');
                     document.getElementById('finalScore').focus();
                     return;
                 }
             }

             const athlete1Score = parseInt(document.getElementById('athlete1Score').value);
             const athlete2Score = parseInt(document.getElementById('athlete2Score').value);

             if (athlete1Score < 0 || athlete2Score < 0) {
                 alert('Lỗi: Điểm không thể âm');
                 return;
             }

             let payload = {
                 athlete1_score: athlete1Score,
                 athlete2_score: athlete2Score,
                 action: actionType
             };

             // Xử lý theo từng loại action
             if (actionType === 'update') {
                 // Chỉ cập nhật điểm, không làm gì thêm
                 payload.status = 'in_progress';
             } else if (actionType === 'end_set') {
                 // Kết thúc set - lưu điểm vào set_scores
                 payload.status = 'in_progress';
             } else if (actionType === 'end_match') {
                 // Kết thúc trận - cần nhập tỷ số cuối cùng
                 payload.status = 'completed';
                 payload.final_score = document.getElementById('finalScore').value;
             }

             fetch(`/homeyard/tournaments/${tournamentId}/matches/${matchId}`, {
                 method: 'PUT',
                 headers: {
                     'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                     'X-Requested-With': 'XMLHttpRequest',
                     'Accept': 'application/json',
                     'Content-Type': 'application/json',
                 },
                 body: JSON.stringify(payload)
             })
             .then(response => response.json())
             .then(data => {
                 if (data.success) {
                     let message;
                     if (actionType === 'update') {
                         message = 'Cập nhật điểm thành công';
                     } else if (actionType === 'end_set') {
                         message = 'Kết thúc set thành công. Điểm đã được lưu.';
                     } else if (actionType === 'end_match') {
                         message = 'Kết thúc trận đấu thành công';
                     }
                     alert(message);
                     closeUpdateScoreModal();
                     location.reload();
                 } else {
                     alert('Lỗi: ' + (data.message || 'Không thể cập nhật kết quả'));
                 }
             })
             .catch(error => {
                 console.error('Error:', error);
                 alert('Lỗi: Có vấn đề khi cập nhật kết quả. ' + error.message);
             });
         }

         // Load page
         document.addEventListener('DOMContentLoaded', () => {
             console.log('Match Management Loaded');
             restoreTabState();
             updatePaginationLinks();

             // Update pagination links whenever tab switches
             document.querySelectorAll('.tab').forEach(tab => {
                 tab.addEventListener('click', () => {
                     setTimeout(updatePaginationLinks, 100);
                 });
             });

             // Handle match status change
             const matchStatus = document.getElementById('matchStatus');
             if (matchStatus) {
                 matchStatus.addEventListener('change', toggleFinalScoreField);
             }

             // Close modal when clicking outside
             const modal = document.getElementById('updateScoreModal');
             if (modal) {
                 modal.addEventListener('click', function(e) {
                     if (e.target === this) {
                         closeUpdateScoreModal();
                     }
                 });
             }
         });
        </script>
        @endsection
