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
            <h1 style="color: white; font-size: clamp(1.75rem, 5vw, 2.5rem); font-weight: 700; margin: 0; line-height: 1.2;">Quản Lý Giải Đấu Của Tôi</h1>
            <p style="color: rgba(255, 255, 255, 0.95); margin-top: 12px; margin-bottom: 0; font-size: clamp(0.95rem, 2vw, 1.1rem); font-weight: 500;">Tạo và quản lý các giải đấu của bạn</p>
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
            <a href="{{ route('homeyard.dashboard') }}" style="color: #ec4899; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
            <a href="{{ route('homeyard.tournaments.create') }}" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i> Tạo Giải Đấu
            </a>
        </div>

        <!-- Table -->
        <div style="background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
            @if($tournaments->count() > 0)
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Ảnh</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Tên Giải</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Ngày Bắt Đầu</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Giá</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Vận Động Viên</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Trạng Thái</th>
                                <th style="padding: 15px 20px; text-align: center; font-weight: 600; color: #475569;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tournaments as $tournament)
                                <tr style="border-bottom: 1px solid #e2e8f0; transition: background-color 0.2s;">
                                    <td style="padding: 15px 20px;">
                                        @if($tournament->image)
                                            <img src="{{ asset('storage/' . $tournament->image) }}" alt="{{ $tournament->name }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                                        @else
                                            <div style="width: 60px; height: 60px; background-color: #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 20px; font-weight: 500;">{{ $tournament->name }}</td>
                                    <td style="padding: 15px 20px; color: #6b7280;">{{ $tournament->start_date->format('d/m/Y') }}</td>
                                    <td style="padding: 15px 20px; font-weight: 500;">{{ number_format($tournament->price, 0, ',', '.') }}đ</td>
                                    <td style="padding: 15px 20px;">
                                        <span style="background-color: #f0f9ff; color: #0369a1; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">
                                            {{ $tournament->athleteCount() }}
                                        </span>
                                    </td>
                                    <td style="padding: 15px 20px;">
                                        @if($tournament->status == '1')
                                            <span style="background-color: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Đang Hoạt Động</span>
                                        @else
                                            <span style="background-color: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Huỷ</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 20px; text-align: center;">
                                        <a href="{{ route('homeyard.tournaments.show', $tournament) }}" style="background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-right: 5px; cursor: pointer;">
                                            <i class="fas fa-eye">Chi Tiết</i>
                                        </a>
                                        <a href="{{ route('homeyard.tournaments.edit', $tournament) }}" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-right: 5px; cursor: pointer;">
                                            <i class="fas fa-edit">Sửa</i>
                                        </a>
                                        <form method="POST" action="{{ route('homeyard.tournaments.destroy', $tournament) }}" style="display: inline;">
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
                    {{ $tournaments->links() }}
                </div>
            @else
                <div style="padding: 60px 20px; text-align: center;">
                    <i class="fas fa-trophy" style="font-size: 3rem; color: #d1d5db; margin-bottom: 20px;"></i>
                    <h4 style="color: #9ca3af; margin: 20px 0;">Chưa có giải đấu nào</h4>
                    <p style="color: #9ca3af;">Hãy <a href="{{ route('homeyard.tournaments.create') }}" style="color: #ec4899; text-decoration: none; font-weight: 600;">tạo giải đấu mới</a> để bắt đầu</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
