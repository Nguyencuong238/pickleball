@extends('layouts.verifier')

@section('title', 'Tổng quan Xác Minh OPR')
@section('header', 'Tổng quan')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['pending'] }}</div>
        <div class="stat-label">Đang chờ duyệt</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['approved'] }}</div>
        <div class="stat-label">Đã duyệt</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['rejected'] }}</div>
        <div class="stat-label">Đã từ chối</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-label">Tổng yêu cầu</div>
    </div>
</div>

<div class="detail-card">
    <div class="detail-card-header">
        <h3>Yêu cầu chờ duyệt gần đây</h3>
    </div>
    <div class="detail-card-body">
        @if($pendingRequests->isEmpty())
            <div class="alert alert-info">
                Không có yêu cầu nào đang chờ duyệt.
            </div>
        @else
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>OPR Level</th>
                        <th>ELO</th>
                        <th>Ngày gửi</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingRequests as $request)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div class="user-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                    {{ $request->user->getInitials() }}
                                </div>
                                <div>
                                    <strong>{{ $request->user->name }}</strong>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                        {{ $request->user->email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $request->user->opr_level ?? '2.0' }}</span>
                        </td>
                        <td>{{ number_format($request->user->elo_rating ?? 1000) }}</td>
                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('verifier.requests.show', $request) }}" class="btn btn-primary btn-sm">
                                Xem chi tiết
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="{{ route('verifier.requests.index') }}" class="btn btn-outline">
                    Xem tất cả yêu cầu
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
