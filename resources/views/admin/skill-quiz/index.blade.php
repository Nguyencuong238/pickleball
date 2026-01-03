@extends('layouts.app')

@section('title', 'Skill Quiz Attempts')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h1>Danh sach Skill Quiz</h1>
        <a href="{{ route('admin.skill-quiz.dashboard') }}" class="btn btn-secondary">
            📊 Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Đang làm</option>
                        <option value="abandoned" {{ request('status') === 'abandoned' ? 'selected' : '' }}>Bỏ dở</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cảnh báo</label>
                    <select name="flagged" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('flagged') === '1' ? 'selected' : '' }}>Có cảnh báo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tìm user</label>
                    <input type="text" name="search" class="form-control" placeholder="Tên hoặc email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
            </form>
            @if(request()->hasAny(['status', 'flagged', 'search', 'date_from', 'date_to']))
                <div class="mt-2">
                    <a href="{{ route('admin.skill-quiz.index') }}" class="btn btn-sm btn-outline-secondary">
                        ✕ Xóa bộ lọc
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Trạng thái</th>
                            <th>ELO</th>
                            <th>Điểm %</th>
                            <th>Thời gian</th>
                            <th>Flags</th>
                            <th>Ngày</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attempts as $attempt)
                            <tr class="{{ count($attempt->flags ?? []) > 0 ? 'table-warning' : '' }}">
                                <td>
                                    <a href="{{ route('admin.oprs.users.detail', $attempt->user) }}">
                                        {{ $attempt->user->name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $attempt->user->email }}</small>
                                </td>
                                <td>
                                    @if($attempt->status === 'completed')
                                        <span class="badge bg-success">Hoàn thành</span>
                                    @elseif($attempt->status === 'in_progress')
                                        <span class="badge bg-warning text-dark">Đang làm</span>
                                    @else
                                        <span class="badge bg-secondary">Bỏ dở</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attempt->final_elo)
                                        <strong>{{ $attempt->final_elo }}</strong>
                                        @if($attempt->calculated_elo && $attempt->calculated_elo !== $attempt->final_elo)
                                            <br>
                                            <small class="text-muted">(gốc: {{ $attempt->calculated_elo }})</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $attempt->quiz_percent ? number_format($attempt->quiz_percent, 1) . '%' : '-' }}</td>
                                <td>
                                    @if($attempt->duration_seconds)
                                        {{ gmdate('i:s', $attempt->duration_seconds) }}
                                        @if($attempt->duration_seconds < 180)
                                            <span class="badge bg-danger">Quá nhanh</span>
                                        @elseif($attempt->duration_seconds > 900)
                                            <span class="badge bg-warning text-dark">Quá lâu</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if(count($attempt->flags ?? []) > 0)
                                        <span class="badge bg-danger">{{ count($attempt->flags) }}</span>
                                        @php
                                            $flagTypes = collect($attempt->flags)->pluck('type')->unique();
                                        @endphp
                                        <br>
                                        <small class="text-muted">{{ $flagTypes->implode(', ') }}</small>
                                    @else
                                        <span class="text-success">✓</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $attempt->created_at->format('d/m/Y') }}<br>
                                    <small class="text-muted">{{ $attempt->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.skill-quiz.show', $attempt) }}" class="btn btn-sm btn-outline-primary">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Không có dữ liệu
                                    @if(request()->hasAny(['status', 'flagged', 'search', 'date_from', 'date_to']))
                                        phù hợp với bộ lọc
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Hiển thị {{ $attempts->firstItem() ?? 0 }} - {{ $attempts->lastItem() ?? 0 }}
                    trên {{ $attempts->total() }} kết quả
                </div>
                <div>
                    {{ $attempts->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
