@extends('layouts.homeyard')

<style>
    /* Page-specific styles */
    .welcome-banner {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        border-radius: var(--radius-xl);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-lg);
    }

    .welcome-banner h2 {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }

    .welcome-banner p {
        opacity: 0.95;
        font-size: 1rem;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .quick-action-btn {
        background: var(--bg-white);
        border: 2px solid var(--border-color);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        text-align: center;
        cursor: pointer;
        transition: all var(--transition);
        text-decoration: none;
        color: var(--text-primary);
    }

    .quick-action-btn:hover {
        border-color: var(--primary-color);
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    .quick-action-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .quick-action-title {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .recent-list {
        list-style: none;
    }

    .recent-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        transition: all var(--transition);
    }

    .recent-item:hover {
        background: var(--bg-light);
    }

    .recent-item:last-child {
        border-bottom: none;
    }

    .recent-item-info {
        flex: 1;
    }

    .recent-item-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .recent-item-meta {
        font-size: 0.75rem;
        color: var(--text-light);
    }

    .activity-timeline {
        position: relative;
        padding-left: 2rem;
    }

    .activity-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .activity-item::before {
        content: '';
        position: absolute;
        left: -2rem;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 3px solid var(--bg-white);
        box-shadow: 0 0 0 2px var(--primary-color);
    }

    .activity-item::after {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 1.5rem;
        bottom: 0;
        width: 2px;
        background: var(--border-color);
    }

    .activity-item:last-child::after {
        display: none;
    }

    .activity-time {
        font-size: 0.75rem;
        color: var(--text-light);
        margin-bottom: 0.25rem;
    }

    .activity-content {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .chart-placeholder {
        height: 300px;
        background: var(--bg-light);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-light);
        font-size: 0.875rem;
    }

    .calendar-widget {
        background: var(--bg-light);
        padding: 1rem;
        border-radius: var(--radius-md);
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .calendar-title {
        font-weight: 700;
        color: var(--text-primary);
    }

    .calendar-nav {
        display: flex;
        gap: 0.5rem;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
    }

    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        cursor: pointer;
        transition: all var(--transition);
    }

    .calendar-day:hover {
        background: var(--bg-white);
    }

    .calendar-day.has-event {
        background: rgba(0, 217, 181, 0.1);
        color: var(--primary-color);
        font-weight: 700;
    }

    .calendar-day.today {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-weight: 700;
    }

    /* Charts responsive styles */
    .chart-container {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 75%;
        overflow: hidden;
    }

    .chart-container canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100% !important;
        height: 100% !important;
    }

    .grid.grid-2 {
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .recent-action {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 1200px) {
        .grid.grid-2 {
            grid-template-columns: 1fr;
        }
    }

    .card-body canvas {
        max-height: 300px;
        width: 100% !important;
        height: auto !important;
    }

    @media (max-width: 768px) {
        .recent-action {
            grid-template-columns: 1fr;
        }
        .top-header {
            margin-top: 100px;
        }
    }
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1>Tổng Quan Hệ Thống</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <span>🏠</span>
                            <span>Dashboard</span>
                        </span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item">Tổng quan</span>
                    </div>
                </div>
                <div class="header-right">
                    {{-- <div class="header-notifications">
                        <button class="notification-btn">
                            <span>🔔</span>
                            <span class="notification-badge">5</span>
                        </button>
                    </div> --}}
                    <div class="header-user">
                        <div class="user-avatar">{{ auth()->user()->getInitials() }}</div>
                        <div class="user-info">
                            <div class="user-name">{{auth()->user()->name}}</div>
                            <div class="user-role">{{auth()->user()->getFirstRoleName()}}</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Welcome Banner -->
            {{-- <div class="welcome-banner fade-in">
                <h2>👋 Xin chào, Admin!</h2>
                <p>Chào mừng trở lại với hệ thống quản lý giải đấu Pickleball. Hôm nay bạn có 3 giải đấu đang diễn ra và 45
                    trận đấu cần quản lý.</p>
            </div> --}}

            <!-- Quick Actions -->
            <div class="quick-actions fade-in">
                <a href="{{ route('homeyard.tournaments.index') }}" class="quick-action-btn">
                    <div class="quick-action-icon">➕</div>
                    <div class="quick-action-title">Tạo Giải Mới</div>
                </a>
                {{-- <a href="#" class="quick-action-btn">
                    <div class="quick-action-icon">✅</div>
                    <div class="quick-action-title">Duyệt VĐV</div>
                </a> --}}
                <a href="{{ route('homeyard.matches') }}" class="quick-action-btn">
                    <div class="quick-action-icon">🎾</div>
                    <div class="quick-action-title">Cập Nhật Kết Quả</div>
                </a>
                {{-- <a href="#" class="quick-action-btn">
                    <div class="quick-action-icon">📊</div>
                    <div class="quick-action-title">Xem Báo Cáo</div>
                </a> --}}
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Tổng Giải Đấu</div>
                            <div class="stat-value">{{ $stats['totalTournaments'] }}</div>
                        </div>
                        <div class="stat-icon primary">🏆</div>
                    </div>
                    <div class="stat-trend {{ $stats['tournamentChangePercent'] >= 0 ? 'up' : 'down' }}">
                        <span>{{ $stats['tournamentChangePercent'] >= 0 ? '↗' : '↘' }}</span>
                        <span>{{ abs($stats['tournamentChangePercent']) }}% vs tháng trước</span>
                    </div>
                    <div class="stat-footer">
                        {{ $stats['ongoingTournaments'] }} đang diễn ra • {{ $stats['upcomingTournaments'] }} sắp tới • {{ $stats['completedTournaments'] }} đã kết thúc
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Vận Động Viên</div>
                            <div class="stat-value">{{ number_format($stats['totalAthletes'], 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon success">👥</div>
                    </div>
                    <div class="stat-trend {{ $stats['athleteChangePercent'] >= 0 ? 'up' : 'down' }}">
                        <span>{{ $stats['athleteChangePercent'] >= 0 ? '↗' : '↘' }}</span>
                        <span>{{ abs($stats['athleteChangePercent']) }}% vs tháng trước</span>
                    </div>
                    <div class="stat-footer">
                        {{ $stats['newAthletesThisMonth'] }} VĐV mới tháng này
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Trận Đấu Hôm Nay</div>
                            <div class="stat-value">{{ $stats['todayMatches'] }}</div>
                        </div>
                        <div class="stat-icon warning">🎾</div>
                    </div>
                    <div class="stat-trend">
                        <span>→</span>
                        <span>{{ $stats['todayLiveMatches'] }} đang diễn ra</span>
                    </div>
                    <div class="stat-footer">
                        {{ $stats['todayRemainingMatches'] }} trận còn lại • {{ $stats['todayDelayedMatches'] }} trận delay
                    </div>
                </div>

                {{-- <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Doanh Thu Tháng</div>
                            <div class="stat-value">₫{{ number_format($stats['monthlyRevenue'], 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon danger">💰</div>
                    </div>
                    <div class="stat-trend up">
                        <span>↗</span>
                        <span>24% vs tháng trước</span>
                    </div>
                    <div class="stat-footer">
                        Mục tiêu: ₫500M (92%)
                    </div>
                </div> --}}
            </div>

            <!-- Main Content Grid -->
            <div class="recent-action">
                <!-- Recent Tournaments -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Giải Đấu Gần Đây</h3>
                        <div class="card-actions">
                            <a href="{{ route('homeyard.tournaments.index') }}" class="btn btn-ghost btn-sm">Xem tất cả →</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="recent-list">
                            @forelse($recentTournaments as $tournament)
                                <li class="recent-item">
                                    <div class="recent-item-info">
                                        <div class="recent-item-title">🏆 {{ $tournament['name'] }}</div>
                                        <div class="recent-item-meta">{{ $tournament['athleteCount'] }} VĐV • {{ $tournament['matchCount'] }} trận • Bắt đầu {{ $tournament['startDate'] }}</div>
                                    </div>
                                    @if($tournament['status'] === 'ongoing')
                                        <span class="badge badge-success">Đang diễn ra</span>
                                    @elseif($tournament['status'] === 'upcoming')
                                        <span class="badge badge-warning">Sắp tới</span>
                                    @else
                                        <span class="badge badge-gray">Đã kết thúc</span>
                                    @endif
                                </li>
                            @empty
                                <li style="text-align: center; padding: 2rem; color: var(--text-light);">
                                    Chưa có giải đấu nào
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Hoạt Động Gần Đây</h3>
                    </div>
                    <div class="card-body">
                        <div class="activity-timeline">
                            @forelse($recentActivities as $activity)
                                <div class="activity-item">
                                    <div class="activity-time">{{ $activity->created_at->diffForHumans() }}</div>
                                    <div class="activity-content">
                                        {{ $activity->action }}
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 2rem; color: var(--text-light);">
                                    Chưa có hoạt động nào
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-2 mt-3">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Thống Kê Giải Đấu Theo Tháng</h3>
                        <select class="form-select" id="yearSelect" style="width: auto;">
                            @php $currentYear = now()->year; $lastYear = $currentYear - 1; @endphp
                            <option value="{{ $currentYear }}" selected>{{ $currentYear }}</option>
                            <option value="{{ $lastYear }}">{{ $lastYear }}</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper" style="position: relative; height: 300px;">
                            <canvas id="tournamentChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Phân Bổ Vận Động Viên</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper" style="position: relative; height: 300px;">
                            <canvas id="athleteChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </main>
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        // Chart.js context
        let tournamentChartInstance = null;
        let athleteChartInstance = null;

        // Real data from database
        const tournamentsData = {!! json_encode($tournamentsByMonth) !!};

        // Real data for athletes by category
        const athletesByContent = {!! json_encode($athletesByCategory) !!};

        function initTournamentChart(year = new Date().getFullYear()) {
            const ctx = document.getElementById('tournamentChart').getContext('2d');
            
            if (tournamentChartInstance) {
                tournamentChartInstance.destroy();
            }

            // Get data for selected year, default to current year if not available
            const yearKey = String(year);
            const data = tournamentsData[yearKey] || Array(12).fill(0);

            tournamentChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                    datasets: [{
                        label: `Số lượng giải đấu ${year}`,
                        data: data,
                        backgroundColor: 'rgba(0, 217, 181, 0.8)',
                        borderColor: 'rgba(0, 217, 181, 1)',
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: 'rgba(0, 217, 181, 1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 12,
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: { size: 11 }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }

        function initAthleteChart() {
            const ctx = document.getElementById('athleteChart').getContext('2d');
            
            if (athleteChartInstance) {
                athleteChartInstance.destroy();
            }

            const colors = [
                'rgba(0, 217, 181, 0.8)',
                'rgba(66, 153, 225, 0.8)',
                'rgba(237, 137, 54, 0.8)',
                'rgba(236, 112, 86, 0.8)',
                'rgba(156, 89, 182, 0.8)'
            ];

            athleteChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(athletesByContent),
                    datasets: [{
                        data: Object.values(athletesByContent),
                        backgroundColor: colors,
                        borderColor: '#fff',
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 12 },
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            padding: 12,
                            titleFont: { size: 14 },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Animate stats on load
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Overview Dashboard Loaded');
            
            // Initialize charts with current year
            const currentYear = new Date().getFullYear();
            initTournamentChart(currentYear);
            initAthleteChart();

            // Year selector change event
            document.getElementById('yearSelect').addEventListener('change', (e) => {
                initTournamentChart(parseInt(e.target.value));
            });
        });
    </script>
@endsection
