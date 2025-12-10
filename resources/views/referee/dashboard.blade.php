@extends('layouts.referee')

@section('title', 'Bảng Điều Khiển Trọng Tài')
@section('header', 'Tổng Quan')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📊 Thống kê</h3>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_matches'] }}</div>
                <div class="stat-label">Tổng trận đấu</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['completed_matches'] }}</div>
                <div class="stat-label">Đã hoàn thành</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['upcoming_matches'] }}</div>
                <div class="stat-label">Sắp diễn ra</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['tournaments'] }}</div>
                <div class="stat-label">Giải đấu</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📅 Trận đấu sắp tới</h3>
    </div>
    <div class="card-body">
        @if($upcomingMatches->isEmpty())
            <div class="alert alert-info">
                ℹ️ Không có trận đấu nào sắp diễn ra
            </div>
        @else
            <div class="table-responsive">
                <table class="matches-table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Giờ</th>
                            <th>Giải đấu</th>
                            <th>Trận đấu</th>
                            <th>Sân</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingMatches as $match)
                            <tr>
                                <td>{{ $match->match_date->format('d/m/Y') }}</td>
                                <td>{{ $match->match_time }}</td>
                                <td>{{ $match->tournament->name }}</td>
                                <td>
                                    <strong>{{ $match->athlete1_name ?? 'TBD' }}</strong>
                                    <span style="color: var(--primary-color); font-weight: 700;"> vs </span>
                                    <strong>{{ $match->athlete2_name ?? 'TBD' }}</strong>
                                    @if($match->category)
                                        <br><small style="color: var(--text-secondary);">{{ $match->category->name }}</small>
                                    @endif
                                </td>
                                <td>{{ $match->court->name ?? 'TBA' }}</td>
                                <td>
                                    <a href="{{ route('referee.matches.show', $match) }}" class="btn btn-primary btn-sm">
                                        👁️ Xem
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1rem;">
                <a href="{{ route('referee.matches.index') }}" class="btn btn-secondary">
                    📋 Xem tất cả trận đấu
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
