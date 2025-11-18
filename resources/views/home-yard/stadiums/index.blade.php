@extends('layouts.front')

@section('content')
<style>
    @media (min-width: 768px) {
        .page-header {
            margin-top: 80px;
        }
    }
</style>
    <div class="page-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 80px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <h1 style="color: white; font-size: clamp(1.75rem, 5vw, 2.5rem); font-weight: 700; margin: 0; line-height: 1.2;">Quản Lý Sân Của Tôi</h1>
            <p style="color: rgba(255, 255, 255, 0.95); margin-top: 12px; margin-bottom: 0; font-size: clamp(0.95rem, 2vw, 1.1rem); font-weight: 500;">Cập nhật và quản lý tất cả các sân của bạn</p>
        </div>
    </div>

<div style="background: #f9fafb; padding: 50px 20px; min-height: 60vh;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <!-- Success Message -->
        @if(session('success'))
            <div style="background: #dcfce7; color: #15803d; padding: 15px 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #15803d;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Header with Add Button -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <a href="{{ route('homeyard.dashboard') }}" style="color: #f59e0b; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
            <a href="{{ route('homeyard.stadiums.create') }}" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i> Thêm Sân Mới
            </a>
        </div>

        <!-- Table -->
        <div style="background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
            @if($stadiums->count() > 0)
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Ảnh</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Tên Sân</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Địa Chỉ</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Số Sân</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Trạng Thái</th>
                                <th style="padding: 15px 20px; text-align: center; font-weight: 600; color: #475569;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stadiums as $stadium)
                                <tr style="border-bottom: 1px solid #e2e8f0; transition: background-color 0.2s;">
                                    <td style="padding: 15px 20px;">
                                        @if($stadium->image)
                                            <img src="{{ asset('storage/' . $stadium->image) }}" alt="{{ $stadium->name }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                                        @else
                                            <div style="width: 60px; height: 60px; background-color: #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 20px; font-weight: 500;">{{ $stadium->name }}</td>
                                    <td style="padding: 15px 20px; color: #6b7280;">{{ Str::limit($stadium->address, 40) }}</td>
                                    <td style="padding: 15px 20px;">
                                        <span style="background-color: #f0f9ff; color: #0369a1; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">
                                            {{ $stadium->courts_count }}
                                        </span>
                                    </td>
                                    <td style="padding: 15px 20px;">
                                        @if($stadium->status === 'active')
                                            <span style="background-color: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Hoạt động</span>
                                        @else
                                            <span style="background-color: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Không hoạt động</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 20px; text-align: center;">
                                        <a href="{{ route('homeyard.stadiums.edit', $stadium) }}" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-right: 5px; cursor: pointer;">
                                            <i class="fas fa-edit">Sửa</i>
                                        </a>
                                        <form method="POST" action="{{ route('homeyard.stadiums.destroy', $stadium) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; cursor: pointer;" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                <i class="fas fa-trash">Xóa</i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div style="padding: 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: center;">
                    {{ $stadiums->links() }}
                </div>
            @else
                <div style="padding: 60px 20px; text-align: center;">
                    <i class="fas fa-building" style="font-size: 3rem; color: #d1d5db; margin-bottom: 20px;"></i>
                    <h4 style="color: #9ca3af; margin: 20px 0;">Chưa có sân nào</h4>
                    <p style="color: #9ca3af;">Hãy <a href="{{ route('homeyard.stadiums.create') }}" style="color: #f59e0b; text-decoration: none; font-weight: 600;">thêm sân mới</a> để bắt đầu</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
