@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- <div class="row">
        <div class="col-12">
            <h1 style="font-size: clamp(1.5rem, 4vw, 2.5rem); margin-bottom: 20px;">Bảng Điều Khiển Quản Trị Viên</h1>
            <hr>
        </div>
    </div> --}}

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
            <div class="card bg-primary text-white h-100" onclick="window.location.href='{{ route('admin.users.index') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="card-body">
                    <h5 class="card-title" style="font-size: clamp(0.9rem, 2vw, 1rem);">Tổng User</h5>
                    <p class="card-text" style="font-size: clamp(1.5rem, 4vw, 2rem); font-weight: 700;">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
            <div class="card bg-success text-white h-100" style="cursor: pointer; transition: transform 0.2s;">
                <div class="card-body">
                    <h5 class="card-title" style="font-size: clamp(0.9rem, 2vw, 1rem);">Admin</h5>
                    <p class="card-text" style="font-size: clamp(1.5rem, 4vw, 2rem); font-weight: 700;">{{ $adminUsers }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
            <div class="card bg-info text-white h-100" style="cursor: pointer; transition: transform 0.2s;">
                <div class="card-body">
                    <h5 class="card-title" style="font-size: clamp(0.9rem, 2vw, 1rem);">User thường</h5>
                    <p class="card-text" style="font-size: clamp(1.5rem, 4vw, 2rem); font-weight: 700;">{{ $normalUsers }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
            <div class="card bg-warning text-white h-100" style="cursor: pointer; transition: transform 0.2s;">
                <div class="card-body">
                    <h5 class="card-title" style="font-size: clamp(0.9rem, 2vw, 1rem);">Chủ sân</h5>
                    <p class="card-text" style="font-size: clamp(1.5rem, 4vw, 2rem); font-weight: 700;">{{ $homeYardUsers }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CRUD Management Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 style="margin-bottom: 20px; font-size: clamp(1.2rem, 3vw, 1.5rem);">Quản Lý Nội Dung</h3>
        </div>
        
        <!-- Users Management -->
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users" style="font-size: 2.5rem; color: #0d6efd; margin-bottom: 15px;"></i>
                    <h5 class="card-title">Người Dùng</h5>
                    <p class="card-text text-muted">Xem, chỉnh sửa và quản lý quyền hạn</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm w-100">Quản Lý</a>
                </div>
            </div>
        </div>

        <!-- News Management -->
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-newspaper" style="font-size: 2.5rem; color: #198754; margin-bottom: 15px;"></i>
                    <h5 class="card-title">Tin Tức</h5>
                    <p class="card-text text-muted">Tạo, chỉnh sửa và xóa bài viết</p>
                    <a href="{{ route('admin.news.index') }}" class="btn btn-success btn-sm w-100">Quản Lý</a>
                </div>
            </div>
        </div>

        <!-- Pages Management -->
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt" style="font-size: 2.5rem; color: #0dcaf0; margin-bottom: 15px;"></i>
                    <h5 class="card-title">Trang Tĩnh</h5>
                    <p class="card-text text-muted">Tạo và chỉnh sửa trang</p>
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-info btn-sm w-100">Quản Lý</a>
                </div>
            </div>
        </div>

        <!-- Stadiums Management -->
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-building" style="font-size: 2.5rem; color: #ffc107; margin-bottom: 15px;"></i>
                    <h5 class="card-title">Sân Pickleball</h5>
                    <p class="card-text text-muted">Thêm, chỉnh sửa và quản lý sân</p>
                    <a href="{{ route('admin.stadiums.index') }}" class="btn btn-warning btn-sm w-100">Quản Lý</a>
                </div>
            </div>
        </div>

        <!-- Tournaments Management -->
        <div class="col-12 col-sm-6 col-md-6 col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-trophy" style="font-size: 2.5rem; color: #dc3545; margin-bottom: 15px;"></i>
                    <h5 class="card-title">Giải Đấu</h5>
                    <p class="card-text text-muted">Tạo và quản lý giải đấu</p>
                    <a href="{{ route('admin.tournaments.index') }}" class="btn btn-danger btn-sm w-100">Quản Lý</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Management Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title" style="font-size: clamp(1rem, 2vw, 1.2rem);">Quản Lý Người Dùng</h5>
                </div>
                <div class="card-body" style="overflow-x: auto;">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="font-size: clamp(0.85rem, 1vw, 0.95rem);">ID</th>
                                <th style="font-size: clamp(0.85rem, 1vw, 0.95rem);">Tên</th>
                                <th style="font-size: clamp(0.85rem, 1vw, 0.95rem);">Email</th>
                                <th style="font-size: clamp(0.85rem, 1vw, 0.95rem);">Vai Trò</th>
                                <th style="font-size: clamp(0.85rem, 1vw, 0.95rem);">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td style="font-size: clamp(0.8rem, 1vw, 0.9rem);">{{ $user->id }}</td>
                                    <td style="font-size: clamp(0.8rem, 1vw, 0.9rem); word-break: break-word;">{{ $user->name }}</td>
                                    <td style="font-size: clamp(0.8rem, 1vw, 0.9rem); word-break: break-word;">{{ $user->email }}</td>
                                    <td style="font-size: clamp(0.8rem, 1vw, 0.9rem);">
                                        @if($user->roles->isEmpty())
                                            <span class="badge bg-secondary">Không Có Vai Trò</span>
                                        @else
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary" style="font-size: clamp(0.75rem, 1vw, 0.85rem); white-space: nowrap;">
                                            <i class="bi bi-pencil"></i> Chỉnh Sửa
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Không tìm thấy người dùng</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles & Permissions Section -->
    <div class="row mt-4 d-none">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title" style="font-size: clamp(1rem, 2vw, 1.2rem);">Vai Trò & Quyền Hạn</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($roles as $role)
                            <div class="col-12 col-sm-6 col-md-6 col-lg-6 mb-3">
                                <div class="card border-light">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0" style="font-size: clamp(0.9rem, 2vw, 1rem);">{{ ucfirst($role->name) }}</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($role->permissions->isEmpty())
                                            <p class="text-muted">Không có quyền hạn nào được gán</p>
                                        @else
                                            <ul class="list-unstyled">
                                                @foreach($role->permissions as $permission)
                                                    <li>
                                                        <span class="badge bg-secondary">{{ $permission->name }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .badge {
        padding: 0.4rem 0.6rem;
        margin-right: 0.3rem;
    }
</style>
@endsection
