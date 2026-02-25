@extends('layouts.front')

@section('content')
<style>
    @media (min-width: 768px) {
        .page-header { margin-top: 80px; }
    }
</style>
<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 80px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <h1 style="color: white; font-size: clamp(1.75rem, 5vw, 2.5rem); font-weight: 700; margin: 0; line-height: 1.2;">Quản Lý Giải Đấu League</h1>
        <p style="color: rgba(255, 255, 255, 0.95); margin-top: 12px; margin-bottom: 0; font-size: clamp(0.95rem, 2vw, 1.1rem); font-weight: 500;">Tạo và quản lý các giải đấu theo đội của bạn</p>
    </div>
</div>

<div style="background: #f9fafb; padding: 50px 20px; min-height: 60vh;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">

        <!-- Header with Back + Create -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 10px;">
            <a href="{{ route('homeyard.overview') }}" style="color: #ec4899; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
            <a href="{{ route('homeyard.leagues.create') }}" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-plus"></i> Tạo League Mới
            </a>
        </div>

        <!-- Stats Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: #1e293b;">{{ $stats['total'] }}</div>
                <div style="color: #6b7280; font-size: 0.9rem; margin-top: 4px;">Tổng Số League</div>
            </div>
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: #15803d;">{{ $stats['active'] }}</div>
                <div style="color: #6b7280; font-size: 0.9rem; margin-top: 4px;">Đang Diễn Ra</div>
            </div>
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: #7c3aed;">{{ $stats['completed'] }}</div>
                <div style="color: #6b7280; font-size: 0.9rem; margin-top: 4px;">Đã Hoàn Thành</div>
            </div>
        </div>

        <!-- Table -->
        <div style="background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
            @if($leagues->count() > 0)
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Tên League</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Mùa Giải</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Trạng Thái</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Số Đội</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Ngày Bắt Đầu</th>
                                <th style="padding: 15px 20px; text-align: center; font-weight: 600; color: #475569;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leagues as $league)
                                <tr style="border-bottom: 1px solid #e2e8f0; transition: background-color 0.2s;">
                                    <td style="padding: 15px 20px; font-weight: 500;">{{ $league->name }}</td>
                                    <td style="padding: 15px 20px; color: #6b7280;">{{ $league->season_name ?? '-' }}</td>
                                    <td style="padding: 15px 20px;">
                                        @switch($league->status)
                                            @case('draft')
                                                <span style="background-color: #f3f4f6; color: #4b5563; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Nháp</span>
                                                @break
                                            @case('registration')
                                                <span style="background-color: #dbeafe; color: #1e40af; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Đăng Ký</span>
                                                @break
                                            @case('active')
                                                <span style="background-color: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Đang Diễn Ra</span>
                                                @break
                                            @case('completed')
                                                <span style="background-color: #f3e8ff; color: #7c3aed; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Hoàn Thành</span>
                                                @break
                                            @case('cancelled')
                                                <span style="background-color: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Đã Hủy</span>
                                                @break
                                            @default
                                                <span style="background-color: #f3f4f6; color: #4b5563; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">{{ ucfirst($league->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td style="padding: 15px 20px;">
                                        <span style="background-color: #f0f9ff; color: #0369a1; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">
                                            {{ $league->teams_count }}
                                        </span>
                                    </td>
                                    <td style="padding: 15px 20px; color: #6b7280;">{{ $league->start_date ? $league->start_date->format('d/m/Y') : '-' }}</td>
                                    <td style="padding: 15px 20px; text-align: center;">
                                        <a href="{{ route('homeyard.leagues.show', $league) }}" style="background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-right: 5px;">
                                            <i class="fas fa-eye"></i> Chi Tiết
                                        </a>
                                        <a href="{{ route('homeyard.leagues.edit', $league) }}" style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: inline-block; margin-right: 5px;">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <form method="POST" action="{{ route('homeyard.leagues.destroy', $league) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; cursor: pointer;" onclick="return confirm('Bạn có chắc chắn muốn xóa league này?')">
                                                <i class="fas fa-trash"></i> Xóa
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
                    {{ $leagues->links() }}
                </div>
            @else
                <div style="padding: 60px 20px; text-align: center;">
                    <i class="fas fa-trophy" style="font-size: 3rem; color: #d1d5db; margin-bottom: 20px;"></i>
                    <h4 style="color: #9ca3af; margin: 20px 0;">Chưa có league nào</h4>
                    <p style="color: #9ca3af;">Hãy <a href="{{ route('homeyard.leagues.create') }}" style="color: #ec4899; text-decoration: none; font-weight: 600;">tạo league mới</a> để bắt đầu</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
