@extends('layouts.referee')

@section('title', 'My Matches')
@section('header', 'Tran Dau Cua Toi')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">[FILTER] Bo loc</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form">
            <select name="tournament_id">
                <option value="">-- Tat ca giai dau --</option>
                @foreach($tournaments as $tournament)
                    <option value="{{ $tournament->id }}" {{ request('tournament_id') == $tournament->id ? 'selected' : '' }}>
                        {{ $tournament->name }}
                    </option>
                @endforeach
            </select>

            <select name="status">
                <option value="">-- Tat ca trang thai --</option>
                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Tu ngay">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Den ngay">

            <button type="submit" class="btn btn-primary">[SEARCH] Loc</button>
            <a href="{{ route('referee.matches.index') }}" class="btn btn-secondary">[CLEAR] Xoa</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">[LIST] Danh sach tran dau</h3>
    </div>
    <div class="card-body">
        @if($matches->isEmpty())
            <div class="alert alert-info">
                [INFO] Khong tim thay tran dau nao
            </div>
        @else
            <div class="table-responsive">
                <table class="matches-table">
                    <thead>
                        <tr>
                            <th>Ngay</th>
                            <th>Giai dau</th>
                            <th>Tran dau</th>
                            <th>Trang thai</th>
                            <th>Ti so</th>
                            <th>Hanh dong</th>
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
                                    <strong>{{ $match->athlete1_name ?? 'TBD' }}</strong>
                                    <span style="color: var(--primary-color); font-weight: 700;"> vs </span>
                                    <strong>{{ $match->athlete2_name ?? 'TBD' }}</strong>
                                    @if($match->category)
                                        <br><small style="color: var(--text-secondary);">{{ $match->category->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($match->status == 'scheduled')
                                        <span class="badge badge-scheduled">[CLOCK] Scheduled</span>
                                    @elseif($match->status == 'in_progress')
                                        <span class="badge badge-in-progress">[PLAY] In Progress</span>
                                    @elseif($match->status == 'completed')
                                        <span class="badge badge-completed">[CHECK] Completed</span>
                                    @else
                                        <span class="badge badge-scheduled">{{ $match->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $match->final_score ?? '-' }}</td>
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

            <div style="margin-top: 1.5rem;">
                {{ $matches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
