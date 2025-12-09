@extends('layouts.referee')

@section('title', 'Referee Dashboard')
@section('header', 'Tong Quan')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">[CHART] Thong ke</h3>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_matches'] }}</div>
                <div class="stat-label">Tong tran dau</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['completed_matches'] }}</div>
                <div class="stat-label">Da hoan thanh</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['upcoming_matches'] }}</div>
                <div class="stat-label">Sap dien ra</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $stats['tournaments'] }}</div>
                <div class="stat-label">Giai dau</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">[CALENDAR] Tran dau sap toi</h3>
    </div>
    <div class="card-body">
        @if($upcomingMatches->isEmpty())
            <div class="alert alert-info">
                [INFO] Khong co tran dau nao sap dien ra
            </div>
        @else
            <div class="table-responsive">
                <table class="matches-table">
                    <thead>
                        <tr>
                            <th>Ngay</th>
                            <th>Gio</th>
                            <th>Giai dau</th>
                            <th>Tran dau</th>
                            <th>San</th>
                            <th>Hanh dong</th>
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
                                        [VIEW] Xem
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1rem;">
                <a href="{{ route('referee.matches.index') }}" class="btn btn-secondary">
                    [LIST] Xem tat ca tran dau
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
