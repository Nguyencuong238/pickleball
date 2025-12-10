@extends('layouts.app')
@section('title', 'Quản lý Yêu Cầu Cấp Quyền')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Yêu Cầu Cấp Quyền</h2>
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Quay Lại</a>
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-3" id="requestTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $status == 'pending' ? 'active' : '' }}" 
                        onclick="filterByStatus('pending')">
                        Chờ Xét Duyệt <span class="badge bg-warning">{{ $pendingCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $status == 'approved' ? 'active' : '' }}" 
                        onclick="filterByStatus('approved')">
                        Đã Phê Duyệt <span class="badge bg-success">{{ $approvedCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $status == 'rejected' ? 'active' : '' }}" 
                        onclick="filterByStatus('rejected')">
                        Đã Từ Chối <span class="badge bg-danger">{{ $rejectedCount }}</span>
                    </button>
                </li>
            </ul>

            <!-- Requests Table -->
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Người Dùng</th>
                            <th>Email</th>
                            <th>Quyền Yêu Cầu</th>
                            <th>Trạng Thái</th>
                            <th>Ngày Yêu Cầu</th>
                            <th>Xét Duyệt Bởi</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            @php
                                $statusClass = $request->status === 'approved' ? 'success' : 
                                              ($request->status === 'rejected' ? 'danger' : 'warning');
                                $statusText = $request->status === 'approved' ? 'Đã Phê Duyệt' : 
                                            ($request->status === 'rejected' ? 'Đã Từ Chối' : 'Chờ Xét Duyệt');
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $request->user->name }}</strong>
                                </td>
                                <td>{{ $request->user->email }}</td>
                                <td>
                                    @if(is_array($request->permissions) && count($request->permissions) > 0)
                                        @foreach($request->permissions as $permission)
                                            @php
                                                $permissionLabel = match($permission) {
                                                    'home_yard' => 'Quản Lý Sân & Giải',
                                                    'referee' => 'Trọng Tài',
                                                    default => $permission
                                                };
                                            @endphp
                                            <span class="badge bg-info">{{ $permissionLabel }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Không có quyền</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($request->reviewer)
                                        <small>{{ $request->reviewer->name }}<br>{{ $request->reviewed_at->format('d/m/Y') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.permission-requests.show', $request->id) }}" 
                                        class="btn btn-sm btn-primary">Xem Chi Tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Không có yêu cầu nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function filterByStatus(status) {
    window.location.href = '{{ route("admin.permission-requests.index") }}?status=' + status;
}
</script>
@endsection
