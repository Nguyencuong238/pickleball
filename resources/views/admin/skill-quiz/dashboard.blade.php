@extends('layouts.app')

@section('title', 'Skill Quiz Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Skill Quiz Management</h1>
        <a href="{{ route('admin.skill-quiz.index') }}" class="btn btn-outline-primary">
            📋 Xem tất cả
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['total_attempts'] }}</h3>
                    <small class="text-muted">Tổng số</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['completed'] }}</h3>
                    <small>Hoàn thành</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-warning">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['in_progress'] }}</h3>
                    <small>Đang làm</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-secondary text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['abandoned'] }}</h3>
                    <small>Bỏ dở</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center bg-danger text-white">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['flagged'] }}</h3>
                    <small>Có cảnh báo</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ELO Distribution --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📊 Phân bố ELO</h5>
                </div>
                <div class="card-body">
                    @php
                        $maxCount = $eloDistribution->max() ?: 1;
                        $totalCompleted = $stats['completed'] ?: 1;
                    @endphp
                    @foreach($eloDistribution as $range => $count)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ $range }}</span>
                                <span>{{ $count }} ({{ number_format(($count / $totalCompleted) * 100, 1) }}%)</span>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-primary" style="width: {{ ($count / $maxCount) * 100 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($eloDistribution->isEmpty())
                        <p class="text-muted text-center">Chưa có dữ liệu</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Flagged Attempts --}}
        <div class="col-md-6 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white d-flex justify-content-between">
                    <h5 class="mb-0">⚠️ Cần xem xét</h5>
                    <span class="badge bg-light text-danger">{{ count($flaggedAttempts) }}</span>
                </div>
                <div class="card-body">
                    @if(count($flaggedAttempts) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>ELO</th>
                                    <th>Flags</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($flaggedAttempts as $attempt)
                                    <tr>
                                        <td>{{ $attempt->user->name }}</td>
                                        <td>{{ $attempt->final_elo }}</td>
                                        <td><span class="badge bg-danger">{{ count($attempt->flags ?? []) }}</span></td>
                                        <td>
                                            <a href="{{ route('admin.skill-quiz.show', $attempt) }}" class="btn btn-sm btn-outline-primary">
                                                Xem
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p class="text-muted text-center mb-0">Không có quiz cần xem xét</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Attempts --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">🕐 Quiz gần đây</h5>
            <a href="{{ route('admin.skill-quiz.index') }}" class="btn btn-sm btn-primary">Xem tất cả</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Trạng thái</th>
                            <th>ELO</th>
                            <th>Điểm %</th>
                            <th>Thời gian</th>
                            <th>Ngày</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttempts as $attempt)
                            <tr>
                                <td>{{ $attempt->user->name }}</td>
                                <td>
                                    @if($attempt->status === 'completed')
                                        <span class="badge bg-success">Hoàn thành</span>
                                    @elseif($attempt->status === 'in_progress')
                                        <span class="badge bg-warning">Đang làm</span>
                                    @else
                                        <span class="badge bg-secondary">Bỏ dở</span>
                                    @endif
                                </td>
                                <td>{{ $attempt->final_elo ?? '-' }}</td>
                                <td>{{ $attempt->quiz_percent ? number_format($attempt->quiz_percent, 1) . '%' : '-' }}</td>
                                <td>{{ $attempt->duration_seconds ? gmdate('i:s', $attempt->duration_seconds) : '-' }}</td>
                                <td>{{ $attempt->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.skill-quiz.show', $attempt) }}" class="btn btn-sm btn-outline-primary">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Chưa có quiz nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">🔗 Liên kết nhanh</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('admin.skill-quiz.index') }}" class="btn btn-outline-primary me-2">
                📋 Tất cả Attempts
            </a>
            <a href="{{ route('admin.skill-quiz.index', ['flagged' => '1']) }}" class="btn btn-outline-danger me-2">
                ⚠️ Có cảnh báo
            </a>
            <a href="{{ route('admin.skill-quiz.index', ['status' => 'completed']) }}" class="btn btn-outline-success me-2">
                ✅ Hoàn thành
            </a>
            <a href="{{ route('admin.oprs.dashboard') }}" class="btn btn-outline-info">
                📈 OPRS Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
