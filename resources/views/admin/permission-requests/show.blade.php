@extends('layouts.app')
@section('title', 'Chi Tiết Yêu Cầu Cấp Quyền')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Chi Tiết Yêu Cầu Cấp Quyền</h4>
                </div>
                <div class="card-body">
                    <!-- Request Status -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Trạng Thái</strong></label>
                        @php
                            $statusClass = $permissionRequest->status === 'approved' ? 'success' : 
                                          ($permissionRequest->status === 'rejected' ? 'danger' : 'warning');
                            $statusText = $permissionRequest->status === 'approved' ? 'Đã Phê Duyệt' : 
                                        ($permissionRequest->status === 'rejected' ? 'Đã Từ Chối' : 'Chờ Xét Duyệt');
                        @endphp
                        <span class="badge bg-{{ $statusClass }} p-2">{{ $statusText }}</span>
                    </div>

                    <!-- User Information -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Thông Tin Người Dùng</strong></label>
                        <div class="border p-3 rounded">
                            <p class="mb-2"><strong>Tên:</strong> {{ $permissionRequest->user->name }}</p>
                            <p class="mb-2"><strong>Email:</strong> {{ $permissionRequest->user->email }}</p>
                            <p class="mb-0"><strong>Điện Thoại:</strong> {{ $permissionRequest->user->phone ?? 'Không có' }}</p>
                        </div>
                    </div>

                    <!-- Requested Permissions -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Quyền Yêu Cầu</strong></label>
                        <div class="border p-3 rounded">
                            @if(is_array($permissionRequest->permissions) && count($permissionRequest->permissions) > 0)
                                @foreach($permissionRequest->permissions as $permission)
                                    @php
                                        $permissionLabel = match($permission) {
                                            'home_yard' => 'Quản Lý Sân & Giải Đấu - Quản lý sân, tổ chức và quản lý giải đấu',
                                            'referee' => 'Trọng Tài - Phân công làm trọng tài cho các giải đấu',
                                            default => $permission
                                        };
                                    @endphp
                                    <div class="mb-2">
                                        <span class="badge bg-info">{{ $permissionLabel }}</span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">Không có quyền yêu cầu</p>
                            @endif
                        </div>
                    </div>

                    <!-- Request Dates -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Ngày Yêu Cầu</strong></label>
                                <p>{{ $permissionRequest->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                            @if($permissionRequest->reviewed_at)
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Ngày Xét Duyệt</strong></label>
                                    <p>{{ $permissionRequest->reviewed_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    @if($permissionRequest->admin_notes)
                        <div class="mb-4">
                            <label class="form-label"><strong>Ghi Chú Của Admin</strong></label>
                            <div class="border p-3 rounded bg-light">
                                {{ $permissionRequest->admin_notes }}
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    @if($permissionRequest->status === 'pending')
                        <div class="mb-4">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="approve-tab" data-bs-toggle="tab" 
                                        data-bs-target="#approve-content" type="button" role="tab">
                                        Phê Duyệt
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="reject-tab" data-bs-toggle="tab" 
                                        data-bs-target="#reject-content" type="button" role="tab">
                                        Từ Chối
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Approve Form -->
                                <div class="tab-pane fade show active" id="approve-content" role="tabpanel">
                                    <form action="{{ route('admin.permission-requests.approve', $permissionRequest->id) }}" 
                                        method="POST" class="mt-3">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Ghi Chú (Tùy Chọn)</label>
                                            <textarea class="form-control" name="admin_notes" rows="4" 
                                                placeholder="Nhập ghi chú của bạn..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">Phê Duyệt Yêu Cầu</button>
                                    </form>
                                </div>

                                <!-- Reject Form -->
                                <div class="tab-pane fade" id="reject-content" role="tabpanel">
                                    <form action="{{ route('admin.permission-requests.reject', $permissionRequest->id) }}" 
                                        method="POST" class="mt-3">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Lý Do Từ Chối <span class="text-danger">*</span></label>
                                            <textarea class="form-control @error('admin_notes') is-invalid @enderror" 
                                                name="admin_notes" rows="4" required
                                                placeholder="Vui lòng nhập lý do từ chối..."></textarea>
                                            @error('admin_notes')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-danger">Từ Chối Yêu Cầu</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <strong>Yêu cầu này đã được xử lý.</strong><br>
                            Xét duyệt bởi: {{ $permissionRequest->reviewer->name ?? 'N/A' }}<br>
                            Ngày xét duyệt: {{ $permissionRequest->reviewed_at->format('d/m/Y H:i') }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('admin.permission-requests.index') }}" class="btn btn-secondary">Quay Lại</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
