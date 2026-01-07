@extends('layouts.verifier')

@section('title', 'Danh sách yêu cầu xác minh')
@section('header', 'Yêu cầu xác minh')

@section('content')
<div class="filter-form">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <label>Trạng thái:</label>
        <a href="{{ route('verifier.requests.index', ['status' => 'pending']) }}"
           class="btn {{ $currentStatus === 'pending' ? 'btn-primary' : 'btn-outline' }} btn-sm">
            Chờ duyệt ({{ $stats['pending'] }})
        </a>
        <a href="{{ route('verifier.requests.index', ['status' => 'approved']) }}"
           class="btn {{ $currentStatus === 'approved' ? 'btn-primary' : 'btn-outline' }} btn-sm">
            Đã duyệt ({{ $stats['approved'] }})
        </a>
        <a href="{{ route('verifier.requests.index', ['status' => 'rejected']) }}"
           class="btn {{ $currentStatus === 'rejected' ? 'btn-primary' : 'btn-outline' }} btn-sm">
            Đã từ chối ({{ $stats['rejected'] }})
        </a>
        <a href="{{ route('verifier.requests.index') }}"
           class="btn {{ !$currentStatus ? 'btn-primary' : 'btn-outline' }} btn-sm">
            Tất cả ({{ $stats['total'] }})
        </a>
    </div>
</div>

<div class="detail-card">
    <div class="detail-card-body">
        @if($requests->isEmpty())
            <div class="alert alert-info">
                Không có yêu cầu nào.
            </div>
        @else
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Trình độ</th>
                        <th>ELO</th>
                        <th>Trạng thái</th>
                        <th>Ngày gửi</th>
                        <th>Người duyệt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
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
                            <span class="badge badge-info">{{ app(\App\Services\SkillQuizService::class)->eloToSkillLevel($request->user->elo_rating ?? 1000) }}</span>
                        </td>
                        <td>{{ number_format($request->user->elo_rating ?? 1000) }}</td>
                        <td>
                            <span class="badge {{ $request->getStatusBadgeClass() }}">
                                {{ $request->getStatusLabel() }}
                            </span>
                        </td>
                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($request->verifier)
                                {{ $request->verifier->name }}
                                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                    {{ $request->verified_at?->format('d/m/Y H:i') }}
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('verifier.requests.show', $request) }}" class="btn btn-primary btn-sm">
                                Xem chi tiết
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 1.5rem;">
                {{ $requests->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
