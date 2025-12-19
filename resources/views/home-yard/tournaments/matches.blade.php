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

    @media (max-width: 768px) {
        .top-header {
            margin-top: 100px;
        }
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
                            <a href="{{route('homeyard.overview')}}" class="breadcrumb-link">Dashboard</a>
                        </span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-item">Trận Đấu</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="matchSearch" class="search-input" placeholder="Tìm kiếm theo tên VĐV...">
                    </div>
                    <div class="header-user">
                        <div class="user-avatar">{{ auth()->user()->getInitials() }}</div>
                        <div class="user-info">
                            <div class="user-name">{{auth()->user()->name}}</div>
                            <div class="user-role">{{auth()->user()->getFirstRoleName()}}</div>
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
                                                {{-- <button class="btn btn-primary btn-sm" onclick="openUpdateScoreModal({{ $match->tournament_id }}, {{ $match->id }})">📊 Cập nhật điểm</button> --}}
                                                <button class="btn btn-ghost btn-sm" onclick="openMatchDetailsModal({{ $match->tournament_id }}, {{ $match->id }})">👁️ Chi tiết</button>
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
                                                🕐 {{ $match->match_time ?? 'N/A' }} • 📅
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
                                                <button class="btn btn-ghost btn-sm" onclick="openMatchDetailsModal({{ $match->tournament_id }}, {{ $match->id }})">👁️ Chi tiết</button>
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
                                                {{ $match->actual_start_time?->format('H:i') ?? ($match->match_time ?? 'N/A') }}
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
                                                <button class="btn btn-ghost btn-sm" onclick="openMatchDetailsModal({{ $match->tournament_id }}, {{ $match->id }})">👁️ Chi tiết</button>
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
                                 <h3 class="calendar-title-full" id="calendarMonth">Tháng 1, 2025</h3>
                                 <div class="calendar-nav-full">
                                     <button type="button" class="btn btn-secondary btn-sm" onclick="previousMonth()">‹ Tháng trước</button>
                                     <button type="button" class="btn btn-secondary btn-sm" onclick="nextMonth()">Tháng sau ›</button>
                                 </div>
                             </div>
                             <div class="calendar-grid-full" id="calendarDays">
                                 <!-- Calendar days will be generated by JavaScript -->
                             </div>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Chi Tiết Trận Đấu -->
    <div id="matchDetailsModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: var(--radius-lg); padding: 2rem; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; font-size: 1.5rem; color: var(--text-primary);">Chi Tiết Trận Đấu</h2>
                <button onclick="closeMatchDetailsModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-secondary);">×</button>
            </div>
            
            <div id="matchDetailsContent">
                <div style="text-align: center; padding: 2rem;">
                    <p style="color: var(--text-secondary);">Đang tải...</p>
                </div>
            </div>
        </div>
    </div>

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

         // Search functionality
         function searchMatches(searchTerm) {
             const term = searchTerm.toLowerCase().trim();
             // Chỉ tìm trong 3 tab: live, upcoming, completed (không tìm calendar)
             const tabIds = ['live', 'upcoming', 'completed'];
             let hasResults = false;

             // Search trong tất cả tabs
             tabIds.forEach(tabId => {
                 const tabContent = document.getElementById(tabId);
                 if (!tabContent) return;
                 
                 const grid = tabContent.querySelector('.schedule-grid');
                 if (!grid) return; // Skip nếu không có schedule-grid
                 
                 const matchCards = grid.querySelectorAll('.match-card');
                 let gridHasResults = false;
                 let noResultsMsg = grid.querySelector('.no-results-message');

                 matchCards.forEach(card => {
                     // Lấy tên vận động viên từ các player-side
                     const playerSides = card.querySelectorAll('.player-side');
                     let athlete1Name = '';
                     let athlete2Name = '';
                     
                     if (playerSides.length >= 1) {
                         athlete1Name = playerSides[0].querySelector('.player-name-sm')?.textContent.toLowerCase().trim() || '';
                     }
                     if (playerSides.length >= 2) {
                         athlete2Name = playerSides[1].querySelector('.player-name-sm')?.textContent.toLowerCase().trim() || '';
                     }
                     
                     const matches = !term || athlete1Name.includes(term) || athlete2Name.includes(term);
                     
                     if (matches) {
                         card.style.display = '';
                         gridHasResults = true;
                         hasResults = true;
                     } else {
                         card.style.display = 'none';
                     }
                 });

                 // Quản lý no results message cho từng grid
                 if (!gridHasResults && term) {
                     if (!noResultsMsg) {
                         noResultsMsg = document.createElement('p');
                         noResultsMsg.className = 'no-results-message';
                         noResultsMsg.style.cssText = 'text-align: center; padding: 2rem; color: var(--text-secondary);';
                         noResultsMsg.textContent = `Không có trận đấu`;
                         grid.appendChild(noResultsMsg);
                     }
                 } else if (noResultsMsg) {
                     noResultsMsg.remove();
                 }
             });

             }

         // Match Details Modal Functions
         function openMatchDetailsModal(tournamentId, matchId) {
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
                     const htmlContent = buildMatchDetailsHTML(match);
                     document.getElementById('matchDetailsContent').innerHTML = htmlContent;
                     const modal = document.getElementById('matchDetailsModal');
                     modal.style.display = 'flex';
                 } else {
                     alert('Lỗi: ' + (data.message || 'Không thể tải dữ liệu'));
                 }
             })
             .catch(error => {
                 console.error('Error:', error);
                 alert('Lỗi: Không thể tải chi tiết trận đấu. ' + error.message);
             });
         }

         function closeMatchDetailsModal() {
             const modal = document.getElementById('matchDetailsModal');
             modal.style.display = 'none';
         }

         // Format date from ISO to d/m/Y
         function formatDate(dateString) {
             if (!dateString) return 'N/A';
             const d = new Date(dateString);
             if (isNaN(d.getTime())) return 'N/A';
             const day = d.getDate().toString().padStart(2, '0');
             const month = (d.getMonth() + 1).toString().padStart(2, '0');
             const year = d.getFullYear();
             return `${day}/${month}/${year}`;
         }

         function buildMatchDetailsHTML(match) {
             const setScores = match.set_scores ? (typeof match.set_scores === 'string' ? JSON.parse(match.set_scores) : match.set_scores) : [];
             let setsHTML = '';
             
             if (Array.isArray(setScores) && setScores.length > 0) {
                 setScores.forEach((set, index) => {
                     const setWinner = set.athlete1_score > set.athlete2_score ? '🏆' : '🏆';
                     setsHTML += `
                         <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-light); border-radius: var(--radius-md); margin-bottom: 0.5rem;">
                             <span style="color: var(--text-secondary); font-size: 0.875rem;">Set ${index + 1}</span>
                             <span style="font-weight: 600; color: var(--text-primary);">${set.athlete1_score} - ${set.athlete2_score}</span>
                         </div>
                     `;
                 });
             }

             const statusColors = {
                 'scheduled': '#FFC107',
                 'in_progress': '#FF6B6B',
                 'completed': '#4ADE80',
                 'cancelled': '#9CA3AF'
             };

             const statusLabels = {
                 'scheduled': 'Sắp tới',
                 'in_progress': '🔴 Đang diễn ra',
                 'completed': '✅ Đã kết thúc',
                 'cancelled': '❌ Bị hủy'
             };

             return `
                 <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                     <!-- Athlete 1 -->
                     <div style="padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md);">
                         <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                             <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                 ${match.athlete1_name ? match.athlete1_name.substring(0, 2).toUpperCase() : 'N/A'}
                             </div>
                             <div>
                                 <div style="font-weight: 600; color: var(--text-primary);">${match.athlete1_name || 'N/A'}</div>
                                 <div style="font-size: 0.75rem; color: var(--text-secondary);">Vận động viên 1</div>
                             </div>
                         </div>
                         <div style="padding-top: 0.5rem; border-top: 1px solid var(--border-color); margin-top: 0.5rem;">
                             <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color); text-align: center;">
                                 ${match.athlete1_score}
                             </div>
                             <div style="font-size: 0.75rem; color: var(--text-secondary); text-align: center;">Điểm hiện tại</div>
                         </div>
                     </div>

                     <!-- Athlete 2 -->
                     <div style="padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md);">
                         <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                             <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                 ${match.athlete2_name ? match.athlete2_name.substring(0, 2).toUpperCase() : 'N/A'}
                             </div>
                             <div>
                                 <div style="font-weight: 600; color: var(--text-primary);">${match.athlete2_name || 'N/A'}</div>
                                 <div style="font-size: 0.75rem; color: var(--text-secondary);">Vận động viên 2</div>
                             </div>
                         </div>
                         <div style="padding-top: 0.5rem; border-top: 1px solid var(--border-color); margin-top: 0.5rem;">
                             <div style="font-size: 2rem; font-weight: 700; color: var(--secondary-color); text-align: center;">
                                 ${match.athlete2_score}
                             </div>
                             <div style="font-size: 0.75rem; color: var(--text-secondary); text-align: center;">Điểm hiện tại</div>
                         </div>
                     </div>
                 </div>

                 <!-- Match Info -->
                 <div style="padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                     <h3 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1rem;">Thông Tin Trận Đấu</h3>
                     
                     <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                         <div>
                             <label style="display: block; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">📅 Ngày thi đấu</label>
                             <div style="font-weight: 600; color: var(--text-primary);">${formatDate(match.match_date)}</div>
                         </div>
                         <div>
                             <label style="display: block; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">🕐 Giờ thi đấu</label>
                             <div style="font-weight: 600; color: var(--text-primary);">${match.match_time || 'N/A'}</div>
                         </div>
                         <div>
                             <label style="display: block; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">🏆 Vòng thi</label>
                             <div style="font-weight: 600; color: var(--text-primary);">${match.round?.name || 'N/A'}</div>
                         </div>
                         <div>
                             <label style="display: block; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">👥 Nội dung thi đấu</label>
                             <div style="font-weight: 600; color: var(--text-primary);">${match.category?.category_name || 'N/A'}</div>
                         </div>
                         <div>
                             <label style="display: block; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">🎾 Sân thi đấu</label>
                             <div style="font-weight: 600; color: var(--text-primary);">${match.court?.name || 'N/A'}</div>
                         </div>
                         <div>
                             <label style="display: block; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Trạng thái</label>
                             <div style="display: inline-block; background: ${statusColors[match.status] || '#999'}; color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600;">
                                 ${statusLabels[match.status] || match.status}
                             </div>
                         </div>
                         <div>
                             <label style="display: block; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Trọng tài</label>
                             <div style="font-weight: 600; color: var(--text-primary);">${match.referee_name || (match.referee ? match.referee.name : 'Chua chi dinh')}</div>
                         </div>
                     </div>
                 </div>

                 <!-- Sets History -->
                 ${setsHTML ? `
                     <div style="padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                         <h3 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1rem;">Kết Quả Các Set</h3>
                         ${setsHTML}
                     </div>
                 ` : ''}

                 <!-- Final Score -->
                 ${match.final_score ? `
                     <div style="padding: 1rem; background: rgba(74, 222, 128, 0.1); border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 2px solid var(--accent-green);">
                         <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary); font-size: 1rem;">🏁 Tỷ Số Cuối Cùng</h3>
                         <div style="font-size: 1.25rem; font-weight: 700; color: var(--accent-green);">${match.final_score}</div>
                     </div>
                 ` : ''}

                 <!-- Notes -->
                 ${match.notes ? `
                     <div style="padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                         <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary); font-size: 1rem;">📝 Ghi chú</h3>
                         <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">${match.notes}</p>
                     </div>
                 ` : ''}

                 <div style="display: flex; gap: 1rem;">
                     <button type="button" onclick="closeMatchDetailsModal()" class="btn btn-primary" style="flex: 1; min-width: 100px;">Đóng</button>
                 </div>
             `;
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

         // Calendar data from PHP
         const matchesByDate = @json($matchesByDate);

         // Calendar management
         let currentDate = new Date();

         function renderCalendar() {
             const year = currentDate.getFullYear();
             const month = currentDate.getMonth();
             
             // Update title
             const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                                'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
             document.getElementById('calendarMonth').textContent = monthNames[month] + ', ' + year;
             
             // Get first day of month and number of days
             const firstDay = new Date(year, month, 1).getDay();
             const daysInMonth = new Date(year, month + 1, 0).getDate();
             const daysInPrevMonth = new Date(year, month, 0).getDate();
             
             let html = '';
             
             // Previous month days (grayed out)
             for (let i = firstDay - 1; i >= 0; i--) {
                 html += `<div class="calendar-day-full" style="opacity: 0.5;">
                     <div class="day-number">${daysInPrevMonth - i}</div>
                 </div>`;
             }
             
             // Current month days
             const today = new Date();
             for (let day = 1; day <= daysInMonth; day++) {
                 const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                 const matchCount = matchesByDate[dateStr] || 0;
                 const isToday = day === today.getDate() && month === today.getMonth() && year === today.getFullYear();
                 
                 let className = 'calendar-day-full';
                 if (matchCount > 0) className += ' has-matches';
                 if (isToday) className += ' today';
                 
                 html += `<div class="${className}">
                     <div class="day-number">${day}</div>
                     ${matchCount > 0 ? `<div class="day-matches">${matchCount} trận</div>` : ''}
                 </div>`;
             }
             
             // Next month days (grayed out)
             const totalCells = firstDay + daysInMonth;
             const remainingCells = Math.ceil(totalCells / 7) * 7 - totalCells;
             for (let i = 1; i <= remainingCells; i++) {
                 html += `<div class="calendar-day-full" style="opacity: 0.5;">
                     <div class="day-number">${i}</div>
                 </div>`;
             }
             
             document.getElementById('calendarDays').innerHTML = html;
         }

         function previousMonth() {
             currentDate.setMonth(currentDate.getMonth() - 1);
             renderCalendar();
         }

         function nextMonth() {
             currentDate.setMonth(currentDate.getMonth() + 1);
             renderCalendar();
         }

         // Load page
         document.addEventListener('DOMContentLoaded', () => {
             console.log('Match Management Loaded');
             renderCalendar();
             restoreTabState();
             updatePaginationLinks();

             // Add search functionality
             const searchInput = document.getElementById('matchSearch');
             if (searchInput) {
                 searchInput.addEventListener('input', (e) => {
                     searchMatches(e.target.value);
                 });
             }

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
             const updateScoreModal = document.getElementById('updateScoreModal');
             if (updateScoreModal) {
                 updateScoreModal.addEventListener('click', function(e) {
                     if (e.target === this) {
                         closeUpdateScoreModal();
                     }
                 });
             }

             // Close match details modal when clicking outside
             const detailsModal = document.getElementById('matchDetailsModal');
             if (detailsModal) {
                 detailsModal.addEventListener('click', function(e) {
                     if (e.target === this) {
                         closeMatchDetailsModal();
                     }
                 });
             }
             });
        </script>
        @endsection
