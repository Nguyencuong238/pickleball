@extends('layouts.referee')

@section('title', 'Trận Đấu Của Tôi')
@section('header', 'Trận Đấu Của Tôi')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🔍 Bộ lọc</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form">
            <select name="tournament_id">
                <option value="">-- Tất cả giải đấu --</option>
                @foreach($tournaments as $tournament)
                    <option value="{{ $tournament->id }}" {{ request('tournament_id') == $tournament->id ? 'selected' : '' }}>
                        {{ $tournament->name }}
                    </option>
                @endforeach
            </select>

            <select name="status">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Đã lên lịch</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Đang diễn ra</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Từ ngày">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Đến ngày">

            <button type="submit" class="btn btn-primary">🔎 Lọc</button>
            <a href="{{ route('referee.matches.index') }}" class="btn btn-secondary">✖️ Xoá</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 Danh sách trận đấu</h3>
    </div>
    <div class="card-body">
        @if($matches->isEmpty())
            <div class="alert alert-info">
                ℹ️ Không tìm thấy trận đấu nào
            </div>
        @else
            <div class="table-responsive">
                <table class="matches-table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Giải đấu</th>
                            <th>Trận đấu</th>
                            <th>Trạng thái</th>
                            <th>Tỉ số</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matches as $match)
                            <tr>
                                <td>
                                    {{ $match->match_date->format('d/m/Y') }}
                                    <br><small style="color: var(--text-secondary);">{{ $match->match_time }}</small>
                                </td>
                                <td>{{ $match->tournament->name }}</td>
                                <td>
                                    <strong>{{ $match->athlete1_name ?? 'Chưa xác định' }}</strong>
                                    <span style="color: var(--primary-color); font-weight: 700;"> vs </span>
                                    <strong>{{ $match->athlete2_name ?? 'Chưa xác định' }}</strong>
                                    @if($match->category)
                                        <br><small style="color: var(--text-secondary);">{{ $match->category->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($match->status == 'scheduled')
                                        <span class="badge badge-scheduled">⏰ Đã lên lịch</span>
                                    @elseif($match->status == 'in_progress')
                                        <span class="badge badge-in-progress">▶️ Đang diễn ra</span>
                                    @elseif($match->status == 'completed')
                                        <span class="badge badge-completed">✅ Đã hoàn thành</span>
                                    @else
                                        <span class="badge badge-scheduled">{{ $match->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $match->final_score ?? '-' }}</td>
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

            <div style="margin-top: 1.5rem;">
                {{ $matches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
