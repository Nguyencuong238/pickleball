@extends('layouts.homeyard')

@section('css')
    <style>
        
    </style>
@endsection

@section('content')
<main class="main-content" id="mainContent">
    <div class="container">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <h1>Quản Lý Sân Của Tôi</h1>
                <div class="breadcrumb">
                    <span class="breadcrumb-item">
                        <a href="{{ route('homeyard.dashboard') }}" class="breadcrumb-link">🏠 Dashboard</a>
                    </span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item">Sân</span>
                </div>
            </div>
            <div class="header-right">
                <div class="header-search">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="matchSearch" class="search-input" placeholder="Tìm kiếm cụm sân...">
                </div>
                <div class="header-user">
                    <div class="user-avatar">{{ auth()->user()->getInitials() }}</div>
                    <div class="user-info">
                        <div class="user-name">{{auth()->user()->name}}</div>
                        <div class="user-role">{{auth()->user()->getFirstRoleName()}}</div>
                    </div>
                </div>
            </div>
        </header>

        <a href="{{ route('homeyard.stadiums.create') }}" class="btn-add mb-3">➕ Thêm Sân Mới</a>

        <!-- Stadium List Card -->
        <div class="card fade-in">
            <div class="card-header">
                <h3 class="card-title">🏟️ Danh Sách Sân</h3>
            </div>
            <div class="card-body">
                @if($stadiums->count() > 0)
                    <div style="overflow-x: auto;">
                        <table class="rankings-table">
                            <thead>
                                <tr>
                                    <th>Ảnh</th>
                                    <th>Tên Sân</th>
                                    <th>Địa Chỉ</th>
                                    <th>Trạng Thái</th>
                                    <th style="text-align: center;">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stadiums as $stadium)
                                    <tr>
                                        <td>
                                            @if($stadium->hasMedia('banner'))
                                                <img src="{{ $stadium->getFirstMediaUrl('banner') }}" alt="{{ $stadium->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                                            @else
                                                <div style="width: 50px; height: 50px; background-color: #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td><strong>{{ $stadium->name }}</strong></td>
                                        <td>{{ Str::limit($stadium->address, 50) }}</td>
                                        <td>
                                            @if($stadium->status === 'active')
                                                <span class="badge badge-success">Hoạt Động</span>
                                            @else
                                                <span class="badge badge-danger">Không Hoạt Động</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ route('homeyard.stadiums.edit', $stadium) }}" class="btn btn-primary btn-sm">✏️ Sửa</a>
                                            <form method="POST" action="{{ route('homeyard.stadiums.destroy', $stadium) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">🗑️ Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($stadiums->hasPages())
                        <div style="margin-top: 20px; text-align: center;">
                            {{ $stadiums->links() }}
                        </div>
                    @endif
                @else
                    <div style="padding: 60px 20px; text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 20px;">🏟️</div>
                        <h4 style="color: #9ca3af; margin: 20px 0;">Chưa có sân nào</h4>
                        <p style="color: #9ca3af;">Hãy <a href="{{ route('homeyard.stadiums.create') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">thêm sân mới</a> để bắt đầu</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection
