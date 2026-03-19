{{-- Tournament Overview Content --}}
@php
    $athleteCount  = $tournament->athletes()->count();
    $approvedCount = $tournament->athletes()->where('status', 'approved')->count();
    $matchTotal    = $tournament->matches()->count();
    $matchDone     = $tournament->matches()->whereNotNull('winner_id')->count();
    $catCount      = $tournament->categories()->count();
    $drawDone      = $tournament->tournament_stage !== 'registration';

    $checklist = [
        ['label' => 'Giải đấu đã tạo',       'done' => true],
        ['label' => "Hạng mục ({$catCount})", 'done' => $catCount > 0],
        ['label' => "VĐV đăng ký ({$athleteCount})", 'done' => $athleteCount > 0],
        ['label' => 'Bốc thăm hoàn tất',      'done' => $drawDone],
        ['label' => "Trận đấu ({$matchTotal})", 'done' => $matchTotal > 0],
        ['label' => 'Kết quả hoàn tất',       'done' => $matchTotal > 0 && $matchDone === $matchTotal],
    ];
@endphp

{{-- Stats row --}}
<div class="td-stats-grid">
    <div class="td-stat-card">
        <div class="td-stat-value">{{ $athleteCount }}</div>
        <div class="td-stat-label">Vận động viên</div>
    </div>
    <div class="td-stat-card">
        <div class="td-stat-value">{{ $approvedCount }}</div>
        <div class="td-stat-label">Đã duyệt</div>
    </div>
    <div class="td-stat-card">
        <div class="td-stat-value">{{ $matchDone }}/{{ $matchTotal }}</div>
        <div class="td-stat-label">Trận đã xong</div>
    </div>
    <div class="td-stat-card">
        <div class="td-stat-value">{{ $catCount }}</div>
        <div class="td-stat-label">Hạng mục</div>
    </div>
</div>

{{-- Tournament info card --}}
<div class="td-card">
    <div class="td-card-title">Thông tin giải đấu</div>

    <div class="td-info-row">
        <span class="td-info-label">Ngày bắt đầu</span>
        <span class="td-info-value">{{ $tournament->start_date->format('d/m/Y') }}</span>
    </div>
    @if($tournament->end_date)
    <div class="td-info-row">
        <span class="td-info-label">Ngày kết thúc</span>
        <span class="td-info-value">{{ $tournament->end_date->format('d/m/Y') }}</span>
    </div>
    @endif
    @if($tournament->registration_deadline)
    <div class="td-info-row">
        <span class="td-info-label">Hạn đăng ký</span>
        <span class="td-info-value">{{ $tournament->registration_deadline->format('d/m/Y H:i') }}</span>
    </div>
    @endif
    @if($tournament->location)
    <div class="td-info-row">
        <span class="td-info-label">Địa điểm</span>
        <span class="td-info-value">{{ $tournament->location }}</span>
    </div>
    @endif
    @if($tournament->competition_format)
    <div class="td-info-row">
        <span class="td-info-label">Hình thức</span>
        <span class="td-info-value">{{ ucfirst($tournament->competition_format) }}</span>
    </div>
    @endif
    <div class="td-info-row">
        <span class="td-info-label">Lệ phí</span>
        <span class="td-info-value">{{ number_format($tournament->price, 0, ',', '.') }}đ</span>
    </div>
    <div class="td-info-row" style="border-bottom: none;">
        <span class="td-info-label">Trạng thái</span>
        <span class="td-info-value">
            <span class="td-status {{ $tournament->status ? 'td-status-active' : 'td-status-inactive' }}">
                {{ $tournament->status ? 'Hoạt động' : 'Không hoạt động' }}
            </span>
        </span>
    </div>
</div>

{{-- Progress checklist --}}
<div class="td-card">
    <div class="td-card-title">Tiến độ thiết lập</div>
    <ul class="td-checklist">
        @foreach($checklist as $item)
        <li>
            <span class="td-check-icon {{ $item['done'] ? 'td-check-done' : 'td-check-pending' }}">
                {{ $item['done'] ? '✓' : '○' }}
            </span>
            <span style="{{ $item['done'] ? '' : 'color:#94a3b8;' }}">{{ $item['label'] }}</span>
        </li>
        @endforeach
    </ul>
</div>

{{-- Quick actions --}}
<div class="td-card">
    <div class="td-card-title">Thao tác nhanh</div>
    <div class="td-actions">
        <a href="{{ route('tournament-manage.athletes.index', $tournament) }}" class="td-btn td-btn-primary">
            Quản lý VĐV
        </a>
        @if($athleteCount > 0)
        <a href="{{ route('tournament-manage.draw.index', $tournament) }}" class="td-btn td-btn-outline">
            Bốc thăm
        </a>
        @endif
        @if($matchTotal > 0)
        <a href="{{ route('tournament-manage.matches.index', $tournament) }}" class="td-btn td-btn-ghost">
            Xem trận đấu
        </a>
        @endif
        <a href="{{ route('tournament-manage.tournaments.edit', $tournament) }}" class="td-btn td-btn-ghost">
            Chỉnh sửa
        </a>
    </div>
</div>
