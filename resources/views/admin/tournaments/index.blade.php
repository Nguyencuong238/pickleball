@extends('layouts.app')

@section('title', 'Quản Lý Giải Đấu')

@section('content')
<div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <!-- Success Message -->
        @if(session('success'))
            <div style="background: #dcfce7; color: #15803d; padding: 15px 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #15803d;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Header with Add Button -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <a href="{{ route('admin.dashboard') }}" style="color: #8b5cf6; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
            <a href="{{ route('admin.tournaments.create') }}" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
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
                                        @if($tournament->status === 'upcoming')
                                            <span style="background-color: #fef3c7; color: #92400e; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;white-space:nowrap">Sắp Diễn Ra</span>
                                        @elseif($tournament->status === 'ongoing')
                                            <span style="background-color: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;white-space:nowrap">Đang Diễn Ra</span>
                                        @elseif($tournament->status === 'completed')
                                            <span style="background-color: #dbeafe; color: #0c4a6e; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;white-space:nowrap">Hoàn Thành</span>
                                        @else
                                            <span style="background-color: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;white-space:nowrap">Huỷ</span>
                                        @endif
                                    </td>
                                    <td class="d-flex" style="padding: 15px 20px; text-align: center;">
                                        {{-- <a href="{{ route('admin.tournaments.show', $tournament) }}" style="background: #10b981; color: white ; border: none; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-right: 5px; cursor: pointer;">
                                           Chi tiết
                                        </a> --}}
                                        <a href="{{ route('admin.tournaments.edit', $tournament) }}" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-right: 5px; cursor: pointer;">
                                            Sửa
                                        </a>
                                        <form method="POST" action="{{ route('admin.tournaments.destroy', $tournament) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; cursor: pointer;" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                Xóa
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
                    <p style="color: #9ca3af;">Hãy <a href="{{ route('admin.tournaments.create') }}" style="color: #8b5cf6; text-decoration: none; font-weight: 600;">tạo giải đấu mới</a> để bắt đầu</p>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
@endsection
