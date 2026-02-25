@extends('layouts.front')

@section('content')
<style>
    @media (min-width: 768px) {
        .page-header { margin-top: 80px; }
    }
    .league-tab { padding: 12px 24px; border: none; background: none; color: #6b7280; font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent; font-size: 0.95rem; transition: all 0.2s; }
    .league-tab:hover { color: #1e293b; }
    .league-tab.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }
    .league-tab-content { display: none; }
    .league-tab-content.active { display: block; }
</style>

<div class="page-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 80px 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <a href="{{ route('homeyard.leagues.index') }}" style="color: rgba(255, 255, 255, 0.9); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Quay Lại
        </a>
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="color: white; font-size: clamp(1.75rem, 5vw, 2.5rem); font-weight: 700; margin: 0; line-height: 1.2; word-break: break-word;">{{ $league->name }}</h1>
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px; flex-wrap: wrap;">
                    @if($league->season_name)
                        <span style="background-color: rgba(255,255,255,0.2); color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">{{ $league->season_name }}</span>
                    @endif
                    @switch($league->status)
                        @case('draft')
                            <span style="background-color: rgba(255,255,255,0.2); color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Nháp</span>
                            @break
                        @case('registration')
                            <span style="background-color: rgba(255,255,255,0.2); color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Đăng Ký</span>
                            @break
                        @case('active')
                            <span style="background-color: rgba(255,255,255,0.2); color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Đang Diễn Ra</span>
                            @break
                        @case('completed')
                            <span style="background-color: rgba(255,255,255,0.2); color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Hoàn Thành</span>
                            @break
                        @case('cancelled')
                            <span style="background-color: rgba(255,255,255,0.2); color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">Đã Hủy</span>
                            @break
                        @default
                            <span style="background-color: rgba(255,255,255,0.2); color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem;">{{ ucfirst($league->status) }}</span>
                    @endswitch
                    @if($league->start_date && $league->end_date)
                        <span style="color: rgba(255,255,255,0.8); font-size: 0.85rem;">{{ $league->start_date->format('d/m/Y') }} - {{ $league->end_date->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('homeyard.leagues.edit', $league) }}" style="background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-edit"></i> Chỉnh Sửa
            </a>
        </div>
    </div>
</div>

<div style="background: #f9fafb; padding: 50px 20px; min-height: 60vh;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">

        <!-- Tab Navigation -->
        <div style="background: white; border-radius: 15px 15px 0 0; box-shadow: 0 2px 10px rgba(0,0,0,0.06); display: flex; overflow-x: auto; border-bottom: 2px solid #e2e8f0;">
            <button class="league-tab active" onclick="switchTab('overview', this)"><i class="fas fa-info-circle"></i> Tổng Quan</button>
            <button class="league-tab" onclick="switchTab('teams', this)"><i class="fas fa-users"></i> Đội ({{ $league->teams->count() }})</button>
            <button class="league-tab" onclick="switchTab('schedule', this)"><i class="fas fa-calendar-alt"></i> Lịch Thi Đấu</button>
            <button class="league-tab" onclick="switchTab('standings', this)"><i class="fas fa-list-ol"></i> Bảng Xếp Hạng</button>
        </div>

        <!-- Tab Contents -->
        <div style="background: white; border-radius: 0 0 15px 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: clamp(20px, 5vw, 30px);">

            <!-- Overview Tab -->
            <div id="tab-overview" class="league-tab-content active">
                @include('home-yard.leagues._tab-overview')
            </div>

            <!-- Teams Tab -->
            <div id="tab-teams" class="league-tab-content">
                @include('home-yard.leagues._tab-teams')
            </div>

            <!-- Schedule Tab -->
            <div id="tab-schedule" class="league-tab-content">
                @include('home-yard.leagues._tab-matches')
            </div>

            <!-- Standings Tab -->
            <div id="tab-standings" class="league-tab-content">
                @include('home-yard.leagues._tab-standings')
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName, btn) {
    document.querySelectorAll('.league-tab').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.league-tab-content').forEach(function(content) { content.classList.remove('active'); });

    document.getElementById('tab-' + tabName).classList.add('active');
    btn.classList.add('active');

    // Update URL hash
    window.location.hash = tabName;
}

// Restore tab from URL hash on page load
document.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById('tab-' + hash)) {
        var tabs = document.querySelectorAll('.league-tab');
        var tabNames = ['overview', 'teams', 'schedule', 'standings'];
        var index = tabNames.indexOf(hash);
        if (index !== -1) {
            document.querySelectorAll('.league-tab').forEach(function(btn) { btn.classList.remove('active'); });
            document.querySelectorAll('.league-tab-content').forEach(function(content) { content.classList.remove('active'); });
            tabs[index].classList.add('active');
            document.getElementById('tab-' + hash).classList.add('active');
        }
    }
});
</script>
@endsection
