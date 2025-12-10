@extends('layouts.app')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            {{-- <h2>Manage User Permissions</h2> --}}

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Loại tài khoản</th>
                        <th>Trạng thái</th>
                        <th>Vai trò</th>
                        <th>Yêu Cầu Quyền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $permissionRequest = \App\Models\PermissionRequest::where('user_id', $user->id)
                                ->where('status', 'pending')
                                ->first();
                        @endphp
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role_type === 'court_owner')
                                    <span class="badge bg-warning">Chủ sân</span>
                                @else
                                    <span class="badge bg-secondary">Người dùng</span>
                                @endif
                            </td>
                            <td>
                                @if($user->status === 'pending')
                                    <span class="badge bg-danger">Chờ duyệt</span>
                                @elseif($user->status === 'approved')
                                    <span class="badge bg-success">Đã duyệt</span>
                                @else
                                    <span class="badge bg-dark">Từ chối</span>
                                @endif
                            </td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge bg-info">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">Chưa gán</span>
                                @endforelse
                            </td>
                            <td>
                                @if($permissionRequest)
                                    @if(is_array($permissionRequest->permissions))
                                        @foreach($permissionRequest->permissions as $perm)
                                            <span class="badge bg-warning">{{ $perm }}</span>
                                        @endforeach
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($permissionRequest)
                                    <form method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" formaction="{{ route('admin.permission-requests.approve', $permissionRequest->id) }}" class="btn btn-sm btn-success">Duyệt</button>
                                        <button type="submit" formaction="{{ route('admin.permission-requests.reject', $permissionRequest->id) }}" class="btn btn-sm btn-danger">Từ chối</button>
                                    </form>
                                @else
                                    @if($user->status === 'pending')
                                        <form method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" formaction="{{ route('admin.users.approve', $user) }}" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc chắn muốn duyệt?')">Duyệt</button>
                                            <button type="submit" formaction="{{ route('admin.users.reject', $user) }}" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối?')">Từ chối</button>
                                        </form>
                                    @endif
                                @endif
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">Sửa</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Không tìm thấy người dùng</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
