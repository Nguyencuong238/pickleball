@extends('layouts.front')

@section('content')
<style>
    @media (min-width: 768px) {
        .page-header {
            margin-top: 80px;
        }
    }
</style>
<div class="page-header" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); padding: 80px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <a href="{{ route('homeyard.tournaments.index') }}" style="color: rgba(255, 255, 255, 0.9); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Quay Lại
        </a>
        <h1 style="color: white; font-size: clamp(1.75rem, 5vw, 2.5rem); font-weight: 700; margin: 0; line-height: 1.2; word-break: break-word;">{{ $tournament->name }}</h1>
    </div>
</div>

<div style="background: #f9fafb; padding: 50px 20px; min-height: 60vh;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        @if(session('success'))
            <div style="background: #dcfce7; color: #15803d; padding: 15px 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #15803d;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Tournament Info -->
        <div style="background: white; border-radius: 15px; padding: clamp(20px, 5vw, 30px); box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                <!-- Image -->
                <div>
                    @if($tournament->image)
                        <img src="{{ asset('storage/' . $tournament->image) }}" alt="{{ $tournament->name }}" style="width: 100%; border-radius: 8px;">
                    @else
                        <div style="width: 100%; padding: 100px 20px; background-color: #fce7f3; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-trophy" style="font-size: 3rem; color: #ec4899;"></i>
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div>
                    <h3 style="color: #1e293b; margin-bottom: 20px; font-size: 1.5rem;">Thông Tin Giải Đấu</h3>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #475569;">Ngày Bắt Đầu:</strong> {{ $tournament->start_date->format('d/m/Y') }}
                    </div>
                    @if($tournament->end_date)
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #475569;">Ngày Kết Thúc:</strong> {{ $tournament->end_date->format('d/m/Y') }}
                        </div>
                    @endif
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #475569;">Giá:</strong> {{ number_format($tournament->price, 0, ',', '.') }}đ
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #475569;">Số Vận Động Viên:</strong> {{ $athletes->total() }}/{{ $tournament->max_participants }}
                    </div>
                    @if($tournament->location)
                        <div style="margin-bottom: 15px;">
                            <strong style="color: #475569;">Địa Điểm:</strong> {{ $tournament->location }}
                        </div>
                    @endif
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #475569;">Trạng Thái:</strong>
                        @if($tournament->status === 'upcoming')
                            <span style="background-color: #fef3c7; color: #92400e; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem; margin-left: 5px;">Sắp Diễn Ra</span>
                        @elseif($tournament->status === 'ongoing')
                            <span style="background-color: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem; margin-left: 5px;">Đang Diễn Ra</span>
                        @elseif($tournament->status === 'completed')
                            <span style="background-color: #dbeafe; color: #0c4a6e; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem; margin-left: 5px;">Hoàn Thành</span>
                        @else
                            <span style="background-color: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem; margin-left: 5px;">Huỷ</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($tournament->description)
                <div style="margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                    <h4 style="color: #1e293b; margin-bottom: 10px;">Mô Tả</h4>
                    <p style="color: #6b7280; white-space: pre-wrap;">{{ $tournament->description }}</p>
                </div>
            @endif

            <!-- Rules -->
            @if($tournament->rules)
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <h4 style="color: #1e293b; margin-bottom: 10px;">Luật Thi Đấu</h4>
                    <p style="color: #6b7280; white-space: pre-wrap;">{{ $tournament->rules }}</p>
                </div>
            @endif

            <!-- Prizes -->
            @if($tournament->prizes)
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <h4 style="color: #1e293b; margin-bottom: 10px;">Giải Thưởng</h4>
                    <p style="color: #6b7280; white-space: pre-wrap;">{{ $tournament->prizes }}</p>
                </div>
            @endif
        </div>

        <!-- Athletes Section -->
        <div style="background: white; border-radius: 15px; padding: clamp(20px, 5vw, 30px); box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: #1e293b; margin: 0;">Danh Sách Vận Động Viên</h3>
                @if($tournament->athleteCount() < $tournament->max_participants)
                    <button onclick="openAthleteModal()" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-plus"></i> Thêm Vận Động Viên
                    </button>
                @endif
            </div>

            @if($athletes->count() > 0)
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">#</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Tên</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Email</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Điện Thoại</th>
                                <th style="padding: 15px 20px; text-align: left; font-weight: 600; color: #475569;">Trạng Thái</th>
                                <th style="padding: 15px 20px; text-align: center; font-weight: 600; color: #475569;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($athletes as $key => $athlete)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 15px 20px; color: #6b7280;">{{ $athletes->firstItem() + $key }}</td>
                                    <td style="padding: 15px 20px; font-weight: 500;">{{ $athlete->athlete_name }}</td>
                                    <td style="padding: 15px 20px; color: #6b7280;">{{ $athlete->email ?? '-' }}</td>
                                    <td style="padding: 15px 20px; color: #6b7280;">{{ $athlete->phone ?? '-' }}</td>
                                    <td style="padding: 15px 20px;">
                                        @if($athlete->status === 'registered')
                                            <span style="background-color: #fef3c7; color: #92400e; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">Đã Đăng Ký</span>
                                        @else
                                            <span style="background-color: #dcfce7; color: #15803d; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem;">{{ ucfirst($athlete->status) }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 15px 20px; text-align: center;">
                                        <form method="POST" action="{{ route('homeyard.tournaments.athletes.remove', [$tournament, $athlete]) }}" style="display: inline;">
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
                <div style="padding: 20px 0; display: flex; justify-content: center; border-top: 1px solid #e2e8f0;">
                    {{ $athletes->links() }}
                </div>
            @else
                <p style="color: #9ca3af; text-align: center; padding: 30px;">Chưa có vận động viên nào đăng ký</p>
            @endif
        </div>

        <!-- Edit Button -->
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <a href="{{ route('homeyard.tournaments.edit', $tournament) }}" style="background: #3b82f6; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">
                <i class="fas fa-edit"></i> Chỉnh Sửa
            </a>
        </div>
    </div>
</div>

<!-- Add Athlete Modal -->
<div id="athleteModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; visibility: hidden; opacity: 0; transition: opacity 0.3s ease;">
    <div style="background: white; border-radius: 15px; padding: 30px; width: 90%; max-width: 500px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: #1e293b; margin: 0;">Thêm Vận Động Viên</h3>
            <button onclick="closeAthleteModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af;">✕</button>
        </div>

        <form id="athleteForm" method="POST" action="{{ route('homeyard.tournaments.athletes.add', $tournament) }}">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Tên Vận Động Viên *</label>
                <input type="text" name="athlete_name" required
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Email</label>
                <input type="email" name="email"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Điện Thoại</label>
                <input type="tel" name="phone"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.95rem; box-sizing: border-box;">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeAthleteModal()" style="background-color: #e2e8f0; color: #1e293b; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Hủy</button>
                <button type="submit" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">Thêm</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAthleteModal() {
        const modal = document.getElementById('athleteModal');
        modal.style.visibility = 'visible';
        modal.style.opacity = '1';
        modal.style.display = 'flex';
    }

    function closeAthleteModal() {
        const modal = document.getElementById('athleteModal');
        modal.style.opacity = '0';
        setTimeout(function() {
            modal.style.visibility = 'hidden';
            modal.style.display = 'none';
        }, 300);
    }

    window.onclick = function(event) {
        const modal = document.getElementById('athleteModal');
        if (event.target === modal) {
            closeAthleteModal();
        }
    }

    document.getElementById('athleteForm').addEventListener('submit', function() {
        closeAthleteModal();
    });
</script>
@endsection
