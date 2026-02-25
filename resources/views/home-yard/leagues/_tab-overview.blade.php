@php
    $config = $league->config ?? [];
    $totalMatches = $league->rounds->flatMap->matches->count();
    $completedMatches = $league->rounds->flatMap->matches->where('status', 'completed')->count();
    $activeTeamsCount = $league->teams->where('status', 'active')->count();
@endphp

<!-- Quick Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 25px;">
    <div style="background: #f0fdf4; border-radius: 10px; padding: 15px; text-align: center;">
        <div style="font-size: 1.5rem; font-weight: 700; color: #15803d;">{{ $league->teams->count() }}</div>
        <div style="color: #6b7280; font-size: 0.85rem;">Số Đội</div>
    </div>
    <div style="background: #eff6ff; border-radius: 10px; padding: 15px; text-align: center;">
        <div style="font-size: 1.5rem; font-weight: 700; color: #1e40af;">{{ $totalMatches }}</div>
        <div style="color: #6b7280; font-size: 0.85rem;">Tổng Trận</div>
    </div>
    <div style="background: #fef3c7; border-radius: 10px; padding: 15px; text-align: center;">
        <div style="font-size: 1.5rem; font-weight: 700; color: #92400e;">{{ $completedMatches }}</div>
        <div style="color: #6b7280; font-size: 0.85rem;">Đã Hoàn Thành</div>
    </div>
    <div style="background: #f3e8ff; border-radius: 10px; padding: 15px; text-align: center;">
        <div style="font-size: 1.5rem; font-weight: 700; color: #7c3aed;">{{ $league->rounds->count() }}</div>
        <div style="color: #6b7280; font-size: 0.85rem;">Số Vòng</div>
    </div>
</div>

<!-- League Details -->
@if($league->description)
    <div style="margin-bottom: 20px;">
        <h4 style="color: #1e293b; margin-bottom: 8px;">Mô Tả</h4>
        <p style="color: #6b7280; white-space: pre-wrap;">{{ $league->description }}</p>
    </div>
@endif

<!-- Config Summary -->
<div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; margin-bottom: 25px; background: #f8fafc;">
    <h4 style="color: #1e293b; margin: 0 0 12px 0;"><i class="fas fa-cog"></i> Cấu Hình</h4>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
        <div><strong style="color: #475569;">Số đội tối đa:</strong> <span style="color: #1e293b;">{{ $config['max_teams'] ?? 16 }}</span></div>
        <div><strong style="color: #475569;">VĐV/đội:</strong> <span style="color: #1e293b;">{{ $config['max_players_per_team'] ?? 10 }}</span></div>
        <div><strong style="color: #475569;">Điểm thắng:</strong> <span style="color: #1e293b;">{{ $config['points_for_win'] ?? 3 }}</span></div>
        <div><strong style="color: #475569;">Điểm thua:</strong> <span style="color: #1e293b;">{{ $config['points_for_loss'] ?? 0 }}</span></div>
        @if(!empty($config['match_format']))
            <div><strong style="color: #475569;">Nội dung:</strong> <span style="color: #1e293b;">{{ implode(', ', $config['match_format']) }}</span></div>
        @endif
    </div>
</div>

<!-- Status Actions -->
<div style="display: flex; flex-wrap: wrap; gap: 10px;">
    @if($league->status === 'draft')
        <form method="POST" action="{{ route('homeyard.leagues.status', $league) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="registration">
            <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-door-open"></i> Mở Đăng Ký
            </button>
        </form>
    @endif

    @if($league->status === 'registration')
        @if($activeTeamsCount >= 2 && $league->rounds->isEmpty())
            <form method="POST" action="{{ route('homeyard.leagues.schedule.generate', $league) }}">
                @csrf
                <button type="submit" style="background: #8b5cf6; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;" onclick="return confirm('Tạo lịch thi đấu round-robin cho {{ $activeTeamsCount }} đội?')">
                    <i class="fas fa-calendar-plus"></i> Tạo Lịch Thi Đấu
                </button>
            </form>
        @endif
        <form method="POST" action="{{ route('homeyard.leagues.status', $league) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="active">
            <button type="submit" style="background: #15803d; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-play"></i> Bắt Đầu League
            </button>
        </form>
    @endif

    @if($league->status === 'active')
        <form method="POST" action="{{ route('homeyard.leagues.status', $league) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="completed">
            <button type="submit" style="background: #7c3aed; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;" onclick="return confirm('Kết thúc league?')">
                <i class="fas fa-flag-checkered"></i> Kết Thúc League
            </button>
        </form>
    @endif

    @if(in_array($league->status, ['draft', 'registration', 'active']))
        <form method="POST" action="{{ route('homeyard.leagues.status', $league) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;" onclick="return confirm('Hủy league? Hành động này không thể hoàn tác.')">
                <i class="fas fa-ban"></i> Hủy League
            </button>
        </form>
    @endif
</div>
