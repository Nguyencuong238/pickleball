@extends('admin.layouts.app')

@section('title', 'Quản lý nạp Gems')

@section('content')
<div class="container-fluid py-4">
    <h1 class="mb-4">Quản lý nạp Gems</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Stats cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Chờ duyệt</h5>
                    <h2>{{ $pendingCount }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Đã duyệt hôm nay</h5>
                    <h2>{{ $approvedToday }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Tổng Gems phát hành hôm nay</h5>
                    <h2>{{ number_format($totalGemsToday) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ !$status ? 'active' : '' }}" href="{{ route('admin.gem-topups.index') }}">
                Tất cả
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('admin.gem-topups.index', ['status' => 'pending']) }}">
                Chờ duyệt
                @if($pendingCount > 0)
                    <span class="badge bg-warning text-dark">{{ $pendingCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'completed' ? 'active' : '' }}" href="{{ route('admin.gem-topups.index', ['status' => 'completed']) }}">
                Đã duyệt
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'cancelled' ? 'active' : '' }}" href="{{ route('admin.gem-topups.index', ['status' => 'cancelled']) }}">
                Từ chối
            </a>
        </li>
    </ul>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Số tiền (VND)</th>
                            <th>Gems</th>
                            <th>Nội dung CK</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td>{{ $tx->id }}</td>
                                <td>
                                    <div>{{ $tx->user->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $tx->user->email ?? '' }}</small>
                                </td>
                                <td>{{ number_format($tx->metadata['amount_vnd'] ?? 0) }}</td>
                                <td><strong>{{ number_format($tx->amount) }}</strong></td>
                                <td><code>GEMS{{ $tx->user_id }}T{{ $tx->id }}</code></td>
                                <td>
                                    <small>{{ $tx->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    @if($tx->status === 'pending')
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    @elseif($tx->status === 'completed')
                                        <span class="badge bg-success">Đã duyệt</span>
                                    @elseif($tx->status === 'cancelled')
                                        <span class="badge bg-danger">Từ chối</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tx->status === 'pending')
                                        <form action="{{ route('admin.gem-topups.approve', $tx) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Duyệt nạp {{ $tx->amount }} Gems cho {{ e($tx->user->name ?? '') }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Duyệt</button>
                                        </form>
                                        <form action="{{ route('admin.gem-topups.reject', $tx) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Từ chối yêu cầu nạp Gems này?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Từ chối</button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Không có yêu cầu nạp nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
